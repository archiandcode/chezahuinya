<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use SplFileInfo;

class CodeQualityScanCommand extends Command
{
    protected $signature = 'code:quality
        {--fail-on=error : Lowest severity that should return a failing exit code: info, warning, error, or none}
        {--format=table : Output format: table or json}';

    protected $description = 'Scan controllers and Blade templates for maintainability risks.';

    /**
     * @var array<string, int>
     */
    private array $severityRank = [
        'info' => 1,
        'warning' => 2,
        'error' => 3,
    ];

    public function handle(): int
    {
        $findings = collect()
            ->merge($this->scanControllers())
            ->merge($this->scanBladeTemplates())
            ->sortBy([
                fn (array $left, array $right): int => $this->severityRank[$right['severity']] <=> $this->severityRank[$left['severity']],
                fn (array $left, array $right): int => strcmp($left['file'], $right['file']),
                fn (array $left, array $right): int => $left['line'] <=> $right['line'],
            ])
            ->values();

        $this->renderFindings($findings);

        $failOn = (string) $this->option('fail-on');
        if ($failOn === 'none' || $findings->isEmpty()) {
            return self::SUCCESS;
        }

        if (! array_key_exists($failOn, $this->severityRank)) {
            $this->error('Invalid --fail-on value. Use info, warning, error, or none.');

            return self::INVALID;
        }

        $shouldFail = $findings->contains(
            fn (array $finding): bool => $this->severityRank[$finding['severity']] >= $this->severityRank[$failOn]
        );

        return $shouldFail ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return Collection<int, array{severity: string, file: string, line: int, rule: string, message: string}>
     */
    private function scanControllers(): Collection
    {
        return collect(File::allFiles(app_path('Http/Controllers')))
            ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'php')
            ->flatMap(fn (SplFileInfo $file): array => $this->controllerFindings($file))
            ->values();
    }

    /**
     * @return array<int, array{severity: string, file: string, line: int, rule: string, message: string}>
     */
    private function controllerFindings(SplFileInfo $file): array
    {
        $path = $file->getPathname();
        $contents = File::get($path);
        $lines = preg_split('/\R/', $contents) ?: [];
        $relativePath = $this->relativePath($path);
        $findings = [];
        $lineCount = $this->logicalLineCount($lines);
        $isSharedConcern = str_contains($relativePath, 'app/Http/Controllers/Concerns/');

        if ($lineCount > 180) {
            $findings[] = $this->finding(
                'warning',
                $relativePath,
                1,
                'large-controller',
                "Controller has {$lineCount} lines. Move query building, validation, and page data assembly into smaller classes."
            );
        } elseif ($lineCount > 140) {
            $findings[] = $this->finding(
                'info',
                $relativePath,
                1,
                'large-controller',
                "Controller has {$lineCount} lines. Watch this class before adding more responsibilities."
            );
        }

        foreach ($this->methodLineCounts($contents) as $method) {
            if ($method['lines'] > 70) {
                $findings[] = $this->finding(
                    'warning',
                    $relativePath,
                    $method['line'],
                    'long-method',
                    "Method {$method['name']}() has {$method['lines']} lines. Extract query, validation, or view-model assembly."
                );
            }
        }

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;

            if (str_contains($line, '$request->validate(')) {
                $findings[] = $this->finding(
                    'info',
                    $relativePath,
                    $lineNumber,
                    'inline-validation',
                    'Validation lives in the controller. Prefer FormRequest classes when rules grow or repeat.'
                );
            }

            if (! $isSharedConcern && str_contains($line, 'private function filterParameters(')) {
                $findings[] = $this->finding(
                    'info',
                    $relativePath,
                    $lineNumber,
                    'duplicated-filter-plumbing',
                    'Filter preservation logic is repeated across controllers. A shared helper or request object would reduce drift.'
                );
            }

            if (preg_match('/\b(clone \$summaryQuery)\b/', $line) === 1) {
                $findings[] = $this->finding(
                    'info',
                    $relativePath,
                    $lineNumber,
                    'summary-query-in-controller',
                    'Summary aggregation is built in the controller. Consider a report query/service class for testable calculations.'
                );
            }
        }

        return $findings;
    }

    /**
     * @return Collection<int, array{severity: string, file: string, line: int, rule: string, message: string}>
     */
    private function scanBladeTemplates(): Collection
    {
        return collect(File::allFiles(resource_path('views')))
            ->filter(fn (SplFileInfo $file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->flatMap(fn (SplFileInfo $file): array => $this->bladeFindings($file))
            ->values();
    }

    /**
     * @return array<int, array{severity: string, file: string, line: int, rule: string, message: string}>
     */
    private function bladeFindings(SplFileInfo $file): array
    {
        $path = $file->getPathname();
        $contents = File::get($path);
        $lines = preg_split('/\R/', $contents) ?: [];
        $relativePath = $this->relativePath($path);
        $findings = [];
        $lineCount = $this->logicalLineCount($lines);

        if ($lineCount > 450) {
            $findings[] = $this->finding(
                'warning',
                $relativePath,
                1,
                'large-blade',
                "Blade template has {$lineCount} lines. Split repeated filters, tables, modals, and scripts into components/partials."
            );
        } elseif ($lineCount > 280) {
            $findings[] = $this->finding(
                'info',
                $relativePath,
                1,
                'large-blade',
                "Blade template has {$lineCount} lines. Watch for mixed presentation, state, and JavaScript responsibilities."
            );
        }

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;

            if (str_contains($line, '@php return; @endphp')) {
                $findings[] = $this->finding(
                    'error',
                    $relativePath,
                    $lineNumber,
                    'disabled-template',
                    'Template exits immediately. This hides dead code and can mask the real rendered view.'
                );
            } elseif (str_contains($line, '@php')) {
                $findings[] = $this->finding(
                    'info',
                    $relativePath,
                    $lineNumber,
                    'blade-php-block',
                    'PHP logic in Blade makes templates harder to reuse and test. Prefer view data, components, or directives.'
                );
            }

            if (preg_match('/<script(\s|>)/i', $line) === 1) {
                $findings[] = $this->finding(
                    'info',
                    $relativePath,
                    $lineNumber,
                    'inline-script',
                    'Inline JavaScript in Blade couples behavior to markup. Prefer Vite assets or small reusable partials.'
                );
            }

            if (preg_match('/\brequest\(/', $line) === 1 || str_contains($line, 'request()->')) {
                $findings[] = $this->finding(
                    'info',
                    $relativePath,
                    $lineNumber,
                    'request-in-view',
                    'The view reads request state directly. Passing normalized filters from the controller keeps rendering deterministic.'
                );
            }
        }

        return $findings;
    }

    /**
     * @param  Collection<int, array{severity: string, file: string, line: int, rule: string, message: string}>  $findings
     */
    private function renderFindings(Collection $findings): void
    {
        if ($this->option('format') === 'json') {
            $this->line($findings->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return;
        }

        if ($findings->isEmpty()) {
            $this->info('No controller or Blade maintainability findings.');

            return;
        }

        $this->table(
            ['Severity', 'File', 'Line', 'Rule', 'Message'],
            $findings->map(fn (array $finding): array => [
                $finding['severity'],
                $finding['file'],
                $finding['line'],
                $finding['rule'],
                $finding['message'],
            ])->all()
        );
    }

    /**
     * @return array<int, array{name: string, line: int, lines: int}>
     */
    private function methodLineCounts(string $contents): array
    {
        $tokens = token_get_all($contents);
        $tokenLines = $this->tokenLines($tokens);
        $methods = [];

        foreach ($tokens as $index => $token) {
            if (! is_array($token) || $token[0] !== T_FUNCTION) {
                continue;
            }

            $name = $this->nextTokenText($tokens, $index, T_STRING);
            if ($name === null) {
                continue;
            }

            $startLine = $token[2];
            $bodyStart = $this->nextTokenIndex($tokens, $index, '{');
            if ($bodyStart === null) {
                continue;
            }

            $bodyEnd = $this->matchingBraceIndex($tokens, $bodyStart);
            if ($bodyEnd === null) {
                continue;
            }

            $endLine = $tokenLines[$bodyEnd] ?? $startLine;
            $methods[] = [
                'name' => $name,
                'line' => $startLine,
                'lines' => $endLine - $startLine + 1,
            ];
        }

        return $methods;
    }

    /**
     * @param  array<int, mixed>  $tokens
     */
    private function nextTokenText(array $tokens, int $start, int $tokenType): ?string
    {
        for ($index = $start + 1; $index < count($tokens); $index++) {
            $token = $tokens[$index];

            if (is_array($token) && $token[0] === $tokenType) {
                return $token[1];
            }
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $tokens
     */
    private function nextTokenIndex(array $tokens, int $start, string $text): ?int
    {
        for ($index = $start + 1; $index < count($tokens); $index++) {
            if ($tokens[$index] === $text) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $tokens
     */
    private function matchingBraceIndex(array $tokens, int $start): ?int
    {
        $depth = 0;

        for ($index = $start; $index < count($tokens); $index++) {
            if ($tokens[$index] === '{') {
                $depth++;
            }

            if ($tokens[$index] === '}') {
                $depth--;

                if ($depth === 0) {
                    return $index;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $tokens
     * @return array<int, int>
     */
    private function tokenLines(array $tokens): array
    {
        $lines = [];
        $line = 1;

        foreach ($tokens as $index => $token) {
            $lines[$index] = is_array($token) ? $token[2] : $line;
            $text = is_array($token) ? $token[1] : $token;

            $line += substr_count($text, "\n");
        }

        return $lines;
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function logicalLineCount(array $lines): int
    {
        return count(array_filter($lines, fn (string $line): bool => trim($line) !== ''));
    }

    /**
     * @return array{severity: string, file: string, line: int, rule: string, message: string}
     */
    private function finding(string $severity, string $file, int $line, string $rule, string $message): array
    {
        return compact('severity', 'file', 'line', 'rule', 'message');
    }

    private function relativePath(string $path): string
    {
        return str_replace(base_path().'/', '', $path);
    }
}
