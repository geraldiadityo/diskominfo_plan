<?php

namespace App\Filament\Resources\RealisasiKegiatans;

use App\Filament\Resources\RealisasiKegiatans\Pages\CreateRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatans\Pages\EditRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatans\Pages\ListRealisasiKegiatans;
use App\Filament\Resources\RealisasiKegiatans\Schemas\RealisasiKegiatanForm;
use App\Filament\Resources\RealisasiKegiatans\Tables\RealisasiKegiatansTable;
use App\Models\RealisasiKegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RealisasiKegiatanResource extends Resource
{
    protected static ?string $model = RealisasiKegiatan::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RealisasiKegiatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RealisasiKegiatansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BuktiFisikRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRealisasiKegiatans::route('/'),
            'create' => CreateRealisasiKegiatan::route('/create'),
            'edit' => EditRealisasiKegiatan::route('/{record}/edit'),
        ];
    }
}
