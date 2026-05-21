<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\PublicDashboard;
use App\Livewire\DetailPendapatan;
use App\Livewire\IndexPendapatan;
use App\Livewire\DetailJenisPendapatan;

use App\Livewire\IndexRenja;
use App\Livewire\DetailRenja;

Route::get('/', PublicDashboard::class)->name('dashboard');
Route::get('/pendapatan', IndexPendapatan::class)->name('pendapatan.index');
Route::get('/pendapatan/detail/{id}', DetailPendapatan::class)->name('pendapatan.detail');
Route::get('/pendapatan/jenis/{jenis}', DetailJenisPendapatan::class)->name('pendapatan.jenis');
Route::get('/renja', IndexRenja::class)->name('renja.index');
Route::get('/renja/{skpdId}', DetailRenja::class)->name('renja.detail');
