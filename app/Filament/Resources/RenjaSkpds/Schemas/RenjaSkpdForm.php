<?php

namespace App\Filament\Resources\RenjaSkpds\Schemas;

use App\Models\ProgramBidang;
use App\Models\ProgramKegiatan;
use App\Models\ProgramProgram;
use App\Models\ProgramSubKegiatan;
use App\Models\ProgramUrusan;
use App\Models\Skpd;
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
                            ->live()
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
                            ->options(function (Get $get) {
                                $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                return ProgramUrusan::query()->get()->mapWithKeys(function ($item) use ($skpdId) {
                                    return [$item->id => Skpd::formatDisplayNomenklatur($item->nomenklatur, $skpdId)];
                                });
                            })
                            ->getOptionLabelUsing(function ($value, Get $get) {
                                if (!$value) return null;
                                $urusan = ProgramUrusan::find($value);
                                if (!$urusan) return null;
                                $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                return Skpd::formatDisplayNomenklatur($urusan->nomenklatur, $skpdId);
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('bidang_id', null) ?? $set('program_id', null) ?? $set('kegiatan_id', null) ?? $set('sub_kegiatan_id', null))
                            ->dehydrated(false),

                        // bidang
                        Select::make('bidang_id')
                            ->label('2. Bidang')
                            ->options(function (Get $get) {
                                $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                return ProgramBidang::query()
                                    ->when($get('urusan_id'), fn($q) => $q->where('urusan_id', $get('urusan_id')))
                                    ->get()
                                    ->mapWithKeys(function ($item) use ($skpdId) {
                                        return [$item->id => Skpd::formatDisplayNomenklatur($item->nomenklatur, $skpdId)];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value, Get $get) {
                                if (!$value) return null;
                                $bidang = ProgramBidang::find($value);
                                if (!$bidang) return null;
                                $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                return Skpd::formatDisplayNomenklatur($bidang->nomenklatur, $skpdId);
                            })
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
                            ->options(function (Get $get) {
                                $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                return ProgramProgram::query()
                                    ->when($get('bidang_id'), fn($q) => $q->where('bidang_id', $get('bidang_id')))
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(function ($item) use ($skpdId) {
                                        $kode = Skpd::formatDisplayKode($item->kode_lengkap, $skpdId);
                                        return [$item->id => $kode . ' - ' . $item->nomenklatur];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value, Get $get) {
                                if (!$value) return null;
                                $program = ProgramProgram::find($value);
                                if (!$program) return null;
                                $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                $kode = Skpd::formatDisplayKode($program->kode_lengkap, $skpdId);
                                return $kode . ' - ' . $program->nomenklatur;
                            })
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
                            ->options(function (Get $get) {
                                $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                return ProgramKegiatan::query()
                                    ->when($get('program_id'), fn($q) => $q->where('program_id', $get('program_id')))
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(function ($item) use ($skpdId) {
                                        $kode = Skpd::formatDisplayKode($item->kode_lengkap, $skpdId);
                                        return [$item->id => $kode . ' - ' . $item->nomenklatur];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value, Get $get) {
                                if (!$value) return null;
                                $kegiatan = ProgramKegiatan::find($value);
                                if (!$kegiatan) return null;
                                $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                $kode = Skpd::formatDisplayKode($kegiatan->kode_lengkap, $skpdId);
                                return $kode . ' - ' . $kegiatan->nomenklatur;
                            })
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
                                    $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                    return ProgramSubKegiatan::where('kegiatan_id', $kegiatanId)
                                        ->get()
                                        ->mapWithKeys(function ($item) use ($skpdId) {
                                            $kode = Skpd::formatDisplayKode($item->kode_lengkap, $skpdId);
                                            return [$item->id => $kode . ' - ' . $item->nomenklatur];
                                        });
                                }
                                return [];
                            })
                            ->getSearchResultsUsing(function (string $search, Get $get) {
                                $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                return ProgramSubKegiatan::query()
                                    ->whereFullText('nomenklatur', $search)
                                    ->orWhere('kode_lengkap', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($item) use ($skpdId) {
                                        $kode = Skpd::formatDisplayKode($item->kode_lengkap, $skpdId);
                                        return [$item->id => $kode . ' - ' . $item->nomenklatur];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value, Get $get): ?string {
                                if (!$value) return null;
                                $sub = ProgramSubKegiatan::find($value);
                                if (!$sub) return null;
                                $skpdId = $get('skpd_id') ?: Auth::user()?->skpd_id;
                                $kode = Skpd::formatDisplayKode($sub->kode_lengkap, $skpdId);
                                return $kode . ' - ' . $sub->nomenklatur;
                            })
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
