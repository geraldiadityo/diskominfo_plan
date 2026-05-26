<?php

namespace App\Repositories;

interface DashboardRepositoryInterface
{
    /**
     * Get the total number of SKPDs.
     *
     * @return int
     */
    public function getSkpdCount(): int;

    /**
     * Get the total realized budget.
     *
     * @return float
     */
    public function getRealisasiKeuanganTotal(): float;

    /**
     * Get the total target revenue.
     *
     * @return float
     */
    public function getTargetPendapatanTotal(): float;

    /**
     * Get chart data for revenue (target vs realized).
     *
     * @return array
     */
    public function getChartDataPendapatan(): array;

    /**
     * Get SKPD by ID.
     *
     * @param int $skpdId
     * @return \App\Models\Skpd|null
     */
    public function getSkpdById(int $skpdId);

    /**
     * Get summary of Pendapatan for all SKPDs.
     *
     * @param int|null $tahun
     * @return \Illuminate\Support\Collection
     */
    public function getAllSkpdPendapatanSummary(?int $tahun = null);

    /**
     * Get detailed Pendapatan summary for a specific SKPD.
     *
     * @param int $skpdId
     * @param int|null $tahun
     * @return array
     */
    public function getPendapatanDetailBySkpd(int $skpdId, ?int $tahun = null): array;

    public function getRekeningPendapatanBySkpd(int $skpdId, ?int $tahun = null);

    /**
     * Get chart data for Renja (target_keuangan vs realisasi_keuangan).
     *
     * @return array
     */
    public function getChartDataRenja(): array;

    /**
     * Get summary of Renja for all SKPDs.
     *
     * @param int|null $tahun
     * @return \Illuminate\Support\Collection
     */
    public function getAllSkpdRenjaSummary(?int $tahun = null);

    /**
     * Get detailed Renja summary for a specific SKPD.
     *
     * @param int $skpdId
     * @param int|null $tahun
     * @return array
     */
    public function getRenjaDetailBySkpd(int $skpdId, ?int $tahun = null): array;

    /**
     * Get the list of Sub Kegiatan with target and realisasi for a specific SKPD.
     *
     * @param int $skpdId
     * @param int|null $tahun
     * @return \Illuminate\Support\Collection
     */
    public function getSubKegiatanRenjaBySkpd(int $skpdId, ?int $tahun = null);

    /**
     * Get grouped table data for Renja (Program -> Kegiatan -> Sub Kegiatan)
     *
     * @param int $skpdId
     * @param int|null $tahun
     * @return array
     */
    public function getRenjaTableDataBySkpd(int $skpdId, ?int $tahun = null): array;

    /**
     * Get summary of pendapatan grouped by jenis PAD (Pajak, Retribusi, etc)
     *
     * @param int|null $tahun
     * @return array
     */
    public function getPendapatanSummaryByJenisRekening(?int $tahun = null): array;

    /**
     * Get detailed list of rekening for a specific PAD category (Pajak, Retribusi, dll)
     *
     * @param string $kodePrefix
     * @param int|null $tahun
     * @return array
     */
    public function getDetailRekeningByJenis(string $kodePrefix, ?int $tahun = null): array;

    /**
     * Get the full hierarchical tree of Pendapatan for a specific year.
     *
     * @param int|null $tahun
     * @return array
     */
    public function getPendapatanTree(?int $tahun = null): array;
}
