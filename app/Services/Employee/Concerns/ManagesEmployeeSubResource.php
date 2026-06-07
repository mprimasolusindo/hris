<?php

namespace App\Services\Employee\Concerns;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;

trait ManagesEmployeeSubResource
{
    abstract protected function modelClass(): string;

    public function listForEmployee(Employee $employee)
    {
        return $this->modelClass()::query()
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();
    }

    public function createForEmployee(Employee $employee, array $data): Model
    {
        return $this->modelClass()::query()->create([
            ...$data,
            'employee_id' => $employee->id,
        ]);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->fresh();
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
