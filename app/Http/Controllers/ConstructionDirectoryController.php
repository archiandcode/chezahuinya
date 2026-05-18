<?php

namespace App\Http\Controllers;

use App\Models\ConstructionSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConstructionDirectoryController extends Controller
{
    public function index(): View
    {
        return view('construction-directories.index', [
            'sections' => ConstructionSection::query()
                ->orderByDesc('is_active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeSection(Request $request): RedirectResponse
    {
        ConstructionSection::create($this->validatedSectionData($request));

        return redirect()
            ->route('construction-directories.index')
            ->with('toast_success', 'Раздел стройки добавлен.');
    }

    public function updateSection(Request $request, ConstructionSection $constructionSection): RedirectResponse
    {
        $constructionSection->update($this->validatedSectionData($request, $constructionSection));

        return redirect()
            ->route('construction-directories.index')
            ->with('status', 'Раздел стройки обновлен.');
    }

    public function destroySection(ConstructionSection $constructionSection): RedirectResponse
    {
        $constructionSection->delete();

        return redirect()
            ->route('construction-directories.index')
            ->with('toast_success', 'Раздел стройки удален.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedSectionData(Request $request, ?ConstructionSection $constructionSection = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('construction_sections', 'name')->ignore($constructionSection)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
