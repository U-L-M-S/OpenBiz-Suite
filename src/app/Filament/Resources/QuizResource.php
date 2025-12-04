<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Filament\Resources\QuizResource\RelationManagers;
use App\Models\Quiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'LMS';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Quiz Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'title')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('lesson_id')
                            ->relationship('lesson', 'title')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\TextInput::make('time_limit_minutes')
                            ->numeric()
                            ->suffix('minutes')
                            ->helperText('Leave empty for no time limit'),
                        Forms\Components\TextInput::make('passing_score')
                            ->numeric()
                            ->default(70)
                            ->suffix('%')
                            ->required(),
                        Forms\Components\TextInput::make('max_attempts')
                            ->numeric()
                            ->helperText('Leave empty for unlimited'),
                        Forms\Components\TextInput::make('points_reward')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('shuffle_questions')
                            ->default(false),
                        Forms\Components\Toggle::make('show_correct_answers')
                            ->default(true),
                        Forms\Components\Toggle::make('is_published')
                            ->default(false),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Questions'),
                Tables\Columns\TextColumn::make('passing_score')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('time_limit_minutes')
                    ->suffix(' min')
                    ->placeholder('No limit'),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
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
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'view' => Pages\ViewQuiz::route('/{record}'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}
