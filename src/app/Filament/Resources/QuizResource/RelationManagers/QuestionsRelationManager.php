<?php

namespace App\Filament\Resources\QuizResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True/False',
                        'short_answer' => 'Short Answer',
                        'essay' => 'Essay',
                    ])
                    ->default('multiple_choice')
                    ->required()
                    ->reactive(),
                Forms\Components\Textarea::make('question')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('points')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('explanation')
                    ->rows(2)
                    ->columnSpanFull()
                    ->helperText('Shown after answering'),

                Forms\Components\Repeater::make('answers')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('answer')
                            ->required(),
                        Forms\Components\Toggle::make('is_correct')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->defaultItems(4)
                    ->visible(fn ($get) => in_array($get('type'), ['multiple_choice', 'true_false']))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('question')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'multiple_choice',
                        'success' => 'true_false',
                        'warning' => 'short_answer',
                        'info' => 'essay',
                    ]),
                Tables\Columns\TextColumn::make('points'),
                Tables\Columns\TextColumn::make('answers_count')
                    ->counts('answers')
                    ->label('Answers'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
