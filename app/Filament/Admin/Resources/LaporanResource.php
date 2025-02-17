<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LaporanResource\Pages;
use App\Filament\Admin\Resources\LaporanResource\RelationManagers;
use App\Models\Laporan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Carbon\Carbon;

class LaporanResource extends Resource
{
    protected static ?string $model = Laporan::class;

    protected static ?string $navigationLabel = 'Data Laporan';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        // \Log::info('Nilai auth()->id(): ' . auth()->id());

        return $form
        ->schema([
            TextInput::make('aktifitas')
                ->label('Aktifitas')
                ->placeholder('Masukkan aktivitas pekerjaan')
                ->required(),
            TextInput::make('hari')
                ->label('Hari')
                ->placeholder('Masukkan hari (contoh: Senin)')
                ->required(),
            TextInput::make('tanggal')
                ->label('Tanggal')
                ->placeholder('Masukkan tanggal (contoh: 01)')
                ->required(),
            TextInput::make('bulan')
                ->label('Bulan')
                ->placeholder('Masukkan bulan (contoh: 12)')
                ->required(),
            TextInput::make('tahun')
                ->label('Tahun')
                ->placeholder('Masukkan tahun (contoh: 2025)')
                ->required(),
            Select::make('user_id')
                ->label('Pembuat')
                ->options(
                    \App\Models\User::pluck('name', 'id')->toArray()
                )
                ->default(auth()->id())
                ->required(),
            FileUpload::make('gambar')
                ->image()
                ->imageEditor()
                ->required(),
                // ->directory('laporan'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('aktifitas')
                    ->label('Aktivitas Pekerjaan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('hari')
                    ->label('Hari / Tanggal')
                    ->searchable()
                    ->formatStateUsing(function ($record) {
                        $hari = $record->hari ?? '-';
                        $tanggal = $record->tanggal ?? '00';
                        $bulan = $record->bulan ?? '00';
                        $tahun = $record->tahun ?? '0000';

                        return "{$hari}, {$tanggal}-{$bulan}-{$tahun}";
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('Pembuat'),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->options(
                        \App\Models\User::pluck('name', 'id')->toArray()
                    ),
                Tables\Filters\SelectFilter::make('bulan')
                    ->label('Bulan')
                    ->options(
                        \App\Models\Laporan::select('bulan')
                            ->distinct()
                            ->orderBy('bulan', 'asc')
                            ->pluck('bulan', 'bulan')
                            ->toArray()
                    ),
                
                Tables\Filters\SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(
                        \App\Models\Laporan::pluck('tahun', 'tahun')->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListLaporans::route('/'),
            'create' => Pages\CreateLaporan::route('/create'),
            'edit' => Pages\EditLaporan::route('/{record}/edit'),
        ];
    }

    public static function beforeCreate(array $data): array
    {
        // Tambahkan nilai user_id secara otomatis
        $data['user_id'] = auth()->id();

        \Log::info('Data sebelum disimpan:', $data); // Debugging
        return $data;
    }
}
