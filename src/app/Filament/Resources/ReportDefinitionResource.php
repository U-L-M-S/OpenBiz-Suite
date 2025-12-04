<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportDefinitionResource\Pages;
use App\Models\ReportDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ReportDefinitionResource extends Resource
{
    protected static ?string $model = ReportDefinition::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Details')
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
                        Forms\Components\Select::make('type')
                            ->options([
                                'table' => 'Table',
                                'chart' => 'Chart',
                                'summary' => 'Summary',
                                'export' => 'Export Only',
                            ])
                            ->default('table')
                            ->required(),
                        Forms\Components\Select::make('data_source')
                            ->options([
                                'employees' => 'Employees',
                                'timesheets' => 'Timesheets',
                                'leave_requests' => 'Leave Requests',
                                'assets' => 'Assets',
                                'courses' => 'Courses',
                                'enrollments' => 'Enrollments',
                                'orders' => 'Orders',
                                'products' => 'Products',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\KeyValue::make('columns')
                            ->keyLabel('Column Key')
                            ->valueLabel('Column Label'),
                        Forms\Components\KeyValue::make('filters')
                            ->keyLabel('Filter Key')
                            ->valueLabel('Filter Type'),
                    ])->columns(2),

                Forms\Components\Section::make('Chart Configuration')
                    ->schema([
                        Forms\Components\Select::make('chart_config.type')
                            ->options([
                                'bar' => 'Bar Chart',
                                'line' => 'Line Chart',
                                'pie' => 'Pie Chart',
                                'doughnut' => 'Doughnut Chart',
                            ])
                            ->visible(fn ($get) => $get('type') === 'chart'),
                        Forms\Components\TextInput::make('chart_config.x_axis')
                            ->visible(fn ($get) => $get('type') === 'chart'),
                        Forms\Components\TextInput::make('chart_config.y_axis')
                            ->visible(fn ($get) => $get('type') === 'chart'),
                    ])->columns(3),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_public')
                            ->default(false),
                        Forms\Components\Toggle::make('is_scheduled')
                            ->default(false)
                            ->reactive(),
                        Forms\Components\TextInput::make('schedule_cron')
                            ->visible(fn ($get) => $get('is_scheduled'))
                            ->placeholder('0 8 * * 1'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'table',
                        'success' => 'chart',
                        'warning' => 'summary',
                        'info' => 'export',
                    ]),
                Tables\Columns\TextColumn::make('data_source'),
                Tables\Columns\TextColumn::make('exports_count')
                    ->counts('exports')
                    ->label('Exports'),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_scheduled')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'table' => 'Table',
                        'chart' => 'Chart',
                        'summary' => 'Summary',
                        'export' => 'Export',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        Forms\Components\Select::make('format')
                            ->options([
                                'csv' => 'CSV',
                                'xlsx' => 'Excel',
                                'pdf' => 'PDF',
                                'json' => 'JSON',
                            ])
                            ->default('csv')
                            ->required(),
                    ])
                    ->action(function (ReportDefinition $record, array $data) {
                        $record->export(auth()->user(), $data['format']);
                    }),
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
            'index' => Pages\ListReportDefinitions::route('/'),
            'create' => Pages\CreateReportDefinition::route('/create'),
            'edit' => Pages\EditReportDefinition::route('/{record}/edit'),
        ];
    }
}
