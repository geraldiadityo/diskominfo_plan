<?php

namespace App\Filament\Resources\IndikatorIkus;

use App\Filament\Resources\IndikatorIkus\Pages\CreateIndikatorIku;
use App\Filament\Resources\IndikatorIkus\Pages\EditIndikatorIku;
use App\Filament\Resources\IndikatorIkus\Pages\ListIndikatorIkus;
use App\Filament\Resources\IndikatorIkus\Schemas\IndikatorIkuForm;
use App\Filament\Resources\IndikatorIkus\Tables\IndikatorIkusTable;
use App\Filament\Resources\MasterIndikators\RelationManager\TargetRelationManager;
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

class IndikatorIkuResource extends Resource
{
    protected static ?string $model = MasterIndikator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;
    protected static string|UnitEnum|null $navigationGroup = 'Master Indikator';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Indikator Kinerja Utama (IKU)';
    protected static ?string $modelLabel = 'Indikator Kinerja Utama';
    protected static ?string $pluralModelLabel = 'Indikator Kinerja Utama';
    protected static ?string $slug = 'indikator-iku';

    public static function getEloquentQuery(): Builder
    {
        $kategoriId = KategoriIndikator::where('nama_kategori', 'IKU')->value('id');

        return parent::getEloquentQuery()
            ->when($kategoriId, fn ($q) => $q->where('kategori_id', $kategoriId));
    }

    public static function form(Schema $schema): Schema
    {
        return IndikatorIkuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndikatorIkusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TargetRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListIndikatorIkus::route('/'),
            'create' => CreateIndikatorIku::route('/create'),
            'edit'   => EditIndikatorIku::route('/{record}/edit'),
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

