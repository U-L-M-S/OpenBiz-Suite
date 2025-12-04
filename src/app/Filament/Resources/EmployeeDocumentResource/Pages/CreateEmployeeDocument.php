<?php

namespace App\Filament\Resources\EmployeeDocumentResource\Pages;

use App\Filament\Resources\EmployeeDocumentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateEmployeeDocument extends CreateRecord
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['uploaded_by'] = auth()->id();

        // Extract file information
        if (isset($data['file_path'])) {
            $filePath = $data['file_path'];
            $data['file_name'] = basename($filePath);
            $data['mime_type'] = Storage::mimeType($filePath);
            $data['file_size'] = Storage::size($filePath);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
