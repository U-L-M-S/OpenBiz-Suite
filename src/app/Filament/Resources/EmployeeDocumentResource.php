<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeDocumentResource\Pages;
use App\Models\EmployeeDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeDocumentResource extends Resource
{
    protected static ?string $model = EmployeeDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Documents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Document Information')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Document Name'),
                        Forms\Components\Select::make('document_type')
                            ->options([
                                'ID Card' => 'ID Card',
                                'Passport' => 'Passport',
                                'Driver License' => 'Driver License',
                                'Certificate' => 'Certificate',
                                'Contract' => 'Contract',
                                'Resume' => 'Resume',
                                'Tax Form' => 'Tax Form',
                                'Bank Details' => 'Bank Details',
                                'Medical' => 'Medical',
                                'Other' => 'Other',
                            ])
                            ->required()
                            ->searchable(),
                        Forms\Components\FileUpload::make('file_path')
                            ->required()
                            ->directory('employee-documents')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240)
                            ->label('File')
                            ->downloadable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Details')
                    ->schema([
                        Forms\Components\DatePicker::make('issue_date')
                            ->label('Issue Date'),
                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->after('issue_date'),
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Verified')
                            ->default(false),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Document'),
                Tables\Columns\TextColumn::make('document_type')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_size_for_humans')
                    ->label('Size'),
                Tables\Columns\TextColumn::make('issue_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->isExpired() ? 'danger' : ($record->isExpiringSoon() ? 'warning' : null)),
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified'),
                Tables\Columns\TextColumn::make('uploadedBy.name')
                    ->label('Uploaded By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name'),
                Tables\Filters\SelectFilter::make('document_type')
                    ->options([
                        'ID Card' => 'ID Card',
                        'Passport' => 'Passport',
                        'Driver License' => 'Driver License',
                        'Certificate' => 'Certificate',
                        'Contract' => 'Contract',
                        'Resume' => 'Resume',
                        'Tax Form' => 'Tax Form',
                        'Bank Details' => 'Bank Details',
                        'Medical' => 'Medical',
                        'Other' => 'Other',
                    ]),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Verified'),
                Tables\Filters\Filter::make('expiring_soon')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '>', now())
                        ->whereDate('expiry_date', '<=', now()->addDays(30)))
                    ->label('Expiring Soon'),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '<', now()))
                    ->label('Expired'),
            ])
            ->actions([
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
            'index' => Pages\ListEmployeeDocuments::route('/'),
            'create' => Pages\CreateEmployeeDocument::route('/create'),
            'view' => Pages\ViewEmployeeDocument::route('/{record}'),
            'edit' => Pages\EditEmployeeDocument::route('/{record}/edit'),
        ];
    }
}
