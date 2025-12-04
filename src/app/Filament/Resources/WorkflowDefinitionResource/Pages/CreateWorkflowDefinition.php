<?php

namespace App\Filament\Resources\WorkflowDefinitionResource\Pages;

use App\Filament\Resources\WorkflowDefinitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkflowDefinition extends CreateRecord
{
    protected static string $resource = WorkflowDefinitionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        return $data;
    }
}
