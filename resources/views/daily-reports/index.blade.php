@extends('layouts.adminlte')

@section('title', 'Ежедневные отчеты | ' . config('app.name'))
@section('page-title', 'Ежедневные отчеты')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
    $filterKeys = ['date_from', 'date_to', 'report_company_id', 'report_company_account_id', 'daily_report_type_id', 'direction', 'per_page', 'page'];
    $directionLabels = ['opening' => 'Начало дня', 'income' => 'Приход', 'expense' => 'Расход'];
    $directionBadges = ['opening' => 'badge-info', 'income' => 'badge-success', 'expense' => 'badge-danger'];
    $selectedCompany = filled($filters['report_company_id'] ?? null) ? $companies->firstWhere('id', (int) $filters['report_company_id']) : null;
    $selectedAccount = filled($filters['report_company_account_id'] ?? null) ? $accounts->firstWhere('id', (int) $filters['report_company_account_id']) : null;
    $selectedType = filled($filters['daily_report_type_id'] ?? null) ? $types->firstWhere('id', (int) $filters['daily_report_type_id']) : null;
    $activeFilters = collect([
        'date_from' => ['label' => 'Дата от', 'value' => $filters['date_from'] ?? null],
        'date_to' => ['label' => 'Дата до', 'value' => $filters['date_to'] ?? null],
        'report_company_id' => ['label' => 'Компания', 'value' => $selectedCompany?->name],
        'report_company_account_id' => ['label' => 'Счет', 'value' => $selectedAccount?->account_number],
        'daily_report_type_id' => ['label' => 'Тип', 'value' => $selectedType?->name],
        'direction' => ['label' => 'Направление', 'value' => $directionLabels[$filters['direction'] ?? ''] ?? null],
    ])->filter(fn ($filter) => filled($filter['value']));
@endphp

@section('content')
    @if (session('toast_success'))
        <div class="daily-report-success-toast" role="status" aria-live="polite">
            <div class="daily-report-success-toast__content">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('toast_success') }}</span>
            </div>
            <div class="daily-report-success-toast__timer"></div>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
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
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $money($openingAmount) }}</h3>
                    <p>На начало дня</p>
                </div>
                <div class="icon">
                    <i class="fas fa-play-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $money($incomeAmount) }}</h3>
                    <p>Итого приход</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $money($expenseAmount) }}</h3>
                    <p>Итого расход</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $money($closingAmount) }}</h3>
                    <p>На конец дня</p>
                </div>
                <div class="icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-light"><i class="fas fa-building"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Компаний в справочнике</span>
                    <span class="info-box-number">{{ $companiesCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-light"><i class="fas fa-credit-card"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Счетов компаний</span>
                    <span class="info-box-number">{{ $accountsCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-light"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Записей по фильтру</span>
                    <span class="info-box-number">{{ $totalCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card filter-card">
        <div class="card-header js-filter-header" data-toggle-target="#dailyReportFilters">
            <h3 class="filter-title">
                <i class="fas fa-sliders-h text-muted"></i>
                Фильтры
            </h3>
            <div class="filter-meta">
                @if ($activeFilters->isNotEmpty())
                    <span class="filter-count">{{ $activeFilters->count() }} активно</span>
                @endif
                <button
                    type="button"
                    class="btn btn-tool js-filter-toggle {{ request('filter_expanded') === '1' ? '' : 'collapsed' }}"
                    data-toggle="collapse"
                    data-target="#dailyReportFilters"
                    aria-expanded="{{ request('filter_expanded') === '1' ? 'true' : 'false' }}"
                    aria-controls="dailyReportFilters"
                    title="Свернуть / развернуть"
                >
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <form method="GET" action="{{ route('daily-reports.index') }}" id="dailyReportFilters" class="collapse {{ request('filter_expanded') === '1' ? 'show' : '' }}">
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
                    <div class="filter-section-title">
                        <i class="far fa-calendar-alt text-muted"></i>
                        Период
                    </div>
                    <div class="row filter-panel">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_from">Дата от</label>
                                <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_to">Дата до</label>
                                <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-section">
                    <div class="filter-section-title">
                        <i class="fas fa-building text-muted"></i>
                        Компания и счет
                    </div>
                    <div class="row filter-panel">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="report_company_id">Компания</label>
                                <select id="report_company_id" name="report_company_id" class="form-control js-company-select">
                                    <option value="">Все</option>
                                    @foreach ($companies->groupBy('category') as $category => $groupedCompanies)
                                        <optgroup label="{{ $category ?: 'Без категории' }}">
                                            @foreach ($groupedCompanies as $company)
                                                <option value="{{ $company->id }}" @selected((int) ($filters['report_company_id'] ?? 0) === $company->id)>{{ $company->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="report_company_account_id">Счет</label>
                                <select id="report_company_account_id" name="report_company_account_id" class="form-control js-account-select">
                                    <option value="">Все</option>
                                    @foreach ($accounts as $account)
                                        <option
                                            value="{{ $account->id }}"
                                            data-company-id="{{ $account->report_company_id }}"
                                            @selected((int) ($filters['report_company_account_id'] ?? 0) === $account->id)
                                        >
                                            {{ $account->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-section">
                    <div class="filter-section-title">
                        <i class="fas fa-list-ul text-muted"></i>
                        Операция
                    </div>
                    <div class="row filter-panel">
                        <div class="col-md-4">
                            <div class="form-group mb-md-0">
                                <label for="daily_report_type_id">Тип</label>
                                <select id="daily_report_type_id" name="daily_report_type_id" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($types->groupBy('direction') as $direction => $groupedTypes)
                                        <optgroup label="{{ $directionLabels[$direction] ?? $direction }}">
                                            @foreach ($groupedTypes as $type)
                                                <option value="{{ $type->id }}" @selected((int) ($filters['daily_report_type_id'] ?? 0) === $type->id)>{{ $type->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-md-0">
                                <label for="direction">Направление</label>
                                <select id="direction" name="direction" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($directionLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(($filters['direction'] ?? '') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-section">
                    <div class="row align-items-end filter-panel">
                        <div class="col-md-12">
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter mr-1"></i> Применить
                                </button>
                                <a href="{{ route('daily-reports.index', request()->filled('per_page') ? ['per_page' => request('per_page')] : []) }}" class="btn btn-default">
                                    <i class="fas fa-times mr-1"></i> Сбросить
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Свод ежедневного отчета</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-bordered text-nowrap mb-0 daily-report-matrix">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width: 220px;">Тип операции</th>
                        @foreach ($matrixCompanies as $company)
                            <th class="text-right">{{ $company->short_name ?: $company->name }}</th>
                        @endforeach
                        <th class="text-right">Итого</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($matrixTypes->groupBy('direction') as $direction => $groupedTypes)
                        <tr class="bg-light">
                            <td class="font-weight-bold">{{ $directionLabels[$direction] ?? $direction }}</td>
                            @foreach ($matrixCompanies as $company)
                                <td></td>
                            @endforeach
                            <td></td>
                        </tr>
                        @foreach ($groupedTypes as $type)
                            @php
                                $rowTotal = 0;
                            @endphp
                            <tr>
                                <td>{{ $type->name }}</td>
                                @foreach ($matrixCompanies as $company)
                                    @php
                                        $amount = (float) ($matrixAmounts[$type->id . '_' . $company->id] ?? 0);
                                        $rowTotal += $amount;
                                    @endphp
                                    <td class="text-right">{{ $amount ? $money($amount) : '-' }}</td>
                                @endforeach
                                <td class="text-right font-weight-bold">{{ $rowTotal ? $money($rowTotal) : '-' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="{{ $matrixCompanies->count() + 2 }}" class="text-center text-muted py-4">Нет типов отчета</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Записи ежедневного отчета: {{ $totalCount }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#createDailyReportModal">
                    <i class="fas fa-plus mr-1"></i> Новая запись
                </button>
                <form method="GET" action="{{ route('daily-reports.index') }}" class="d-inline-block">
                    @foreach (request()->only(['date_from', 'date_to', 'report_company_id', 'report_company_account_id', 'daily_report_type_id', 'direction', 'filter_expanded']) as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <select name="per_page" class="form-control form-control-sm d-inline-block w-auto js-per-page-select" aria-label="На странице">
                        @foreach ([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 10) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered text-nowrap mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Дата</th>
                        <th>Компания</th>
                        <th>Счет</th>
                        <th>Тип</th>
                        <th>Направление</th>
                        <th class="text-right">Сумма</th>
                        <th>Контрагент</th>
                        <th>Комментарий</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entries->firstItem() + $loop->index }}</td>
                            <td>{{ $entry->report_date->format('d.m.Y') }}</td>
                            <td>{{ $entry->company->name }}</td>
                            <td>{{ $entry->account?->account_number ?: '-' }}</td>
                            <td>{{ $entry->type->name }}</td>
                            <td><span class="badge {{ $directionBadges[$entry->type->direction] ?? 'badge-secondary' }}">{{ $directionLabels[$entry->type->direction] ?? $entry->type->direction }}</span></td>
                            <td class="text-right font-weight-bold">{{ $money($entry->amount) }}</td>
                            <td>{{ $entry->counterparty ?: '-' }}</td>
                            <td class="text-wrap" style="min-width: 240px;">{{ $entry->comment ?: '-' }}</td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary js-edit-daily-report"
                                    data-toggle="modal"
                                    data-target="#editDailyReportModal"
                                    data-action="{{ route('daily-reports.update', $entry) }}"
                                    data-date="{{ $entry->report_date->format('Y-m-d') }}"
                                    data-company-id="{{ $entry->report_company_id }}"
                                    data-account-id="{{ $entry->report_company_account_id }}"
                                    data-type-id="{{ $entry->daily_report_type_id }}"
                                    data-amount="{{ $entry->amount }}"
                                    data-counterparty="{{ $entry->counterparty }}"
                                    data-comment="{{ $entry->comment }}"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('daily-reports.destroy', $entry) }}" class="d-inline" onsubmit="return confirm('Удалить запись ежедневного отчета?')">
                                    @csrf
                                    @method('DELETE')
                                    @foreach (request()->only($filterKeys) as $name => $value)
                                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                    @endforeach
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Записей пока нет</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($entries->hasPages())
            <div class="card-footer">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

    @include('daily-reports.partials.form-modal', [
        'modalId' => 'createDailyReportModal',
        'title' => 'Новая запись ежедневного отчета',
        'action' => route('daily-reports.store'),
        'method' => 'POST',
        'filterKeys' => $filterKeys,
        'companies' => $companies,
        'accounts' => $accounts,
        'types' => $types,
        'directionLabels' => $directionLabels,
        'defaultReportDate' => $defaultReportDate,
    ])

    @include('daily-reports.partials.form-modal', [
        'modalId' => 'editDailyReportModal',
        'title' => 'Редактировать запись ежедневного отчета',
        'action' => '#',
        'method' => 'PUT',
        'filterKeys' => $filterKeys,
        'companies' => $companies,
        'accounts' => $accounts,
        'types' => $types,
        'directionLabels' => $directionLabels,
    ])
@endsection

@push('scripts')
    <style>
        .daily-report-amount-input {
            -moz-appearance: textfield;
        }

        .daily-report-amount-input::-webkit-outer-spin-button,
        .daily-report-amount-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .daily-report-success-toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1080;
            width: min(360px, calc(100vw - 2rem));
            overflow: hidden;
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: .25rem;
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
        }

        .daily-report-success-toast__content {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1rem;
        }

        .daily-report-success-toast__timer {
            height: 3px;
            background: #28a745;
            animation: dailyReportSuccessToastTimer 4s linear forwards;
        }

        .daily-report-success-toast.is-hiding {
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity .2s ease, transform .2s ease;
        }

        @keyframes dailyReportSuccessToastTimer {
            from {
                width: 100%;
            }

            to {
                width: 0;
            }
        }
    </style>
    <script>
        $(function () {
            $('#dailyReportFilters').on('submit', function () {
                $(this).find('[name="filter_expanded"]').val($(this).hasClass('show') ? '1' : '0');
            });

            if (window.history.replaceState) {
                var url = new URL(window.location.href);

                if (url.searchParams.has('filter_expanded')) {
                    url.searchParams.delete('filter_expanded');
                    window.history.replaceState({}, document.title, url.toString());
                }
            }

            function filterAccounts(scope, selectedAccountId) {
                var companyId = scope.find('.js-entry-company').val() || scope.find('.js-company-select').val();
                var accountSelect = scope.find('.js-entry-account, .js-account-select');

                accountSelect.find('option').each(function () {
                    var option = $(this);
                    var optionCompanyId = option.attr('data-company-id');

                    if (!option.val() || !companyId || optionCompanyId === companyId) {
                        option.prop('hidden', false);
                    } else {
                        option.prop('hidden', true);
                    }
                });

                if (selectedAccountId) {
                    accountSelect.val(String(selectedAccountId));
                }

                var selectedOption = accountSelect.find('option:selected');
                if (selectedOption.length && selectedOption.prop('hidden')) {
                    accountSelect.val('');
                }
            }

            $('.js-company-select').on('change', function () {
                filterAccounts($(this).closest('form'));
            }).each(function () {
                filterAccounts($(this).closest('form'));
            });

            $('.js-entry-company').on('change', function () {
                filterAccounts($(this).closest('.modal'));
            });

            $('.js-edit-daily-report').on('click', function () {
                var button = $(this);
                var modal = $('#editDailyReportModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="report_date"]').val(button.attr('data-date'));
                modal.find('[name="report_company_id"]').val(button.attr('data-company-id'));
                modal.find('[name="daily_report_type_id"]').val(button.attr('data-type-id'));
                modal.find('[name="amount"]').val(button.attr('data-amount'));
                modal.find('[name="counterparty"]').val(button.attr('data-counterparty'));
                modal.find('[name="comment"]').val(button.attr('data-comment'));
                filterAccounts(modal, button.attr('data-account-id'));
            });

            $('#createDailyReportModal').on('show.bs.modal', function () {
                var modal = $(this);

                modal.find('[name="report_date"]').val('{{ $defaultReportDate }}');
                modal.find('[name="report_company_id"]').val('');
                modal.find('[name="report_company_account_id"]').val('');
                modal.find('[name="daily_report_type_id"]').val('');
                modal.find('[name="amount"]').val('');
                modal.find('[name="counterparty"]').val('');
                modal.find('[name="comment"]').val('');
                filterAccounts(modal);
            });

            $('.modal').on('shown.bs.modal', function () {
                filterAccounts($(this));
            });

            var toast = $('.daily-report-success-toast');

            if (toast.length) {
                setTimeout(function () {
                    toast.addClass('is-hiding');

                    setTimeout(function () {
                        toast.remove();
                    }, 200);
                }, 4000);
            }

            $('.js-per-page-select').on('change', function () {
                $(this).closest('form').trigger('submit');
            });
        });
    </script>
@endpush
