<?php

namespace App\Http\Controllers\Api;

use Session;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Misc\ErrorMessages;
use App\Misc\Logs;
use App\Stripe\PlansManager;
use App\Stripe\SubscriptionsManager;
use App\Events\UserRegistered;
use App\Rules\AuthenticEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials, true)) {
            Auth::logoutOtherDevices($request->password);
            $response = [
                'success' => true,
                'message' => 'User login successfully',
                'token' => auth()->user()->createToken('tokens')->plainTextToken,
                'user' => auth()->user(),
                'active_sub_user' => auth()->user()->hasActiveSubscription()
            ];
            return response()->json($response);
        }

        $response = [
            'success' => false,
            'message' => 'Unauthorised',
        ];
        return response()->json($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Get current user
     */
    public function current(Request $request)
    {
        $response = [
            'success' => true,
            'token' => auth()->user()->createToken('tokens')->plainTextToken,
            'user' => auth()->user(),
            'active_sub_user' => auth()->user()->hasActiveSubscription()
        ];
        return response()->json($response);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        try {
            Session::flush();
            $success = true;
            $message = 'Successfully logged out';
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
            $message = $ex->getMessage();
        }

        // response
        $response = [
            'success' => $success,
            'message' => $message,
        ];
        return response()->json($response);
    }

    /**
     * Send password reset link.
     */
    public function sendPasswordResetLink(Request $request)
    {
        $this->sendResetLinkEmail($request);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json([
            'success' => true,
            'message' => 'Password reset email sent.',
            'data' => $response
        ]);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json([
            'success' => false,
            'message' => 'Email could not be sent to this email address.'
        ]);
    }

    /**
     * Handle reset password
     */
    public function callResetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => ErrorMessages::BASIC,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password was reset successfully',
        ]);
    }

    /**
     * Returns the payment methods the user has saved
     *
     * @param Request $request The request data from the user.
     */
    public function getPaymentMethods(Request $request)
    {
        $user = $request->user();

        $methods = array();

        if ($user->hasPaymentMethod()) {
            foreach ($user->paymentMethods() as $method) {
                array_push($methods, [
                    'id' => $method->id,
                    'brand' => $method->card->brand,
                    'last_four' => $method->card->last4,
                    'exp_month' => $method->card->exp_month,
                    'exp_year' => $method->card->exp_year,
                ]);
            }
        }

        return response()->json($methods);
    }

    /**
     * Creates an intent for payment so we can capture the payment
     * method for the user.
     *
     * @param Request $request The request data from the user.
     */
    public function getSetupIntent(User $uesr)
    {
        return $uesr->createSetupIntent();
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->state = $data['state'];
        $user->city = $data['city'];
        $user->address = $data['address'];
        $user->agree_terms = true;
        $user->type = 'merchant';
        $user->save();
        return $user;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', app(AuthenticEmail::class)],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    protected function guard()
    {
        return Auth::guard();
    }

    public function register(Request $request, PlansManager $plans_manager, SubscriptionsManager $subs_manager)
    {
        if (!$plans_manager->planExists($request->get('plan', '')) || !$request->has('payment_method')) {
            return response()->json(['message' => ErrorMessages::BASIC], 422);
        }

        $this->validator($request->all())->validate();
        $user = $this->create($request->all());
        $subscription = $plan_name = $plans_manager->getIdByStripePrice($request->get('plan'));
        $payment_method = $request->get('payment_method');
        $trial_days = 3;
        $coupon = $request->get('coupon');
        $ip = $request->get('idev_custom');

        try {
            $subs_manager->newSubscription($user, $subscription, $plan_name, $trial_days, $coupon, $ip, $payment_method);
            event(new UserRegistered($user));
            $this->guard()->login($user);
        } catch (Throwable $e) {
            Logs::logMessageToDaily('subscriptions', join(' | ', [$user->email, $e->getMessage()]));
            if (!is_null($user->stripe_id)) {
                $user->asStripeCustomer()->delete();
            }
            $user->delete();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'User Register successfully',
            'token' => auth()->user()->createToken('tokens')->plainTextToken,
            'user' => auth()->user(),
            'active_sub_user' => auth()->user()->hasActiveSubscription()
        ]);
    }
}
