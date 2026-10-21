<?php

namespace App\Filament\Resources\ProgulPrograms;

use App\Filament\Resources\ProgulPrograms\Pages\CreateProgulProgram;
use App\Filament\Resources\ProgulPrograms\Pages\EditProgulProgram;
use App\Filament\Resources\ProgulPrograms\Pages\ListProgulPrograms;
use App\Filament\Resources\ProgulPrograms\RelationManagers\IndikatorRelationManager;
use App\Filament\Resources\ProgulPrograms\Schemas\ProgulProgramForm;
use App\Filament\Resources\ProgulPrograms\Tables\ProgulProgramsTable;
use App\Models\ProgulProgram;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProgulProgramResource extends Resource
{
    protected static ?string $model = ProgulProgram::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Star;
    protected static string|UnitEnum|null $navigationGroup = 'Program Unggulan';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Program Unggulan';
    protected static ?string $modelLabel = 'Program Unggulan';
    protected static ?string $pluralModelLabel = 'Program Unggulan';

    public static function form(Schema $schema): Schema
    {
        return ProgulProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgulProgramsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            IndikatorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProgulPrograms::route('/'),
            'create' => CreateProgulProgram::route('/create'),
            'edit'   => EditProgulProgram::route('/{record}/edit'),
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

