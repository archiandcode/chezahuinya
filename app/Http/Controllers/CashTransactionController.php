<?php

namespace App\Http\Controllers;

use App\Models\CashCompany;
use App\Models\CashFlowCategory;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cash_register_id' => ['nullable', 'integer', 'exists:cash_registers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'cash_company_id' => ['nullable', 'integer', 'exists:cash_companies,id'],
            'cash_flow_category_id' => ['nullable', 'integer', 'exists:cash_flow_categories,id'],
            'has_supporting_document' => ['nullable', 'in:yes,no'],
            'direction' => ['nullable', 'in:income,expense'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $query = CashTransaction::query()
            ->with(['cashRegister', 'cashCompany', 'cashFlowCategory'])
            ->orderBy('transaction_date')
            ->orderBy('id');

        $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $transactions = $query
            ->paginate($perPage)
            ->withQueryString();

        $balances = $this->runningBalances();
        $summaryQuery = CashTransaction::query();
        $this->applyFilters($summaryQuery, $filters);

        return view('cash-transactions.index', [
            'transactions' => $transactions,
            'balances' => $balances,
            'filters' => $filters,
            'openingBalance' => $this->openingBalance($filters),
            'totalIncome' => (clone $summaryQuery)->sum('income_amount'),
            'totalExpense' => (clone $summaryQuery)->sum('expense_amount'),
            'totalCount' => (clone $summaryQuery)->count(),
            'currentBalance' => $this->currentBalance($filters),
            'cashRegisters' => CashRegister::query()->orderByDesc('is_active')->orderBy('name')->get(),
            'companies' => CashCompany::query()->orderByDesc('is_active')->orderBy('name')->get(),
            'cashFlows' => CashFlowCategory::query()->orderByDesc('is_active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        CashTransaction::create($this->validatedData($request));

        return redirect()
            ->route('cash-transactions.index')
            ->with('toast_success', 'Запись кассы добавлена.');
    }

    public function update(Request $request, CashTransaction $cashTransaction): RedirectResponse
    {
        $cashTransaction->update($this->validatedData($request));

        return redirect()
            ->route('cash-transactions.index', $this->filterParameters($request))
            ->with('status', 'Запись кассы обновлена.');
    }

    public function destroy(Request $request, CashTransaction $cashTransaction): RedirectResponse
    {
        $cashTransaction->delete();

        return redirect()
            ->route('cash-transactions.index', $request->only([
                'cash_register_id',
                'date_from',
                'date_to',
                'cash_company_id',
                'cash_flow_category_id',
                'has_supporting_document',
                'direction',
                'per_page',
                'page',
            ]))
            ->with('toast_success', 'Запись кассы удалена.');
    }

    /**
     * @param Builder<CashTransaction> $query
     * @param array<string, mixed> $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['cash_register_id'] ?? null, fn (Builder $query, int $cashRegisterId) => $query->where('cash_register_id', $cashRegisterId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('transaction_date', '<=', $date))
            ->when($filters['cash_company_id'] ?? null, fn (Builder $query, int $companyId) => $query->where('cash_company_id', $companyId))
            ->when($filters['cash_flow_category_id'] ?? null, fn (Builder $query, int $categoryId) => $query->where('cash_flow_category_id', $categoryId))
            ->when($filters['has_supporting_document'] ?? null, function (Builder $query, string $value): void {
                $query->where('has_supporting_document', $value === 'yes');
            })
            ->when($filters['direction'] ?? null, function (Builder $query, string $direction): void {
                $direction === 'income'
                    ? $query->where('income_amount', '>', 0)
                    : $query->where('expense_amount', '>', 0);
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'cash_register_id' => ['required', 'integer', 'exists:cash_registers,id'],
            'transaction_date' => ['required', 'date'],
            'income_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'expense_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'cash_company_id' => ['nullable', 'integer', 'exists:cash_companies,id'],
            'cash_flow_category_id' => ['nullable', 'integer', 'exists:cash_flow_categories,id'],
            'has_supporting_document' => ['nullable', 'boolean'],
        ]);

        $data['income_amount'] = $data['income_amount'] ?? 0;
        $data['expense_amount'] = $data['expense_amount'] ?? 0;
        $data['cash_company_id'] = $data['cash_company_id'] ?? null;
        $data['cash_flow_category_id'] = $data['cash_flow_category_id'] ?? null;
        $data['company'] = $data['cash_company_id']
            ? CashCompany::query()->find($data['cash_company_id'])?->name
            : null;
        $data['cash_flow'] = $data['cash_flow_category_id']
            ? CashFlowCategory::query()->find($data['cash_flow_category_id'])?->name
            : null;
        $data['has_supporting_document'] = $request->has('has_supporting_document')
            ? (bool) $request->boolean('has_supporting_document')
            : null;

        if ((float) $data['income_amount'] <= 0 && (float) $data['expense_amount'] <= 0) {
            throw ValidationException::withMessages([
                'income_amount' => 'Укажите сумму поступления или расхода.',
            ]);
        }

        return $data;
    }

    /**
     * @return array<int, float>
     */
    private function runningBalances(): array
    {
        $balancesByRegister = CashRegister::query()
            ->pluck('opening_balance', 'id')
            ->map(fn (string $balance): float => (float) $balance)
            ->all();
        $balances = [];

        CashTransaction::query()
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get(['id', 'cash_register_id', 'income_amount', 'expense_amount'])
            ->each(function (CashTransaction $transaction) use (&$balancesByRegister, &$balances): void {
                $registerId = $transaction->cash_register_id;

                if (! $registerId) {
                    $registerId = 0;
                    $balancesByRegister[$registerId] ??= 0.0;
                }

                $balancesByRegister[$registerId] += (float) $transaction->income_amount - (float) $transaction->expense_amount;
                $balances[$transaction->id] = $balancesByRegister[$registerId];
            });

        return $balances;
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function openingBalance(array $filters): float
    {
        if ($filters['cash_register_id'] ?? null) {
            return (float) CashRegister::query()
                ->whereKey($filters['cash_register_id'])
                ->value('opening_balance');
        }

        return (float) CashRegister::query()->sum('opening_balance');
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function currentBalance(array $filters): float
    {
        $query = CashTransaction::query();
        $this->applyFilters($query, [
            'cash_register_id' => $filters['cash_register_id'] ?? null,
        ]);

        return $this->openingBalance($filters)
            + (float) $query->sum('income_amount')
            - (float) $query->sum('expense_amount');
    }

    /**
     * @return array<int, string>
     */
    private function filterKeys(): array
    {
        return [
            'cash_register_id',
            'date_from',
            'date_to',
            'cash_company_id',
            'cash_flow_category_id',
            'has_supporting_document',
            'direction',
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
            }
        }

        return array_filter($filters, fn (mixed $value): bool => filled($value));
    }
}
