<?php

namespace App\Filament\Resources\Skpds\Schemas;

use App\Models\ProgramBidang;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SkpdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                TextInput::make('kode_skpd')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('nama_skpd')
                    ->required()
                    ->maxLength(255),

                Select::make('bidang_id')
                    ->label('Bidang (Mapping Kode Urusan)')
                    ->helperText('Pilih bidang dari master program kegiatan yang sesuai dengan SKPD ini. Kode bidang ini akan ditampilkan menggantikan kode urusan (X.XX) di tampilan Renja SKPD.')
                    ->relationship(
                        name: 'bidang',
                        titleAttribute: 'nomenklatur',
                        modifyQueryUsing: fn ($query) => $query->with('urusan')
                    )
                    ->getOptionLabelFromRecordUsing(fn(ProgramBidang $record) => "{$record->kode_lengkap} - {$record->nomenklatur}" . ($record->urusan ? " ({$record->urusan->nomenklatur})" : ''))
                    ->searchable(['kode_lengkap', 'nomenklatur'])
                    ->preload()
                    ->nullable()
                    ->placeholder('-- Pilih Bidang (Opsional) --'),
            ]);
    }
}
