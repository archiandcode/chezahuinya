<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PreservesFilterParameters;
use App\Models\ConstructionPayment;
use App\Models\ConstructionSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConstructionPaymentController extends Controller
{
    use PreservesFilterParameters;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'construction_section_id' => ['nullable', 'integer', 'exists:construction_sections,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'contract' => ['nullable', 'string', 'max:255'],
            'payment_source' => ['nullable', 'string', 'max:255'],
            'amount_from' => ['nullable', 'numeric', 'min:0'],
            'amount_to' => ['nullable', 'numeric', 'min:0'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $query = ConstructionPayment::query()
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        $this->applyFilters($query, $filters);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $payments = $query
            ->paginate($perPage)
            ->withQueryString();

        $summaryQuery = ConstructionPayment::query();
        $this->applyFilters($summaryQuery, $filters);

        return view('construction-payments.index', [
            'payments' => $payments,
            'filters' => $filters,
            'sections' => ConstructionSection::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'totalAmount' => (clone $summaryQuery)->sum('amount'),
            'totalCount' => (clone $summaryQuery)->count(),
            'suppliersCount' => (clone $summaryQuery)->whereNotNull('supplier')->distinct('supplier')->count('supplier'),
            'sourcesCount' => (clone $summaryQuery)->whereNotNull('payment_source')->distinct('payment_source')->count('payment_source'),
            'suppliers' => ConstructionPayment::query()
                ->whereNotNull('supplier')
                ->where('supplier', '<>', '')
                ->distinct()
                ->orderBy('supplier')
                ->pluck('supplier'),
            'contracts' => ConstructionPayment::query()
                ->whereNotNull('contract')
                ->where('contract', '<>', '')
                ->distinct()
                ->orderBy('contract')
                ->pluck('contract'),
            'paymentSources' => ConstructionPayment::query()
                ->whereNotNull('payment_source')
                ->where('payment_source', '<>', '')
                ->distinct()
                ->orderBy('payment_source')
                ->pluck('payment_source'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ConstructionPayment::create($this->validatedData($request));

        return redirect()
            ->route('construction-payments.index', $this->filterParameters($request))
            ->with('toast_success', 'Запись стройки добавлена.');
    }

    public function update(Request $request, ConstructionPayment $constructionPayment): RedirectResponse
    {
        $constructionPayment->update($this->validatedData($request));

        return redirect()
            ->route('construction-payments.index', $this->filterParameters($request))
            ->with('status', 'Запись стройки обновлена.');
    }

    public function destroy(Request $request, ConstructionPayment $constructionPayment): RedirectResponse
    {
        $constructionPayment->delete();

        return redirect()
            ->route('construction-payments.index', $request->only($this->filterKeys()))
            ->with('status', 'Запись стройки удалена.');
    }

    /**
     * @param  Builder<ConstructionPayment>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['construction_section_id'] ?? null, fn (Builder $query, int $sectionId) => $query->where('construction_section_id', $sectionId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('payment_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('payment_date', '<=', $date))
            ->when($filters['supplier'] ?? null, fn (Builder $query, string $supplier) => $query->where('supplier', $supplier))
            ->when($filters['contract'] ?? null, fn (Builder $query, string $contract) => $query->where('contract', $contract))
            ->when($filters['payment_source'] ?? null, fn (Builder $query, string $source) => $query->where('payment_source', $source))
            ->when($filters['amount_from'] ?? null, fn (Builder $query, string $amount) => $query->where('amount', '>=', $amount))
            ->when($filters['amount_to'] ?? null, fn (Builder $query, string $amount) => $query->where('amount', '<=', $amount));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'construction_section_id' => ['required', 'integer', 'exists:construction_sections,id'],
            'payment_date' => ['required', 'date'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'contract' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string'],
            'payment_source' => ['nullable', 'string', 'max:255'],
        ]);

        $data['construction_section_id'] = $data['construction_section_id'] ?? null;
        $data['supplier'] = $data['supplier'] ?? null;
        $data['contract'] = $data['contract'] ?? null;
        $data['purpose'] = $data['purpose'] ?? null;
        $data['payment_source'] = $data['payment_source'] ?? null;

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function filterKeys(): array
    {
        return [
            'construction_section_id',
            'date_from',
            'date_to',
            'supplier',
            'contract',
            'payment_source',
            'amount_from',
            'amount_to',
            'per_page',
            'page',
        ];
    }

    private function includesUnprefixedFilterParameters(): bool
    {
        return false;
    }

    private function dropsBlankFilterParameters(): bool
    {
        return true;
    }
}
