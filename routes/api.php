<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\employeeController;
use App\Http\Controllers\permissionController;
use App\Http\Controllers\FormContentController;
use App\Http\Controllers\InternalMailController;
use App\Http\Controllers\PathController;
use App\Http\Controllers\TransactionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::middleware(['throttle:10,1'])->group(
    function () {

        Route::controller(AuthController::class)->group(function () {
            Route::post('/login', 'login');
        });

        Route::middleware('auth:api', 'Verify.Session')->controller(AuthController::class)->group(function () {
            Route::post('/logout', 'logout');
            Route::post('/refresh', 'refresh');
            Route::post('/reset-password/{user_id}', 'ResetPassword');
        });



        Route::middleware(['auth:api', 'Verify.Session'])->post('/register-employee', [EmployeeController::class, 'create_employee']);
        Route::middleware(['auth:api', 'Verify.Session'])->post('/edit_employee_information', [employeeController::class, 'edit_employee_information']);
        Route::middleware(['auth:api', 'Verify.Session'])->get('/show_employees', [employeeController::class, 'show_employees']);
        Route::middleware(['auth:api', 'Verify.Session'])->get('/convert_employee_status', [employeeController::class, 'convert_employee_status']);



        Route::controller(ManagerController::class)->group(function () {
            Route::get('Manager_Roles', 'ManagerRoles');
            Route::post('/register-manager/{role_id}', 'create_manager')->middleware(['role:نائب المدير', 'auth:api', 'Verify.Session']);
            Route::get('show_my_employees', 'show_my_employees')->middleware('auth:api', 'Verify.Session');
            Route::get('show_all_managers', 'show_all_managers');
        });




        Route::controller(permissionController::class)->group(function () {
            Route::post('addPermissions/{userId}', 'add_permission');
            Route::get('show_my_permissions', 'show_my_permissions')->middleware('auth:api');
            Route::delete('remove_permission/{userId}', 'remove_permission')->middleware('auth:api');
        });

        Route::middleware(['auth:api', 'role:admin'])->group(function () {
            Route::put('/working-hours', [AdminController::class, 'updateWorkingHours']);
        });

        Route::controller(InternalMailController::class)->group(function () {
            Route::post('create_internal_mail', 'create_internal_mail')->middleware('Verify.Session');
            Route::get('show_internal_mails_export', 'show_internal_mails_export')->middleware('Verify.Session');
            ROute::post('edit_status_internal_mails', 'edit_status_internal_mails')->middleware('Verify.Session');
            Route::get('show_import_internal_mails', 'show_import_internal_mails')->middleware('Verify.Session');
            Route::get('show_export_internal_mail_details', 'show_export_internal_mail_details')->middleware('Verify.Session');
            Route::get('show_import_internal_mail_details', 'show_import_internal_mail_details')->middleware('Verify.Session');
        });


        Route::controller(FormController::class)->group(function () {
            Route::prefix('form')->group(function () {

                // مسارات خاصة برئيس الديوان
                Route::middleware(['auth:api', 'role:رئيس الديوان'])->group(function () {
                    Route::post('/upload-word', 'storeFromWord');
                    Route::post('/manual', 'storeManually');
                    Route::get('/show_all', 'index');
                    Route::patch('/toggle-status/{id}', 'toggleStatus');
                });

                // باقي المسارات
                Route::get('/active', 'activeForms');
                Route::get('/under-review', 'underReviewForms');
                Route::get('/{id}', 'show_Form');
                Route::patch('/under-review-to-active/{id}', 'setUnderReviewToActive');
            });
        });

        Route::controller(TransactionController::class)->group(function () {
            Route::prefix('transaction')->group(function () {
                Route::get('/import', 'Import_Transaction')->middleware('auth:api');
                Route::get('/export', 'Export_Transaction')->middleware('auth:api');
                Route::get('/show/{id}', 'showFormContent')->middleware('auth:api');
                Route::patch('/forward/{uuid}', 'forwardTransaction')->middleware('auth:api');
                Route::patch('/reject/{uuid}', 'rejectTransaction')->middleware('auth:api');
                Route::patch('/approve_receipt/{uuid}', 'approveReceipt')->middleware('auth:api', 'role:موظف المالية');
                Route::patch('/reject_receipt/{uuid}', 'rejectReceipt')->middleware('auth:api', 'role:موظف المالية');
            });
        });
        Route::controller(FormContentController::class)->group(function () {
            Route::post('create_form_content', 'create_form_content')->middleware(['Verify.Session']);
        });

        Route::get('all_paths',[PathController::class,'index']);
    }

);
