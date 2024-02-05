<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Requests\UsersRequest;
use App\Http\Requests\PasswordsRequest;
use App\Services\MailChimp;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
    //
    public function updateProfile(UsersRequest $request)
    {
        $user = auth()->user();
        $should_update_apis = $user->email !== $request->get('email');
        $old_email = $user->email;
        // dd($request->all());
        $user->update($request->all());
        if ($should_update_apis) {
            /** @var MailChimp $mc */
            $mc = app(MailChimp::class);
            $mc->syncUserWithMasterList($user, $old_email);

            $user->updateStripeCustomer(['email' => $user->email]);
        }

        return response()->json(['success' => true]);
    }

    public function checkPassword(Request $request)
    {
        $user = auth()->user();
        if (Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['success' => true]);
        } else {
            return response()->json([
                'success' => false,
                'message' => "current_password_not_match"
            ], 422);
        }
    }

    public function updatePassword(PasswordsRequest $request)
    {
        $user = auth()->user();
        if (Hash::check($request->input('current_password'), $user->password)) {
            auth()->user()->update([
                'password' => bcrypt($request->input('password'))
            ]);

            return response()->json(['success' => true]);
        } else {
            return response()->json([
                'success' => false,
                'message' => "current_password_not_match"
            ], 422);
        }
    }

    public function userDetails(Request $request)
    {
        $response = ['success' => false];

        try {
            $response['user'] = User::where('email', $request->get('email'))->firstOrFail([
                'id', 'email', 'created_at', 'api_token'
            ]);
            $response['success'] = true;
        } catch (\Throwable $e) {
            $response['message'] = 'No such user';
            Logs::logToDaily('user-details', $e);
        }

        return response()->json($response);
    }

    public function apiCheckForBonus(Request $request)
    {
        $response = ['success' => false];
        if (!$request->has('user_id')) {
            $response['message'] = 'Please provide user id for this operation';
            return response()->json($response);
        }

        $user_id = $request->get('user_id');
        $user = User::find($user_id);
        if (!$user) {
            $response['message'] = 'No such user';
            return response()->json($response);
        }

        $bonus = false;
        $scales = [
            'monthly_to_lifetime' => 'monthly_to_lifetime',
            'graphics' => 'graphics',
        ];
        foreach ($scales as $bonus_name => $bonus_type) {
            $bonus = $this->checkForBonus($user, $bonus_name, $bonus_type);
            if ($bonus) break;
        }
        $response['bonus'] = $bonus;
        $response['success'] = true;

        return response()->json($response);
    }

    public function apiHideBonus(Request $request)
    {
        $response = ['success' => false];

        try {
            $user = User::findOrFail($request->get('user_id'));
            $bonus_type = $request->get('bonus_type');
            $user->offeredBonuses()
                ->where('bonus_type', $bonus_type)
                ->update([
                    'show' => false
                ]);

            $response['success'] = true;
        } catch (\Throwable $e) {
            Logs::logToDaily('bonus', $e);
        }

        return response()->json($response);
    }

    public function apiApplyBonus(Request $request)
    {
        $response = ['success' => false];

        $bonus_name = $request->get('bonus_name');

        try {
            $user = User::findOrFail($request->get('user_id'));
            $bonus = BonusFactory::create($bonus_name);
            $bonus->setUser($user);
            $bonus_data = $bonus->getBonusData();
            $bonus->apply($bonus_data);
            $response['message'] = 'Successfully applied bonus!';
            $response['success'] = true;
        } catch (\Throwable $e) {
            $response['message'] = ErrorMessages::BASIC;
            Logs::logToDaily('bonuses', $e);
        }

        return response()->json($response);
    }

    public function ImportUsersFromExcel(Request $request) {
        $file = $request->input('file');

            
    }   
}
