<?php

namespace App\Livewire;

use App\Models\ProgulProgram;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.frontend')]
class PublicProgramPrioritas extends Component
{
    #[Url(as: 'program')]
    public ?int $selectedProgramId = null;

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
    }

    public function selectProgram(int $programId): void
    {
        if ($this->selectedProgramId === $programId) {
            // Keep selected or allow toggle off if user clicks again
            $this->selectedProgramId = $programId;
        } else {
            $this->selectedProgramId = $programId;
            $this->search = '';
            $this->selectedSkpd = '';
        }
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
                'indikators.urusan',
                'indikators.realisasis.skpd',
                'indikators.realisasis.buktiDukung',
            ])->find($this->selectedProgramId);

            if ($selectedProgram) {
                $indikators = $selectedProgram->indikators;

                // Collect SKPD list for filter
                $skpdList = $indikators->map(function ($ind) {
                    return $ind->realisasis->first()?->skpd?->nama_skpd;
                })->filter()->unique()->sort()->values()->toArray();

                // Filter search
                if (!empty($this->search)) {
                    $searchLower = strtolower($this->search);
                    $indikators = $indikators->filter(function ($ind) use ($searchLower) {
                        return str_contains(strtolower($ind->nama_indikator), $searchLower)
                            || str_contains(strtolower($ind->detail ?? ''), $searchLower);
                    });
                }

                // Filter SKPD
                if (!empty($this->selectedSkpd)) {
                    $indikators = $indikators->filter(function ($ind) {
                        $skpdName = $ind->realisasis->first()?->skpd?->nama_skpd;
                        return $skpdName === $this->selectedSkpd;
                    });
                }
            }
        }

        return view('livewire.public-program-prioritas', [
            'programs'        => $programs,
            'selectedProgram' => $selectedProgram,
            'indikators'      => $indikators,
            'skpdList'        => $skpdList,
            'years'           => $this->years,
        ]);
    }
}

