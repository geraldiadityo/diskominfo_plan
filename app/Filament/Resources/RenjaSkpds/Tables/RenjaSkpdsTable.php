<?php

namespace App\Filament\Resources\RenjaSkpds\Tables;

use App\Models\RenjaSkpd;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RenjaSkpdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->hidden(fn() => Auth::user()->role === 'SKPD'),

                TextColumn::make('subKegiatan.kegiatan.program.bidang.urusan.nomenklatur')
                    ->label('Urusan')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subKegiatan.kegiatan.program.bidang.nomenklatur')
                    ->label('Bidang')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subKegiatan.kegiatan.program.nomenklatur')
                    ->label('Program')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subKegiatan.kegiatan.nomenklatur')
                    ->label('Kegiatan')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subKegiatan.kode_lengkap')
                    ->label('Kode Sub Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('subKegiatan.nomenklatur')
                    ->label('Sub Kegiatan')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('pagu_anggaran')
                    ->label('Pagu Anggaran')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->color(fn($state) => $state == 0 ? 'danger' : 'success')
                    ->description(fn($record) => $record->pagu_anggaran == 0 ? 'Anggaran Belum di isi' : '')
                    ->toggleable(),

                TextColumn::make('target_keuangan')
                    ->label('Target Keuangan')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('target_kinerja')
                    ->label('Target')
                    ->wrap()
                    ->placeholder('Belum Diisi')
                    ->toggleable(),
            ])
            ->filters([
                //
                SelectFilter::make('tahun')
                    ->options([
                        '2024' => '2024',
                        '2025' => '2025',
                        '2026' => '2026',
                        '2027' => '2027',
                        '2028' => '2028',
                        '2029' => '2029',
                        '2030' => '2030'
                    ])
                    ->default(date('Y')),
            ])
            ->recordActions([
                Action::make('isiAnggaran')
                    ->label('Isi Anggaran')
                    ->icon(Heroicon::CurrencyDollar)
                    ->color('success')
                    ->fillForm(fn(RenjaSkpd $record): array => [
                        'pagu_anggaran' => $record->pagu_anggaran,
                        'target_keuangan' => $record->target_keuangan,
                        'target_kinerja' => $record->target_kinerja,
                    ])
                    ->form([
                        TextInput::make('pagu_anggaran')
                            ->label('Pagu Anggaran (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        TextInput::make('target_keuangan')
                            ->label('Target Keuangan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Textarea::make('target_kinerja')
                            ->label('Target Kinerja fisik / volume')
                            ->required(),
                    ])
                    ->action(function (RenjaSkpd $record, array $data): void {
                        $record->update([
                            'pagu_anggaran' => $data['pagu_anggaran'],
                            'target_kinerja' => $data['target_kinerja'],
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
