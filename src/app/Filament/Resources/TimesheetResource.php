<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimesheetResource\Pages;
use App\Models\Timesheet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class TimesheetResource extends Resource
{
    protected static ?string $model = Timesheet::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Timesheet Information')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($context) => $context === 'edit'),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now())
                            ->disabled(fn ($context) => $context === 'edit'),
                        Forms\Components\TimePicker::make('clock_in')
                            ->required()
                            ->seconds(false),
                        Forms\Components\TimePicker::make('clock_out')
                            ->seconds(false)
                            ->after('clock_in'),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Approval Information')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('draft')
                            ->disabled(),
                        Forms\Components\TextInput::make('hours_worked')
                            ->numeric()
                            ->disabled()
                            ->suffix('hours'),
                        Forms\Components\TextInput::make('overtime_hours')
                            ->numeric()
                            ->disabled()
                            ->suffix('hours'),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->rows(2)
                            ->maxLength(65535)
                            ->disabled()
                            ->hidden(fn ($record) => !$record || $record->status !== 'rejected')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->hidden(fn ($context) => $context === 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->searchable()
                    ->sortable()
                    ->label('Employee'),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_in')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_out')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hours_worked')
                    ->numeric(2)
                    ->suffix(' hrs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('overtime_hours')
                    ->numeric(2)
                    ->suffix(' hrs')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'submitted',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Approved By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Timesheet $record) {
                        $record->approve(auth()->user());
                        Notification::make()
                            ->success()
                            ->title('Timesheet approved')
                            ->send();
                    })
                    ->visible(fn (Timesheet $record) => $record->status === 'submitted' && auth()->user()->can('timesheets.approve')),

                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->label('Rejection Reason'),
                    ])
                    ->action(function (Timesheet $record, array $data) {
                        $record->reject(auth()->user(), $data['reason']);
                        Notification::make()
                            ->warning()
                            ->title('Timesheet rejected')
                            ->send();
                    })
                    ->visible(fn (Timesheet $record) => $record->status === 'submitted' && auth()->user()->can('timesheets.approve')),

                Tables\Actions\Action::make('submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function (Timesheet $record) {
                        $record->submit();
                        Notification::make()
                            ->success()
                            ->title('Timesheet submitted for approval')
                            ->send();
                    })
                    ->visible(fn (Timesheet $record) => $record->status === 'draft'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('tenant_id', auth()->user()->tenant_id);
            });
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
            'index' => Pages\ListTimesheets::route('/'),
            'create' => Pages\CreateTimesheet::route('/create'),
            'edit' => Pages\EditTimesheet::route('/{record}/edit'),
        ];
    }
}
