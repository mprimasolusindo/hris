<?php

use App\Http\Controllers\Admin\BillingPaymentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BugReport\BugReportController;
use App\Http\Controllers\BugReport\BugReportSettingController;
use App\Http\Controllers\Billing\VendorBillingController;
use App\Http\Controllers\Contract\ContractController;
use App\Http\Controllers\Outsourcing\ComplianceController;
use App\Http\Controllers\Outsourcing\PlacementController;
use App\Http\Controllers\Outsourcing\PlacementTrackingController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\AllowanceController as EmployeeAllowanceController;
use App\Http\Controllers\Employee\ArchiveController as EmployeeArchiveController;
use App\Http\Controllers\Employee\BankAccountController as EmployeeBankAccountController;
use App\Http\Controllers\Employee\BulkActionController as EmployeeBulkActionController;
use App\Http\Controllers\Employee\DeductionController as EmployeeDeductionController;
use App\Http\Controllers\Employee\DocumentController as EmployeeDocumentController;
use App\Http\Controllers\Employee\EmergencyContactController as EmployeeEmergencyContactController;
use App\Http\Controllers\Employee\FamilyMemberController as EmployeeFamilyMemberController;
use App\Http\Controllers\Employee\IdentityController as EmployeeIdentityController;
use App\Http\Controllers\Employee\ImportExportController as EmployeeImportExportController;
use App\Http\Controllers\Employee\JobHistoryController as EmployeeJobHistoryController;
use App\Http\Controllers\Employee\LinkUserController as EmployeeLinkUserController;
use App\Http\Controllers\Employee\LoanController as EmployeeLoanController;
use App\Http\Controllers\Employee\SiteAssignmentController as EmployeeSiteAssignmentController;
use App\Http\Controllers\Employee\PhotoController as EmployeePhotoController;
use App\Http\Controllers\Employee\TaxProfileController as EmployeeTaxProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Leave\LeaveApprovalController;
use App\Http\Controllers\Leave\LeaveBalanceController;
use App\Http\Controllers\Leave\LeaveController;
use App\Http\Controllers\Leave\LeaveTypeController;
use App\Http\Controllers\Shift\ShiftAssignController;
use App\Http\Controllers\Shift\ShiftCalendarController;
use App\Http\Controllers\Shift\ShiftController;
use App\Http\Controllers\Master\AllowanceTypeController;
use App\Http\Controllers\Organization\CompanyController;
use App\Http\Controllers\Organization\DepartmentController;
use App\Http\Controllers\Organization\PositionController;
use App\Http\Controllers\Organization\SiteController;
use App\Http\Controllers\Overtime\OvertimeController;
use App\Http\Controllers\Payroll\BpjsConfigController;
use App\Http\Controllers\Payroll\MasterAllowanceController;
use App\Http\Controllers\Payroll\MasterDeductionController;
use App\Http\Controllers\Payroll\TaxRuleController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Recruitment\CandidateController;
use App\Http\Controllers\Recruitment\InterviewController;
use App\Http\Controllers\Recruitment\JobPostingController;
use App\Http\Controllers\Recruitment\PipelineController;
use App\Http\Controllers\Talent\NineBoxController;
use App\Http\Controllers\Talent\PerformanceReviewController;
use App\Http\Controllers\Talent\SuccessionPlanController;
use App\Http\Controllers\Talent\TalentPoolController;
use App\Http\Controllers\Talent\TrainingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    Route::get('/search', [SearchController::class, 'index'])
        ->middleware('can:search.view')
        ->name('search.index');

    Route::resource('employees', EmployeeController::class)->middleware([
        'index' => 'can:employees.view',
        'show' => 'can:employees.view',
        'create' => 'can:employees.create',
        'store' => 'can:employees.create',
        'edit' => 'can:employees.update',
        'update' => 'can:employees.update',
        'destroy' => 'can:employees.archive',
    ]);
    Route::post('employees/bulk', EmployeeBulkActionController::class)
        ->middleware('can:employees.bulk-update')
        ->name('employees.bulk');
    Route::post('employees/import', [EmployeeImportExportController::class, 'import'])
        ->middleware('can:employees.import')
        ->name('employees.import');
    Route::get('employees/export', [EmployeeImportExportController::class, 'export'])
        ->middleware('can:employees.export')
        ->name('employees.export');
    Route::patch('employees/{employee}/archive', [EmployeeArchiveController::class, 'archive'])
        ->middleware('can:employees.archive')
        ->name('employees.archive');
    Route::patch('employees/{employeeId}/restore', [EmployeeArchiveController::class, 'restore'])
        ->middleware('can:employees.restore')
        ->name('employees.restore');
    Route::post('employees/{employee}/photo', [EmployeePhotoController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.photo.store');
    Route::post('employees/{employee}/link-user', [EmployeeLinkUserController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.link-user');
    Route::post('employees/{employee}/identity', [EmployeeIdentityController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.identity.store');
    Route::delete('employees/{employee}/identity', [EmployeeIdentityController::class, 'destroy'])
        ->middleware('can:employees.update')
        ->name('employees.identity.destroy');
    Route::post('employees/{employee}/tax-profile', [EmployeeTaxProfileController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.tax-profile.store');
    Route::post('employees/{employee}/family-members', [EmployeeFamilyMemberController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.family-members.store');
    Route::put('employees/{employee}/family-members/{familyMember}', [EmployeeFamilyMemberController::class, 'update'])
        ->middleware('can:employees.update')
        ->name('employees.family-members.update');
    Route::delete('employees/{employee}/family-members/{familyMember}', [EmployeeFamilyMemberController::class, 'destroy'])
        ->middleware('can:employees.update')
        ->name('employees.family-members.destroy');
    Route::post('employees/{employee}/emergency-contacts', [EmployeeEmergencyContactController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.emergency-contacts.store');
    Route::put('employees/{employee}/emergency-contacts/{emergencyContact}', [EmployeeEmergencyContactController::class, 'update'])
        ->middleware('can:employees.update')
        ->name('employees.emergency-contacts.update');
    Route::delete('employees/{employee}/emergency-contacts/{emergencyContact}', [EmployeeEmergencyContactController::class, 'destroy'])
        ->middleware('can:employees.update')
        ->name('employees.emergency-contacts.destroy');
    Route::post('employees/{employee}/bank-accounts', [EmployeeBankAccountController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.bank-accounts.store');
    Route::put('employees/{employee}/bank-accounts/{bankAccount}', [EmployeeBankAccountController::class, 'update'])
        ->middleware('can:employees.update')
        ->name('employees.bank-accounts.update');
    Route::delete('employees/{employee}/bank-accounts/{bankAccount}', [EmployeeBankAccountController::class, 'destroy'])
        ->middleware('can:employees.update')
        ->name('employees.bank-accounts.destroy');
    Route::post('employees/{employee}/allowances', [EmployeeAllowanceController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.allowances.store');
    Route::put('employees/{employee}/allowances/{allowance}', [EmployeeAllowanceController::class, 'update'])
        ->middleware('can:employees.update')
        ->name('employees.allowances.update');
    Route::delete('employees/{employee}/allowances/{allowance}', [EmployeeAllowanceController::class, 'destroy'])
        ->middleware('can:employees.update')
        ->name('employees.allowances.destroy');
    Route::post('employees/{employee}/deductions', [EmployeeDeductionController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.deductions.store');
    Route::put('employees/{employee}/deductions/{deduction}', [EmployeeDeductionController::class, 'update'])
        ->middleware('can:employees.update')
        ->name('employees.deductions.update');
    Route::delete('employees/{employee}/deductions/{deduction}', [EmployeeDeductionController::class, 'destroy'])
        ->middleware('can:employees.update')
        ->name('employees.deductions.destroy');
    Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.documents.store');
    Route::delete('employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])
        ->middleware('can:employees.update')
        ->name('employees.documents.destroy');
    Route::post('employees/{employee}/loans', [EmployeeLoanController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.loans.store');
    Route::put('employees/{employee}/loans/{loan}', [EmployeeLoanController::class, 'update'])
        ->middleware('can:employees.update')
        ->name('employees.loans.update');
    Route::delete('employees/{employee}/loans/{loan}', [EmployeeLoanController::class, 'destroy'])
        ->middleware('can:employees.update')
        ->name('employees.loans.destroy');
    Route::post('employees/{employee}/jobs', [EmployeeJobHistoryController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.jobs.store');
    Route::put('employees/{employee}/jobs/{job}', [EmployeeJobHistoryController::class, 'update'])
        ->middleware('can:employees.update')
        ->name('employees.jobs.update');
    Route::delete('employees/{employee}/jobs/{job}', [EmployeeJobHistoryController::class, 'destroy'])
        ->middleware('can:employees.update')
        ->name('employees.jobs.destroy');
    Route::post('employees/{employee}/site-assignments', [EmployeeSiteAssignmentController::class, 'store'])
        ->middleware('can:employees.update')
        ->name('employees.site-assignments.store');
    Route::put('employees/{employee}/site-assignments/{siteAssignment}', [EmployeeSiteAssignmentController::class, 'update'])
        ->middleware('can:employees.update')
        ->name('employees.site-assignments.update');
    Route::delete('employees/{employee}/site-assignments/{siteAssignment}', [EmployeeSiteAssignmentController::class, 'destroy'])
        ->middleware('can:employees.update')
        ->name('employees.site-assignments.destroy');

    Route::get('attendance', [AttendanceController::class, 'index'])
        ->middleware('can:attendance.view')
        ->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])
        ->middleware('can:attendance.create')
        ->name('attendance.store');
    Route::put('attendance/{attendance}', [AttendanceController::class, 'update'])
        ->middleware('can:attendance.update')
        ->name('attendance.update');
    Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy'])
        ->middleware('can:attendance.update')
        ->name('attendance.destroy');

    Route::get('overtime', [OvertimeController::class, 'index'])
        ->middleware('can:overtime.view')
        ->name('overtime.index');
    Route::post('overtime', [OvertimeController::class, 'store'])
        ->middleware('can:overtime.create')
        ->name('overtime.store');
    Route::put('overtime/{overtime}', [OvertimeController::class, 'update'])
        ->middleware('can:overtime.update')
        ->name('overtime.update');
    Route::delete('overtime/{overtime}', [OvertimeController::class, 'destroy'])
        ->middleware('can:overtime.delete')
        ->name('overtime.destroy');

    Route::get('payroll', [PayrollController::class, 'index'])
        ->middleware('can:payroll.view')
        ->name('payroll.index');
    Route::post('payroll', [PayrollController::class, 'store'])
        ->middleware('can:payroll.create')
        ->name('payroll.store');
    Route::post('payroll/bulk-update', [PayrollController::class, 'bulkUpdate'])
        ->middleware('can:payroll.bulk-update')
        ->name('payroll.bulk-update');

    Route::get('payroll/master-allowances', [MasterAllowanceController::class, 'index'])
        ->middleware('can:payroll.master-allowances.view')
        ->name('payroll.master-allowances.index');
    Route::post('payroll/master-allowances', [MasterAllowanceController::class, 'store'])
        ->middleware('can:payroll.master-allowances.create')
        ->name('payroll.master-allowances.store');
    Route::put('payroll/master-allowances/{salaryComponent}', [MasterAllowanceController::class, 'update'])
        ->middleware('can:payroll.master-allowances.update')
        ->name('payroll.master-allowances.update');
    Route::delete('payroll/master-allowances/{salaryComponent}', [MasterAllowanceController::class, 'destroy'])
        ->middleware('can:payroll.master-allowances.delete')
        ->name('payroll.master-allowances.destroy');

    Route::get('payroll/master-deductions', [MasterDeductionController::class, 'index'])
        ->middleware('can:payroll.master-deductions.view')
        ->name('payroll.master-deductions.index');
    Route::post('payroll/master-deductions', [MasterDeductionController::class, 'store'])
        ->middleware('can:payroll.master-deductions.create')
        ->name('payroll.master-deductions.store');
    Route::put('payroll/master-deductions/{salaryComponent}', [MasterDeductionController::class, 'update'])
        ->middleware('can:payroll.master-deductions.update')
        ->name('payroll.master-deductions.update');
    Route::delete('payroll/master-deductions/{salaryComponent}', [MasterDeductionController::class, 'destroy'])
        ->middleware('can:payroll.master-deductions.delete')
        ->name('payroll.master-deductions.destroy');

    Route::get('payroll/bpjs-config', [BpjsConfigController::class, 'index'])
        ->middleware('can:payroll.bpjs-config.view')
        ->name('payroll.bpjs-config.index');
    Route::post('payroll/bpjs-config', [BpjsConfigController::class, 'store'])
        ->middleware('can:payroll.bpjs-config.create')
        ->name('payroll.bpjs-config.store');
    Route::put('payroll/bpjs-config/{bpjsConfig}', [BpjsConfigController::class, 'update'])
        ->middleware('can:payroll.bpjs-config.update')
        ->name('payroll.bpjs-config.update');
    Route::delete('payroll/bpjs-config/{bpjsConfig}', [BpjsConfigController::class, 'destroy'])
        ->middleware('can:payroll.bpjs-config.delete')
        ->name('payroll.bpjs-config.destroy');

    Route::get('payroll/tax-rules', [TaxRuleController::class, 'index'])
        ->middleware('can:payroll.tax-rules.view')
        ->name('payroll.tax-rules.index');
    Route::post('payroll/tax-rules', [TaxRuleController::class, 'store'])
        ->middleware('can:payroll.tax-rules.create')
        ->name('payroll.tax-rules.store');
    Route::put('payroll/tax-rules/{taxRule}', [TaxRuleController::class, 'update'])
        ->middleware('can:payroll.tax-rules.update')
        ->name('payroll.tax-rules.update');
    Route::delete('payroll/tax-rules/{taxRule}', [TaxRuleController::class, 'destroy'])
        ->middleware('can:payroll.tax-rules.delete')
        ->name('payroll.tax-rules.destroy');

    Route::get('payroll/{payroll}', [PayrollController::class, 'show'])
        ->middleware('can:payroll.view')
        ->name('payroll.show');
    Route::put('payroll/{payroll}', [PayrollController::class, 'update'])
        ->middleware('can:payroll.update')
        ->name('payroll.update');

    Route::prefix('organization')->name('organization.')->group(function () {
        Route::get('companies', [CompanyController::class, 'index'])
            ->middleware('can:organization.companies.view')
            ->name('companies.index');
        Route::post('companies', [CompanyController::class, 'store'])
            ->middleware('can:organization.companies.create')
            ->name('companies.store');
        Route::put('companies/{company}', [CompanyController::class, 'update'])
            ->middleware('can:organization.companies.update')
            ->name('companies.update');
        Route::delete('companies/{company}', [CompanyController::class, 'destroy'])
            ->middleware('can:organization.companies.delete')
            ->name('companies.destroy');

        Route::get('sites', [SiteController::class, 'index'])
            ->middleware('can:organization.sites.view')
            ->name('sites.index');
        Route::post('sites', [SiteController::class, 'store'])
            ->middleware('can:organization.sites.create')
            ->name('sites.store');
        Route::put('sites/{site}', [SiteController::class, 'update'])
            ->middleware('can:organization.sites.update')
            ->name('sites.update');
        Route::delete('sites/{site}', [SiteController::class, 'destroy'])
            ->middleware('can:organization.sites.delete')
            ->name('sites.destroy');

        Route::get('departments', [DepartmentController::class, 'index'])
            ->middleware('can:organization.departments.view')
            ->name('departments.index');
        Route::post('departments', [DepartmentController::class, 'store'])
            ->middleware('can:organization.departments.create')
            ->name('departments.store');
        Route::put('departments/{department}', [DepartmentController::class, 'update'])
            ->middleware('can:organization.departments.update')
            ->name('departments.update');
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])
            ->middleware('can:organization.departments.delete')
            ->name('departments.destroy');

        Route::get('positions', [PositionController::class, 'index'])
            ->middleware('can:organization.positions.view')
            ->name('positions.index');
        Route::post('positions', [PositionController::class, 'store'])
            ->middleware('can:organization.positions.create')
            ->name('positions.store');
        Route::put('positions/{position}', [PositionController::class, 'update'])
            ->middleware('can:organization.positions.update')
            ->name('positions.update');
        Route::delete('positions/{position}', [PositionController::class, 'destroy'])
            ->middleware('can:organization.positions.delete')
            ->name('positions.destroy');
    });

    Route::get('shifts', [ShiftController::class, 'index'])
        ->middleware('can:shifts.view')
        ->name('shifts.index');
    Route::post('shifts', [ShiftController::class, 'store'])
        ->middleware('can:shifts.create')
        ->name('shifts.store');
    Route::get('shifts/calendar', [ShiftCalendarController::class, 'index'])
        ->middleware('can:shifts.view')
        ->name('shifts.calendar');
    Route::get('shifts/assign', [ShiftAssignController::class, 'index'])
        ->middleware('can:shifts.assign')
        ->name('shifts.assign');
    Route::post('shifts/assign', [ShiftAssignController::class, 'store'])
        ->middleware('can:shifts.assign')
        ->name('shifts.assign.store');
    Route::get('shifts/{shift}', [ShiftController::class, 'show'])
        ->middleware('can:shifts.view')
        ->name('shifts.show');
    Route::put('shifts/{shift}', [ShiftController::class, 'update'])
        ->middleware('can:shifts.update')
        ->name('shifts.update');
    Route::delete('shifts/{shift}', [ShiftController::class, 'destroy'])
        ->middleware('can:shifts.delete')
        ->name('shifts.destroy');

    Route::get('leave', [LeaveController::class, 'index'])
        ->middleware('can:leave.view')
        ->name('leave.index');
    Route::post('leave', [LeaveController::class, 'store'])
        ->middleware('can:leave.create')
        ->name('leave.store');
    Route::patch('leave/{leave}/cancel', [LeaveController::class, 'cancel'])
        ->middleware('can:leave.cancel')
        ->name('leave.cancel');
    Route::get('leave/approvals', [LeaveApprovalController::class, 'index'])
        ->middleware('can:leave.approvals.view')
        ->name('leave.approvals.index');
    Route::patch('leave/{leave}/decide', [LeaveApprovalController::class, 'decide'])
        ->middleware('can:leave.approvals.approve')
        ->name('leave.approvals.decide');
    Route::get('leave/balance', [LeaveBalanceController::class, 'index'])
        ->middleware('can:leave.balance.view')
        ->name('leave.balance.index');
    Route::get('leave/types', [LeaveTypeController::class, 'index'])
        ->middleware('can:leave.types.view')
        ->name('leave.types.index');
    Route::post('leave/types', [LeaveTypeController::class, 'store'])
        ->middleware('can:leave.types.create')
        ->name('leave.types.store');
    Route::put('leave/types/{leaveType}', [LeaveTypeController::class, 'update'])
        ->middleware('can:leave.types.update')
        ->name('leave.types.update');
    Route::delete('leave/types/{leaveType}', [LeaveTypeController::class, 'destroy'])
        ->middleware('can:leave.types.delete')
        ->name('leave.types.destroy');

    Route::get('vendors', [VendorController::class, 'index'])
        ->middleware('can:vendors.view')
        ->name('vendors.index');
    Route::post('vendors', [VendorController::class, 'store'])
        ->middleware('can:vendors.create')
        ->name('vendors.store');
    Route::get('vendors/{vendor}', [VendorController::class, 'show'])
        ->middleware('can:vendors.view')
        ->name('vendors.show');
    Route::put('vendors/{vendor}', [VendorController::class, 'update'])
        ->middleware('can:vendors.update')
        ->name('vendors.update');
    Route::delete('vendors/{vendor}', [VendorController::class, 'destroy'])
        ->middleware('can:vendors.delete')
        ->name('vendors.destroy');

    Route::get('outsourcing', [PlacementController::class, 'index'])
        ->middleware('can:outsourcing.view')
        ->name('outsourcing.index');
    Route::post('outsourcing', [PlacementController::class, 'store'])
        ->middleware('can:outsourcing.create')
        ->name('outsourcing.store');
    Route::put('outsourcing/{placement}', [PlacementController::class, 'update'])
        ->middleware('can:outsourcing.update')
        ->name('outsourcing.update');
    Route::delete('outsourcing/{placement}', [PlacementController::class, 'destroy'])
        ->middleware('can:outsourcing.delete')
        ->name('outsourcing.destroy');
    Route::get('outsourcing/tracking', [PlacementTrackingController::class, 'index'])
        ->middleware('can:outsourcing.tracking.view')
        ->name('outsourcing.tracking.index');
    Route::get('outsourcing/compliance', [ComplianceController::class, 'index'])
        ->middleware('can:outsourcing.compliance.view')
        ->name('outsourcing.compliance.index');
    Route::post('outsourcing/compliance/resolve', [ComplianceController::class, 'resolve'])
        ->middleware('can:outsourcing.compliance.resolve')
        ->name('outsourcing.compliance.resolve');

    Route::get('vendor-billing', [VendorBillingController::class, 'index'])
        ->middleware('can:vendor-billing.view')
        ->name('vendor-billing.index');
    Route::post('vendor-billing', [VendorBillingController::class, 'store'])
        ->middleware('can:vendor-billing.create')
        ->name('vendor-billing.store');
    Route::put('vendor-billing/{invoice}', [VendorBillingController::class, 'update'])
        ->middleware('can:vendor-billing.update')
        ->name('vendor-billing.update');
    Route::patch('vendor-billing/{invoice}/mark-paid', [VendorBillingController::class, 'markPaid'])
        ->middleware('can:vendor-billing.update')
        ->name('vendor-billing.mark-paid');

    Route::get('contracts', [ContractController::class, 'index'])
        ->middleware('can:contracts.view')
        ->name('contracts.index');
    Route::post('contracts', [ContractController::class, 'store'])
        ->middleware('can:contracts.create')
        ->name('contracts.store');
    Route::get('contracts/{contract}', [ContractController::class, 'show'])
        ->middleware('can:contracts.view')
        ->name('contracts.show');
    Route::put('contracts/{contract}', [ContractController::class, 'update'])
        ->middleware('can:contracts.update')
        ->name('contracts.update');
    Route::delete('contracts/{contract}', [ContractController::class, 'destroy'])
        ->middleware('can:contracts.delete')
        ->name('contracts.destroy');

    Route::prefix('recruitment')->name('recruitment.')->group(function () {
        Route::get('jobs', [JobPostingController::class, 'index'])
            ->middleware('can:recruitment.jobs.view')
            ->name('jobs.index');
        Route::post('jobs', [JobPostingController::class, 'store'])
            ->middleware('can:recruitment.jobs.create')
            ->name('jobs.store');
        Route::get('jobs/{jobPosting}', [JobPostingController::class, 'show'])
            ->middleware('can:recruitment.jobs.view')
            ->name('jobs.show');
        Route::put('jobs/{jobPosting}', [JobPostingController::class, 'update'])
            ->middleware('can:recruitment.jobs.update')
            ->name('jobs.update');
        Route::delete('jobs/{jobPosting}', [JobPostingController::class, 'destroy'])
            ->middleware('can:recruitment.jobs.delete')
            ->name('jobs.destroy');

        Route::get('candidates', [CandidateController::class, 'index'])
            ->middleware('can:recruitment.candidates.view')
            ->name('candidates.index');
        Route::post('candidates', [CandidateController::class, 'store'])
            ->middleware('can:recruitment.candidates.create')
            ->name('candidates.store');
        Route::get('candidates/{candidate}', [CandidateController::class, 'show'])
            ->middleware('can:recruitment.candidates.view')
            ->name('candidates.show');
        Route::put('candidates/{candidate}', [CandidateController::class, 'update'])
            ->middleware('can:recruitment.candidates.update')
            ->name('candidates.update');
        Route::delete('candidates/{candidate}', [CandidateController::class, 'destroy'])
            ->middleware('can:recruitment.candidates.delete')
            ->name('candidates.destroy');

        Route::get('pipeline', [PipelineController::class, 'index'])
            ->middleware('can:recruitment.pipeline.view')
            ->name('pipeline.index');
        Route::post('applications', [PipelineController::class, 'storeApplication'])
            ->middleware('can:recruitment.pipeline.manage')
            ->name('applications.store');
        Route::patch('applications/{application}/stage', [PipelineController::class, 'updateStage'])
            ->middleware('can:recruitment.pipeline.manage')
            ->name('applications.stage');
        Route::post('applications/{application}/hire', [PipelineController::class, 'hire'])
            ->middleware('can:recruitment.pipeline.manage')
            ->name('applications.hire');

        Route::get('interviews', [InterviewController::class, 'index'])
            ->middleware('can:recruitment.interviews.view')
            ->name('interviews.index');
        Route::post('interviews', [InterviewController::class, 'store'])
            ->middleware('can:recruitment.interviews.create')
            ->name('interviews.store');
        Route::put('interviews/{interview}', [InterviewController::class, 'update'])
            ->middleware('can:recruitment.interviews.update')
            ->name('interviews.update');
        Route::delete('interviews/{interview}', [InterviewController::class, 'destroy'])
            ->middleware('can:recruitment.interviews.delete')
            ->name('interviews.destroy');
    });

    Route::get('performance', [PerformanceReviewController::class, 'index'])
        ->middleware('can:talent.performance.view')
        ->name('performance.index');
    Route::post('performance', [PerformanceReviewController::class, 'store'])
        ->middleware('can:talent.performance.create')
        ->name('performance.store');
    Route::put('performance/{performanceReview}', [PerformanceReviewController::class, 'update'])
        ->middleware('can:talent.performance.update')
        ->name('performance.update');
    Route::delete('performance/{performanceReview}', [PerformanceReviewController::class, 'destroy'])
        ->middleware('can:talent.performance.delete')
        ->name('performance.destroy');

    Route::get('training', [TrainingController::class, 'index'])
        ->middleware('can:talent.training.view')
        ->name('training.index');
    Route::post('training', [TrainingController::class, 'store'])
        ->middleware('can:talent.training.create')
        ->name('training.store');
    Route::get('training/{training}', [TrainingController::class, 'show'])
        ->middleware('can:talent.training.view')
        ->name('training.show');
    Route::put('training/{training}', [TrainingController::class, 'update'])
        ->middleware('can:talent.training.update')
        ->name('training.update');
    Route::delete('training/{training}', [TrainingController::class, 'destroy'])
        ->middleware('can:talent.training.delete')
        ->name('training.destroy');
    Route::post('training/{training}/assign', [TrainingController::class, 'assign'])
        ->middleware('can:talent.training.assign')
        ->name('training.assign');
    Route::delete('training/{training}/participants/{employee}', [TrainingController::class, 'unassign'])
        ->middleware('can:talent.training.assign')
        ->name('training.unassign');

    Route::get('talent-pool', [TalentPoolController::class, 'index'])
        ->middleware('can:talent.talent-pool.view')
        ->name('talent-pool.index');
    Route::post('talent-pool', [TalentPoolController::class, 'store'])
        ->middleware('can:talent.talent-pool.create')
        ->name('talent-pool.store');
    Route::put('talent-pool/{talentPool}', [TalentPoolController::class, 'update'])
        ->middleware('can:talent.talent-pool.update')
        ->name('talent-pool.update');
    Route::delete('talent-pool/{talentPool}', [TalentPoolController::class, 'destroy'])
        ->middleware('can:talent.talent-pool.delete')
        ->name('talent-pool.destroy');

    Route::get('succession', [SuccessionPlanController::class, 'index'])
        ->middleware('can:talent.succession.view')
        ->name('succession.index');
    Route::post('succession', [SuccessionPlanController::class, 'store'])
        ->middleware('can:talent.succession.create')
        ->name('succession.store');
    Route::put('succession/{successionPlan}', [SuccessionPlanController::class, 'update'])
        ->middleware('can:talent.succession.update')
        ->name('succession.update');
    Route::delete('succession/{successionPlan}', [SuccessionPlanController::class, 'destroy'])
        ->middleware('can:talent.succession.delete')
        ->name('succession.destroy');

    Route::get('succession/nine-box', [NineBoxController::class, 'index'])
        ->middleware('can:talent.nine-box.view')
        ->name('succession.nine-box.index');
    Route::post('succession/nine-box', [NineBoxController::class, 'store'])
        ->middleware('can:talent.nine-box.create')
        ->name('succession.nine-box.store');
    Route::put('succession/nine-box/{nineBox}', [NineBoxController::class, 'update'])
        ->middleware('can:talent.nine-box.update')
        ->name('succession.nine-box.update');
    Route::delete('succession/nine-box/{nineBox}', [NineBoxController::class, 'destroy'])
        ->middleware('can:talent.nine-box.delete')
        ->name('succession.nine-box.destroy');

    Route::get('master/allowance-types', [AllowanceTypeController::class, 'index'])
        ->middleware('can:master.allowance-types.view')
        ->name('master.allowance-types.index');
    Route::post('master/allowance-types', [AllowanceTypeController::class, 'store'])
        ->middleware('can:master.allowance-types.create')
        ->name('master.allowance-types.store');
    Route::put('master/allowance-types/{salaryComponent}', [AllowanceTypeController::class, 'update'])
        ->middleware('can:master.allowance-types.update')
        ->name('master.allowance-types.update');
    Route::delete('master/allowance-types/{salaryComponent}', [AllowanceTypeController::class, 'destroy'])
        ->middleware('can:master.allowance-types.delete')
        ->name('master.allowance-types.destroy');

    Route::get('bug-reports', [BugReportController::class, 'index'])
        ->middleware('can:bug-reports.view')
        ->name('bug-reports.index');
    Route::post('bug-reports', [BugReportController::class, 'store'])
        ->middleware('can:bug-reports.create')
        ->name('bug-reports.store');
    Route::get('bug-reports/settings', [BugReportSettingController::class, 'edit'])
        ->middleware('can:bug-reports.manage')
        ->name('bug-reports.settings.edit');
    Route::put('bug-reports/settings', [BugReportSettingController::class, 'update'])
        ->middleware('can:bug-reports.manage')
        ->name('bug-reports.settings.update');
    Route::get('bug-reports/{bugReport}', [BugReportController::class, 'show'])
        ->middleware('can:bug-reports.view')
        ->name('bug-reports.show');
    Route::patch('bug-reports/{bugReport}/status', [BugReportController::class, 'updateStatus'])
        ->middleware('can:bug-reports.update')
        ->name('bug-reports.status.update');
    Route::delete('bug-reports/{bugReport}', [BugReportController::class, 'destroy'])
        ->middleware('can:bug-reports.delete')
        ->name('bug-reports.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show'])->middleware([
            'index' => 'can:users.view',
            'create' => 'can:users.create',
            'store' => 'can:users.create',
            'edit' => 'can:users.update',
            'update' => 'can:users.update',
            'destroy' => 'can:users.delete',
        ]);
        Route::resource('roles', RoleController::class)->except(['show'])->middleware([
            'index' => 'can:roles.view',
            'create' => 'can:roles.create',
            'store' => 'can:roles.create',
            'edit' => 'can:roles.update',
            'update' => 'can:roles.update',
            'destroy' => 'can:roles.delete',
        ]);

        Route::prefix('saas')->name('saas.')->group(function () {
            Route::get('tenants', [TenantController::class, 'index'])
                ->middleware('can:saas.tenants.view')->name('tenants.index');
            Route::post('tenants', [TenantController::class, 'store'])
                ->middleware('can:saas.tenants.create')->name('tenants.store');
            Route::put('tenants/{tenant}', [TenantController::class, 'update'])
                ->middleware('can:saas.tenants.update')->name('tenants.update');
            Route::delete('tenants/{tenant}', [TenantController::class, 'destroy'])
                ->middleware('can:saas.tenants.delete')->name('tenants.destroy');

            Route::get('plans', [SubscriptionPlanController::class, 'index'])
                ->middleware('can:saas.plans.view')->name('plans.index');
            Route::post('plans', [SubscriptionPlanController::class, 'store'])
                ->middleware('can:saas.plans.create')->name('plans.store');
            Route::put('plans/{plan}', [SubscriptionPlanController::class, 'update'])
                ->middleware('can:saas.plans.update')->name('plans.update');
            Route::delete('plans/{plan}', [SubscriptionPlanController::class, 'destroy'])
                ->middleware('can:saas.plans.delete')->name('plans.destroy');

            Route::get('subscriptions', [SubscriptionController::class, 'index'])
                ->middleware('can:saas.subscriptions.view')->name('subscriptions.index');
            Route::post('subscriptions', [SubscriptionController::class, 'store'])
                ->middleware('can:saas.subscriptions.create')->name('subscriptions.store');
            Route::put('subscriptions/{subscription}', [SubscriptionController::class, 'update'])
                ->middleware('can:saas.subscriptions.update')->name('subscriptions.update');
            Route::delete('subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])
                ->middleware('can:saas.subscriptions.delete')->name('subscriptions.destroy');

            Route::get('payments', [BillingPaymentController::class, 'index'])
                ->middleware('can:saas.payments.view')->name('payments.index');
            Route::post('payments', [BillingPaymentController::class, 'store'])
                ->middleware('can:saas.payments.create')->name('payments.store');
            Route::put('payments/{payment}', [BillingPaymentController::class, 'update'])
                ->middleware('can:saas.payments.update')->name('payments.update');
            Route::delete('payments/{payment}', [BillingPaymentController::class, 'destroy'])
                ->middleware('can:saas.payments.delete')->name('payments.destroy');
        });
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::fallback(fn () => Inertia::render('NotFound'))->name('not-found');
});

require __DIR__.'/auth.php';
