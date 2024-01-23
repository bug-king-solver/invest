<?php

namespace App\Stripe;

use App\Misc\Logs;
use Auth;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Cashier\Exceptions\SubscriptionUpdateFailure;
use Laravel\Cashier\Subscription;

class SubscriptionsManager
{
    const GRAPHICS_SUB_INDEX = 1;

    const GRAPHICS_SUB_NAME = 'graphics-mon';

    const ADDON_SUBS = [
        1 => 'graphics'
    ];

    /**
     * @param User $user
     * @param $subscription
     * @param $plan
     * @param null $trial_days
     * @param null $coupon
     * @param null $ip
     * @param null $payment_method
     * @throws \Laravel\Cashier\Exceptions\PaymentActionRequired
     * @throws \Laravel\Cashier\Exceptions\PaymentFailure
     */
    public function newSubscription(User $user, $subscription, $plan, $trial_days = null, $coupon = null, $ip = null, $payment_method = null)
    {
        $sub_builder = $user->newSubscription($plan, $plan);

        if (!is_null($trial_days)) {
            $sub_builder->trialDays($trial_days);
        } else {
            $sub_builder->skipTrial();
        }

        if (!is_null($coupon)) {
            $sub_builder->withCoupon($coupon);
        }

        $options = [];
        if (!is_null($ip)) {
            $options['metadata'] = $ip;
        }
        $sub_builder->create($payment_method, $options);
    }

    /**
     * Cancel subscription for a given user
     * @param User $user
     * @param $sub_type
     * @return void
     */
    public function cancel(User $user, $sub_type)
    {
        try {
            $cashier_sub = $user->currentSubscription($sub_type);
            if ($cashier_sub->onTrial()) {
                $cashier_sub->trial_ends_at = Carbon::now()->subMinute()->toDateTimeString();
                $cashier_sub->save();
                $cashier_sub->cancelNow();
            } else {
                $cashier_sub->cancel();
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function removeDiscounts(Subscription $cashier_sub)
    {
        try {
            $stripe_sub = $cashier_sub->asStripeSubscription();
            if ($stripe_sub->discount) {
                $stripe_sub->deleteDiscount();
            }
        } catch (\Throwable $e) {
            Logs::logToDaily('subscriptions', $e);
        }
    }

    /**
     * Swap subscription for a user
     * @param User $user
     * @param string $plan
     * @param null $coupon
     * @param null $end_trial
     * @param bool $prorate
     * @return void
     * @throws SubscriptionUpdateFailure
     * @throws \Laravel\Cashier\Exceptions\IncompletePayment
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function userSwapPlan(User $user, $plan, $coupon = null, $end_trial = null, $prorate = true)
    {
        $cashier_sub = $user->currentSubscription();

        $this->removeDiscounts($cashier_sub);

        if ($coupon) {
            $stripe_sub = $cashier_sub->asStripeSubscription();
            $stripe_sub->coupon = $coupon;
            $stripe_sub->save();
        }

        if (false === $prorate) {
            $cashier_sub->noProrate();
        }
        $subscription = $cashier_sub->swap($plan);

        // we save the name as the stripe plan because that's what we use for authentication
        $subscription->name = $plan;
        $subscription->save();

        if ($end_trial && $cashier_sub->onTrial()) {
            $stripe_sub = $cashier_sub->asStripeSubscription();
            $stripe_sub->trial_end = 'now';
            $stripe_sub->save();
            $cashier_sub->trial_ends_at = Carbon::now()->subMinute();
            $cashier_sub->save();
        }

        if (!$subscription->onTrial()) {
            $subscription->invoice();
        }

        return true;
    }

    public function resolveAffiliateTrials($aff)
    {
        $allowed_affiliates = [
            'pm' => 7
        ];

        if (!is_null($aff) && array_key_exists($aff, $allowed_affiliates)) {
            return $allowed_affiliates[$aff];
        }

        return 3;
    }

    public function planAllowsCoupon($plan, $coupon)
    {
        switch ($coupon) {
            case 'MIYEARLY':
            case 'OWLPRO':
            case 'AmazonbeclicAnnu':
                $allowed = ($plan == 'yearly-pro');
                break;
            default:
                $allowed = true;
        }

        return $allowed;
    }

    /**
     * @param Subscription $sub
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function resetBillingAnchor(Subscription $sub)
    {
        $sub->updateStripeSubscription([
            'billing_cycle_anchor' => 'now',
            'proration_behavior' => 'always_invoice'
        ]);
    }
}
