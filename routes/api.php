<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\PathController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\employeeController;
use App\Http\Controllers\ExamRequestController;
use App\Http\Controllers\permissionController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\FormContentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\InternalMailController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\InternalMailArchiveController;
use App\Http\Controllers\ProgramController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::middleware(['throttle:10,1'])->group(
    function () {


        Route::controller(AuthController::class)->group(function () {
            Route::post('/login', 'login');
            Route::post('/refresh', 'refresh');
            Route::post('/logout', 'logout');
            Route::get('/check-session', 'checkSession');
        });


        Route::middleware(['Verify.Session'])->post('/register-employee', [EmployeeController::class, 'create_employee']);
        Route::middleware(['Verify.Session'])->post('/edit_employee_information', [employeeController::class, 'edit_employee_information']);
        Route::middleware(['Verify.Session'])->get('/show_employees', [employeeController::class, 'show_employees']);
        Route::middleware(['Verify.Session'])->get('/convert_employee_status', [employeeController::class, 'convert_employee_status']);



        Route::controller(ManagerController::class)->group(function () {
            Route::get('Manager_Roles', 'ManagerRoles');
            Route::post('/register-manager/{role_id}', 'create_manager')->middleware(['role:نائب المدير', 'Verify.Session']);
            Route::get('show_my_employees', 'show_my_employees')->middleware('Verify.Session');
            Route::get('show_all_managers', 'show_all_managers');
        });




        Route::controller(permissionController::class)->group(function () {
            Route::post('addPermissions/{userId}', 'add_permission');
            Route::get('show_my_permissions', 'show_my_permissions')->middleware('auth:api');
            Route::delete('remove_permission/{userId}', 'remove_permission')->middleware('auth:api');
        });

        Route::middleware(['Verify.Session', 'role:المدير'])->group(function () {
            Route::put('/working-hours', [AdminController::class, 'updateWorkingHours']);
            Route::get('/working-hours/show', [AdminController::class, 'showWorkingHours']);
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
            Route::prefix('form')->middleware('Verify.Session')->group(function () {
                Route::post('/upload-word', 'storeFromWord')->middleware('role:رئيس الديوان');
                Route::post('/manual', 'storeManually')->middleware('role:رئيس الديوان');
                Route::get('/show_all', 'index')->middleware('role:المدير|رئيس الديوان');
                Route::patch('/toggle-status/{id}', 'toggleStatus')->middleware('role:رئيس الديوان');
                Route::get('/active', 'activeForms')->middleware('role:الطبيب');
                Route::get('/{id}', 'show_Form')->middleware('role:رئيس الديوان|المدير|الطبيب');
                Route::post('/review/{id}', 'formReviewDecision')->middleware('role:المدير');
            });
        });



        Route::controller(TransactionController::class)->group(function () {
            Route::prefix('transaction')->group(function () {
                Route::get('/import', 'Import_Transaction')->middleware('Verify.Session');
                Route::get('/export', 'Export_Transaction')->middleware('Verify.Session');
                Route::get('/archived-export', 'archivedExportedTransactions')->middleware('Verify.Session');
                Route::get('archive', 'show_archive'); //->middleware('Verify.Session','role:المدير');
                Route::get('/archive/path/{id}', 'archiveByPath')->middleware('Verify.Session', 'role:المدير');
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
            Route::get('paths/achievement', [StatisticsController::class, 'AllPathsAchievementStatistics'])->middleware('role:المدير');
            Route::get('/external', [StatisticsController::class, 'ExternalStatisticsSummary']);
            Route::get('/weekly-done', [StatisticsController::class, 'weeklyDone']);
            Route::get('/weekly/path/{id}', [StatisticsController::class, 'weeklyDoneByPath'])->middleware('role:المدير');
            Route::get('/InternalStatisticsSummary', [StatisticsController::class, 'InternalStatisticsSummary']);
        });

        Route::get('all_paths', [PathController::class, 'index'])->middleware(['Verify.Session', 'role:رئيس الديوان']);
        Route::get('/archive', [InternalMailArchiveController::class, 'add_to_archive'])->middleware(['Verify.Session']);



        Route::prefix('specializations')->group(function () {
            Route::get('/show_all', [SpecializationController::class, 'index'])
                ->middleware(['Verify.Session', 'role:رئيس الامتحانات|موظف الامتحانات|المدير|نائب المدير']);;
            Route::post('/add', [SpecializationController::class, 'store'])->middleware('Verify.Session', 'role:رئيس الامتحانات');
            Route::post('/{id}', [SpecializationController::class, 'update'])->middleware('Verify.Session', 'role:رئيس الامتحانات');
        });

        Route::controller(ExamRequestController::class)->group(function () {
            Route::post('create_form_content_exam', 'create_form_content_exam')->middleware('Verify.Session', 'role:الطبيب');
        });
    }
);

Route::controller(QuestionBankController::class)->group(function () {
    Route::post('/add_question_manual', 'addManual')->middleware('role:رئيس الامتحانات');
    Route::post('/addExcelQuestions', 'importFromExcel')->middleware('role:رئيس الامتحانات');
});


Route::post('/program/add', [ProgramController::class, 'store'])->middleware('Verify.Session', 'role:رئيس الامتحانات');
Route::get('/program/{id}', [ProgramController::class, 'show_program_details'])
    ->middleware('Verify.Session', 'role:رئيس الامتحانات|نائب المدير|المدير|موظف الامتحانات');;
Route::get('/programs', [ProgramController::class, 'index'])
    ->middleware('Verify.Session', 'role:رئيس الامتحانات|نائب المدير|المدير|موظف الامتحانات');;



//     }
// );
