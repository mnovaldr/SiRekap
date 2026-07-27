<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizerResource\Pages;
use App\Filament\Resources\OrganizerResource\RelationManagers;
use App\Models\Organizer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrganizerResource extends Resource
{
    protected static ?string $model = Organizer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Penyelenggara';
    protected static ?string $modelLabel = 'Penyelenggara';
    protected static ?string $pluralModelLabel = 'Data Penyelenggara';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bidang')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_telepon')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_events')
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
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bidang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_events')
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
                    Tables\Actions\BulkAction::make('merge')
                        ->label('Gabungkan Data (Merge)')
                        ->icon('heroicon-o-arrows-pointing-in')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Gabungkan Penyelenggara')
                        ->modalDescription('Pilih nama penyelenggara utama. Semua event dari penyelenggara yang digabungkan akan dipindahkan ke nama utama ini, dan data ganda akan dihapus.')
                        ->form(function (\Illuminate\Database\Eloquent\Collection $records) {
                            return [
                                Forms\Components\Select::make('primary_organizer_id')
                                    ->label('Penyelenggara Utama')
                                    ->options($records->pluck('nama', 'id'))
                                    ->required(),
                            ];
                        })
                        ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records): void {
                            $primaryId = $data['primary_organizer_id'];
                            $primaryOrganizer = $records->firstWhere('id', $primaryId);
                            
                            if (!$primaryOrganizer) return;

                            $duplicates = $records->filter(fn ($record) => $record->id != $primaryId);
                            
                            foreach ($duplicates as $duplicate) {
                                // Pindahkan semua event ke nama organisasi utama
                                \App\Models\Event::where('nama_organisasi', $duplicate->nama)
                                    ->update(['nama_organisasi' => $primaryOrganizer->nama]);
                                    
                                // Hapus data penyelenggara duplikat
                                $duplicate->delete();
                            }
                            
                            // Hitung ulang total events untuk penyelenggara utama
                            $primaryOrganizer->total_events = \App\Models\Event::where('nama_organisasi', $primaryOrganizer->nama)->count();
                            $primaryOrganizer->save();
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil digabungkan')
                                ->body($duplicates->count() . ' data penyelenggara telah digabungkan ke "' . $primaryOrganizer->nama . '".')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListOrganizers::route('/'),
            'create' => Pages\CreateOrganizer::route('/create'),
            'edit' => Pages\EditOrganizer::route('/{record}/edit'),
        ];
    }
}
