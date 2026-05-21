<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class IndexRenja extends Component
{
    public $tahun;

    public function mount()
    {
        $this->tahun = date('Y');
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $skpds = $repository->getAllSkpdRenjaSummary($this->tahun);

        // Sort descending by percentage
        $skpds = $skpds->sortByDesc('persentase')->values();

        return view('livewire.index-renja', [
            'skpds' => $skpds,
        ]);
    }
}
