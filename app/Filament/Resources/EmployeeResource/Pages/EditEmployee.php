<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Enums\RolesEnum;
use App\Filament\Resources\EmployeeResource;
use App\Models\Employee;
use App\Traits\HasBackUrl;
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

        if(in_array(RolesEnum::MANAGER->value,$employee->user->getRoleNames()->toArray())){
            $data['branch'] = $employee->branch()?->pluck('branch_id');
        }else{
            $data['branch'] = $employee->branch()->first()?->branch_id;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['user_id'] = $record->user->id;


        $record->update($data);
        $record->user->syncRoles($data['role']);

        if(in_array(RolesEnum::MANAGER->value,$data['role'])){
            $this->handleBranchForManager($record,$data);
        }else{
            $this->handleBranchForOtherRoles($record,$data);
        }

        return $record;
    }


    public function handleBranchForManager($record,$data){

        $branches = $data['branch'];

        $record->branch()->delete();

        if(!is_int($branches) && $branches != null){
            foreach($branches as $branch){
                $record->branch()->create([
                    'branch_id'   => $branch,
                    'employee_id' => $record->id,
                    'status'      => 'active',
                ]);
            }
        }
    }

    public function handleBranchForOtherRoles($record, $data)
    {
        $newBranchId = $data['branch'];
        $existingBranch = $record->branch()->first();
        $currentBranchId = $existingBranch?->branch_id;

        if (!$newBranchId && $record->branch->isNotEmpty()) {
            $record->branch->each(fn ($branch) => $branch->delete());
            return;
        }

        if ($newBranchId && $newBranchId != $currentBranchId) {
            $record->branch()->delete();

            $record->branch()->create([
                'branch_id'   => $newBranchId,
                'employee_id' => $record->id,
                'status'      => 'active',
            ]);
        }
    }

}
