<?php

namespace App\Filament\Resources\ReportDefinitionResource\Pages;

use App\Filament\Resources\ReportDefinitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReportDefinition extends CreateRecord
{
    protected static string $resource = ReportDefinitionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['created_by'] = auth()->id();
        return $data;
    }
}
