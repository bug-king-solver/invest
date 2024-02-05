<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailsController;
use App\Http\Controllers\Api\SubscriptionsController;
use App\Http\Controllers\Api\UsersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/** Authentication Start */
Route::post('/subscription-list', [SubscriptionsController::class, 'listSubscriptions']);
Route::get('/user/setup-intent', [AuthController::class, 'getSetupIntent']);
Route::post('/register-email', [EmailsController::class, 'regEmail']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/user', [AuthController::class, 'current'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('reset/password', [AuthController::class, 'callResetPassword']);

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

/* using middleware */
Route::group(['middleware' => ['auth:sanctum']], function () {

    /* Subscriptions */
    Route::prefix('subscription')->group(
        function () {
            Route::get('subscription-settings', [SubscriptionsController::class, 'subscriptionSettingsPage']);
            Route::post('create-credit-card', [SubscriptionsController::class, 'createCreditCard']);
            Route::post('payment-method', [SubscriptionsController::class, 'updatePaymentMethod']);
            Route::delete('payment-method/{paymentMethodId}', [SubscriptionsController::class, 'deletePaymentMethod']);
            Route::post('create-subscription', [SubscriptionsController::class, 'newSubscription']);
            Route::post('update-subscription', [SubscriptionsController::class, 'updateSubscription']);
            Route::post('resume-subscription', [SubscriptionsController::class, 'resumeSubscription']);
            Route::post('cancel-subscription', [SubscriptionsController::class, 'cancelSubscription']);
        }

    );

    /* Profile */
    Route::prefix('profile')->group(
        function () {
            Route::post('update-profile', [UsersController::class, 'updateProfile']);
            Route::post('change-password', [UsersController::class, 'updatePassword']);
            Route::post('check-password', [UsersController::class, 'checkPassword']);
        }
    );

    Route::prefix('user')->group(
        function () {
            Route::post('import-excel', [UsersController::class, 'importUsersFromExcel'])->name('users.importUsersFromExcel')
        }
    );
});
