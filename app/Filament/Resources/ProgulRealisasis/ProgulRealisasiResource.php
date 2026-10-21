<?php

namespace App\Filament\Resources\ProgulRealisasis;

use App\Filament\Resources\ProgulRealisasis\Pages\CreateProgulRealisasi;
use App\Filament\Resources\ProgulRealisasis\Pages\EditProgulRealisasi;
use App\Filament\Resources\ProgulRealisasis\Pages\ListProgulRealisasis;
use App\Filament\Resources\ProgulRealisasis\RelationManagers\BuktiDukungRelationManager;
use App\Filament\Resources\ProgulRealisasis\Schemas\ProgulRealisasiForm;
use App\Filament\Resources\ProgulRealisasis\Tables\ProgulRealisasisTable;
use App\Models\ProgulRealisasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProgulRealisasiResource extends Resource
{
    protected static ?string $model = ProgulRealisasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCheck;
    protected static string|UnitEnum|null $navigationGroup = 'Program Unggulan';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Realisasi Program Unggulan';
    protected static ?string $modelLabel = 'Realisasi Program Unggulan';
    protected static ?string $pluralModelLabel = 'Realisasi Program Unggulan';

    public static function form(Schema $schema): Schema
    {
        return ProgulRealisasiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgulRealisasisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BuktiDukungRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProgulRealisasis::route('/'),
            'create' => CreateProgulRealisasi::route('/create'),
            'edit'   => EditProgulRealisasi::route('/{record}/edit'),
        ];
    }

    /**
     * SKPD hanya melihat data realisasi milik SKPD-nya sendiri.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if ($user && $user->role === 'SKPD') {
            $query->where('skpd_id', $user->skpd_id);
        }

        return $query;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['ADMIN', 'BAPPEDA', 'SKPD']);
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['ADMIN', 'SKPD']);
    }
}

