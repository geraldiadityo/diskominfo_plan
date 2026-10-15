<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans;

use App\Filament\Resources\IndikatorPenyelenggaraans\Pages\CreateIndikatorPenyelenggaraan;
use App\Filament\Resources\IndikatorPenyelenggaraans\Pages\EditIndikatorPenyelenggaraan;
use App\Filament\Resources\IndikatorPenyelenggaraans\Pages\ListIndikatorPenyelenggaraans;
use App\Filament\Resources\IndikatorPenyelenggaraans\RelationManager\ChildIndikatorRelationManager;
use App\Filament\Resources\IndikatorPenyelenggaraans\Schemas\IndikatorPenyelenggaraanForm;
use App\Filament\Resources\IndikatorPenyelenggaraans\Tables\IndikatorPenyelenggaraansTable;
use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class IndikatorPenyelenggaraanResource extends Resource
{
    protected static ?string $model = MasterIndikator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;
    protected static string|UnitEnum|null $navigationGroup = 'Master Indikator';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Indikator Penyelenggaraan';
    protected static ?string $modelLabel = 'Indikator Penyelenggaraan';
    protected static ?string $pluralModelLabel = 'Indikator Penyelenggaraan';
    protected static ?string $slug = 'indikator-penyelenggaraan';

    public static function getEloquentQuery(): Builder
    {
        $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');

        return parent::getEloquentQuery()
            ->when($kategoriId, fn ($q) => $q->where('kategori_id', $kategoriId));
    }

    public static function form(Schema $schema): Schema
    {
        return IndikatorPenyelenggaraanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndikatorPenyelenggaraansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ChildIndikatorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIndikatorPenyelenggaraans::route('/'),
            'create' => CreateIndikatorPenyelenggaraan::route('/create'),
            'edit' => EditIndikatorPenyelenggaraan::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return in_array($user?->role, [
            'ADMIN',
            'BAPPEDA',
        ]);
    }
}

