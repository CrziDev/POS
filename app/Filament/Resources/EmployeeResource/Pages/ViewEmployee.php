<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Enums\RolesEnum;
use App\Filament\Resources\EmployeeResource;
use App\Models\Employee;
use App\Traits\HasBackUrl;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewEmployee extends ViewRecord
{

    protected static string $resource = EmployeeResource::class;

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


}
