<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\RelationManager;

use App\Models\KategoriIndikator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ChildIndikatorRelationManager extends RelationManager
{
    protected static string $relationship = 'childIndikators';
    protected static ?string $title = 'Daftar Indikator Turunan';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return is_null($ownerRecord->parent_id);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('nama_indikator')
                ->label('Nama Indikator')
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Select::make('satuan_id')
                ->label('Satuan')
                ->relationship('satuan', 'nama_satuan')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('nama_satuan')
                        ->label('Nama Satuan')
                        ->required(),
                ])
                ->required()
                ->helperText('Satuan pengukuran indikator'),

            Toggle::make('is_measurable')
                ->label('Dapat Diukur')
                ->default(true)
                ->inline(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_indikator')
            ->columns([
                TextColumn::make('nama_indikator')
                    ->label('Indikator Turunan')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('satuan.nama_satuan')
                    ->label('Satuan')
                    ->badge()
                    ->placeholder('-'),

                IconColumn::make('is_measurable')
                    ->label('Dapat Diukur')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');
                        $data['kategori_id'] = $kategoriId;
                        $data['is_measurable'] = true;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

