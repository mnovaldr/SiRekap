<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterKategoriResource\Pages;
use App\Filament\Resources\MasterKategoriResource\RelationManagers;
use App\Models\MasterKategori;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterKategoriResource extends Resource
{
    protected static ?string $model = MasterKategori::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Master Kategori';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;
    protected static ?string $pluralLabel = 'Master Kategori';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
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
            'index' => Pages\ListMasterKategoris::route('/'),
            'create' => Pages\CreateMasterKategori::route('/create'),
            'edit' => Pages\EditMasterKategori::route('/{record}/edit'),
        ];
    }
}
