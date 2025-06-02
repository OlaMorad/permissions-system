<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\managerController;
use App\Http\Controllers\employeeController;
use App\Http\Controllers\Head_of_Front_Desk_Controller;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





Route::post('addManager/{roleName}',[managerController::class,'create_manager']);
Route::post('addEmployee/{roleName}',[employeeController::class,'create_employee']);
