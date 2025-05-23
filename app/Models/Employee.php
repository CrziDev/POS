<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $guarded = ['role','branch'];

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name .' '. $this->last_name
        );
    }

    public static function getOptionsArray($notDeployed = false,$html=true,$managers=false,$branch = null): array
    {
        $query = self::query()->whereHas('user.roles',function(Builder $query){
            return $query->whereNot('name','super-admin');
        });

        if($notDeployed)
        {
            $query->doesntHave('branch');
        }

        
        if($managers)
        {
            $query->whereHas('user.roles',function(Builder $query){
                return $query->where('name','manager');
            });

            if ($branch) {
                $query->whereDoesntHave('branch', function (Builder $q) use ($branch) {
                    $q->where('branch_id', $branch);
                });
            }
        }

        if(!$html){
            return $query
                ->get()->mapWithKeys(fn($item) =>
                    [
                        $item->id => 
                            "<span> <b>Employee:</b> " .  $item->first_name . ' ' . $item->last_name. "</span>". "<br>"
                    ])->all();
        }


        return $query->get()->mapWithKeys(fn($item) =>
            [
                $item->id => 
                    "<span> <b>Employee:</b> " . $item->fullName . "</span>". "<br>".
                    "<small>" .
                        "<span>Role: " . $item->user->roles[0]->name  . "</span>" .
                    "</small>"
            ]
        )->all();
    }

    public function branch()
    {
        return $this->hasMany(BranchEmployee::class,'employee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
