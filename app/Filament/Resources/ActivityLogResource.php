<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Log Aktivitas';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Log Aktivitas';

    // Read-only — tidak perlu create/edit
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                // Waktu
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable()
                    ->timezone('Asia/Jakarta'),

                // Aksi
                Tables\Columns\BadgeColumn::make('action')
                    ->label('Aksi')
                    ->formatStateUsing(fn ($record) => $record->action_label)
                    ->color(fn ($record) => $record->action_color)
                    ->icon(fn ($record) => $record->action_icon)
                    ->searchable(),

                // Deskripsi
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->wrap()
                    ->limit(80),

                // Model
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Objek')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state ?? '-'),

                // User
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->default('System')
                    ->sortable(),

                // IP
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Jenis Aksi')
                    ->options([
                        'create'   => 'Tambah Data',
                        'update'   => 'Edit Data',
                        'delete'   => 'Hapus Data',
                        'import'   => 'Import Excel',
                        'export'   => 'Export Data',
                        'validate' => 'Validasi',
                        'login'    => 'Login',
                        'logout'   => 'Logout',
                        'db_clean' => 'Bersihkan DB',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Dari'),
                        Forms\Components\DatePicker::make('sampai')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['dari'],    fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['sampai'],  fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari'])   $indicators[] = 'Dari: ' . $data['dari'];
                        if ($data['sampai']) $indicators[] = 'Sampai: ' . $data['sampai'];
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Detail Log: ' . $record->action_label)
                    ->modalContent(fn ($record) => view('filament.modals.activity-log-detail', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->bulkActions([])
            ->poll('30s'); // auto-refresh setiap 30 detik
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Badge: jumlah log hari ini
        $count = static::getModel()::whereDate('created_at', today())->count();
        return $count > 0 ? (string) $count : null;
    }
}
