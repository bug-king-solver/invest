<?php

namespace App\Http\Controllers\Api;

use App\Misc\Logs;
use App\Models\Cancellation;
use App\Models\User;
use App\Services\Integromat\Campaigns\ChurnedUser;
use App\Services\MailChimp;
use App\Stripe\Bonuses\BonusFactory;
use Auth;
use Carbon\Carbon;
use Exception;
use Throwable;
use App\Misc\ErrorMessages;
use Illuminate\Http\Request;
use App\Stripe\PlansManager;
use App\Http\Controllers\Controller;
use App\Stripe\SubscriptionsManager;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Cashier\Payment;
use Laravel\Cashier\Subscription;
use Stripe\Error\InvalidRequest;
use Stripe\PaymentIntent as StripePaymentIntent;
use App\Http\Requests\UpdatePaymentMethodRequest;

class SubscriptionsController extends Controller
{
    protected $plans_manager;
    public function __construct(PlansManager $plans_manager)
    {
        $this->plans_manager = $plans_manager;
    }

    public function listSubscriptions(Request $request)
    {
        if ($request->has('ref_code') && User::where('ref_code', $request->get('ref_code'))->exists()) {
            cookie()->queue('ref_code', $request->get('ref_code'), 5 * 365 * 24 * 60 * 60);
        }

        return response()->json([
            'page_title' => 'Plans',
            'plans' => $this->plans_manager->getPlans(),
            'aff_suffix' => $request->get('aff')
        ]);
    }

    public function subscriptionSettingsPage(Request $request)
    {
        $view_data = $this->getSubscriptionData();
        $payment_methods = $this->getPaymentMethods();
        $invoices = auth()->user()->invoices();
        return response()->json([
            'data' => $view_data,
            'payment_methods' => $payment_methods,
            'invoices' => $invoices,
            'success' => true
        ]);
    }

    public function confirmPayment($payment_id)
    {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));
        $payment = new Payment(
            StripePaymentIntent::retrieve($payment_id)
        );
        return response()->json([
            'page_title' => 'Confirm Payment',
            'stripe_public_key' => config('cashier.key'),
            'payment' => [
                'isSucceed' => $payment->isSucceeded(),
                'isCancelled' => $payment->isCancelled(),
                'client' => $payment->clientSecret()
            ]
        ]);
    }

    /**
     * Unnecessary method
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function updateCreditCardPage(Request $request)
    {
        $user = auth()->user();
        $card_last_four = $user->card_last_four;
        $intent = $user->createSetupIntent();
        $stripe_public_key = config('cashier.key');
        try {
            $sub_status = $user->currentSubscription()->stripe_status;
        } catch (\Throwable $e) {
            $sub_status = null;
        }
        return view('subscriptions.credit_card')->with([
            'page_title' => 'Credit Card',
            'intent' => $intent,
            'card_last_four' => $card_last_four,
            'stripe_public_key' => $stripe_public_key,
            'sub_status' => $sub_status
        ]);
    }

    public function createCreditCard(Request $request)
    {
        if (!$request->has('payment_method')) {
            $response = ['success' => false];
            $response['message'] = 'No valid payment method provided.';
        }

        try {
            /** @var User $user */
            $user = auth()->user();
            $user->updateDefaultPaymentMethod($request->get('payment_method'));
            $response['data'] = $this->getSubscriptionData();
            $response['payment_methods'] = $this->getPaymentMethods();
            $response['success'] = true;
        } catch (Throwable $e) {
            $response = ['success' => false];
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function payInvoice(Request $request)
    {
        try {
            $last_invoice = auth()->user()->invoices(true)->first()->asStripeInvoice();
            if (in_array($last_invoice->status, ['draft', 'paid', 'void'])) {
                throw new Exception('Invoice cannot be paid');
            }
            $last_invoice->pay();
            if ($last_invoice->status !== 'paid') {
                throw new Exception('Invoice not paid');
            }
            $message = ['success' => 'Successfully paid invoice.'];
        } catch (\Throwable $e) {
            $message = ['fail' => 'Something went wrong. Please check your credit card data.'];
        }

        sleep(3); // give it a little time for the webhook to kick in and update the subscription status
        return redirect()->route('subscription.settings.page')->with($message);
    }

    public function cancelSubscription(Request $request, SubscriptionsManager $subs_manager)
    {
        /** @var User $user */
        $user = auth()->user();
        if ($request->has('cancel_reason')) {
            try {
                Cancellation::create([
                    'user_id' => $user->id,
                    'subscription_id' => $user->currentSubscription()->id,
                    'reason' => str_replace('#', '', $request->get('cancel_reason')),
                    'comment' => $request->get('cancel_comment')
                ]);
            } catch (Throwable $e) {
                Logs::logToDaily('subscriptions', $e);
            }
        }

        $sub_type = $request->has('addon_sub') ?
            $request->get('addon_sub') :
            false;

        try {
            $subs_manager->cancel($user, $sub_type);
        } catch (Throwable $e) {
            Log::info('cancel failed ' . $user->email . ': ' . $e->getMessage());
            return response()->json(['fail' => ErrorMessages::BASIC]);
        }

        $response = $this->getSubscriptionData();

        return response()->json([
            'data' => $response,
            'success' => 'Successfully cancelled subscription.'
        ]);
    }

    public function getCancelModal()
    {
        $monthly_newbie_sub = auth()->user()->currentSubscription()->name == 'monthly-newbie';
        $response = [
            'success' => true,
            'modal' => view('modals.cancel_subscription')
                ->with([
                    'title' => 'Cancel subscription',
                    'large_modal' => true,
                    'monthly_newbie_sub' => $monthly_newbie_sub
                ])
                ->render()
        ];
        return response()->json($response);
    }

    public function activateCancelCoupon(Request $request)
    {
        $response = ['success' => false];
        if ($request->get('cancel_coupon') == 1) {
            try {
                auth()->user()->applyCouponToSubscription('20off_ch');
                $response['success'] = true;
            } catch (\Throwable $e) {
                $response['message'] = $e->getMessage();
            }
        }

        return response()->json($response);
    }

    public function affiliateDiscount($coupon_name, Request $request)
    {
        $allowed_coupons = [
            'merchmoney'
        ];
        if (in_array($coupon_name, $allowed_coupons)) {
            try {
                auth()->user()->applyCouponToSubscription($coupon_name);
            } catch (\Throwable $e) {
            }
        }

        return redirect()->route('tutorials.index');
    }

    public function getSubscriptionData()
    {
        $request = request();
        $fbwt = $request->has('fbwt');
        $mpd = $request->has('mpd');
        /** @var User $user */
        $user = auth()->user();
        $view_data = [
            'plans' => $this->plans_manager->getPlans(),
            'page_title' => 'Subscription settings',
            'ref_code' => $user->ref_code,
            'in_bonus_period' => $user->inBonusPeriod(),
            'sub_status' => null,
            'sub_id' => null,
            'customer_stripe_id' => $user->stripe_id,
            'confirm_payment_id' => false,
            'pm_last_four' => $user->pm_last_four,
            'intent' => $user->createSetupIntent(),
            'stripe_public_key' => config('cashier.key'),
        ];

        $last_subscription = $user->currentSubscription();
        if (!is_null($last_subscription)) {
            if ($last_subscription->hasIncompletePayment()) {
                try {
                    $view_data['confirm_payment_id'] = $last_subscription->latestPayment()->id;
                } catch (Throwable $e) {
                    $view_data['confirm_payment_id'] = false;
                }
            }

            $stripe_sub = $last_subscription->asStripeSubscription();
            $subscription_name = $last_subscription->stripe_price;

            $view_data['subscription_name'] = $subscription_name;
            $coupon = null;
            if (isset($stripe_sub->discount->coupon)) {
                $coupon = $stripe_sub->discount->coupon;
            }
            $view_data['plan_price'] = $this->plans_manager->getPlanPrice($subscription_name, $coupon);

            // if on trial
            $on_trial = $user->onTrial($subscription_name);
            $view_data['on_trial'] = $on_trial;
            $view_data['trial_ends_at'] = $on_trial ? $last_subscription->trial_ends_at->format('F jS Y') : null;

            // if on grace period
            $on_grace_period = $last_subscription->onGracePeriod();
            $view_data['on_grace_period'] = $on_grace_period;
            $view_data['grace_period_ends_at'] = $on_grace_period ? $last_subscription->ends_at->format('F jS Y') : null;

            // if sub ended
            $view_data['sub_ended'] = $last_subscription->ended();

            $view_data['user_subscribed'] = $user->subscribed($subscription_name);
            $view_data['show_sub_form'] = true;
            $view_data['sub_status'] = $last_subscription->stripe_status;

            try {
                if (!$view_data['user_subscribed'] || $on_grace_period) {
                    throw new Exception('only for subscribed users');
                }
                $next_bill = Carbon::createFromTimestamp($stripe_sub->current_period_end);
                if (Carbon::now()->lt($next_bill)) {
                    $view_data['next_bill'] = $next_bill->format('F jS Y');
                } else {
                    throw new Exception('next bill is before now');
                }
            } catch (Throwable $e) {
                $stripe_sub = null;
                $view_data['next_bill'] = null;
            }
        } else {
            // no subscription record
            // probably stopped during checkout
            // or some error occurred
            $subscription_name = $view_data['subscription_name'] = null;
            $view_data['plan_price'] = null;
            $on_trial = $on_grace_period = $view_data['on_grace_period'] = $view_data['on_trial'] = false;
            $view_data['sub_ended'] = true;
            $view_data['user_subscribed'] = false;
            $view_data['sub_status'] = null;
            $view_data['next_bill'] = null;
        }

        $view_data['new_sub_with_trial'] = false;

        if ($fbwt && $view_data['sub_ended']) {
            $view_data['new_sub_with_trial'] = true;
            $view_data['plans'] = ['monthly-newbie' => 'Monthly Newbie - $9.99'];
            $view_data['fb_pixel'] = true;
        }

        if ($view_data['sub_status'] == 'canceled' && $mpd) {
            $view_data['plans'] = $this->plans_manager->getPromo(1);
            $view_data['mpd'] = true;
        }

        return $view_data;
    }

    public function updateSubscription(Request $request, SubscriptionsManager $subs_manager)
    {
        if (!$request->has('plan') || !$this->plans_manager->planExists($request->input('plan'))) {
            return response()->json([
                'success' => false,
                'message' => ErrorMessages::BASIC
            ]);
        }

        /** @var User $user */
        $user = auth()->user();
        $current_subscription = $user->currentSubscription();

        try {
            if ($request->get('plan') == 'lifetime') {
                $current_subscription->cancelNow();
                $subs_manager->newSubscription($user, 'lifetime', 'lifetime', null, null);
            } else {
                $coupon = $request->get('coupon');
                if ($request->has('cphid')) {
                    $coupon = $this->plans_manager->getCoupon($request->get('cphid'));
                }
                $end_trial = $request->get('end_trial');
                if ($current_subscription->trial_ends_at) {
                    $end_trial = 3 < $current_subscription->trial_ends_at->diffInDays(Carbon::now());
                }
                $subs_manager->userSwapPlan($user, $request->input('plan'), $coupon, $end_trial);
            }
            $response['data'] = $this->getSubscriptionData();
            $response['payment_methods'] = $this->getSubscriptionData();
            $response['success'] = true;
            if ($request->has('fb_landed')) {
                $redirect_with['fb_track_converted'] = true;
            }
        } catch (InvalidRequest $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        } catch (Throwable $e) {
            $response['success'] = false;
            $response['message'] = ErrorMessages::PAYMENTS_ERROR;
        }

        if ($request->get('plan') == 'y-pro-spec' && $user->canGetBonus('january_training')) {
            $mc = app(MailChimp::class);
            $mc->triggerJanuaryPromoTrainingEvent($user);
            $user->appliedBonuses()->create(['bonus_name' => 'january_training']);
        }

        return response()->json($response);
    }

    public function resumeSubscription(Request $request)
    {
        try {
            $sub_type = $request->has('addon_sub') ?
                $request->get('addon_sub') :
                false;

            auth()->user()->currentSubscription($sub_type)->resume();
            $response['success'] = true;
            $response['message'] = 'Successfully resumed subscription!';
        } catch (\Throwable $e) {
            $response['success'] = false;
            $response['message'] = ErrorMessages::BASIC;
        }
        $response['data'] = $this->getSubscriptionData();
        return response()->json($response);
    }

    public function newSubscription(Request $request, SubscriptionsManager $subs_manager)
    {
        if (!$request->has('plan') || !$this->plans_manager->planExists($request->get('plan'))) {
            return response()->json(['success' => false, 'message' => 'Plan doesn\'t exist.']);
        }

        /** @var User $user */
        $user = auth()->user();
        $subscription = $plan_name = $request->get('plan');
        $coupon = $request->has('coupon') && $request->input('coupon') != '' ? $request->input('coupon') : null;
        $trial_days = null;
        $new_sub_success = false;

        try {
            $subs_manager->newSubscription($user, $subscription, $plan_name, $trial_days, $coupon);
            $response['data'] = $this->getSubscriptionData();
            $response['success'] = 'Successfully created new subscription!';
            $new_sub_success = true;
        } catch (IncompletePayment $e) {
            $response['success'] = false;
        } catch (\Throwable $e) {
            $response['fail'] = $e->getMessage();
        }

        if ($new_sub_success && $request->get('mpd') == 1 && $plan_name == 'mon-pro-spec') {
            try {
                $user->applyCouponToSubscription('monprofree');
            } catch (\Throwable $e) {
                Logs::logToDaily('subscriptions', $e);
            }
        }

        return response()->json($response);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activateReferrerCode()
    {
        auth()->user()->activateReferrerCode();
        $response = $this->getSubscriptionData();

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function apiActiveSubscription(Request $request)
    {
        $response = [
            'success' => false,
            'active_sub' => false,
            'email' => false
        ];

        try {
            /** @var User $user */
            $user = auth()->guard('api')->user();
            $response['email'] = $user->email;
            $response['user_id'] = $user->id;
            $response['stripe_id'] = $user->stripe_id;

            if (!$user->isAdmin()) {
                $has_active_sub = $user->hasActiveSubscription();
                $response['active_sub'] = $has_active_sub;
                if ($has_active_sub) {
                    $response['allowed_to_use'] = !Str::contains($user->currentSubscription()->stripe_price, ['newbie']);
                } else {
                    $response['allowed_to_use'] = false;
                }
            } else {
                $response['active_sub'] = true;
                $response['allowed_to_use'] = true;
            }

            if ($response['active_sub']) {
                $response = array_merge($response, $user->getLegendExtensionLimits());
            }

            $response['success'] = true;
        } catch (\Throwable $e) {
            $response['active_sub'] = false;
        }

        return response()->json($response);
    }

    public function applyBonus(Request $request)
    {
        $response = ['success' => false];

        $bonus_name = $request->get('bonus_name');

        if (is_null($bonus_data = session()->get('pending_bonus_' . $bonus_name))) {
            $response['message'] = 'No cheating!';
            return response()->json($response);
        }

        try {
            $bonus = BonusFactory::create($bonus_name);
            $bonus->setUser(auth()->user());
            $bonus_response = $bonus->apply($bonus_data);
            $response['bonus_response'] = $bonus_response;
            $response['message'] = 'Successfully applied bonus!';
            $response['success'] = true;
        } catch (\Throwable $e) {
            $response['message'] = ErrorMessages::BASIC;
            Logs::logToDaily('subscriptions', $e);
        }

        return response()->json($response);
    }

    public function hideBonus(Request $request)
    {
        $user = auth()->user();
        $bonus_type = $request->get('bonus_type');
        $user->offeredBonuses()
            ->where('bonus_type', $bonus_type)
            ->update([
                'show' => false
            ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function getPaymentMethods()
    {
        $user = Auth::user();

        if (!$user->hasPaymentMethod()) {
            return false;
        } else {
            $paymentMethods = $user->paymentMethods()->map(function ($paymentMethod) {
                return $paymentMethod->asStripePaymentMethod()->toArray();
            })->all();

            if ($user->hasDefaultPaymentMethod()) {
                $defaultPaymentMethod = $user->defaultPaymentMethod()->asStripePaymentMethod()->toArray();

                foreach ($paymentMethods as &$paymentMethod) {
                    $paymentMethod['default'] = $paymentMethod['id'] === $defaultPaymentMethod['id'];
                }
            }

            return $paymentMethods;
        }
    }
    public function addPaymentMethod(AddPaymentMethodRequest $request)
    {
        try {
            $user = auth()->user();
            $payment_method = $user->addPaymentMethod($request->payment_method_id);

            $user->updateDefaultPaymentMethod($request->payment_method_id);

            return response()
                ->json([
                    'success' => true,
                    'payment_method' => $payment_method->asStripePaymentMethod()->toArray()
                ]);
        } catch (\Throwable $exception) {
            Logs::logException('subscriptions', $exception);

            abort(400, 'Cannot update payment method');
        }

        return response()
            ->json([
                'success' => false
            ]);
    }

    public function deletePaymentMethod($paymentMethodId)
    {
        try {
            $user = auth()->user();
            $paymentMethods = $user->paymentMethods();

            $deletedPaymentMethod = null;

            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod->id === $paymentMethodId) {
                    $deletedPaymentMethod = $paymentMethod->asStripePaymentMethod()->toArray();

                    $paymentMethod->delete();
                }
            }

            $paymentMethods = $user->paymentMethods();
            if ($paymentMethods->isNotEmpty()) {
                $user->updateDefaultPaymentMethod($paymentMethods->first()->id);
            }

            return response()
                ->json([
                    'success' => true,
                    'payment_methods' => $this->getPaymentMethods(),
                ]);
        } catch (\Throwable $exception) {
            Logs::logException('subscriptions', $exception);

            abort(400, 'Cannot update payment method');
        }

        return response()
            ->json([
                'success' => false
            ]);
    }

    public function updatePaymentMethod(UpdatePaymentMethodRequest $request)
    {
        try {
            $user = auth()->user();
            $payment_method = $user->updateDefaultPaymentMethod($request->payment_method_id);

            $updated_payment_method = is_null($payment_method)
                ? $user->defaultPaymentMethod()
                : $payment_method;

            return response()
                ->json([
                    'success' => true,
                    'payment_methods' => $this->getPaymentMethods(),
                ]);
        } catch (\Throwable $exception) {
            Logs::logException('subscriptions', $exception);

            abort(400, 'Cannot update payment method');
        }

        return response()
            ->json([
                'success' => false
            ]);
    }
}
