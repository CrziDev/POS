<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name .' '. $this->last_name
        );
    }

    public static function getOptionsArray($notDeployed = false): array
    {
        $query = self::query();

        if($notDeployed)
        {
            $query->doesntHave('branch');
        }

        return [];
    }

    public function branch()
    {
        return $this->hasOne(BranchEmployee::class,'employee_id');
    }
}
