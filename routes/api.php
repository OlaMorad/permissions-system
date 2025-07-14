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
use App\Http\Controllers\PathController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\InternalMailController;
use App\Http\Controllers\InternalMailArchiveController;
use App\Http\Controllers\SpecializationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::middleware(['throttle:10,1','working.hours'])->group(
    function () {

        Route::controller(AuthController::class)->group(function () {
            Route::post('/login', 'login');
             Route::post('/refresh', 'refresh');
               Route::post('/logout', 'logout');
        });


        Route::middleware(['Verify.Session'])->post('/register-employee', [EmployeeController::class, 'create_employee']);
        Route::middleware(['Verify.Session'])->post('/edit_employee_information', [employeeController::class, 'edit_employee_information']);
        Route::middleware([ 'Verify.Session'])->get('/show_employees', [employeeController::class, 'show_employees']);
        Route::middleware(['Verify.Session'])->get('/convert_employee_status', [employeeController::class, 'convert_employee_status']);



        Route::controller(ManagerController::class)->group(function () {
            Route::get('Manager_Roles', 'ManagerRoles');
            Route::post('/register-manager/{role_id}', 'create_manager')->middleware(['role:نائب المدير','Verify.Session']);
            Route::get('show_my_employees', 'show_my_employees')->middleware( 'Verify.Session');
            Route::get('show_all_managers', 'show_all_managers');
        });




        Route::controller(permissionController::class)->group(function () {
            Route::post('addPermissions/{userId}', 'add_permission');
            Route::get('show_my_permissions', 'show_my_permissions')->middleware('auth:api');
            Route::delete('remove_permission/{userId}', 'remove_permission')->middleware('auth:api');
        });

        Route::middleware(['role:المدير'])->group(function () {
            Route::put('/working-hours', [AdminController::class, 'updateWorkingHours']);
        });

        Route::controller(InternalMailController::class)->group(function () {
            Route::post('create_internal_mail', 'create_internal_mail')->middleware('Verify.Session');
            Route::get('show_internal_mails_export', 'show_internal_mails_export')->middleware('Verify.Session');
            Route::post('edit_status_internal_mails', 'edit_status_internal_mails')->middleware('Verify.Session');
            Route::get('show_internal_mail_details', 'show_internal_mail_details')->middleware('Verify.Session');
            Route::get('show_export_internal_mail_details', 'show_export_internal_mail_details')->middleware('Verify.Session');
            Route::get('show_import_internal_mails', 'show_import_internal_mails')->middleware('Verify.Session');
        });


Route::controller(FormController::class)->group(function () {
    Route::prefix('form')->group(function () {

        // مسارات خاصة برئيس الديوان
        Route::middleware(['Verify.Session', 'role:رئيس الديوان'])->group(function () {
            Route::post('/upload-word', 'storeFromWord');
            Route::post('/manual', 'storeManually');
            Route::get('/show_all', 'index');
            Route::patch('/toggle-status/{id}', 'toggleStatus');
        });

        // باقي المسارات
        Route::get('/active', 'activeForms')->middleware('Verify.Session', 'role:الطبيب');
        Route::get('/under-review', 'underReviewForms')->middleware('Verify.Session', 'role:المدير');
        Route::get('/{id}', 'show_Form')->middleware(['Verify.Session', 'role:رئيس الديوان']);
        Route::post('/review/{id}', 'formReviewDecision')->middleware('Verify.Session', 'role:المدير');
    });
});

Route::controller(TransactionController::class)->group(function () {
    Route::prefix('transaction')->group(function () {
        Route::get('/import', 'Import_Transaction')->middleware('Verify.Session');
        Route::get('/export', 'Export_Transaction')->middleware('Verify.Session');
        Route::get('/archived-export', 'archivedExportedTransactions')->middleware('Verify.Session');
        Route::get('archive', 'show_archive'); //->middleware('Verify.Session','role:المدير');
        Route::get('/show/{uuid}', 'showFormContent')->middleware('Verify.Session');
        Route::get('content/{uuid}', 'ShowTransactionContent')->middleware('Verify.Session');
        Route::post('/status/{uuid}', 'updateTransactionStatus')->middleware('Verify.Session');
        Route::post('/receipt_status', 'updateReceiptStatus')->middleware('Verify.Session', 'role:موظف المالية');
    });
});
        Route::controller(FormContentController::class)->group(function () {
            Route::post('create_form_content', 'create_form_content')->middleware(['Verify.Session']);
        });


        Route::prefix('statistics')->middleware(['Verify.Session'])->group(function () {
            Route::get('/section', [StatisticsController::class, 'ExternalStatisticsSummary']);
            Route::get('/employees', [StatisticsController::class, 'employeePerformance']);
            Route::get('/weekly-done', [StatisticsController::class, 'weeklyDone']);
            Route::get('/InternalStatisticsSummary', [StatisticsController::class, 'InternalStatisticsSummary'])->middleware('Verify.Session');
        });

        Route::get('all_paths', [PathController::class, 'index'])->middleware(['Verify.Session', 'role:رئيس الديوان']);
        Route::get('/archive', [InternalMailArchiveController::class, 'add_to_archive'])->middleware(['Verify.Session']);



        Route::prefix('specializations')->group(function () {
            Route::get('/show_all', [SpecializationController::class, 'index']);
            Route::post('/add', [SpecializationController::class, 'store'])->middleware('Verify.Session','role:رئيس الامتحانات');
        });
    }
);
