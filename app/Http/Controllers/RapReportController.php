<?php

namespace App\Http\Controllers;

use App\Models\RapReportEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RapReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'section' => ['nullable', Rule::in(array_keys(RapReportEntry::SECTIONS))],
            'counterparty' => ['nullable', 'string', 'max:255'],
            'sale_month' => ['nullable', 'string', 'max:255'],
            'payment_status' => ['nullable', 'in:paid,unpaid,unknown'],
            'amount_from' => ['nullable', 'numeric', 'min:0'],
            'amount_to' => ['nullable', 'numeric', 'min:0'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $query = RapReportEntry::query()
            ->orderByDesc('report_date')
            ->orderBy('section')
            ->orderByDesc('id');

        $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 25);
        $entries = $query
            ->paginate($perPage)
            ->withQueryString();

        $summaryQuery = RapReportEntry::query();
        $this->applyFilters($summaryQuery, $filters);

        return view('rap-reports.index', [
            'entries' => $entries,
            'filters' => $filters,
            'sections' => RapReportEntry::SECTIONS,
            'totalCount' => (clone $summaryQuery)->count(),
            'totalQuantity' => (clone $summaryQuery)->sum('quantity'),
            'totalSaleAmount' => (clone $summaryQuery)->sum('sale_amount'),
            'totalPaidAmount' => (clone $summaryQuery)->sum('paid_amount'),
            'totalUnpaidAmount' => (clone $summaryQuery)->sum('unpaid_amount'),
            'counterparties' => RapReportEntry::query()
                ->whereNotNull('counterparty')
                ->where('counterparty', '<>', '')
                ->distinct()
                ->orderBy('counterparty')
                ->pluck('counterparty'),
            'saleMonths' => RapReportEntry::query()
                ->whereNotNull('sale_month')
                ->where('sale_month', '<>', '')
                ->distinct()
                ->orderBy('sale_month')
                ->pluck('sale_month'),
            'defaultReportDate' => RapReportEntry::query()->max('report_date') ?? now()->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        RapReportEntry::create($this->validatedData($request));

        return redirect()
            ->route('rap-reports.index')
            ->with('toast_success', 'Запись отчета RAP добавлена.');
    }

    public function update(Request $request, RapReportEntry $rapReport): RedirectResponse
    {
        $rapReport->update($this->validatedData($request));

        return redirect()
            ->route('rap-reports.index', $this->filterParameters($request))
            ->with('status', 'Запись отчета RAP обновлена.');
    }

    public function destroy(Request $request, RapReportEntry $rapReport): RedirectResponse
    {
        $rapReport->delete();

        return redirect()
            ->route('rap-reports.index', $this->filterParameters($request))
            ->with('status', 'Запись отчета RAP удалена.');
    }

    /**
     * @param Builder<RapReportEntry> $query
     * @param array<string, mixed> $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('report_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('report_date', '<=', $date))
            ->when($filters['section'] ?? null, fn (Builder $query, string $section) => $query->where('section', $section))
            ->when($filters['counterparty'] ?? null, fn (Builder $query, string $counterparty) => $query->where('counterparty', $counterparty))
            ->when($filters['sale_month'] ?? null, fn (Builder $query, string $month) => $query->where('sale_month', $month))
            ->when($filters['payment_status'] ?? null, function (Builder $query, string $status): void {
                match ($status) {
                    'paid' => $query->where('is_paid', true),
                    'unpaid' => $query->where('is_paid', false),
                    default => $query->whereNull('is_paid'),
                };
            })
            ->when($filters['amount_from'] ?? null, fn (Builder $query, string $amount) => $query->where('sale_amount', '>=', $amount))
            ->when($filters['amount_to'] ?? null, fn (Builder $query, string $amount) => $query->where('sale_amount', '<=', $amount));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'report_date' => ['required', 'date'],
            'section' => ['required', Rule::in(array_keys(RapReportEntry::SECTIONS))],
            'counterparty' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'sale_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'sale_month' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['nullable', 'date'],
            'actual_payment_date' => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'planned_payment_date' => ['nullable', 'date'],
            'unpaid_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'is_paid' => ['nullable', 'boolean'],
            'comment' => ['nullable', 'string'],
        ]);

        foreach (['quantity', 'unit_price', 'sale_amount', 'paid_amount', 'unpaid_amount'] as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        foreach (['counterparty', 'sale_month', 'invoice_date', 'actual_payment_date', 'planned_payment_date', 'comment'] as $field) {
            $data[$field] = $data[$field] ?? null;
        }

        $data['is_paid'] = $request->has('is_paid') ? (bool) $request->boolean('is_paid') : null;

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
            'section',
            'counterparty',
            'sale_month',
            'payment_status',
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
