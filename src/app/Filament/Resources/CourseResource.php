<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'LMS';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Course Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set('slug', Str::slug($state) . '-' . Str::random(6))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('instructor_id')
                            ->relationship('instructor', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('level')
                            ->options([
                                'beginner' => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced' => 'Advanced',
                            ])
                            ->default('beginner')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->directory('course-thumbnails'),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing & Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_free')
                            ->default(true)
                            ->reactive(),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->visible(fn ($get) => !$get('is_free')),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->numeric()
                            ->suffix('minutes'),
                        Forms\Components\TextInput::make('max_enrollments')
                            ->numeric()
                            ->helperText('Leave empty for unlimited'),
                        Forms\Components\TextInput::make('points_reward')
                            ->numeric()
                            ->default(0)
                            ->helperText('Points awarded on completion'),
                    ])->columns(2),

                Forms\Components\Section::make('Learning Outcomes')
                    ->schema([
                        Forms\Components\Repeater::make('learning_outcomes')
                            ->simple(
                                Forms\Components\TextInput::make('outcome')
                                    ->required()
                            )
                            ->defaultItems(0)
                            ->addActionLabel('Add Learning Outcome'),
                    ]),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->default(false),
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('published_at'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('instructor.name')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('level')
                    ->colors([
                        'success' => 'beginner',
                        'warning' => 'intermediate',
                        'danger' => 'advanced',
                    ]),
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Enrolled'),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ]),
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TernaryFilter::make('is_featured'),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->icon('heroicon-o-globe-alt')
                    ->color('success')
                    ->action(fn (Course $record) => $record->publish())
                    ->visible(fn (Course $record) => !$record->is_published)
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('unpublish')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->action(fn (Course $record) => $record->unpublish())
                    ->visible(fn (Course $record) => $record->is_published)
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
            RelationManagers\ModulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
