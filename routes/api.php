<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\PathController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExamRequestController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\FormContentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\InternalMailController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\InternalMailArchiveController;
use App\Http\Controllers\ProgramController;
use App\Models\InternalMailArchive;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['throttle:10,1'])->group(function () {

    // Auth
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/refresh', 'refresh');
        Route::post('/logout', 'logout');
        Route::get('/check-session', 'checkSession');
    });

    // Employee
    Route::middleware('Verify.Session')->group(function () {
        Route::post('/register-employee', [EmployeeController::class, 'create_employee']);
        Route::post('/edit_employee_information', [EmployeeController::class, 'edit_employee_information']);
        Route::get('/show_employees', [EmployeeController::class, 'show_employees']);
        Route::get('/convert_employee_status', [EmployeeController::class, 'convert_employee_status']);
    });

    // Manager
    Route::controller(ManagerController::class)->group(function () {
        Route::get('Manager_Roles', 'ManagerRoles');
        Route::post('/register-manager/{role_id}', 'create_manager')->middleware(['role:نائب المدير', 'Verify.Session']);
        Route::get('show_my_employees', 'show_my_employees')->middleware('Verify.Session');
        Route::get('show_all_managers', 'show_all_managers');
    });

    // Permissions
    Route::controller(PermissionController::class)->group(function () {
        Route::post('addPermissions/{userId}', 'add_permission');
        Route::get('show_my_permissions', 'show_my_permissions')->middleware('auth:api');
        Route::delete('remove_permission/{userId}', 'remove_permission')->middleware('auth:api');
    });

    // Admin
    Route::middleware(['Verify.Session', 'role:المدير'])->group(function () {
        Route::put('/working-hours', [AdminController::class, 'updateWorkingHours']);
        Route::get('/working-hours/show', [AdminController::class, 'showWorkingHours']);
    });
    Route::get('show/employees/by/{path}', [AdminController::class, 'show_employees_by_path'])->middleware('Verify.Session','role:المدير|نائب المدير');


    // Internal Mail
    Route::controller(InternalMailController::class)->group(function () {
        Route::middleware('Verify.Session')->group(function () {
            Route::post('create_internal_mail', 'create_internal_mail');
            Route::get('show_internal_mails_export', 'show_internal_mails_export');
            Route::post('edit_status_internal_mails', 'edit_status_internal_mails');
            Route::get('show_internal_mail_details', 'show_internal_mail_details');
            // Route::get('show_export_internal_mail_details', 'show_export_internal_mail_details');
            Route::get('show_import_internal_mails', 'show_import_internal_mails');
        });
    });

    // Form
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

    // Transaction
    Route::controller(TransactionController::class)->group(function () {
        Route::prefix('transaction')->group(function () {
            Route::get('/import', 'Import_Transaction')->middleware('Verify.Session');
            Route::get('/export', 'Export_Transaction')->middleware('Verify.Session');
            Route::get('/archived-export', 'archivedExportedTransactions')->middleware('Verify.Session');
            Route::get('archive', 'show_archive'); //->middleware('Verify.Session','role:المدير');
            Route::get('/archive/path/{id}', 'archiveByPath')->middleware('Verify.Session', 'role:المدير');
            Route::get('/show/{uuid}', 'showFormContent')->middleware('Verify.Session');
            Route::get('content/{uuid}', 'ShowTransactionContent')->middleware('Verify.Session');
            Route::patch('/under-review/{uuid}', 'MarkAsUnderReview')->middleware('Verify.Session');
            Route::post('/status/{uuid}', 'updateTransactionStatus')->middleware('Verify.Session');
            Route::post('/receipt_status', 'updateReceiptStatus')->middleware('Verify.Session', 'role:موظف المالية');
        });
    });

    // Form Content
    Route::post('create_form_content', [FormContentController::class, 'create_form_content'])->middleware('Verify.Session','role:الطبيب');

    // Statistics
    Route::prefix('statistics')->middleware(['Verify.Session'])->group(function () {
        Route::get('paths/achievement', [StatisticsController::class, 'AllPathsAchievementStatistics'])->middleware('role:المدير');
        Route::get('/external', [StatisticsController::class, 'ExternalStatisticsSummary']);
        Route::get('/weekly-done', [StatisticsController::class, 'weeklyDone']);
        Route::get('/weekly/path/{id}', [StatisticsController::class, 'weeklyDoneByPath'])->middleware('role:المدير');
        Route::get('/InternalStatisticsSummary', [StatisticsController::class, 'InternalStatisticsSummary']);
    });

    // Paths
    Route::get('all_paths', [PathController::class, 'index'])->middleware(['Verify.Session', 'role:رئيس الديوان']);

    // Archive
    Route::get('/archive/internal/mails', [InternalMailArchiveController::class, 'add_to_archive'])->middleware('Verify.Session');
    Route::get('/archive/import/internal/mails', [InternalMailArchiveController::class, 'show_received_archive'])->middleware('Verify.Session');
    Route::get('archive/export/for/admin/{path}', [InternalMailArchiveController::class, 'show_sent_archive_for_director'])->middleware(['Verify.Session', 'role:المدير|نائب المدير']);
    Route::get('archive/import/for/admin/{path}', [InternalMailArchiveController::class, 'show_received_archive_for_director'])->middleware(['Verify.Session', 'role:المدير|نائب المدير']);


    // Specializations
    Route::prefix('specializations')->group(function () {
        Route::get('/show_all', [SpecializationController::class, 'index'])->middleware(['Verify.Session', 'role:رئيس الامتحانات|موظف الامتحانات|المدير|نائب المدير']);
        Route::post('/add', [SpecializationController::class, 'store'])->middleware('Verify.Session', 'role:رئيس الامتحانات');
        Route::post('/{id}', [SpecializationController::class, 'update'])->middleware('Verify.Session', 'role:رئيس الامتحانات');
    });

    // Exam Requests
    Route::controller(ExamRequestController::class)->group(function () {
        Route::post('create_form_content_exam', 'create_form_content_exam')->middleware('Verify.Session', 'role:الطبيب');
        Route::get('show_form_content_exam', 'show_form_content_exam')->middleware('Verify.Session');
        Route::post('edit_form_content_exam_status', 'edit_form_content_exam_status')->middleware('Verify.Session', 'role:موظف الامتحانات');
        Route::get('show_all_import_request_exam', 'show_all_import_request_exam')->middleware('Verify.Session', 'role:موظف الامتحانات|رئيس الامتحانات');
        Route::get('show_all_end_request_exam', 'show_all_end_request_exam')->middleware('Verify.Session', 'role:موظف الامتحانات|رئيس الامتحانات');
    });

    // Question Bank
    Route::controller(QuestionBankController::class)->group(function () {
        Route::post('/add_question_manual', 'addManual')->middleware('Verify.Session', 'role:رئيس الامتحانات');
        Route::post('/addExcelQuestions', 'importFromExcel')->middleware('Verify.Session', 'role:رئيس الامتحانات');
    });

    // Doctor
    Route::controller(DoctorController::class)->group(function () {
        Route::post('add_specialization', 'add_specialization')->middleware('Verify.Session', 'role:الطبيب');
    });

    // Program
    Route::prefix('program')->controller(ProgramController::class)->group(function () {
        Route::post('/add', 'store')->middleware('Verify.Session', 'role:رئيس الامتحانات');
        Route::get('/show_all', 'index')->middleware('Verify.Session', 'role:رئيس الامتحانات|نائب المدير|المدير|موظف الامتحانات');
        Route::get('/approved', 'get_approved_programs');
        Route::get('/{id}', 'show_program_details')->middleware('Verify.Session', 'role:رئيس الامتحانات|نائب المدير|المدير|موظف الامتحانات');
        Route::post('/update-status/{id}', 'update_status')->middleware('Verify.Session', 'role:المدير');
    });

    // candidates
    Route::controller(CandidateController::class)->prefix('candidates')
        ->middleware(['Verify.Session', 'role:رئيس الامتحانات|نائب المدير|المدير|موظف الامتحانات'])
        ->group(function () {
            // عدد المرشحين لامتحان معيّن
            Route::get('exam/{id}', 'show_candidates_By_ExamId');
            // عدد المتقدّمين لامتحان معيّن
            Route::get('/present/exam/{examId}', 'show_present_candidates_By_ExamId');
            // علامات الأطباء
            Route::get('/present/all', 'show_all_present_candidates');
        });

});
