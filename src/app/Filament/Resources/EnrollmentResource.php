<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'LMS';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Enrollment Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'enrolled' => 'Enrolled',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'dropped' => 'Dropped',
                            ])
                            ->default('enrolled')
                            ->required(),
                        Forms\Components\TextInput::make('progress_percentage')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Dates')
                    ->schema([
                        Forms\Components\DateTimePicker::make('enrolled_at')
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('started_at'),
                        Forms\Components\DateTimePicker::make('completed_at'),
                        Forms\Components\DateTimePicker::make('expires_at'),
                    ])->columns(2),

                Forms\Components\Section::make('Payment')
                    ->schema([
                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'enrolled',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'dropped',
                    ]),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrolled_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->placeholder('Not completed'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'enrolled' => 'Enrolled',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'dropped' => 'Dropped',
                    ]),
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title'),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Enrollment $record) => $record->complete())
                    ->visible(fn (Enrollment $record) => $record->status !== 'completed')
                    ->requiresConfirmation(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'view' => Pages\ViewEnrollment::route('/{record}'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
