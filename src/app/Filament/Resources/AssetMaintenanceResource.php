<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetMaintenanceResource\Pages;
use App\Models\AssetMaintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class AssetMaintenanceResource extends Resource
{
    protected static ?string $model = AssetMaintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Asset Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Maintenance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Maintenance Details')
                    ->schema([
                        Forms\Components\Select::make('asset_id')
                            ->relationship(
                                'asset',
                                'name',
                                fn (Builder $query) => $query->where('tenant_id', auth()->user()->tenant_id)
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->asset_code} - {$record->name}")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Asset')
                            ->disabled(fn ($operation) => $operation === 'edit'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'preventive' => 'Preventive',
                                'corrective' => 'Corrective',
                                'inspection' => 'Inspection',
                                'repair' => 'Repair',
                                'upgrade' => 'Upgrade',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Schedule & Status')
                    ->schema([
                        Forms\Components\DatePicker::make('scheduled_date')
                            ->required()
                            ->label('Scheduled Date'),
                        Forms\Components\DatePicker::make('completed_date')
                            ->label('Completed Date')
                            ->visible(fn ($operation) => $operation === 'edit'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('scheduled')
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Cost & Vendor')
                    ->schema([
                        Forms\Components\TextInput::make('vendor')
                            ->maxLength(255)
                            ->label('Vendor/Service Provider'),
                        Forms\Components\TextInput::make('cost')
                            ->numeric()
                            ->prefix('$')
                            ->label('Cost'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->maxLength(65535),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset.asset_code')
                    ->label('Asset Code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Asset Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'info' => 'preventive',
                        'warning' => 'corrective',
                        'gray' => 'inspection',
                        'danger' => 'repair',
                        'success' => 'upgrade',
                    ]),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_date')
                    ->date()
                    ->sortable()
                    ->placeholder('Not completed'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'scheduled',
                        'info' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('vendor')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cost')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reportedBy.name')
                    ->label('Reported By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'preventive' => 'Preventive',
                        'corrective' => 'Corrective',
                        'inspection' => 'Inspection',
                        'repair' => 'Repair',
                        'upgrade' => 'Upgrade',
                    ]),
                Tables\Filters\SelectFilter::make('asset')
                    ->relationship('asset', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('start')
                    ->label('Start')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'scheduled')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'in_progress']);
                        $record->asset->update(['status' => 'maintenance']);

                        Notification::make()
                            ->title('Maintenance Started')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['scheduled', 'in_progress']))
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
                    ->action(function ($record, array $data) {
                        $record->complete($data['notes'], $data['cost']);

                        Notification::make()
                            ->title('Maintenance Completed')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => in_array($record->status, ['scheduled', 'cancelled'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('tenant_id', auth()->user()->tenant_id);
            })
            ->defaultSort('scheduled_date', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetMaintenances::route('/'),
            'create' => Pages\CreateAssetMaintenance::route('/create'),
            'view' => Pages\ViewAssetMaintenance::route('/{record}'),
            'edit' => Pages\EditAssetMaintenance::route('/{record}/edit'),
        ];
    }
}
