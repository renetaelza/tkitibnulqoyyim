<?php

namespace App\Services;

use App\Models\FundSource;
use App\Models\FundTransaction;
use Illuminate\Support\Facades\DB;

class FundBalanceService
{
    /**
     * Saldo aktual = sum(in) - sum(out) untuk source code tertentu.
     */
    public function balanceFor(string $code): float
    {
        $source = FundSource::query()->where('code', $code)->first();
        if (!$source) {
            return 0.0;
        }

        return $this->balanceForId((int) $source->id);
    }

    public function balanceForId(int $fundSourceId): float
    {
        $row = FundTransaction::query()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN direction = 'in' THEN amount ELSE 0 END), 0) AS total_in,
                COALESCE(SUM(CASE WHEN direction = 'out' THEN amount ELSE 0 END), 0) AS total_out
            ")
            ->where('id_fund_source', $fundSourceId)
            ->first();

        return (float) (($row->total_in ?? 0) - ($row->total_out ?? 0));
    }

    /**
     * @return array<string, float>  ['spp' => 1250000, 'bop' => 300000, ...]
     */
    public function balancesAll(): array
    {
        $rows = DB::table('fund_transactions as ft')
            ->join('fund_sources as fs', 'fs.id', '=', 'ft.id_fund_source')
            ->selectRaw("
                fs.code AS code,
                COALESCE(SUM(CASE WHEN ft.direction = 'in' THEN ft.amount ELSE 0 END), 0) AS total_in,
                COALESCE(SUM(CASE WHEN ft.direction = 'out' THEN ft.amount ELSE 0 END), 0) AS total_out
            ")
            ->groupBy('fs.code')
            ->get();

        $balances = [];
        foreach (FundSource::query()->active()->pluck('code') as $code) {
            $balances[$code] = 0.0;
        }
        foreach ($rows as $row) {
            $balances[$row->code] = (float) ($row->total_in - $row->total_out);
        }

        return $balances;
    }

    public function totalBalance(): float
    {
        return array_sum($this->balancesAll());
    }
}
