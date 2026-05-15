<?php

namespace App\Http\Controllers;

use App\Models\BuffetReportEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BuffetReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'report_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'metric' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'amount_from' => ['nullable', 'numeric'],
            'amount_to' => ['nullable', 'numeric'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $query = BuffetReportEntry::query()
            ->orderByDesc('report_year')
            ->orderBy('sort_order')
            ->orderBy('metric')
            ->orderBy('id');

        $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 50);
        $entries = $query->paginate($perPage)->withQueryString();

        $summaryQuery = BuffetReportEntry::query();
        $this->applyFilters($summaryQuery, $filters);

        return view('buffet-reports.index', [
            'entries' => $entries,
            'filters' => $filters,
            'metrics' => BuffetReportEntry::METRICS,
            'totalCount' => (clone $summaryQuery)->count(),
            'totalAmount' => (clone $summaryQuery)->sum('amount'),
            'incomeAmount' => $this->sumMetric(clone $summaryQuery, 'income'),
            'expenseAmount' => $this->sumMetric(clone $summaryQuery, 'expense'),
            'profitLossAmount' => $this->sumMetric(clone $summaryQuery, 'profit_loss'),
            'years' => BuffetReportEntry::query()->distinct()->orderByDesc('report_year')->pluck('report_year'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        BuffetReportEntry::create($this->validatedData($request));

        return redirect()->route('buffet-reports.index')->with('toast_success', 'Запись буфета добавлена.');
    }

    public function update(Request $request, BuffetReportEntry $buffetReport): RedirectResponse
    {
        $buffetReport->update($this->validatedData($request));

        return redirect()->route('buffet-reports.index', $this->filterParameters($request))->with('status', 'Запись буфета обновлена.');
    }

    public function destroy(Request $request, BuffetReportEntry $buffetReport): RedirectResponse
    {
        $buffetReport->delete();

        return redirect()->route('buffet-reports.index', $this->filterParameters($request))->with('status', 'Запись буфета удалена.');
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['report_year'] ?? null, fn (Builder $query, int|string $year) => $query->where('report_year', $year))
            ->when($filters['metric'] ?? null, fn (Builder $query, string $metric) => $query->where('metric', $metric))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('period_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('period_date', '<=', $date))
            ->when($filters['amount_from'] ?? null, fn (Builder $query, string $amount) => $query->where('amount', '>=', $amount))
            ->when($filters['amount_to'] ?? null, fn (Builder $query, string $amount) => $query->where('amount', '<=', $amount));
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'report_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'period_label' => ['required', 'string', 'max:255'],
            'period_date' => ['nullable', 'date'],
            'metric' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:-999999999999.99', 'max:999999999999.99'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $data['period_date'] = $data['period_date'] ?? null;
        $data['amount'] = $data['amount'] ?? 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }

    private function sumMetric(Builder $query, string $metric): float
    {
        return (float) $query->where('metric', $metric)->sum('amount');
    }

    private function filterKeys(): array
    {
        return ['report_year', 'metric', 'date_from', 'date_to', 'amount_from', 'amount_to', 'per_page', 'page'];
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
