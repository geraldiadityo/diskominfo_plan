<?php

namespace App\Filament\Resources\RenjaSkpds;

use App\Filament\Resources\RenjaSkpds\Pages\CreateRenjaSkpd;
use App\Filament\Resources\RenjaSkpds\Pages\EditRenjaSkpd;
use App\Filament\Resources\RenjaSkpds\Pages\ListRenjaSkpds;
use App\Filament\Resources\RenjaSkpds\RelationManagers\RealisasiKegiatanRelationManager;
use App\Filament\Resources\RenjaSkpds\Schemas\RenjaSkpdForm;
use App\Filament\Resources\RenjaSkpds\Tables\RenjaSkpdsTable;
use App\Models\RenjaSkpd;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class RenjaSkpdResource extends Resource
{
    protected static ?string $model = RenjaSkpd::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Newspaper;
    protected static string|UnitEnum|null $navigationGroup = 'Evaluasi Dan Pelaporan';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Renja SKPD';
    protected static ?string $modelLabel = 'Renja SKPD';

    public static function form(Schema $schema): Schema
    {
        return RenjaSkpdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RenjaSkpdsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
            RealisasiKegiatanRelationManager::class,
        ];
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with([
            'subKegiatan.kegiatan.program.bidang.urusan'
        ]);

        if (Auth::user()->role === 'SKPD') {
            $query->where('skpd_id', Auth::user()->skpd_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRenjaSkpds::route('/'),
            'create' => CreateRenjaSkpd::route('/create'),
            'edit' => EditRenjaSkpd::route('/{record}/edit'),
        ];
    }
}
