<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\HasilLaporanResource\Pages;
use App\Filament\Admin\Resources\HasilLaporanResource\RelationManagers;
use App\Models\HasilLaporan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HasilLaporanResource extends Resource
{
    protected static ?string $model = HasilLaporan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Hasil Laporan';
    protected static ?string $pluralLabel = 'Hasil Laporan';
    protected static ?string $slug = 'hasil-laporan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\TextInput::make('hari')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tanggal')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bulan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tahun')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('aktifitas')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('lampiran')
                    ->multiple()
                    ->directory('lampiran')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Disusun Oleh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hari')
                    ->description(fn ($record): string => "{$record->tanggal}/{$record->bulan}/{$record->tahun}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('aktifitas')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\ImageColumn::make('lampiran')
                    ->stacked()
                    ->circular(),
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
            ->defaultSort('id', 'asc')
            ->modifyQueryUsing(function (Builder $query) {
                return $query->orderByRaw('CAST(tahun AS UNSIGNED) ASC')
                             ->orderByRaw('CAST(bulan AS UNSIGNED) ASC')
                             ->orderByRaw('CAST(tanggal AS UNSIGNED) ASC');
            })
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
            'index' => Pages\ListHasilLaporans::route('/'),
            'create' => Pages\CreateHasilLaporan::route('/create'),
            'edit' => Pages\EditHasilLaporan::route('/{record}/edit'),
        ];
    }
}
