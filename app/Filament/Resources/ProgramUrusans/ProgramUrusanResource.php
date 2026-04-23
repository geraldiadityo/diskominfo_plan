<?php

namespace App\Filament\Resources\ProgramUrusans;

use App\Filament\Resources\ProgramUrusans\Pages\CreateProgramUrusan;
use App\Filament\Resources\ProgramUrusans\Pages\EditProgramUrusan;
use App\Filament\Resources\ProgramUrusans\Pages\ListProgramUrusans;
use App\Filament\Resources\ProgramUrusans\RelationManager\BidangRelationManager;
use App\Filament\Resources\ProgramUrusans\Schemas\ProgramUrusanForm;
use App\Filament\Resources\ProgramUrusans\Tables\ProgramUrusansTable;
use App\Models\ProgramUrusan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;


class ProgramUrusanResource extends Resource
{
    protected static ?string $model = ProgramUrusan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::InboxStack;
    protected static string|UnitEnum|null $navigationGroup = 'Master Program Kegiatan';
    protected static ?string $modelLabel = 'Urusan';
    protected static ?string $navigationLabel = 'Urusan';

    protected static ?int $navigationSort = 1;


    public static function form(Schema $schema): Schema
    {
        return ProgramUrusanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramUrusansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
            BidangRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => ListProgramUrusans::route('/'),
            'create' => CreateProgramUrusan::route('/create'),
            'edit' => EditProgramUrusan::route('/{record}/edit'),
        ];
    }
}
