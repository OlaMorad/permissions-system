<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\managerController;
use App\Http\Controllers\employeeController;
use App\Http\Controllers\permissionController;
use App\Http\Controllers\Head_of_Front_Desk_Controller;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
});

Route::middleware('auth:api')->controller(AuthController::class)->group(function () {
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
});

// Route::post('addManager/{roleName}',[managerController::class,'create_manager']);
Route::middleware(['auth:api'])->group(function () {
    Route::post('/register-manager/{role_id}', [ManagerController::class, 'create_manager'])
        ->middleware('role:sub_admin');
});
Route::get('Manager_Roles',[managerController::class, 'ManagerRoles']);

// Route::post('addEmployee/{roleName}',[employeeController::class,'create_employee']);
Route::middleware(['auth:api'])->post('/register-employee', [EmployeeController::class, 'create_employee']);





Route::controller(permissionController::class)->group(function () {
   Route::post('addPermissions/{userId}','add_permission');
   Route::get('show_my_permissions','show_my_permissions')->middleware('auth:api');
   Route::delete('remove_permission/{userId}','remove_permission')->middleware('auth:api');
});
