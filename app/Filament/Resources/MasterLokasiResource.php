<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterLokasiResource\Pages;
use App\Filament\Resources\MasterLokasiResource\RelationManagers;
use App\Models\MasterLokasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterLokasiResource extends Resource
{
    protected static ?string $model = MasterLokasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Master Lokasi';
    protected static ?string $modelLabel = 'Master Lokasi';
    protected static ?string $pluralModelLabel = 'Master Lokasi';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('raw_value')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('standard_value')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('frequency')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('raw_value')
                    ->searchable(),
                Tables\Columns\TextColumn::make('standard_value')
                    ->searchable(),
                Tables\Columns\TextColumn::make('frequency')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListMasterLokasis::route('/'),
            'create' => Pages\CreateMasterLokasi::route('/create'),
            'edit' => Pages\EditMasterLokasi::route('/{record}/edit'),
        ];
    }
}
