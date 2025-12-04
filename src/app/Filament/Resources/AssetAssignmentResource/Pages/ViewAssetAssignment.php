<?php

namespace App\Filament\Resources\AssetAssignmentResource\Pages;

use App\Filament\Resources\AssetAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Notifications\Notification;

class ViewAssetAssignment extends ViewRecord
{
    protected static string $resource = AssetAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('return')
                ->label('Return Asset')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'active')
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('return_notes')
                        ->label('Return Notes')
                        ->rows(3),
                    Forms\Components\Select::make('status')
                        ->options([
                            'returned' => 'Returned (Good Condition)',
                            'damaged' => 'Returned (Damaged)',
                        ])
                        ->default('returned')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->returnAsset($data['return_notes'], $data['status']);

                    Notification::make()
                        ->title('Asset Returned')
                        ->success()
                        ->send();
                }),
        ];
    }
}
