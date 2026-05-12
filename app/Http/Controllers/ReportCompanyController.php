<?php

namespace App\Http\Controllers;

use App\Models\ReportCompany;
use App\Models\ReportCompanyAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReportCompanyController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $query = ReportCompany::query()
            ->withCount('accounts')
            ->with('accounts')
            ->orderBy('category')
            ->orderBy('name');

        $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $companies = $query
            ->paginate($perPage)
            ->withQueryString();

        return view('report-companies.index', [
            'companies' => $companies,
            'filters' => $filters,
            'totalCompanies' => ReportCompany::query()->count(),
            'totalAccounts' => ReportCompanyAccount::query()->count(),
            'categories' => ReportCompany::query()
                ->whereNotNull('category')
                ->where('category', '<>', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ReportCompany::create($this->validatedCompanyData($request));

        return redirect()
            ->route('report-companies.index')
            ->with('status', 'Компания добавлена.');
    }

    public function update(Request $request, ReportCompany $reportCompany): RedirectResponse
    {
        $reportCompany->update($this->validatedCompanyData($request, $reportCompany));

        return redirect()
            ->route('report-companies.index', $request->only($this->filterKeys()))
            ->with('status', 'Компания обновлена.');
    }

    public function destroy(Request $request, ReportCompany $reportCompany): RedirectResponse
    {
        try {
            $reportCompany->delete();
        } catch (QueryException) {
            return redirect()
                ->route('report-companies.index', $request->only($this->filterKeys()))
                ->withErrors(['company' => 'Нельзя удалить компанию, которая уже используется в ежедневных отчетах.']);
        }

        return redirect()
            ->route('report-companies.index', $request->only($this->filterKeys()))
            ->with('status', 'Компания удалена.');
    }

    public function storeAccount(Request $request, ReportCompany $reportCompany): RedirectResponse
    {
        $reportCompany->accounts()->create($this->validatedAccountData($request, $reportCompany));

        return redirect()
            ->route('report-companies.index', $request->only($this->filterKeys()))
            ->with('status', 'Счет компании добавлен.');
    }

    public function updateAccount(Request $request, ReportCompanyAccount $account): RedirectResponse
    {
        $account->update($this->validatedAccountData($request, $account->company, $account));

        return redirect()
            ->route('report-companies.index', $request->only($this->filterKeys()))
            ->with('status', 'Счет компании обновлен.');
    }

    public function destroyAccount(Request $request, ReportCompanyAccount $account): RedirectResponse
    {
        try {
            $account->delete();
        } catch (QueryException) {
            return redirect()
                ->route('report-companies.index', $request->only($this->filterKeys()))
                ->withErrors(['account' => 'Нельзя удалить счет, который уже используется в ежедневных отчетах.']);
        }

        return redirect()
            ->route('report-companies.index', $request->only($this->filterKeys()))
            ->with('status', 'Счет компании удален.');
    }

    /**
     * @param Builder<ReportCompany> $query
     * @param array<string, mixed> $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhereHas('accounts', fn (Builder $query) => $query->where('account_number', 'like', "%{$search}%"));
                });
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedCompanyData(Request $request, ?ReportCompany $company = null): array
    {
        $ignoreId = $company?->id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:report_companies,name,'.$ignoreId],
            'short_name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $data['short_name'] = $data['short_name'] ?? null;
        $data['category'] = $data['category'] ?? null;

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedAccountData(Request $request, ReportCompany $company, ?ReportCompanyAccount $account = null): array
    {
        $data = $request->validate([
            'account_number' => ['required', 'string', 'max:255'],
            'bank' => ['nullable', 'string', 'max:255'],
        ]);

        $exists = $company->accounts()
            ->where('account_number', $data['account_number'])
            ->when($account, fn (Builder $query) => $query->whereKeyNot($account->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'account_number' => 'Такой счет уже есть у этой компании.',
            ]);
        }

        $data['bank'] = $data['bank'] ?? null;

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function filterKeys(): array
    {
        return ['category', 'search', 'per_page', 'page'];
    }
}
