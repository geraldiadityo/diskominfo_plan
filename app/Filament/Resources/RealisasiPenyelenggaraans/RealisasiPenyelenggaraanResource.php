<?php

namespace App\Filament\Resources\RealisasiPenyelenggaraans;

use App\Filament\Resources\RealisasiPenyelenggaraans\Pages\EditRealisasiPenyelenggaraan;
use App\Filament\Resources\RealisasiPenyelenggaraans\Pages\ListRealisasiPenyelenggaraans;
use App\Filament\Resources\RealisasiPenyelenggaraans\Schemas\RealisasiPenyelenggaraanForm;
use App\Filament\Resources\RealisasiPenyelenggaraans\Tables\RealisasiPenyelenggaraansTable;
use App\Models\IndikatorRealisasi;
use App\Models\KategoriIndikator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class RealisasiPenyelenggaraanResource extends Resource
{
    protected static ?string $model = IndikatorRealisasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentCheck;
    protected static string|UnitEnum|null $navigationGroup = 'Evaluasi Dan Pelaporan';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Realisasi Penyelenggaraan';
    protected static ?string $modelLabel = 'Realisasi Penyelenggaraan';
    protected static ?string $pluralModelLabel = 'Realisasi Penyelenggaraan';
    protected static ?string $slug = 'realisasi-penyelenggaraan';

    public static function getEloquentQuery(): Builder
    {
        $user       = Auth::user();
        $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');

        return parent::getEloquentQuery()
            ->whereHas('indikator', function (Builder $q) use ($kategoriId) {
                $q->where('kategori_id', $kategoriId);
            })
            ->when(
                $user?->role === 'SKPD',
                fn ($q) => $q->where('skpd_id', $user->skpd_id)
            );
    }

    public static function form(Schema $schema): Schema
    {
        return RealisasiPenyelenggaraanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RealisasiPenyelenggaraansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRealisasiPenyelenggaraans::route('/'),
            'edit'  => EditRealisasiPenyelenggaraan::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return in_array($user?->role, [
            'ADMIN',
            'BAPPEDA',
            'SKPD',
        ]);
    }
}

