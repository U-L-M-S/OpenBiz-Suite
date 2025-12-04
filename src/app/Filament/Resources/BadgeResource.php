<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeResource\Pages;
use App\Models\Badge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BadgeResource extends Resource
{
    protected static ?string $model = Badge::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'LMS';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Badge Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set('slug', Str::slug($state) . '-' . Str::random(6))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('type')
                            ->options([
                                'course_completion' => 'Course Completion',
                                'quiz_score' => 'Quiz Score',
                                'streak' => 'Learning Streak',
                                'points' => 'Points Milestone',
                                'custom' => 'Custom',
                            ])
                            ->default('custom')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Appearance')
                    ->schema([
                        Forms\Components\TextInput::make('icon')
                            ->maxLength(255)
                            ->helperText('Heroicon name (e.g., heroicon-o-star)'),
                        Forms\Components\ColorPicker::make('color')
                            ->default('#3b82f6'),
                    ])->columns(2),

                Forms\Components\Section::make('Rewards & Settings')
                    ->schema([
                        Forms\Components\TextInput::make('points_reward')
                            ->numeric()
                            ->default(0)
                            ->helperText('Points awarded when badge is earned'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\KeyValue::make('criteria')
                            ->keyLabel('Criteria Key')
                            ->valueLabel('Criteria Value')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'course_completion',
                        'warning' => 'quiz_score',
                        'primary' => 'streak',
                        'info' => 'points',
                        'gray' => 'custom',
                    ]),
                Tables\Columns\TextColumn::make('points_reward')
                    ->label('Points'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Awarded'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'course_completion' => 'Course Completion',
                        'quiz_score' => 'Quiz Score',
                        'streak' => 'Learning Streak',
                        'points' => 'Points Milestone',
                        'custom' => 'Custom',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'view' => Pages\ViewBadge::route('/{record}'),
            'edit' => Pages\EditBadge::route('/{record}/edit'),
        ];
    }
}
