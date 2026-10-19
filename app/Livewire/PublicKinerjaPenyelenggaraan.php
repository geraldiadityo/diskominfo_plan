<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class PublicKinerjaPenyelenggaraan extends Component
{
    #[Url(as: 'tahun_awal')]
    public int $filterStartYear = 2024;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'skpd')]
    public string $selectedSkpd = '';

    public function mount(): void
    {
        if (request()->filled('tahun_awal')) {
            $this->filterStartYear = (int) request('tahun_awal');
        }
        if (request()->filled('skpd')) {
            $this->selectedSkpd = (string) request('skpd');
        }
        if (request()->filled('search')) {
            $this->search = (string) request('search');
        }
    }

    /**
     * Menghasilkan array 6 tahun berdasarkan tahun awal filter.
     * Tahun pertama = baseline.
     */
    public function getYearsProperty(): array
    {
        return range($this->filterStartYear, $this->filterStartYear + 5);
    }

    /**
     * Daftar pilihan periode 6 tahun yang tersedia.
     */
    public function getAvailablePeriodsProperty(): array
    {
        return [
            2024 => '2024 – 2029 (Baseline: 2024)',
            2025 => '2025 – 2030 (Baseline: 2025)',
            2021 => '2021 – 2026 (Baseline: 2021)',
            2022 => '2022 – 2027 (Baseline: 2022)',
            2023 => '2023 – 2028 (Baseline: 2023)',
        ];
    }

    public function setStartYear(int $year): void
    {
        $this->filterStartYear = $year;
    }

    public function resetFilter(): void
    {
        $this->search = '';
        $this->selectedSkpd = '';
        $this->filterStartYear = 2024;
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $years = $this->years;
        $baselineYear = $years[0];
        $remainingYears = array_slice($years, 1);

        $aspects = $repository->getKinerjaPenyelenggaraanData($years);

        // Filter search / SKPD jika pengguna melakukan pencarian atau memilih SKPD
        if (!empty($this->search) || !empty($this->selectedSkpd)) {
            $aspects = $aspects->map(function ($aspect) {
                $filteredIndikators = $aspect['indikators']->filter(function ($ind) {
                    $matchSearch = empty($this->search) || str_contains(
                        strtolower($ind['nama_indikator']),
                        strtolower($this->search)
                    );
                    $matchSkpd = empty($this->selectedSkpd) || str_contains(
                        strtolower($ind['skpd']),
                        strtolower($this->selectedSkpd)
                    );

                    return $matchSearch && $matchSkpd;
                })->values();

                return [
                    'id' => $aspect['id'],
                    'nama_aspek' => $aspect['nama_aspek'],
                    'indikators' => $filteredIndikators,
                ];
            })->filter(function ($aspect) {
                return $aspect['indikators']->isNotEmpty();
            })->values();
        }

        // Ambil hanya SKPD yang memiliki indikator penyelenggaraan
        $skpdList = \App\Models\Skpd::whereHas('indikator', function ($q) {
            $q->whereHas('kategori', fn ($k) => $k->where('nama_kategori', 'Penyelenggaraan'));
        })->orderBy('nama_skpd')->pluck('nama_skpd')->toArray();

        return view('livewire.public-kinerja-penyelenggaraan', [
            'aspects'          => $aspects,
            'years'            => $years,
            'baselineYear'     => $baselineYear,
            'remainingYears'   => $remainingYears,
            'availablePeriods' => $this->availablePeriods,
            'skpdList'         => $skpdList,
        ]);
    }
}
