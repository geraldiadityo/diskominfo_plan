<?php

namespace App\Filament\Resources\ProgramSubKegiatans;

use App\Filament\Resources\ProgramSubKegiatans\Pages\CreateProgramSubKegiatan;
use App\Filament\Resources\ProgramSubKegiatans\Pages\EditProgramSubKegiatan;
use App\Filament\Resources\ProgramSubKegiatans\Pages\ListProgramSubKegiatans;
use App\Filament\Resources\ProgramSubKegiatans\Schemas\ProgramSubKegiatanForm;
use App\Filament\Resources\ProgramSubKegiatans\Tables\ProgramSubKegiatansTable;
use App\Models\ProgramSubKegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProgramSubKegiatanResource extends Resource
{
    protected static ?string $model = ProgramSubKegiatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Sub-Kegiatan';
    protected static ?string $modelLabel = 'sub-kegiatan';
    protected static ?string $pluralLabel = 'sub-kegiatan';
    protected static string|UnitEnum|null $navigationGroup = 'Master Program Kegiatan';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'nomenklatur';
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'kode_lengkap',
            'nomenklatur'
        ];
    }


    public static function form(Schema $schema): Schema
    {
        return ProgramSubKegiatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramSubKegiatansTable::configure($table);
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
            'index' => ListProgramSubKegiatans::route('/'),
            'create' => CreateProgramSubKegiatan::route('/create'),
            'edit' => EditProgramSubKegiatan::route('/{record}/edit'),
        ];
    }
}
