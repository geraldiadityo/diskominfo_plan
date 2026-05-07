<?php

namespace App\Filament\Resources\IndikatorRealisasis;

use App\Filament\Resources\IndikatorRealisasis\Pages\CreateIndikatorRealisasi;
use App\Filament\Resources\IndikatorRealisasis\Pages\EditIndikatorRealisasi;
use App\Filament\Resources\IndikatorRealisasis\Pages\ListIndikatorRealisasis;
use App\Filament\Resources\IndikatorRealisasis\Schemas\IndikatorRealisasiForm;
use App\Filament\Resources\IndikatorRealisasis\Tables\IndikatorRealisasisTable;
use App\Models\IndikatorRealisasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class IndikatorRealisasiResource extends Resource
{
    protected static ?string $model = IndikatorRealisasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCheck;
    protected static string|UnitEnum|null $navigationGroup = 'Evaluasi Dan Pelaporan';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Realisasi Indikator';
    protected static ?string $modelLabel = 'Realisasi Indikator';
    public static function form(Schema $schema): Schema
    {
        return IndikatorRealisasiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndikatorRealisasisTable::configure($table);
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
            'index' => ListIndikatorRealisasis::route('/'),
            // 'create' => CreateIndikatorRealisasi::route('/create'),
            'edit' => EditIndikatorRealisasi::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->role === 'SKPD') {
            $query->where('skpd_id', $user->skpd_id);
        }

        return $query;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
