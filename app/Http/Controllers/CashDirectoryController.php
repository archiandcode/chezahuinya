<?php

namespace App\Http\Controllers;

use App\Models\CashCompany;
use App\Models\CashFlowCategory;
use App\Models\CashRegister;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashDirectoryController extends Controller
{
    public function index(): View
    {
        return view('cash-directories.redesigned', [
            'cashRegisters' => CashRegister::query()->orderByDesc('is_active')->orderBy('name')->get(),
            'companies' => CashCompany::query()->orderByDesc('is_active')->orderBy('name')->get(),
            'cashFlows' => CashFlowCategory::query()->orderByDesc('is_active')->orderBy('name')->get(),
        ]);
    }

    public function storeRegister(Request $request): RedirectResponse
    {
        CashRegister::create($this->validatedRegisterData($request));

        return redirect()->route('cash-directories.index')->with('toast_success', 'Касса добавлена.');
    }

    public function updateRegister(Request $request, CashRegister $cashRegister): RedirectResponse
    {
        $cashRegister->update($this->validatedRegisterData($request, $cashRegister));

        return redirect()->route('cash-directories.index')->with('status', 'Касса обновлена.');
    }

    public function destroyRegister(CashRegister $cashRegister): RedirectResponse
    {
        try {
            $cashRegister->delete();
        } catch (QueryException) {
            return redirect()
                ->route('cash-directories.index')
                ->withErrors(['cash_register' => 'Нельзя удалить кассу, которая используется в операциях.']);
        }

        return redirect()->route('cash-directories.index')->with('toast_success', 'Касса удалена.');
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        CashCompany::create($this->validatedCompanyData($request));

        return redirect()->route('cash-directories.index')->with('toast_success', 'Компания добавлена.');
    }

    public function updateCompany(Request $request, CashCompany $cashCompany): RedirectResponse
    {
        $cashCompany->update($this->validatedCompanyData($request, $cashCompany));

        return redirect()->route('cash-directories.index')->with('status', 'Компания обновлена.');
    }

    public function destroyCompany(CashCompany $cashCompany): RedirectResponse
    {
        try {
            $cashCompany->delete();
        } catch (QueryException) {
            return redirect()
                ->route('cash-directories.index')
                ->withErrors(['cash_company' => 'Нельзя удалить компанию, которая используется в операциях.']);
        }

        return redirect()->route('cash-directories.index')->with('toast_success', 'Компания удалена.');
    }

    public function storeCashFlow(Request $request): RedirectResponse
    {
        CashFlowCategory::create($this->validatedCashFlowData($request));

        return redirect()->route('cash-directories.index')->with('toast_success', 'Запись ДДС добавлена.');
    }

    public function updateCashFlow(Request $request, CashFlowCategory $cashFlowCategory): RedirectResponse
    {
        $cashFlowCategory->update($this->validatedCashFlowData($request, $cashFlowCategory));

        return redirect()->route('cash-directories.index')->with('status', 'Запись ДДС обновлена.');
    }

    public function destroyCashFlow(CashFlowCategory $cashFlowCategory): RedirectResponse
    {
        try {
            $cashFlowCategory->delete();
        } catch (QueryException) {
            return redirect()
                ->route('cash-directories.index')
                ->withErrors(['cash_flow_category' => 'Нельзя удалить запись ДДС, которая используется в операциях.']);
        }

        return redirect()->route('cash-directories.index')->with('toast_success', 'Запись ДДС удалена.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedRegisterData(Request $request, ?CashRegister $cashRegister = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('cash_registers', 'name')->ignore($cashRegister)],
            'currency' => ['required', 'string', 'size:3'],
            'opening_balance' => ['required', 'numeric', 'min:-999999999999.99', 'max:999999999999.99'],
            'opening_balance_date' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['currency'] = strtoupper($data['currency']);
        $data['opening_balance_date'] = $data['opening_balance_date'] ?? null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedCompanyData(Request $request, ?CashCompany $cashCompany = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('cash_companies', 'name')->ignore($cashCompany)],
            'short_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['short_name'] = $data['short_name'] ?? null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedCashFlowData(Request $request, ?CashFlowCategory $cashFlowCategory = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('cash_flow_categories', 'name')->ignore($cashFlowCategory)],
            'direction' => ['nullable', 'in:income,expense'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['direction'] = $data['direction'] ?? null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
