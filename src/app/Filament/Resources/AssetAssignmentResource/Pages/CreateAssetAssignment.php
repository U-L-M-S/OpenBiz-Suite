<?php

namespace App\Filament\Resources\AssetAssignmentResource\Pages;

use App\Filament\Resources\AssetAssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetAssignment extends CreateRecord
{
    protected static string $resource = AssetAssignmentResource::class;

    protected static ?string $title = 'Assign Asset';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['assigned_by'] = auth()->id();
        $data['status'] = 'active';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Update asset status to assigned
        $this->record->asset->update(['status' => 'assigned']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
