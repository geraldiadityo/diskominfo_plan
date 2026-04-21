<?php

namespace App\Filament\Resources\Skpds;

use App\Filament\Resources\Skpds\Pages\CreateSkpd;
use App\Filament\Resources\Skpds\Pages\EditSkpd;
use App\Filament\Resources\Skpds\Pages\ListSkpds;
use App\Filament\Resources\Skpds\Schemas\SkpdForm;
use App\Filament\Resources\Skpds\Tables\SkpdsTable;
use App\Models\Skpd;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SkpdResource extends Resource
{
    protected static ?string $model = Skpd::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;
    protected static string|UnitEnum|null $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'SKPD';

    public static function form(Schema $schema): Schema
    {
        return SkpdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkpdsTable::configure($table);
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
            'index' => ListSkpds::route('/'),
            // 'create' => CreateSkpd::route('/create'),
            // 'edit' => EditSkpd::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user->role === 'ADMIN';
    }
}
