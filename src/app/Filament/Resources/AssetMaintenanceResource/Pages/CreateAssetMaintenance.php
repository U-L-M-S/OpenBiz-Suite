<?php

namespace App\Filament\Resources\AssetMaintenanceResource\Pages;

use App\Filament\Resources\AssetMaintenanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetMaintenance extends CreateRecord
{
    protected static string $resource = AssetMaintenanceResource::class;

    protected static ?string $title = 'Schedule Maintenance';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['reported_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
