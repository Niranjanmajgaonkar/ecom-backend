<?php

use App\Http\Controllers\CashfreePaymentController;
use App\Http\Controllers\Usercontroller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/registration',[Usercontroller::class,'registration']);
Route::post('/login',[Usercontroller::class,'login']);
Route::get('/fetchproducts',[Usercontroller::class,'fetchproducts']);
Route::post('/addcart',[Usercontroller::class,'addcart']);
Route::get('/getcartitems',[Usercontroller::class,'getcartitems']);
Route::post('/removeitemsfromcart',[Usercontroller::class,'removeitemsfromcart']);
Route::post('/order_place',[Usercontroller::class,'order_place']);
Route::get('/ordersdata',[Usercontroller::class,'ordersdata']);
Route::post('/cancelorder',[Usercontroller::class,'cancelorder']);
Route::post('/help_request',[Usercontroller::class,'help_request']);



