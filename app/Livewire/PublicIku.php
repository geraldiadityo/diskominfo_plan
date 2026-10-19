<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class PublicIku extends Component
{
    #[Url(as: 'tahun_awal')]
    public int $filterStartYear = 2025;

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
            2025 => '2025 – 2030',
            2024 => '2024 – 2029',
            2021 => '2021 – 2026',
            2022 => '2022 – 2027',
            2023 => '2023 – 2028',
        ];
    }

    public function resetFilter(): void
    {
        $this->search = '';
        $this->selectedSkpd = '';
        $this->filterStartYear = 2025;
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $years = $this->years;
        $kategoriData = $repository->getIkuData($years);

        // Filter pencarian dan SKPD jika pengguna memasukkan kata kunci atau memilih SKPD
        if (!empty($this->search) || !empty($this->selectedSkpd)) {
            $kategoriData = $kategoriData->map(function ($kategori) {
                $filteredIndikators = $kategori['indikators']->filter(function ($ind) {
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
                    'kategori_id'   => $kategori['kategori_id'],
                    'nama_kategori' => $kategori['nama_kategori'],
                    'indikators'    => $filteredIndikators,
                ];
            })->filter(function ($kategori) {
                return $kategori['indikators']->isNotEmpty();
            })->values();
        }

        // Ambil daftar SKPD yang memiliki indikator kinerja utama (IKU)
        $skpdList = \App\Models\Skpd::whereHas('indikator', function ($q) {
            $q->whereHas('kategori', fn ($k) => $k->where('nama_kategori', 'IKU'));
        })->orderBy('nama_skpd')->pluck('nama_skpd')->toArray();

        return view('livewire.public-iku', [
            'kategoriData'     => $kategoriData,
            'years'            => $years,
            'availablePeriods' => $this->availablePeriods,
            'skpdList'         => $skpdList,
            'filterStartYear'  => $this->filterStartYear,
            'search'           => $this->search,
            'selectedSkpd'     => $this->selectedSkpd,
        ]);
    }
}
