<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkflowDefinitionResource\Pages;
use App\Models\WorkflowDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WorkflowDefinitionResource extends Resource
{
    protected static ?string $model = WorkflowDefinition::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Workflows';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Workflow Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set('slug', Str::slug($state) . '-' . Str::random(6))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('entity_type')
                            ->options([
                                'App\\Models\\LeaveRequest' => 'Leave Request',
                                'App\\Models\\Timesheet' => 'Timesheet',
                                'App\\Models\\Order' => 'Order',
                                'App\\Models\\Asset' => 'Asset',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Steps')
                    ->schema([
                        Forms\Components\Repeater::make('steps')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('label')
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'start' => 'Start',
                                        'approval' => 'Approval',
                                        'task' => 'Task',
                                        'notification' => 'Notification',
                                        'end' => 'End',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('assigned_role')
                                    ->relationship('', 'name')
                                    ->options(\Spatie\Permission\Models\Role::pluck('name', 'id')),
                            ])
                            ->columns(4)
                            ->defaultItems(2),
                    ]),

                Forms\Components\Section::make('Transitions')
                    ->schema([
                        Forms\Components\Repeater::make('transitions')
                            ->schema([
                                Forms\Components\TextInput::make('from')
                                    ->required(),
                                Forms\Components\TextInput::make('to')
                                    ->required(),
                                Forms\Components\TextInput::make('label'),
                                Forms\Components\TextInput::make('condition'),
                            ])
                            ->columns(4)
                            ->defaultItems(1),
                    ]),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\TextInput::make('version')
                            ->numeric()
                            ->default(1)
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('entity_type')
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                Tables\Columns\TextColumn::make('instances_count')
                    ->counts('instances')
                    ->label('Instances'),
                Tables\Columns\TextColumn::make('version'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkflowDefinitions::route('/'),
            'create' => Pages\CreateWorkflowDefinition::route('/create'),
            'edit' => Pages\EditWorkflowDefinition::route('/{record}/edit'),
        ];
    }
}
