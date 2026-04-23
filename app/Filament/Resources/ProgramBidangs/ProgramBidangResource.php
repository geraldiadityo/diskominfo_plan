<?php

namespace App\Filament\Resources\ProgramBidangs;

use App\Filament\Resources\ProgramBidangs\Pages\CreateProgramBidang;
use App\Filament\Resources\ProgramBidangs\Pages\EditProgramBidang;
use App\Filament\Resources\ProgramBidangs\Pages\ListProgramBidangs;
use App\Filament\Resources\ProgramBidangs\RelationManager\ProgramRelationManager;
use App\Filament\Resources\ProgramBidangs\Schemas\ProgramBidangForm;
use App\Filament\Resources\ProgramBidangs\Tables\ProgramBidangsTable;
use App\Models\ProgramBidang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProgramBidangResource extends Resource
{
    protected static ?string $model = ProgramBidang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Bidang';
    protected static ?string $modelLabel = 'Bidang';
    protected static ?string $pluralModelLabel = 'Bidang';
    protected static string|UnitEnum|null $navigationGroup = 'Master Program Kegiatan';
    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 2;


    public static function form(Schema $schema): Schema
    {
        return ProgramBidangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramBidangsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
            ProgramRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProgramBidangs::route('/'),
            'create' => CreateProgramBidang::route('/create'),
            'edit' => EditProgramBidang::route('/{record}/edit'),
        ];
    }
}
