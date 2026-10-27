<?php

namespace App\Livewire;

use App\Models\ProgulProgram;
use App\Models\RenjaSkpd;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.frontend')]
class PublicProgramPrioritas extends Component
{
    #[Url(as: 'program')]
    public ?int $selectedProgramId = null;

    #[Url(as: 'tahun')]
    public int $tahun = 2025;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'skpd')]
    public string $selectedSkpd = '';

    public array $years = [2025, 2026, 2027, 2028, 2029];

    public function mount(): void
    {
        if (request()->filled('program')) {
            $this->selectedProgramId = (int) request('program');
        }

        if (request()->filled('tahun')) {
            $t = (int) request('tahun');
            if (in_array($t, $this->years)) {
                $this->tahun = $t;
            }
        }
    }

    public function selectProgram(int $programId): void
    {
        if ($this->selectedProgramId === $programId) {
            $this->selectedProgramId = $programId;
        } else {
            $this->selectedProgramId = $programId;
            $this->search = '';
            $this->selectedSkpd = '';
        }
    }

    public function setTahun(int $year): void
    {
        $this->tahun = $year;
    }

    public function closeTable(): void
    {
        $this->selectedProgramId = null;
        $this->search = '';
        $this->selectedSkpd = '';
    }

    public function resetFilter(): void
    {
        $this->search = '';
        $this->selectedSkpd = '';
    }

    public function getIconForProgram(string $namaProgram): string
    {
        $namaLower = strtolower($namaProgram);

        if (str_contains($namaLower, 'maju')) {
            return 'shield-check';
        }
        if (str_contains($namaLower, 'sejahtera')) {
            return 'heart';
        }
        if (str_contains($namaLower, 'nyaman')) {
            return 'sparkles';
        }
        if (str_contains($namaLower, 'unggul')) {
            return 'academic-cap';
        }
        if (str_contains($namaLower, 'sehat')) {
            return 'plus-circle';
        }
        if (str_contains($namaLower, 'agamais')) {
            return 'star';
        }
        if (str_contains($namaLower, 'perempuan')) {
            return 'user-group';
        }
        if (str_contains($namaLower, 'ilok')) {
            return 'light-bulb';
        }
        if (str_contains($namaLower, 'melayani')) {
            return 'face-smile';
        }

        return 'bolt';
    }

    public function render()
    {
        $programs = ProgulProgram::withCount('indikators')
            ->orderBy('urutan')
            ->get();

        $selectedProgram = null;
        $indikators = collect();
        $skpdList = [];

        if ($this->selectedProgramId) {
            $selectedProgram = ProgulProgram::with([
                'indikators.subKegiatan.kegiatan.program',
                'indikators.urusan',
                'indikators.realisasis' => function ($q) {
                    $q->where('tahun', $this->tahun)->orderBy('triwulan');
                },
                'indikators.realisasis.skpd',
            ])->find($this->selectedProgramId);

            if ($selectedProgram) {
                // Eager load live renja for this year based on sub_kegiatan_id
                $subKegIds = $selectedProgram->indikators->pluck('sub_kegiatan_id')->filter()->unique();
                $renjas = RenjaSkpd::with('realisasi_kegiatans')
                    ->whereIn('sub_kegiatan_id', $subKegIds)
                    ->where('tahun', $this->tahun)
                    ->get()
                    ->keyBy('sub_kegiatan_id');

                $indikators = $selectedProgram->indikators;

                // Attach live renja to each indicator
                $indikators->each(function ($ind) use ($renjas) {
                    $ind->liveRenja = $ind->sub_kegiatan_id ? $renjas->get($ind->sub_kegiatan_id) : null;
                });

                // Collect SKPD list for filter
                $skpdList = $indikators->map(function ($ind) {
                    return $ind->realisasis->first()?->skpd?->nama_skpd
                        ?? $ind->urusan->first()?->nama_urusan;
                })->filter()->unique()->sort()->values()->toArray();

                // Filter search
                if (!empty($this->search)) {
                    $searchLower = strtolower($this->search);
                    $indikators = $indikators->filter(function ($ind) use ($searchLower) {
                        return str_contains(strtolower($ind->nama_indikator), $searchLower)
                            || str_contains(strtolower($ind->rincian ?? ''), $searchLower)
                            || str_contains(strtolower($ind->detail ?? ''), $searchLower)
                            || str_contains(strtolower($ind->subKegiatan?->kode_lengkap ?? ''), $searchLower)
                            || str_contains(strtolower($ind->subKegiatan?->kegiatan?->nomenklatur ?? ''), $searchLower);
                    });
                }

                // Filter SKPD
                if (!empty($this->selectedSkpd)) {
                    $indikators = $indikators->filter(function ($ind) {
                        $skpdName = $ind->realisasis->first()?->skpd?->nama_skpd
                            ?? $ind->urusan->first()?->nama_urusan;
                        return $skpdName === $this->selectedSkpd;
                    });
                }
            }
        }

        return view('livewire.public-program-prioritas', [
            'programs'          => $programs,
            'selectedProgram'   => $selectedProgram,
            'selectedProgramId' => $this->selectedProgramId,
            'indikators'        => $indikators,
            'skpdList'          => $skpdList,
            'years'             => $this->years,
            'tahun'             => $this->tahun,
        ]);
    }
}
