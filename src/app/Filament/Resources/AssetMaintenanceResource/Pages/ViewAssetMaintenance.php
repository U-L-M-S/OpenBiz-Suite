<?php

namespace App\Filament\Resources\AssetMaintenanceResource\Pages;

use App\Filament\Resources\AssetMaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Notifications\Notification;

class ViewAssetMaintenance extends ViewRecord
{
    protected static string $resource = AssetMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('start')
                ->label('Start Maintenance')
                ->icon('heroicon-o-play')
                ->color('info')
                ->visible(fn () => $this->record->status === 'scheduled')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'in_progress']);
                    $this->record->asset->update(['status' => 'maintenance']);

                    Notification::make()
                        ->title('Maintenance Started')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('complete')
                ->label('Complete Maintenance')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['scheduled', 'in_progress']))
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label('Completion Notes')
                        ->rows(3),
                    Forms\Components\TextInput::make('cost')
                        ->numeric()
                        ->prefix('$')
                        ->label('Final Cost'),
                ])
                ->action(function (array $data) {
                    $this->record->complete($data['notes'], $data['cost']);

                    Notification::make()
                        ->title('Maintenance Completed')
                        ->success()
                        ->send();
                }),
        ];
    }
}
