<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MegaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;

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
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('forgotpass', [UserController::class, 'forgotPassword']);
Route::get('resetpass', [UserController::class, 'resetpass']);
Route::post('updatepassword', [UserController::class, 'updatepass']);
Route::post("translator", [MegaController::class, 'translator']);
Route::post('translatefile', [MegaController::class, 'translatefile']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();


});
Route::group(['middleware' => 'auth:sanctum'], function (){
    Route::post('apikey', [MegaController::class, 'apikey']);
    Route::post('pricing', [MegaController::class, 'pricing']);
    Route::post('addreview', [MegaController::class, "addreview"]);
    Route::get('getreviews', [MegaController::class, 'getreviews']);
    Route::get('getapiusage', [MegaController::class, 'getapiusage']);
    Route::get('getapikey', [MegaController::class, 'getapikey']);
    Route::get('getfaq', [MegaController::class, 'getfaq']);
    Route::get('getuserinfo', [UserController::class, 'getuserinfo']);
    Route::post('updateuserinfo', [UserController::class, 'updateuserinfo']);

    //for payment integration

    Route::post('stripePayment', [PaymentController::class, 'stripePayment']);
    Route::post('paystackpayment', [PaymentController::class, 'paystackpayment']);
    Route::post('handlePaymentCallback', [PaymentController::class,'handlePaymentCallback']);

    Route::get('getpaymentmethod', [PaymentController::class, 'getpaymentmethod']);
    Route::get('getsubscribplan', [PaymentController::class, 'getsubscribplan']);
});
