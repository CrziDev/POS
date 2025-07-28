<?php

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        User::role(RolesEnum::SALESCLERK->value)->each(function ($user) {
            if ($user->employee) {
                $user->employee->branch?->each(fn($item)=>$item->delete());
                $user->employee->delete();
                $user->delete();
            }
            
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
