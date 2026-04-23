<?php

namespace App\Filament\Resources\ProgramPrograms;

use App\Filament\Resources\ProgramPrograms\Pages\CreateProgramProgram;
use App\Filament\Resources\ProgramPrograms\Pages\EditProgramProgram;
use App\Filament\Resources\ProgramPrograms\Pages\ListProgramPrograms;
use App\Filament\Resources\ProgramPrograms\RelationManager\KegiatanRelationManager;
use App\Filament\Resources\ProgramPrograms\Schemas\ProgramProgramForm;
use App\Filament\Resources\ProgramPrograms\Tables\ProgramProgramsTable;
use App\Models\ProgramProgram;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProgramProgramResource extends Resource
{
    protected static ?string $model = ProgramProgram::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Program';
    protected static ?string $modelLabel = 'Program';
    protected static ?string $pluralModelLabel = 'Program';
    protected static string|UnitEnum|null $navigationGroup = 'Master Program Kegiatan';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return ProgramProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramProgramsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
            KegiatanRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProgramPrograms::route('/'),
            'create' => CreateProgramProgram::route('/create'),
            'edit' => EditProgramProgram::route('/{record}/edit'),
        ];
    }
}
