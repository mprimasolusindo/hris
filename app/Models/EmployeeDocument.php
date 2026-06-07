<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EmployeeDocument — uploaded files (KTP, NPWP, contract scans, etc.).
 *
 * category values: ktp | npwp | contract | certificate | other
 *
 * @property int $id
 * @property int $employee_id
 * @property string $category
 * @property string $file_path
 * @property string $original_name
 * @property string|null $mime_type
 * @property int $size
 * @property int|null $uploaded_by
 */
class EmployeeDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_documents';

    protected $fillable = [
        'employee_id',
        'category',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
