<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TandaTanganResource\Pages;
use App\Filament\Admin\Resources\TandaTanganResource\RelationManagers;
use App\Models\TandaTangan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TandaTanganResource extends Resource
{
    protected static ?string $model = TandaTangan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Lengkap')
                ->required(),
            Forms\Components\TextInput::make('jabatan')
                ->label('Jabatan')
                ->required(),
            Forms\Components\TextInput::make('nip')
                ->label('NIP'),
            Forms\Components\Select::make('type')
                ->label('Tipe Tanda Tangan')
                ->options([
                    'pembuat' => 'Pembuat',
                    'verifikator' => 'Verifikator',
                    'persetujuan' => 'Persetujuan',
                ])
                ->required(),
        ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe TTD')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'pembuat' => 'Pembuat',
                            'verifikator' => 'Verifikator',
                            'persetujuan' => 'Persetujuan',
                            default => '-',
                        };
                    }),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListTandaTangans::route('/'),
            'create' => Pages\CreateTandaTangan::route('/create'),
            'edit' => Pages\EditTandaTangan::route('/{record}/edit'),
        ];
    }
}
