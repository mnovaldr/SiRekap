<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Data Event';
    protected static ?string $navigationGroup = 'Data Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'judul_kegiatan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kegiatan')
                    ->schema([
                        Forms\Components\TextInput::make('nama_organisasi')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('judul_kegiatan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('bidang')
                            ->options([
                                'Komunitas' => 'Komunitas',
                                'Bisnis' => 'Bisnis',
                                'Akademisi' => 'Akademisi',
                                'Pemerintah' => 'Pemerintah',
                                'Media' => 'Media',
                                'Perorangan' => 'Perorangan',
                                'Institusi Finansial' => 'Institusi Finansial',
                            ])
                            ->required(),
                        Forms\Components\Select::make('jenis_kegiatan')
                            ->options([
                                'Pertunjukan' => 'Pertunjukan',
                                'Meeting' => 'Meeting',
                                'Workshop' => 'Workshop',
                                'Pelatihan' => 'Pelatihan',
                                'Talkshow/Podcast' => 'Talkshow/Podcast',
                                'Take Konten Foto & Video' => 'Take Konten Foto & Video',
                                'Produksi Musik' => 'Produksi Musik',
                                'Pameran' => 'Pameran',
                                'Kunjungan' => 'Kunjungan',
                                'Sosialisasi' => 'Sosialisasi',
                                'Kompetisi' => 'Kompetisi',
                                'Produksi Film' => 'Produksi Film',
                                'Voice Over' => 'Voice Over',
                                'Interview' => 'Interview',
                                'Penelitian' => 'Penelitian',
                                'Produksi Olahan' => 'Produksi Olahan',
                                'Konsultasi' => 'Konsultasi',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('sub_sektor')
                            ->maxLength(100),
                        Forms\Components\Select::make('komersial')
                            ->options([
                                'Komersial' => 'Komersial',
                                'Non Komersial' => 'Non Komersial',
                            ])
                            ->required()
                            ->default('Non Komersial'),
                        Forms\Components\Select::make('program_sdgs')
                            ->label('Program SDGS')
                            ->options(array_combine(
                                array_map(fn($i) => "SDGS $i", range(1, 17)),
                                array_map(fn($i) => "SDGS $i", range(1, 17))
                            ))
                            ->nullable()
                            ->placeholder('Tidak ada / Tidak diisi'),
                    ])->columns(2),

                Forms\Components\Section::make('Waktu & Lokasi')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y'),
                        Forms\Components\Select::make('lantai')
                            ->options([
                                '2' => 'Lantai 2',
                                '3' => 'Lantai 3',
                                '4' => 'Lantai 4',
                                '5' => 'Lantai 5',
                            ])
                            ->required(),
                        Forms\Components\Select::make('lokasi')
                            ->options([
                                'Studio Musik' => 'Studio Musik',
                                'Studio Foto' => 'Studio Foto',
                                'Co-Working Space 1' => 'Co-Working Space 1',
                                'Co-Working Space 2' => 'Co-Working Space 2',
                                'Food Lab' => 'Food Lab',
                                'Multi Purpose Area' => 'Multi Purpose Area',
                                'Amphitheater 1' => 'Amphitheater 1',
                                'Amphitheater 2' => 'Amphitheater 2',
                                'Multifunction Room' => 'Multifunction Room',
                                'Open Public Space Barat' => 'Open Public Space Barat',
                                'Open Public Space Utara' => 'Open Public Space Utara',
                                'Ruang Meeting' => 'Ruang Meeting',
                                'Ruang Fashion' => 'Ruang Fashion',
                                'KIDZONE' => 'KIDZONE',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('jumlah_peserta')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Kontak & Status')
                    ->schema([
                        Forms\Components\TextInput::make('pic')
                            ->label('PIC')
                            ->maxLength(150),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_telepon')
                            ->tel()
                            ->maxLength(50),
                        Forms\Components\Select::make('status_data')
                            ->options([
                                'Valid' => 'Valid',
                                'Review' => 'Review',
                                'Duplikat' => 'Duplikat',
                            ])
                            ->required()
                            ->default('Valid'),
                        Forms\Components\Textarea::make('catatan_review')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_organisasi')
                    ->label('Organisasi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('judul_kegiatan')
                    ->label('Kegiatan')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lokasi')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('lantai')
                    ->label('Lt')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('jumlah_peserta')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status_data')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Valid' => 'success',
                        'Review' => 'warning',
                        'Duplikat' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('bidang')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('program_sdgs')
                    ->label('SDGS')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pic')
                    ->label('PIC')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_data')
                    ->options([
                        'Valid' => 'Valid',
                        'Review' => 'Review',
                        'Duplikat' => 'Duplikat',
                    ]),
                Tables\Filters\SelectFilter::make('bidang')
                    ->options([
                        'Komunitas' => 'Komunitas',
                        'Bisnis' => 'Bisnis',
                        'Akademisi' => 'Akademisi',
                        'Pemerintah' => 'Pemerintah',
                        'Media' => 'Media',
                        'Perorangan' => 'Perorangan',
                        'Institusi Finansial' => 'Institusi Finansial',
                    ]),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari'),
                        Forms\Components\DatePicker::make('sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
