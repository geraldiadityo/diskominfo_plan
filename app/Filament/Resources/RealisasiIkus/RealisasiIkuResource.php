<?php

namespace App\Filament\Resources\RealisasiIkus;

use App\Filament\Resources\RealisasiIkus\Pages\EditRealisasiIku;
use App\Filament\Resources\RealisasiIkus\Pages\ListRealisasiIkus;
use App\Filament\Resources\RealisasiIkus\Schemas\RealisasiIkuForm;
use App\Filament\Resources\RealisasiIkus\Tables\RealisasiIkusTable;
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

class RealisasiIkuResource extends Resource
{
    protected static ?string $model = IndikatorRealisasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentCheck;
    protected static string|UnitEnum|null $navigationGroup = 'Evaluasi Dan Pelaporan';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Realisasi IKU';
    protected static ?string $modelLabel = 'Realisasi IKU';
    protected static ?string $pluralModelLabel = 'Realisasi IKU';
    protected static ?string $slug = 'realisasi-iku';

    public static function getEloquentQuery(): Builder
    {
        $user       = Auth::user();
        $kategoriId = KategoriIndikator::where('nama_kategori', 'IKU')->value('id');

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
        return RealisasiIkuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RealisasiIkusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRealisasiIkus::route('/'),
            'edit'  => EditRealisasiIku::route('/{record}/edit'),
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

