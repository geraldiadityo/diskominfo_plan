<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Skpd;
use App\Models\KategoriIndikator;
use App\Models\SatuanIndikator;
use App\Models\ProgramUrusan;
use App\Models\ProgramBidang;
use App\Models\ProgramProgram;
use App\Models\ProgramKegiatan;
use App\Models\ProgramSubKegiatan;
use App\Models\Rekening;
use App\Models\MasterIndikator;
use App\Models\ProgulProgram;
use App\Models\ProgulIndikator;

class WarmupCache extends Command
{
    protected $signature   = 'cache:warmup';
    protected $description = 'Pre-populate Redis cache dengan data master';

    public function handle(): int
    {
        $this->info('Warming up cache...');

        Skpd::getAllCached();
        Skpd::getAllWithBidangCached();
        KategoriIndikator::getAllCached();
        SatuanIndikator::getAllCached();
        ProgramUrusan::getAllCached();
        ProgramBidang::getAllCached();
        ProgramProgram::getAllCached();
        ProgramKegiatan::getAllCached();
        ProgramSubKegiatan::getAllCached();
        Rekening::getAllCached();
        MasterIndikator::getIkuCached();
        MasterIndikator::getPenyelenggaraanCached();
        ProgulProgram::getAllWithIndikatorsCached();
        ProgulIndikator::getAllCached();

        $this->info('✅ Cache warmup selesai!');
        return self::SUCCESS;
    }
}

