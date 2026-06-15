<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class PublicKinerjaProgram extends Component
{
    public $years = [2021, 2022, 2023, 2024, 2025, 2026];
    
    #[Url(as: 'bidang')]
    public $selectedBidangId = null;
    
    public $selectedBidangName = null;

    public function mount()
    {
        if ($this->selectedBidangId) {
            $this->loadBidangName();
        }
    }

    public function loadBidangName()
    {
        $bidang = \App\Models\ProgramBidang::find($this->selectedBidangId);
        $this->selectedBidangName = $bidang ? $bidang->nomenklatur : 'Bidang';
    }

    public function showBidang($bidangId)
    {
        $this->selectedBidangId = $bidangId;
        $this->loadBidangName();
    }

    public function goBack()
    {
        $this->selectedBidangId = null;
        $this->selectedBidangName = null;
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $kinerjaData = $repository->getKinerjaProgramData($this->years);

        // If a Bidang is selected, we find its data to pass specifically, or we can just pass the whole data
        // and filter in the view.
        
        return view('livewire.public-kinerja-program', [
            'kinerjaData' => $kinerjaData,
        ]);
    }
}
