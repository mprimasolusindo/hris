<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Employee — Core HR master record. Distinct from the Laravel auth User.
 *
 * gender values:         male | female
 * marital_status values: single | married | divorced | widowed
 * status values:         active | resigned | terminated | retired | suspended
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property int $company_id
 * @property string $employee_code
 * @property string $full_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $gender
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property string|null $marital_status
 * @property string|null $religion
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $join_date
 * @property \Illuminate\Support\Carbon|null $resign_date
 * @property string|null $profile_photo_path
 * @property int|null $user_id
 */
class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_employees';

    protected $fillable = [
        'tenant_id', 'company_id', 'employee_code', 'full_name',
        'email', 'phone', 'gender', 'birth_date', 'marital_status',
        'religion', 'status', 'join_date', 'resign_date',
        'profile_photo_path', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'join_date' => 'date',
            'resign_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function identity(): HasOne
    {
        return $this->hasOne(EmployeeIdentity::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(EmployeeFamilyMember::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(EmployeeBankAccount::class);
    }

    public function taxProfile(): HasOne
    {
        return $this->hasOne(EmployeeTaxProfile::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(EmployeeJob::class);
    }

    public function siteAssignments(): HasMany
    {
        return $this->hasMany(EmployeeSite::class);
    }

    public function vendorAssignments(): HasMany
    {
        return $this->hasMany(VendorEmployee::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmploymentContract::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(EmployeeAllowance::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(EmployeeDeduction::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class);
    }
}
