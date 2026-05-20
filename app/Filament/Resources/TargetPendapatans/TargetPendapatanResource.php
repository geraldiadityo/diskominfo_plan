<?php

namespace App\Filament\Resources\TargetPendapatans;

use App\Filament\Resources\TargetPendapatans\Pages\CreateTargetPendapatan;
use App\Filament\Resources\TargetPendapatans\Pages\EditTargetPendapatan;
use App\Filament\Resources\TargetPendapatans\Pages\ListTargetPendapatans;
use App\Filament\Resources\TargetPendapatans\RelationManager\TransaksiPendapatanRelationManager;
use App\Filament\Resources\TargetPendapatans\Schemas\TargetPendapatanForm;
use App\Filament\Resources\TargetPendapatans\Tables\TargetPendapatansTable;
use App\Models\TargetPendapatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class TargetPendapatanResource extends Resource
{
    protected static ?string $model = TargetPendapatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;
    protected static string|UnitEnum|null $navigationGroup = 'Evaluasi Dan Pelaporan';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'PAD';

    public static function form(Schema $schema): Schema
    {
        return TargetPendapatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TargetPendapatansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
            TransaksiPendapatanRelationManager::class,
        ];
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['transaksi_pendapatan', 'rekening']);

        if (Auth::user()->role === 'SKPD') {
            $query->where('skpd_id', Auth::user()->skpd_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTargetPendapatans::route('/'),
            'create' => CreateTargetPendapatan::route('/create'),
            'edit' => EditTargetPendapatan::route('/{record}/edit'),
        ];
    }
}
