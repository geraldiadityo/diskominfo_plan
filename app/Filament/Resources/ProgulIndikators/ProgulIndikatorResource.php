<?php

namespace App\Filament\Resources\ProgulIndikators;

use App\Filament\Resources\ProgulIndikators\Pages\CreateProgulIndikator;
use App\Filament\Resources\ProgulIndikators\Pages\EditProgulIndikator;
use App\Filament\Resources\ProgulIndikators\Pages\ListProgulIndikators;
use App\Filament\Resources\ProgulIndikators\RelationManagers\RealisasiRelationManager;
use App\Filament\Resources\ProgulIndikators\Schemas\ProgulIndikatorForm;
use App\Filament\Resources\ProgulIndikators\Tables\ProgulIndikatorsTable;
use App\Models\ProgulIndikator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProgulIndikatorResource extends Resource
{
    protected static ?string $model = ProgulIndikator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;
    protected static string|UnitEnum|null $navigationGroup = 'Program Unggulan';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Indikator Kinerja';
    protected static ?string $modelLabel = 'Indikator Kinerja Program Unggulan';
    protected static ?string $pluralModelLabel = 'Indikator Kinerja';

    public static function form(Schema $schema): Schema
    {
        return ProgulIndikatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgulIndikatorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RealisasiRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProgulIndikators::route('/'),
            'create' => CreateProgulIndikator::route('/create'),
            'edit'   => EditProgulIndikator::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['ADMIN', 'BAPPEDA']);
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->role === 'ADMIN';
    }
}

