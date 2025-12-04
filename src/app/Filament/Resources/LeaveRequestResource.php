<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Leave Requests';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Leave Request Information')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($context) => $context === 'edit'),
                        Forms\Components\Select::make('leave_type_id')
                            ->relationship('leaveType', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->native(false)
                            ->after('start_date'),
                        Forms\Components\Textarea::make('reason')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Information')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_days')
                            ->numeric()
                            ->disabled()
                            ->suffix('days'),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->rows(2)
                            ->maxLength(65535)
                            ->disabled()
                            ->hidden(fn ($record) => !$record || $record->status !== 'rejected')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
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
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->badge()
                    ->color(fn ($record) => $record->leaveType->color ?? 'gray')
                    ->label('Type'),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_days')
                    ->numeric()
                    ->suffix(' days')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Approved By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name'),
                Tables\Filters\SelectFilter::make('leave_type')
                    ->relationship('leaveType', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (LeaveRequest $record) {
                        $record->approve(auth()->user());
                        Notification::make()
                            ->success()
                            ->title('Leave request approved')
                            ->send();
                    })
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending' && auth()->user()->can('leave.approve')),

                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->label('Rejection Reason'),
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $record->reject(auth()->user(), $data['reason']);
                        Notification::make()
                            ->warning()
                            ->title('Leave request rejected')
                            ->send();
                    })
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending' && auth()->user()->can('leave.approve')),

                Tables\Actions\Action::make('cancel')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (LeaveRequest $record) {
                        $record->cancel();
                        Notification::make()
                            ->info()
                            ->title('Leave request cancelled')
                            ->send();
                    })
                    ->visible(fn (LeaveRequest $record) => in_array($record->status, ['pending', 'approved'])),

                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'view' => Pages\ViewLeaveRequest::route('/{record}'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
