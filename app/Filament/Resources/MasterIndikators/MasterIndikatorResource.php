<?php

namespace App\Filament\Resources\MasterIndikators;

use App\Filament\Resources\MasterIndikators\Pages\CreateMasterIndikator;
use App\Filament\Resources\MasterIndikators\Pages\EditMasterIndikator;
use App\Filament\Resources\MasterIndikators\Pages\ListMasterIndikators;
use App\Filament\Resources\MasterIndikators\RelationManager\TargetRelationManager;
use App\Filament\Resources\MasterIndikators\Schemas\MasterIndikatorForm;
use App\Filament\Resources\MasterIndikators\Tables\MasterIndikatorsTable;
use App\Models\MasterIndikator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MasterIndikatorResource extends Resource
{
    protected static ?string $model = MasterIndikator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocument;
    protected static string|UnitEnum|null $navigationGroup = 'Master Indikator';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Data Indikator';

    public static function form(Schema $schema): Schema
    {
        return MasterIndikatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterIndikatorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
            TargetRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterIndikators::route('/'),
            'create' => CreateMasterIndikator::route('/create'),
            'edit' => EditMasterIndikator::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return in_array($user->role, [
            'ADMIN',
            'BAPPEDA',
        ]);
    }
}
