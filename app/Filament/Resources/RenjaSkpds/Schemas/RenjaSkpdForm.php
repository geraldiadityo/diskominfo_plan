<?php

namespace App\Filament\Resources\RenjaSkpds\Schemas;

use App\Models\ProgramBidang;
use App\Models\ProgramKegiatan;
use App\Models\ProgramProgram;
use App\Models\ProgramSubKegiatan;
use App\Models\ProgramUrusan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class RenjaSkpdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                //
                Section::make('Pilih Nomenklatur Kegiatan')
                    ->description('Silahkan Pilih Sub Kegiatan untuk di masukan kedalam draf renja')
                    ->schema([
                        Select::make('skpd_id')
                            ->relationship('skpd', 'nama_skpd')
                            ->default(fn() => Auth::user()->skpd_id)
                            ->disabled(fn() => Auth::user()->role === 'SKPD')
                            ->dehydrated()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),

                        TextInput::make('tahun')
                            ->label('Tahun Anggaran')
                            ->numeric()
                            ->default(date('Y'))
                            ->required()
                            ->columnSpan(1),

                        // urusan
                        Select::make('urusan_id')
                            ->label('1. Urusan')
                            ->options(ProgramUrusan::query()->pluck('nomenklatur', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('bidang_id', null) ?? $set('program_id', null) ?? $set('kegiatan_id', null) ?? $set('sub_kegiatan_id', null))
                            ->dehydrated(false),

                        // bidang
                        Select::make('bidang_id')
                            ->label('2. Bidang')
                            ->options(
                                fn(Get $get) => ProgramBidang::query()
                                    ->when($get('urusan_id'), fn($q) => $q->where('urusan_id', $get('urusan_id')))
                                    ->pluck('nomenklatur', 'id')
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state && $bidang = ProgramBidang::find($state)) {
                                    $set('urusan_id', $bidang->urusan_id);
                                }
                            })
                            ->dehydrated(false),

                        // program
                        Select::make('program_id')
                            ->label('3. Program')
                            ->options(
                                fn(Get $get) => ProgramProgram::query()
                                    ->when($get('bidang_id'), fn($q) => $q->where('bidang_id', $get('bidang_id')))
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(fn($item) => [$item->id => $item->kode_lengkap . ' - ' . $item->nomenklatur])
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state && $program = ProgramProgram::with('bidang')->find($state)) {
                                    $set('bidang_id', $program->bidang_id);
                                    if ($program->bidang) $set('urusan_id', $program->bidang->urusan_id);
                                }
                            })
                            ->dehydrated(false),

                        // Kegiatan
                        Select::make('kegiatan_id')
                            ->label('4. Kegiatan')
                            ->options(
                                fn(Get $get) => ProgramKegiatan::query()
                                    ->when($get('program_id'), fn($q) => $q->where('program_id', $get('program_id')))
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(fn($item) => [$item->id => $item->kode_lengkap . ' - ' . $item->nomenklatur])
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state && $kegiatan = ProgramKegiatan::with('program.bidang')->find($state)) {
                                    $set('program_id', $kegiatan->program_id);
                                    if ($kegiatan->program) {
                                        $set('bidang_id', $kegiatan->program->bidang_id);
                                        if ($kegiatan->program->bidang) $set('urusan_id', $kegiatan->program->bidang->urusan_id);
                                    }
                                }
                            })
                            ->dehydrated(false)
                            ->columnSpanFull(),

                        // sub kegiatan
                        Select::make('sub_kegiatan_id')
                            ->label('5. Sub Kegiatan')
                            ->options(function (Get $get) {
                                if ($kegiatanId = $get('kegiatan_id')) {
                                    return ProgramSubKegiatan::where('kegiatan_id', $kegiatanId)
                                        ->get()
                                        ->mapWithKeys(fn($item) => [$item->id => $item->kode_lengkap . ' - ' . $item->nomenklatur]);
                                }
                                return [];
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                return ProgramSubKegiatan::query()
                                    ->whereFullText('nomenklatur', $search)
                                    ->orWhere('kode_lengkap', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($item) => [$item->id => $item->kode_lengkap . ' - ' . $item->nomenklatur]);
                            })
                            ->getOptionLabelUsing(fn($value): ?string => ($sub = ProgramSubKegiatan::find($value)) ? $sub->kode_lengkap . ' - ' . $sub->nomenklatur : null)
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state && $sub = ProgramSubKegiatan::with('kegiatan.program.bidang')->find($state)) {
                                    $set('kegiatan_id', $sub->kegiatan_id);
                                    if ($sub->kegiatan) {
                                        $set('program_id', $sub->kegiatan->program_id);
                                        if ($sub->kegiatan->program) {
                                            $set('bidang_id', $sub->kegiatan->program->bidang_id);
                                            if ($sub->kegiatan->program->bidang) {
                                                $set('urusan_id', $sub->kegiatan->program->bidang->urusan_id);
                                            }
                                        }
                                    }
                                }
                            })
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }
}
