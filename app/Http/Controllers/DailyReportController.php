<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PreservesFilterParameters;
use App\Models\DailyReportEntry;
use App\Models\DailyReportType;
use App\Models\ReportCompany;
use App\Models\ReportCompanyAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DailyReportController extends Controller
{
    use PreservesFilterParameters;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'report_company_id' => ['nullable', 'integer', 'exists:report_companies,id'],
            'report_company_account_id' => ['nullable', 'integer', 'exists:report_company_accounts,id'],
            'daily_report_type_id' => ['nullable', 'integer', 'exists:daily_report_types,id'],
            'direction' => ['nullable', 'in:opening,income,expense'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $query = DailyReportEntry::query()
            ->with(['company', 'account', 'type'])
            ->orderByDesc('report_date')
            ->orderByDesc('id');

        $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $entries = $query
            ->paginate($perPage)
            ->withQueryString();

        $summaryQuery = DailyReportEntry::query()->with('type');
        $this->applyFilters($summaryQuery, $filters);

        $opening = $this->sumByDirection(clone $summaryQuery, 'opening');
        $income = $this->sumByDirection(clone $summaryQuery, 'income');
        $expense = $this->sumByDirection(clone $summaryQuery, 'expense');

        $matrixCompanies = $companies = ReportCompany::query()
            ->with('accounts')
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        $types = DailyReportType::query()
            ->orderBy('direction')
            ->orderBy('sort_order')
            ->get();

        $matrixEntriesQuery = DailyReportEntry::query()->with('type');
        $this->applyFilters($matrixEntriesQuery, $filters);
        $matrixEntries = $matrixEntriesQuery->get();

        if ($filters['report_company_id'] ?? null) {
            $matrixCompanies = $matrixCompanies->where('id', (int) $filters['report_company_id'])->values();
        }

        $matrixTypes = $types;
        if ($filters['daily_report_type_id'] ?? null) {
            $matrixTypes = $matrixTypes->where('id', (int) $filters['daily_report_type_id'])->values();
        }
        if ($filters['direction'] ?? null) {
            $matrixTypes = $matrixTypes->where('direction', $filters['direction'])->values();
        }

        $matrixAmounts = $matrixEntries
            ->groupBy(fn (DailyReportEntry $entry): string => $entry->daily_report_type_id.'_'.$entry->report_company_id)
            ->map(fn ($entries): float => (float) $entries->sum('amount'));

        return view('daily-reports.index', [
            'entries' => $entries,
            'filters' => $filters,
            'totalCount' => (clone $summaryQuery)->count(),
            'openingAmount' => $opening,
            'incomeAmount' => $income,
            'expenseAmount' => $expense,
            'closingAmount' => $opening + $income - $expense,
            'companiesCount' => ReportCompany::query()->count(),
            'accountsCount' => ReportCompanyAccount::query()->count(),
            'companies' => $companies,
            'accounts' => ReportCompanyAccount::query()
                ->with('company')
                ->orderBy('account_number')
                ->get(),
            'types' => $types,
            'matrixCompanies' => $matrixCompanies,
            'matrixTypes' => $matrixTypes,
            'matrixAmounts' => $matrixAmounts,
            'defaultReportDate' => DailyReportEntry::query()->max('report_date') ?? now()->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DailyReportEntry::create($this->validatedData($request));

        return redirect()
            ->route('daily-reports.index')
            ->with('toast_success', 'Запись ежедневного отчета добавлена.');
    }

    public function update(Request $request, DailyReportEntry $dailyReport): RedirectResponse
    {
        $dailyReport->update($this->validatedData($request));

        return redirect()
            ->route('daily-reports.index', $this->filterParameters($request))
            ->with('status', 'Запись ежедневного отчета обновлена.');
    }

    public function destroy(Request $request, DailyReportEntry $dailyReport): RedirectResponse
    {
        $dailyReport->delete();

        return redirect()
            ->route('daily-reports.index', $this->filterParameters($request))
            ->with('status', 'Запись ежедневного отчета удалена.');
    }

    /**
     * @param  Builder<DailyReportEntry>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('report_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('report_date', '<=', $date))
            ->when($filters['report_company_id'] ?? null, fn (Builder $query, int|string $companyId) => $query->where('report_company_id', $companyId))
            ->when($filters['report_company_account_id'] ?? null, fn (Builder $query, int|string $accountId) => $query->where('report_company_account_id', $accountId))
            ->when($filters['daily_report_type_id'] ?? null, fn (Builder $query, int|string $typeId) => $query->where('daily_report_type_id', $typeId))
            ->when($filters['direction'] ?? null, function (Builder $query, string $direction): void {
                $query->whereHas('type', fn (Builder $query) => $query->where('direction', $direction));
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'report_date' => ['required', 'date'],
            'report_company_id' => ['required', 'integer', 'exists:report_companies,id'],
            'report_company_account_id' => ['nullable', 'integer', 'exists:report_company_accounts,id'],
            'daily_report_type_id' => ['required', 'integer', 'exists:daily_report_types,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'counterparty' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
        ]);

        if (! empty($data['report_company_account_id'])) {
            $accountBelongsToCompany = ReportCompanyAccount::query()
                ->whereKey($data['report_company_account_id'])
                ->where('report_company_id', $data['report_company_id'])
                ->exists();

            if (! $accountBelongsToCompany) {
                throw ValidationException::withMessages([
                    'report_company_account_id' => 'Выбранный счет не принадлежит выбранной компании.',
                ]);
            }
        }

        $data['report_company_account_id'] = $data['report_company_account_id'] ?? null;
        $data['counterparty'] = $data['counterparty'] ?? null;
        $data['comment'] = $data['comment'] ?? null;

        return $data;
    }

    /**
     * @param  Builder<DailyReportEntry>  $query
     */
    private function sumByDirection(Builder $query, string $direction): float
    {
        return (float) $query
            ->whereHas('type', fn (Builder $query) => $query->where('direction', $direction))
            ->sum('amount');
    }

    /**
     * @return array<int, string>
     */
    private function filterKeys(): array
    {
        return [
            'date_from',
            'date_to',
            'report_company_id',
            'report_company_account_id',
            'daily_report_type_id',
            'direction',
            'per_page',
            'page',
        ];
    }
}
