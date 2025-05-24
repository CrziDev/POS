<?php

namespace App\Actions\Notifications;

use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class PurchaseOrderDelivered
{
    protected string $branchName;
    protected string $userName;
    protected string $route;
    protected $roles;

    public function __construct(string $branchName, string $userName, string $route, $roles)
    {
        $this->branchName = $branchName;
        $this->userName = $userName;
        $this->route = $route;
        $this->roles = $roles;
    }

    public function handle()
    {

        $notifiedUser = User::whereHas('roles', function ($query) {
            $query->whereIn('name', $this->roles);
        })->get();

        Notification::make()
            ->title('Purchase Order Delivered')
            ->body($this->bodyMessage())
            ->actions([
                Action::make('View')
                    ->button()
                    ->markAsRead()
                    ->url($this->route, shouldOpenInNewTab: true),
            ])
            ->sendToDatabase($notifiedUser);
    }

    protected function bodyMessage(): string
    {
        return "A purchase order was Confirmed and Delivered on Branch {$this->branchName} by {$this->userName}.";
    }
}
