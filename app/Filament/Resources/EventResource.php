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
                        Forms\Components\Select::make('autofill_event_id')
                            ->label('Cari & Duplikasi Event Sebelumnya')
                            ->placeholder('Ketik nama organisasi atau judul kegiatan untuk duplikasi data...')
                            ->searchable()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Event::query()
                                    ->where('nama_organisasi', 'ilike', "%{$search}%")
                                    ->orWhere('judul_kegiatan', 'ilike', "%{$search}%")
                                    ->limit(15)
                                    ->get()
                                    ->mapWithKeys(fn ($event) => [
                                        $event->id => "[{$event->nama_organisasi}] - {$event->judul_kegiatan} (" . ($event->tanggal ? $event->tanggal->format('d M Y') : '-') . ")"
                                    ]);
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (!$state) return;
                                
                                $event = \App\Models\Event::find($state);
                                if (!$event) return;

                                $set('nama_organisasi', $event->nama_organisasi);
                                $set('judul_kegiatan', $event->judul_kegiatan);
                                $set('bidang', $event->bidang);
                                $set('jenis_kegiatan', $event->jenis_kegiatan);
                                $set('sub_sektor', $event->sub_sektor);
                                $set('komersial', $event->komersial);
                                
                                // SDGs parsing
                                $sdgs = [];
                                if ($event->program_sdgs) {
                                    $sdgs = array_filter(array_map('trim', preg_split('/[;,]/', $event->program_sdgs)));
                                }
                                $set('program_sdgs', $sdgs);
                                
                                $set('lantai', $event->lantai);
                                $set('lokasi', $event->lokasi);
                                $set('jumlah_peserta', $event->jumlah_peserta);
                                $set('pic', $event->pic);
                                $set('email', $event->email);
                                $set('no_telepon', $event->no_telepon);
                            }),
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
                            ->label('Program SDGs')
                            ->multiple()
                            ->options(array_combine(
                                array_map(fn($i) => "SDGS $i", range(1, 17)),
                                array_map(fn($i) => "SDGS $i", range(1, 17))
                            ))
                            ->placeholder('Pilih Program SDGs (bisa pilih lebih dari satu)')
                            ->formatStateUsing(function ($state) {
                                if (is_array($state)) return $state;
                                if (!$state) return [];
                                return array_filter(array_map('trim', preg_split('/[;,]/', $state)));
                            })
                            ->dehydrateStateUsing(function ($state) {
                                if (is_array($state)) {
                                    return implode('; ', array_filter($state));
                                }
                                return $state;
                            }),
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
                    ->separator('; ')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pic')
                    ->label('PIC')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_data')
                    ->label('Status Data')
                    ->options([
                        'Valid' => 'Valid',
                        'Review' => 'Review',
                        'Duplikat' => 'Duplikat',
                        'Dihapus' => 'Dihapus',
                        'Diarsipkan' => 'Diarsipkan',
                    ]),
                Tables\Filters\SelectFilter::make('bidang')
                    ->label('Bidang')
                    ->options([
                        'Komunitas' => 'Komunitas',
                        'Bisnis' => 'Bisnis',
                        'Akademisi' => 'Akademisi',
                        'Pemerintah' => 'Pemerintah',
                        'Media' => 'Media',
                        'Perorangan' => 'Perorangan',
                        'Institusi Finansial' => 'Institusi Finansial',
                    ]),
                Tables\Filters\SelectFilter::make('sub_sektor')
                    ->label('Kategori Ekraf')
                    ->options(fn () => Event::whereNotNull('sub_sektor')
                        ->where('sub_sektor', '!=', '')
                        ->distinct()
                        ->orderBy('sub_sektor')
                        ->pluck('sub_sektor', 'sub_sektor')
                        ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('jenis_kegiatan')
                    ->label('Jenis Kegiatan')
                    ->options(fn () => Event::whereNotNull('jenis_kegiatan')
                        ->where('jenis_kegiatan', '!=', '')
                        ->distinct()
                        ->orderBy('jenis_kegiatan')
                        ->pluck('jenis_kegiatan', 'jenis_kegiatan')
                        ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('komersial')
                    ->label('Komersial / Non-Komersial')
                    ->options([
                        'Komersial' => 'Komersial',
                        'Non Komersial' => 'Non Komersial',
                    ]),
                Tables\Filters\SelectFilter::make('sdg')
                    ->label('SDGs')
                    ->options(array_combine(
                        array_map(fn($i) => "SDGS $i", range(1, 17)),
                        array_map(fn($i) => "SDGS $i", range(1, 17))
                    ))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->bySdg($value)
                        );
                    }),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Dari'),
                        Forms\Components\DatePicker::make('sampai')->label('Sampai'),
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
                Tables\Actions\Action::make('delete')
                    ->label('Hapus')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Event (Ubah Status)')
                    ->modalDescription('Apakah Anda yakin ingin menghapus event ini? Tindakan ini akan mengubah status data menjadi Dihapus agar tetap dapat diaudit.')
                    ->form([
                        Forms\Components\Textarea::make('alasan_dihapus')
                            ->label('Alasan Penghapusan')
                            ->placeholder('Tuliskan alasan mengapa data ini dihapus...')
                            ->required(),
                    ])
                    ->action(function (Event $record, array $data): void {
                        $record->update([
                            'status_data' => 'Dihapus',
                            'alasan_dihapus' => $data['alasan_dihapus'],
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Data berhasil dipindahkan ke Sampah')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Event $record): bool => $record->status_data !== 'Dihapus'),
                Tables\Actions\Action::make('restore')
                    ->label('Pulihkan')
                    ->color('success')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->visible(fn (Event $record): bool => in_array($record->status_data, ['Dihapus', 'Diarsipkan']))
                    ->action(function (Event $record): void {
                        $record->update([
                            'status_data' => 'Valid',
                            'alasan_dihapus' => null,
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Data berhasil dipulihkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('delete_status')
                        ->label('Hapus Terpilih')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Event Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data event terpilih? Status data akan diubah menjadi Dihapus.')
                        ->form([
                            Forms\Components\Textarea::make('alasan_dihapus')
                                ->label('Alasan Penghapusan Massal')
                                ->placeholder('Tuliskan alasan mengapa data ini dihapus...')
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                if ($record->status_data !== 'Dihapus') {
                                    $record->update([
                                        'status_data' => 'Dihapus',
                                        'alasan_dihapus' => $data['alasan_dihapus'],
                                    ]);
                                }
                            });
                            \Filament\Notifications\Notification::make()
                                ->title('Data terpilih berhasil dipindahkan ke Sampah')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('restore_status')
                        ->label('Pulihkan Terpilih')
                        ->color('success')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Support\Collection $records): void {
                            $records->each(function ($record) {
                                if (in_array($record->status_data, ['Dihapus', 'Diarsipkan'])) {
                                    $record->update([
                                        'status_data' => 'Valid',
                                        'alasan_dihapus' => null,
                                    ]);
                                }
                            });
                            \Filament\Notifications\Notification::make()
                                ->title('Data terpilih berhasil dipulihkan')
                                ->success()
                                ->send();
                        }),
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
