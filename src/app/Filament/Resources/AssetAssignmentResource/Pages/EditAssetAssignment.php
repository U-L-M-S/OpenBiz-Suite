<?php

namespace App\Filament\Resources\AssetAssignmentResource\Pages;

use App\Filament\Resources\AssetAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetAssignment extends EditRecord
{
    protected static string $resource = AssetAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status !== 'active'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
