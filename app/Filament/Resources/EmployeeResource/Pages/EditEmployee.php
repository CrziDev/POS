<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Employee;
use App\Traits\HasBackUrl;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditEmployee extends EditRecord
{
    use HasBackUrl;
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {  

        $employee = Employee::find($data['id']);
        $data['role'] = $employee->user->getRoleNames();
        $data['branch'] = $employee->branch?->branch_id;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['user_id'] = $record->user->id;

        $record->update($data);

        $record->user->syncRoles($data['role']);

        $currentBranchId = $record->branch?->branch_id;
        $newBranchId = $data['branch'];

        if (!$newBranchId && $record->branch) {
            $record->branch->delete();
        }

        if ($newBranchId && $newBranchId != $currentBranchId) {
            if ($record->branch) {
                $record->branch->delete();
            }

            $record->branch()->create([
                'branch_id'   => $newBranchId,
                'employee_id' => $record->id,
                'status'      => 'active',
            ]);
        }

        return $record;
    }

}
