<?php

namespace App\Http\Controllers;

use App\Models\TradingStockBalance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TradingStockBalanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'product_group' => ['nullable', 'string', 'max:255'],
            'quantity_from' => ['nullable', 'numeric', 'min:0'],
            'quantity_to' => ['nullable', 'numeric', 'min:0'],
            'amount_from' => ['nullable', 'numeric', 'min:0'],
            'amount_to' => ['nullable', 'numeric', 'min:0'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $query = TradingStockBalance::query()
            ->orderByDesc('balance_date')
            ->orderBy('sort_order')
            ->orderBy('id');

        $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 25);
        $balances = $query
            ->paginate($perPage)
            ->withQueryString();

        $summaryQuery = TradingStockBalance::query();
        $this->applyFilters($summaryQuery, $filters);

        return view('trading-stock-balances.index', [
            'balances' => $balances,
            'filters' => $filters,
            'totalCount' => (clone $summaryQuery)->count(),
            'totalQuantity' => (clone $summaryQuery)->sum('quantity'),
            'totalCostAmount' => (clone $summaryQuery)->sum('cost_amount'),
            'groupsCount' => (clone $summaryQuery)->whereNotNull('product_group')->distinct('product_group')->count('product_group'),
            'productGroups' => TradingStockBalance::query()
                ->whereNotNull('product_group')
                ->where('product_group', '<>', '')
                ->distinct()
                ->orderBy('product_group')
                ->pluck('product_group'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TradingStockBalance::create($this->validatedData($request));

        return redirect()
            ->route('trading-stock-balances.index')
            ->with('toast_success', 'Запись остатка по торговым добавлена.');
    }

    public function update(Request $request, TradingStockBalance $tradingStockBalance): RedirectResponse
    {
        $tradingStockBalance->update($this->validatedData($request));

        return redirect()
            ->route('trading-stock-balances.index', $this->filterParameters($request))
            ->with('status', 'Запись остатка по торговым обновлена.');
    }

    public function destroy(Request $request, TradingStockBalance $tradingStockBalance): RedirectResponse
    {
        $tradingStockBalance->delete();

        return redirect()
            ->route('trading-stock-balances.index', $this->filterParameters($request))
            ->with('status', 'Запись остатка по торговым удалена.');
    }

    /**
     * @param Builder<TradingStockBalance> $query
     * @param array<string, mixed> $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('balance_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('balance_date', '<=', $date))
            ->when($filters['product_group'] ?? null, fn (Builder $query, string $group) => $query->where('product_group', $group))
            ->when($filters['quantity_from'] ?? null, fn (Builder $query, string $quantity) => $query->where('quantity', '>=', $quantity))
            ->when($filters['quantity_to'] ?? null, fn (Builder $query, string $quantity) => $query->where('quantity', '<=', $quantity))
            ->when($filters['amount_from'] ?? null, fn (Builder $query, string $amount) => $query->where('cost_amount', '>=', $amount))
            ->when($filters['amount_to'] ?? null, fn (Builder $query, string $amount) => $query->where('cost_amount', '<=', $amount));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'balance_date' => ['required', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'product_group' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'cost_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['quantity'] = $data['quantity'] ?? 0;
        $data['cost_amount'] = $data['cost_amount'] ?? 0;

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
            'product_group',
            'quantity_from',
            'quantity_to',
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
