<?php

namespace App\Filament\Resources\SatuanIndikators;

use App\Filament\Resources\SatuanIndikators\Pages\CreateSatuanIndikator;
use App\Filament\Resources\SatuanIndikators\Pages\EditSatuanIndikator;
use App\Filament\Resources\SatuanIndikators\Pages\ListSatuanIndikators;
use App\Filament\Resources\SatuanIndikators\Schemas\SatuanIndikatorForm;
use App\Filament\Resources\SatuanIndikators\Tables\SatuanIndikatorsTable;
use App\Models\SatuanIndikator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SatuanIndikatorResource extends Resource
{
    protected static ?string $model = SatuanIndikator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AdjustmentsHorizontal;
    protected static string|UnitEnum|null $navigationGroup = 'Master Indikator';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Satuan Indikator';

    public static function form(Schema $schema): Schema
    {
        return SatuanIndikatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SatuanIndikatorsTable::configure($table);
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
            'index' => ListSatuanIndikators::route('/'),
            // 'create' => CreateSatuanIndikator::route('/create'),
            // 'edit' => EditSatuanIndikator::route('/{record}/edit'),
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
