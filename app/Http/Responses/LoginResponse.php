<?php

namespace App\Http\Responses;

use App\Enums\RolesEnum;
use App\Filament\Pages\Welcome;
use App\Models\SaleTransaction;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends BaseLoginResponse
{   
    public function toResponse($request): RedirectResponse | Redirector
    {
       $user = auth()->user();

        if ($user->role === RolesEnum::CASHIER->value) {
            return redirect()->to('pos-panel');
        }

        return redirect()->intended('');
    }
}