<?php

namespace App\Http\Controllers;

use App\Models\CashBalance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashBalanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'company' => ['nullable', 'string', 'max:255'],
            'amount_from' => ['nullable', 'numeric', 'min:0'],
            'amount_to' => ['nullable', 'numeric', 'min:0'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $query = CashBalance::query()
            ->orderByDesc('balance_date')
            ->orderBy('sort_order')
            ->orderBy('id');

        $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 50);
        $balances = $query
            ->paginate($perPage)
            ->withQueryString();

        $summaryQuery = CashBalance::query();
        $this->applyFilters($summaryQuery, $filters);

        return view('cash-balances.index', [
            'balances' => $balances,
            'filters' => $filters,
            'totalCount' => (clone $summaryQuery)->count(),
            'totalBalance' => (clone $summaryQuery)->sum('balance_amount'),
            'totalCustodyAssets' => (clone $summaryQuery)->sum('custody_assets_amount'),
            'totalOwnAssets' => (clone $summaryQuery)->sum('own_assets_amount'),
            'companies' => CashBalance::query()
                ->whereNotNull('company')
                ->where('company', '<>', '')
                ->distinct()
                ->orderBy('company')
                ->pluck('company'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        CashBalance::create($this->validatedData($request));

        return redirect()
            ->route('cash-balances.index')
            ->with('toast_success', 'Запись остатка ДС добавлена.');
    }

    public function update(Request $request, CashBalance $cashBalance): RedirectResponse
    {
        $cashBalance->update($this->validatedData($request));

        return redirect()
            ->route('cash-balances.index', $this->filterParameters($request))
            ->with('status', 'Запись остатка ДС обновлена.');
    }

    public function destroy(Request $request, CashBalance $cashBalance): RedirectResponse
    {
        $cashBalance->delete();

        return redirect()
            ->route('cash-balances.index', $this->filterParameters($request))
            ->with('status', 'Запись остатка ДС удалена.');
    }

    /**
     * @param Builder<CashBalance> $query
     * @param array<string, mixed> $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('balance_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('balance_date', '<=', $date))
            ->when($filters['company'] ?? null, fn (Builder $query, string $company) => $query->where('company', $company))
            ->when($filters['amount_from'] ?? null, fn (Builder $query, string $amount) => $query->where('balance_amount', '>=', $amount))
            ->when($filters['amount_to'] ?? null, fn (Builder $query, string $amount) => $query->where('balance_amount', '<=', $amount));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'balance_date' => ['required', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'company' => ['required', 'string', 'max:255'],
            'balance_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'custody_assets_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'own_assets_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['balance_amount'] = $data['balance_amount'] ?? 0;
        $data['custody_assets_amount'] = $data['custody_assets_amount'] ?? 0;
        $data['own_assets_amount'] = $data['own_assets_amount'] ?? 0;

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function filterKeys(): array
    {
        return [
            'date_from',
            'date_to',
            'company',
            'amount_from',
            'amount_to',
            'per_page',
            'page',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filterParameters(Request $request): array
    {
        $filters = [];

        foreach ($this->filterKeys() as $key) {
            $prefixedKey = 'filter_'.$key;

            if ($request->has($prefixedKey)) {
                $filters[$key] = $request->input($prefixedKey);
            } elseif ($request->has($key)) {
                $filters[$key] = $request->input($key);
            }
        }

        return $filters;
    }
}
