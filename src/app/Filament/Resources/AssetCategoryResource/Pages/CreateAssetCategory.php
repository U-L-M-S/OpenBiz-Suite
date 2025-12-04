<?php

namespace App\Filament\Resources\AssetCategoryResource\Pages;

use App\Filament\Resources\AssetCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetCategory extends CreateRecord
{
    protected static string $resource = AssetCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
