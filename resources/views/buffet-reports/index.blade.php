@extends('layouts.adminlte')

@section('title', 'Буфет | ' . config('app.name'))
@section('page-title', 'Буфет')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
    $filterKeys = ['report_year', 'metric', 'date_from', 'date_to', 'amount_from', 'amount_to', 'per_page', 'page'];
    $activeFilters = collect([
        'report_year' => ['label' => 'Год', 'value' => $filters['report_year'] ?? null],
        'metric' => ['label' => 'Показатель', 'value' => isset($filters['metric']) ? ($metrics[$filters['metric']] ?? $filters['metric']) : null],
        'date_from' => ['label' => 'Дата от', 'value' => $filters['date_from'] ?? null],
        'date_to' => ['label' => 'Дата до', 'value' => $filters['date_to'] ?? null],
        'amount_from' => ['label' => 'Сумма от', 'value' => $filters['amount_from'] ?? null],
        'amount_to' => ['label' => 'Сумма до', 'value' => $filters['amount_to'] ?? null],
    ])->filter(fn ($filter) => filled($filter['value']));
@endphp

@section('content')
    @if (session('toast_success'))
        <div class="alert alert-success">{{ session('toast_success') }}</div>
    @endif
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Проверьте данные.</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $money($expenseAmount) }}</h3>
                    <p>Расход</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-circle-up"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $money($incomeAmount) }}</h3>
                    <p>Приход</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-circle-down"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $money($profitLossAmount) }}</h3>
                    <p>Доход / убыток</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $totalCount }}</h3>
                    <p>Записей</p>
                </div>
                <div class="icon"><i class="fas fa-list"></i></div>
            </div>
        </div>
    </div>

    <div class="card filter-card">
        <div class="card-header js-filter-header" data-toggle-target="#buffetFilters">
            <h3 class="filter-title"><i class="fas fa-sliders-h text-muted"></i> Фильтры</h3>
            <div class="filter-meta">
                @if ($activeFilters->isNotEmpty())
                    <span class="filter-count">{{ $activeFilters->count() }} активно</span>
                @endif
                <button type="button" class="btn btn-tool js-filter-toggle {{ request('filter_expanded') === '1' ? '' : 'collapsed' }}" data-toggle="collapse" data-target="#buffetFilters" aria-expanded="{{ request('filter_expanded') === '1' ? 'true' : 'false' }}">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <form method="GET" action="{{ route('buffet-reports.index') }}" id="buffetFilters" class="collapse {{ request('filter_expanded') === '1' ? 'show' : '' }}">
            <input type="hidden" name="filter_expanded" value="{{ request('filter_expanded') === '1' ? '1' : '0' }}">
            @if ($activeFilters->isNotEmpty())
                <div class="filter-summary">
                    @foreach ($activeFilters as $filter)
                        <span class="filter-chip"><strong>{{ $filter['label'] }}:</strong> {{ $filter['value'] }}</span>
                    @endforeach
                </div>
            @endif
            <div class="card-body">
                <div class="filter-section">
                    <div class="row filter-panel">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="report_year">Год</label>
                                <select id="report_year" name="report_year" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" @selected((int) ($filters['report_year'] ?? 0) === $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="metric">Показатель</label>
                                <select id="metric" name="metric" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($metrics as $metricValue => $metricLabel)
                                        <option value="{{ $metricValue }}" @selected(($filters['metric'] ?? '') === $metricValue)>{{ $metricLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date_from">Дата от</label>
                                <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date_to">Дата до</label>
                                <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-1"></i> Применить</button>
                                <a href="{{ route('buffet-reports.index') }}" class="btn btn-default"><i class="fas fa-times mr-1"></i> Сбросить</a>
                            </div>
                        </div>
                    </div>
                    <div class="row filter-panel">
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label for="amount_from">Сумма от</label>
                                <input type="number" step="0.01" id="amount_from" name="amount_from" value="{{ $filters['amount_from'] ?? '' }}" class="form-control buffet-amount-input">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label for="amount_to">Сумма до</label>
                                <input type="number" step="0.01" id="amount_to" name="amount_to" value="{{ $filters['amount_to'] ?? '' }}" class="form-control buffet-amount-input">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Свод буфета за {{ $matrixYear }}</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-bordered text-nowrap mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width: 220px;">Показатель</th>
                        @foreach ($matrixPeriods as $period)
                            <th class="text-right">{{ $period->period_label }}</th>
                        @endforeach
                        <th class="text-right">Итого</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($matrixMetrics as $metricValue => $metricLabel)
                        @php
                            $rowTotal = 0;
                        @endphp
                        <tr>
                            <td class="font-weight-bold">{{ $metricLabel }}</td>
                            @foreach ($matrixPeriods as $period)
                                @php
                                    $amount = (float) ($matrixAmounts[$metricValue . '|' . $period->period_label . '|' . $period->period_date?->format('Y-m-d')] ?? 0);
                                    $rowTotal += $amount;
                                @endphp
                                <td class="text-right">{{ $amount ? $money($amount) : '-' }}</td>
                            @endforeach
                            <td class="text-right font-weight-bold">{{ $rowTotal ? $money($rowTotal) : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $matrixPeriods->count() + 2 }}" class="text-center text-muted py-4">Нет данных за выбранный год</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Записи буфета: {{ $totalCount }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#createBuffetModal">
                    <i class="fas fa-plus mr-1"></i> Новая запись
                </button>
                <form method="GET" action="{{ route('buffet-reports.index') }}" class="d-inline-block">
                    @foreach (request()->only(['report_year', 'metric', 'date_from', 'date_to', 'amount_from', 'amount_to', 'filter_expanded']) as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <select name="per_page" class="form-control form-control-sm d-inline-block w-auto js-per-page-select">
                        @foreach ([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 50) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered text-nowrap mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Год</th>
                        <th>Период</th>
                        <th>Дата</th>
                        <th>Показатель</th>
                        <th class="text-right">Сумма</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->report_year }}</td>
                            <td>{{ $entry->period_label }}</td>
                            <td>{{ $entry->period_date?->format('d.m.Y') ?: '-' }}</td>
                            <td>{{ $metrics[$entry->metric] ?? $entry->metric }}</td>
                            <td class="text-right font-weight-bold">{{ $money($entry->amount) }}</td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-outline-primary js-edit-buffet" data-toggle="modal" data-target="#editBuffetModal" data-action="{{ route('buffet-reports.update', $entry) }}" data-report-year="{{ $entry->report_year }}" data-period-label="{{ $entry->period_label }}" data-period-date="{{ $entry->period_date?->format('Y-m-d') }}" data-metric="{{ $entry->metric }}" data-amount="{{ $entry->amount }}" data-sort-order="{{ $entry->sort_order }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('buffet-reports.destroy', $entry) }}" class="d-inline" onsubmit="return confirm('Удалить запись буфета?')">
                                    @csrf
                                    @method('DELETE')
                                    @foreach (request()->only($filterKeys) as $name => $value)
                                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                    @endforeach
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Записей пока нет</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($entries->hasPages())
            <div class="card-footer">{{ $entries->links() }}</div>
        @endif
    </div>

    @include('buffet-reports.partials.form-modal', [
        'modalId' => 'createBuffetModal',
        'title' => 'Новая запись буфета',
        'action' => route('buffet-reports.store'),
        'method' => 'POST',
        'filterKeys' => $filterKeys,
        'metrics' => $metrics,
        'defaultYear' => $defaultYear,
    ])
    @include('buffet-reports.partials.form-modal', [
        'modalId' => 'editBuffetModal',
        'title' => 'Редактировать запись буфета',
        'action' => '#',
        'method' => 'PUT',
        'filterKeys' => $filterKeys,
        'metrics' => $metrics,
    ])
@endsection

@push('scripts')
    <style>
        .buffet-amount-input {
            -moz-appearance: textfield;
        }

        .buffet-amount-input::-webkit-outer-spin-button,
        .buffet-amount-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
    <script>
        $(function () {
            $('.js-edit-buffet').on('click', function () {
                var button = $(this);
                var modal = $('#editBuffetModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="report_year"]').val(button.attr('data-report-year'));
                modal.find('[name="period_label"]').val(button.attr('data-period-label'));
                modal.find('[name="period_date"]').val(button.attr('data-period-date'));
                modal.find('[name="metric"]').val(button.attr('data-metric'));
                modal.find('[name="amount"]').val(button.attr('data-amount'));
                modal.find('[name="sort_order"]').val(button.attr('data-sort-order'));
            });

            $('#createBuffetModal').on('show.bs.modal', function () {
                var modal = $(this);

                modal.find('[name="report_year"]').val('{{ $defaultYear }}');
                modal.find('[name="period_label"]').val('');
                modal.find('[name="period_date"]').val('');
                modal.find('[name="metric"]').val('expense');
                modal.find('[name="amount"]').val('');
                modal.find('[name="sort_order"]').val('');
            });

            $('.js-per-page-select').on('change', function () {
                $(this).closest('form').trigger('submit');
            });
        });
    </script>
@endpush
