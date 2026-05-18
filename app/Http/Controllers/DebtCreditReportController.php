<?php

namespace App\Http\Controllers;

use App\Models\DebtCreditEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DebtCreditReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'section' => ['nullable', Rule::in(array_keys(DebtCreditEntry::SECTIONS))],
            'group_name' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'amount_from' => ['nullable', 'numeric', 'min:0'],
            'amount_to' => ['nullable', 'numeric', 'min:0'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $query = DebtCreditEntry::query()
            ->orderByDesc('report_date')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('id');

        $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 50);
        $entries = $query->paginate($perPage)->withQueryString();

        $summaryQuery = DebtCreditEntry::query();
        $this->applyFilters($summaryQuery, $filters);

        $reportQuery = DebtCreditEntry::query()
            ->orderByDesc('report_date')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('id');
        $this->applyFilters($reportQuery, $filters);
        $reportEntries = $reportQuery->get();

        return view('debt-credit-reports.index', [
            'entries' => $entries,
            'filters' => $filters,
            'sections' => DebtCreditEntry::SECTIONS,
            'totalCount' => (clone $summaryQuery)->count(),
            'totalAmount' => (clone $summaryQuery)->sum('amount'),
            'creditorAmount' => $this->sumSection(clone $summaryQuery, DebtCreditEntry::SECTION_CREDITOR),
            'debtorAmount' => $this->sumSection(clone $summaryQuery, DebtCreditEntry::SECTION_DEBTOR),
            'groups' => DebtCreditEntry::query()
                ->whereNotNull('group_name')
                ->where('group_name', '<>', '')
                ->distinct()
                ->orderBy('group_name')
                ->pluck('group_name'),
            'companies' => DebtCreditEntry::query()
                ->whereNotNull('company')
                ->where('company', '<>', '')
                ->distinct()
                ->orderBy('company')
                ->pluck('company'),
            'reportEntriesBySection' => $reportEntries->groupBy('section'),
            'defaultReportDate' => DebtCreditEntry::query()->max('report_date') ?? now()->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DebtCreditEntry::create($this->validatedData($request));

        return redirect()
            ->route('debt-credit-reports.index')
            ->with('toast_success', 'Запись Дт и Кт добавлена.');
    }

    public function update(Request $request, DebtCreditEntry $debtCreditReport): RedirectResponse
    {
        $debtCreditReport->update($this->validatedData($request));

        return redirect()
            ->route('debt-credit-reports.index', $this->filterParameters($request))
            ->with('status', 'Запись Дт и Кт обновлена.');
    }

    public function destroy(Request $request, DebtCreditEntry $debtCreditReport): RedirectResponse
    {
        $debtCreditReport->delete();

        return redirect()
            ->route('debt-credit-reports.index', $this->filterParameters($request))
            ->with('status', 'Запись Дт и Кт удалена.');
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('report_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('report_date', '<=', $date))
            ->when($filters['section'] ?? null, fn (Builder $query, string $section) => $query->where('section', $section))
            ->when($filters['group_name'] ?? null, fn (Builder $query, string $group) => $query->where('group_name', $group))
            ->when($filters['company'] ?? null, fn (Builder $query, string $company) => $query->where('company', $company))
            ->when($filters['amount_from'] ?? null, fn (Builder $query, string $amount) => $query->where('amount', '>=', $amount))
            ->when($filters['amount_to'] ?? null, fn (Builder $query, string $amount) => $query->where('amount', '<=', $amount));
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'report_date' => ['required', 'date'],
            'section' => ['required', Rule::in(array_keys(DebtCreditEntry::SECTIONS))],
            'group_name' => ['nullable', 'string', 'max:255'],
            'counterparty' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'company' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $data['amount'] = $data['amount'] ?? 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['group_name'] = $data['group_name'] ?? null;
        $data['company'] = $data['company'] ?? null;
        $data['note'] = $data['note'] ?? null;

        return $data;
    }

    private function sumSection(Builder $query, string $section): float
    {
        return (float) $query->where('section', $section)->sum('amount');
    }

    private function filterKeys(): array
    {
        return ['date_from', 'date_to', 'section', 'group_name', 'company', 'amount_from', 'amount_to', 'per_page', 'page'];
    }

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
