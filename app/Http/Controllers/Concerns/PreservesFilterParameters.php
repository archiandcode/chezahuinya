<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait PreservesFilterParameters
{
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
            } elseif ($this->includesUnprefixedFilterParameters() && $request->has($key)) {
                $filters[$key] = $request->input($key);
            }
        }

        if ($this->dropsBlankFilterParameters()) {
            return array_filter($filters, fn (mixed $value): bool => filled($value));
        }

        return $filters;
    }

    private function includesUnprefixedFilterParameters(): bool
    {
        return true;
    }

    private function dropsBlankFilterParameters(): bool
    {
        return false;
    }
}
