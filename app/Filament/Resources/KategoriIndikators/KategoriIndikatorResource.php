<?php

namespace App\Filament\Resources\KategoriIndikators;

use App\Filament\Resources\KategoriIndikators\Pages\CreateKategoriIndikator;
use App\Filament\Resources\KategoriIndikators\Pages\EditKategoriIndikator;
use App\Filament\Resources\KategoriIndikators\Pages\ListKategoriIndikators;
use App\Filament\Resources\KategoriIndikators\Schemas\KategoriIndikatorForm;
use App\Filament\Resources\KategoriIndikators\Tables\KategoriIndikatorsTable;
use App\Models\KategoriIndikator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class KategoriIndikatorResource extends Resource
{
    protected static ?string $model = KategoriIndikator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ListBullet;
    protected static string|UnitEnum|null $navigationGroup = 'Master Indikator';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Kategori Indikator';

    public static function form(Schema $schema): Schema
    {
        return KategoriIndikatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KategoriIndikatorsTable::configure($table);
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
            'index' => ListKategoriIndikators::route('/'),
            // 'create' => CreateKategoriIndikator::route('/create'),
            // 'edit' => EditKategoriIndikator::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return in_array($user->role, [
            'ADMIN',
            'BAPPEDA'
        ]);
    }
}
