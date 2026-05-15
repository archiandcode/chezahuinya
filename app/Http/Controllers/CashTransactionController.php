<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashTransactionController extends Controller
{
    private const OPENING_BALANCE = 318943.20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'company' => ['nullable', 'string', 'max:255'],
            'cash_flow' => ['nullable', 'string', 'max:255'],
            'has_supporting_document' => ['nullable', 'in:yes,no'],
            'direction' => ['nullable', 'in:income,expense'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $query = CashTransaction::query()
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
            'openingBalance' => self::OPENING_BALANCE,
            'totalIncome' => (clone $summaryQuery)->sum('income_amount'),
            'totalExpense' => (clone $summaryQuery)->sum('expense_amount'),
            'totalCount' => (clone $summaryQuery)->count(),
            'currentBalance' => $this->currentBalance(),
            'companies' => CashTransaction::query()
                ->whereNotNull('company')
                ->where('company', '<>', '')
                ->distinct()
                ->orderBy('company')
                ->pluck('company'),
            'cashFlows' => CashTransaction::query()
                ->whereNotNull('cash_flow')
                ->where('cash_flow', '<>', '')
                ->distinct()
                ->orderBy('cash_flow')
                ->pluck('cash_flow'),
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

        $redirectFilters = $request->only([
            'date_from',
            'date_to',
            'company',
            'cash_flow',
            'direction',
            'per_page',
            'page',
        ]);

        if ($request->filled('filter_has_supporting_document')) {
            $redirectFilters['has_supporting_document'] = $request->input('filter_has_supporting_document');
        }

        return redirect()
            ->route('cash-transactions.index', $redirectFilters)
            ->with('status', 'Запись кассы обновлена.');
    }

    public function destroy(Request $request, CashTransaction $cashTransaction): RedirectResponse
    {
        $cashTransaction->delete();

        return redirect()
            ->route('cash-transactions.index', $request->only([
                'date_from',
                'date_to',
                'company',
                'cash_flow',
                'has_supporting_document',
                'direction',
                'per_page',
                'page',
            ]))
            ->with('status', 'Запись кассы удалена.');
    }

    /**
     * @param Builder<CashTransaction> $query
     * @param array<string, mixed> $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('transaction_date', '<=', $date))
            ->when($filters['company'] ?? null, fn (Builder $query, string $company) => $query->where('company', $company))
            ->when($filters['cash_flow'] ?? null, fn (Builder $query, string $cashFlow) => $query->where('cash_flow', $cashFlow))
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
            'transaction_date' => ['required', 'date'],
            'income_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'expense_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'company' => ['nullable', 'string', 'max:255'],
            'cash_flow' => ['nullable', 'string', 'max:255'],
            'has_supporting_document' => ['nullable', 'boolean'],
        ]);

        $data['income_amount'] = $data['income_amount'] ?? 0;
        $data['expense_amount'] = $data['expense_amount'] ?? 0;
        $data['company'] = $data['company'] ?? null;
        $data['cash_flow'] = $data['cash_flow'] ?? null;
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
        $balance = self::OPENING_BALANCE;
        $balances = [];

        CashTransaction::query()
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get(['id', 'income_amount', 'expense_amount'])
            ->each(function (CashTransaction $transaction) use (&$balance, &$balances): void {
                $balance += (float) $transaction->income_amount - (float) $transaction->expense_amount;
                $balances[$transaction->id] = $balance;
            });

        return $balances;
    }

    private function currentBalance(): float
    {
        return self::OPENING_BALANCE
            + (float) CashTransaction::query()->sum('income_amount')
            - (float) CashTransaction::query()->sum('expense_amount');
    }
}
