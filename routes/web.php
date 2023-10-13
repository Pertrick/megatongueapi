<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('resetpass', [UserController::class, 'resetpass']);
Route::post('updatepassword', [UserController::class, 'updatepass']);

Route::get('handlePaymentCallback', [PaymentController::class,'handlePaymentCallback']);


// Route::get('api/documentation', function () {
//     return response()->file(storage_path('api-docs/api-docs.json'));
// });

// Route::get('api/documentation', function () {
//     return view('vendor/l5-swagger/index');
// });

Route::get('api/documentation', function () {
    $documentation = 'default'; // Replace 'default' with the appropriate documentation key
    $urlToDocs = asset('swagger/api-docs.json'); // Replace 'docs.json' with the actual Swagger JSON filename
    $useAbsolutePath = true;
    return view('vendor/l5-swagger/index', compact('documentation', 'urlToDocs', 'useAbsolutePath'));
});
