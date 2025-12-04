<?php

namespace App\Filament\Resources\ReportDefinitionResource\Pages;

use App\Filament\Resources\ReportDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportDefinitions extends ListRecords
{
    protected static string $resource = ReportDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
