<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetAssignmentResource\Pages;
use App\Models\AssetAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class AssetAssignmentResource extends Resource
{
    protected static ?string $model = AssetAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Asset Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Assignments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Assignment Details')
                    ->schema([
                        Forms\Components\Select::make('asset_id')
                            ->relationship(
                                'asset',
                                'name',
                                fn (Builder $query) => $query
                                    ->where('tenant_id', auth()->user()->tenant_id)
                                    ->where('status', 'available')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->asset_code} - {$record->name}")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Asset')
                            ->disabled(fn ($operation) => $operation === 'edit'),
                        Forms\Components\Select::make('employee_id')
                            ->relationship(
                                'employee',
                                'first_name',
                                fn (Builder $query) => $query->where('tenant_id', auth()->user()->tenant_id)
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->employee_id} - {$record->first_name} {$record->last_name}")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Employee')
                            ->disabled(fn ($operation) => $operation === 'edit'),
                        Forms\Components\DatePicker::make('assigned_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('return_date')
                            ->label('Expected Return Date'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('assignment_notes')
                            ->label('Assignment Notes')
                            ->rows(3)
                            ->maxLength(65535),
                        Forms\Components\Textarea::make('return_notes')
                            ->label('Return Notes')
                            ->rows(3)
                            ->maxLength(65535)
                            ->visible(fn ($operation) => $operation === 'edit'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'returned' => 'Returned',
                                'lost' => 'Lost',
                                'damaged' => 'Damaged',
                            ])
                            ->default('active')
                            ->required()
                            ->disabled(fn ($operation) => $operation === 'create'),
                    ])
                    ->visible(fn ($operation) => $operation === 'edit'),
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
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}"),
                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->label('Assigned By')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assigned_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_date')
                    ->date()
                    ->sortable()
                    ->placeholder('Not returned'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'gray' => 'returned',
                        'danger' => 'lost',
                        'warning' => 'damaged',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'returned' => 'Returned',
                        'lost' => 'Lost',
                        'damaged' => 'Damaged',
                    ]),
                Tables\Filters\SelectFilter::make('asset')
                    ->relationship('asset', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('return')
                    ->label('Return Asset')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'active')
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
                    ->action(function ($record, array $data) {
                        $record->returnAsset($data['return_notes'], $data['status']);

                        Notification::make()
                            ->title('Asset Returned')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->status !== 'active'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('tenant_id', auth()->user()->tenant_id);
            })
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListAssetAssignments::route('/'),
            'create' => Pages\CreateAssetAssignment::route('/create'),
            'view' => Pages\ViewAssetAssignment::route('/{record}'),
            'edit' => Pages\EditAssetAssignment::route('/{record}/edit'),
        ];
    }
}
