@extends('layouts.adminlte')

@section('title', 'Отчет RAP | ' . config('app.name'))
@section('page-title', 'Отчет RAP')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
    $date = fn ($value) => $value ? $value->format('d.m.Y') : '-';
    $filterKeys = ['date_from', 'date_to', 'section', 'counterparty', 'sale_month', 'payment_status', 'amount_from', 'amount_to', 'per_page', 'page'];
    $activeFilters = collect([
        'date_from' => ['label' => 'Дата от', 'value' => $filters['date_from'] ?? null],
        'date_to' => ['label' => 'Дата до', 'value' => $filters['date_to'] ?? null],
        'section' => ['label' => 'Раздел', 'value' => isset($filters['section']) ? $sections[$filters['section']] : null],
        'counterparty' => ['label' => 'Контрагент', 'value' => $filters['counterparty'] ?? null],
        'sale_month' => ['label' => 'Месяц', 'value' => $filters['sale_month'] ?? null],
        'payment_status' => ['label' => 'Оплата', 'value' => ['paid' => 'Оплачен', 'unpaid' => 'Не оплачен', 'unknown' => 'Не указано'][$filters['payment_status'] ?? ''] ?? null],
        'amount_from' => ['label' => 'Сумма от', 'value' => $filters['amount_from'] ?? null],
        'amount_to' => ['label' => 'Сумма до', 'value' => $filters['amount_to'] ?? null],
    ])->filter(fn ($filter) => filled($filter['value']));
@endphp

@section('content')
    @if (session('toast_success'))
        <div class="rap-report-success-toast" role="status" aria-live="polite">
            <div class="rap-report-success-toast__content">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('toast_success') }}</span>
            </div>
            <div class="rap-report-success-toast__timer"></div>
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
                    <h3>{{ $money($totalSaleAmount) }}</h3>
                    <p>Сумма реализации</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $money($totalPaidAmount) }}</h3>
                    <p>Сумма оплаты</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $money($totalUnpaidAmount) }}</h3>
                    <p>Нет оплаты</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $money($totalQuantity) }}</h3>
                    <p>Количество</p>
                </div>
                <div class="icon"><i class="fas fa-list-ol"></i></div>
            </div>
        </div>
    </div>

    <div class="card filter-card">
        <div class="card-header js-filter-header" data-toggle-target="#rapReportFilters">
            <h3 class="filter-title">
                <i class="fas fa-sliders-h text-muted"></i>
                Фильтры
            </h3>
            <div class="filter-meta">
                @if ($activeFilters->isNotEmpty())
                    <span class="filter-count">{{ $activeFilters->count() }} активно</span>
                @endif
                <button type="button" class="btn btn-tool js-filter-toggle {{ request('filter_expanded') === '1' ? '' : 'collapsed' }}" data-toggle="collapse" data-target="#rapReportFilters" aria-expanded="{{ request('filter_expanded') === '1' ? 'true' : 'false' }}" aria-controls="rapReportFilters" title="Свернуть / развернуть">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <form method="GET" action="{{ route('rap-reports.index') }}" id="rapReportFilters" class="collapse {{ request('filter_expanded') === '1' ? 'show' : '' }}">
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
                        Период и раздел
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="section">Раздел</label>
                                <select id="section" name="section" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($sections as $sectionValue => $sectionLabel)
                                        <option value="{{ $sectionValue }}" @selected(($filters['section'] ?? '') === $sectionValue)>{{ $sectionLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="payment_status">Оплата</label>
                                <select id="payment_status" name="payment_status" class="form-control">
                                    <option value="">Все</option>
                                    <option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>Оплачен</option>
                                    <option value="unpaid" @selected(($filters['payment_status'] ?? '') === 'unpaid')>Не оплачен</option>
                                    <option value="unknown" @selected(($filters['payment_status'] ?? '') === 'unknown')>Не указано</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-section">
                    <div class="filter-section-title">
                        <i class="fas fa-list-ul text-muted"></i>
                        Параметры
                    </div>
                    <div class="row filter-panel">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="counterparty">Контрагент</label>
                                <select id="counterparty" name="counterparty" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($counterparties as $counterparty)
                                        <option value="{{ $counterparty }}" @selected(($filters['counterparty'] ?? '') === $counterparty)>{{ $counterparty }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="sale_month">Месяц</label>
                                <select id="sale_month" name="sale_month" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($saleMonths as $saleMonth)
                                        <option value="{{ $saleMonth }}" @selected(($filters['sale_month'] ?? '') === $saleMonth)>{{ $saleMonth }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount_from">Сумма от</label>
                                <input type="number" step="0.01" min="0" id="amount_from" name="amount_from" value="{{ $filters['amount_from'] ?? '' }}" class="form-control rap-report-number-input">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount_to">Сумма до</label>
                                <input type="number" step="0.01" min="0" id="amount_to" name="amount_to" value="{{ $filters['amount_to'] ?? '' }}" class="form-control rap-report-number-input">
                            </div>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-1"></i> Применить
                        </button>
                        <a href="{{ route('rap-reports.index', request()->filled('per_page') ? ['per_page' => request('per_page')] : []) }}" class="btn btn-default">
                            <i class="fas fa-times mr-1"></i> Сбросить
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Записи RAP: {{ $totalCount }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#createRapReportModal">
                    <i class="fas fa-plus mr-1"></i> Новая запись
                </button>
                <form method="GET" action="{{ route('rap-reports.index') }}" class="d-inline-block">
                    @foreach (request()->only(['date_from', 'date_to', 'section', 'counterparty', 'sale_month', 'payment_status', 'amount_from', 'amount_to', 'filter_expanded']) as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <select name="per_page" class="form-control form-control-sm d-inline-block w-auto js-per-page-select" aria-label="На странице">
                        @foreach ([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 25) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered text-nowrap mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Дата</th>
                        <th>Раздел</th>
                        <th>Контрагент</th>
                        <th class="text-right">Количество</th>
                        <th class="text-right">Цена</th>
                        <th class="text-right">Реализация</th>
                        <th>Месяц</th>
                        <th>Дата СФ</th>
                        <th>Дата факт. оплаты</th>
                        <th class="text-right">Сумма</th>
                        <th>Дата план. оплаты</th>
                        <th class="text-right">Нет оплаты</th>
                        <th>Оплачен</th>
                        <th>Коммент</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->report_date->format('d.m.Y') }}</td>
                            <td>{{ $sections[$entry->section] ?? $entry->section }}</td>
                            <td>{{ $entry->counterparty ?: '-' }}</td>
                            <td class="text-right">{{ $money($entry->quantity) }}</td>
                            <td class="text-right">{{ $money($entry->unit_price) }}</td>
                            <td class="text-right font-weight-bold">{{ $money($entry->sale_amount) }}</td>
                            <td>{{ $entry->sale_month ?: '-' }}</td>
                            <td>{{ $date($entry->invoice_date) }}</td>
                            <td>{{ $date($entry->actual_payment_date) }}</td>
                            <td class="text-right">{{ $money($entry->paid_amount) }}</td>
                            <td>{{ $date($entry->planned_payment_date) }}</td>
                            <td class="text-right">{{ $money($entry->unpaid_amount) }}</td>
                            <td>
                                @if (is_null($entry->is_paid))
                                    -
                                @elseif ($entry->is_paid)
                                    Да
                                @else
                                    Нет
                                @endif
                            </td>
                            <td class="text-wrap" style="min-width: 220px;">{{ $entry->comment ?: '-' }}</td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary js-edit-rap-report"
                                    data-toggle="modal"
                                    data-target="#editRapReportModal"
                                    data-action="{{ route('rap-reports.update', $entry) }}"
                                    data-report-date="{{ $entry->report_date->format('Y-m-d') }}"
                                    data-section="{{ $entry->section }}"
                                    data-counterparty="{{ $entry->counterparty }}"
                                    data-quantity="{{ $entry->quantity }}"
                                    data-unit-price="{{ $entry->unit_price }}"
                                    data-sale-amount="{{ $entry->sale_amount }}"
                                    data-sale-month="{{ $entry->sale_month }}"
                                    data-invoice-date="{{ $entry->invoice_date?->format('Y-m-d') }}"
                                    data-actual-payment-date="{{ $entry->actual_payment_date?->format('Y-m-d') }}"
                                    data-paid-amount="{{ $entry->paid_amount }}"
                                    data-planned-payment-date="{{ $entry->planned_payment_date?->format('Y-m-d') }}"
                                    data-unpaid-amount="{{ $entry->unpaid_amount }}"
                                    data-is-paid="{{ is_null($entry->is_paid) ? '' : (int) $entry->is_paid }}"
                                    data-comment="{{ $entry->comment }}"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('rap-reports.destroy', $entry) }}" class="d-inline" onsubmit="return confirm('Удалить запись отчета RAP?')">
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
                            <td colspan="15" class="text-center text-muted py-4">Записей пока нет</td>
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

    @include('rap-reports.partials.form-modal', [
        'modalId' => 'createRapReportModal',
        'title' => 'Новая запись отчета RAP',
        'action' => route('rap-reports.store'),
        'method' => 'POST',
        'filterKeys' => $filterKeys,
        'sections' => $sections,
        'defaultReportDate' => $defaultReportDate,
    ])

    @include('rap-reports.partials.form-modal', [
        'modalId' => 'editRapReportModal',
        'title' => 'Редактировать запись отчета RAP',
        'action' => '#',
        'method' => 'PUT',
        'filterKeys' => $filterKeys,
        'sections' => $sections,
    ])
@endsection

@push('scripts')
    <style>
        .rap-report-number-input {
            -moz-appearance: textfield;
        }

        .rap-report-number-input::-webkit-outer-spin-button,
        .rap-report-number-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .rap-report-success-toast {
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

        .rap-report-success-toast__content {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1rem;
        }

        .rap-report-success-toast__timer {
            height: 3px;
            background: #28a745;
            animation: rapReportSuccessToastTimer 4s linear forwards;
        }

        .rap-report-success-toast.is-hiding {
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity .2s ease, transform .2s ease;
        }

        @keyframes rapReportSuccessToastTimer {
            from { width: 100%; }
            to { width: 0; }
        }
    </style>
    <script>
        $(function () {
            $('#rapReportFilters').on('submit', function () {
                $(this).find('[name="filter_expanded"]').val($(this).hasClass('show') ? '1' : '0');
            });

            if (window.history.replaceState) {
                var url = new URL(window.location.href);

                if (url.searchParams.has('filter_expanded')) {
                    url.searchParams.delete('filter_expanded');
                    window.history.replaceState({}, document.title, url.toString());
                }
            }

            $('.js-edit-rap-report').on('click', function () {
                var button = $(this);
                var modal = $('#editRapReportModal');
                var isPaid = button.attr('data-is-paid');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="report_date"]').val(button.attr('data-report-date'));
                modal.find('[name="section"]').val(button.attr('data-section'));
                modal.find('[name="counterparty"]').val(button.attr('data-counterparty'));
                modal.find('[name="quantity"]').val(button.attr('data-quantity'));
                modal.find('[name="unit_price"]').val(button.attr('data-unit-price'));
                modal.find('[name="sale_amount"]').val(button.attr('data-sale-amount'));
                modal.find('[name="sale_month"]').val(button.attr('data-sale-month'));
                modal.find('[name="invoice_date"]').val(button.attr('data-invoice-date'));
                modal.find('[name="actual_payment_date"]').val(button.attr('data-actual-payment-date'));
                modal.find('[name="paid_amount"]').val(button.attr('data-paid-amount'));
                modal.find('[name="planned_payment_date"]').val(button.attr('data-planned-payment-date'));
                modal.find('[name="unpaid_amount"]').val(button.attr('data-unpaid-amount'));
                modal.find('[name="comment"]').val(button.attr('data-comment'));
                modal.find('[name="is_paid"]').prop('checked', isPaid === '1');
            });

            $('#createRapReportModal').on('show.bs.modal', function () {
                var modal = $(this);

                modal.find('[name="report_date"]').val('{{ $defaultReportDate }}');
                modal.find('[name="section"]').val('uninvoiced');
                modal.find('[name="counterparty"]').val('');
                modal.find('[name="quantity"]').val('');
                modal.find('[name="unit_price"]').val('');
                modal.find('[name="sale_amount"]').val('');
                modal.find('[name="sale_month"]').val('');
                modal.find('[name="invoice_date"]').val('');
                modal.find('[name="actual_payment_date"]').val('');
                modal.find('[name="paid_amount"]').val('');
                modal.find('[name="planned_payment_date"]').val('');
                modal.find('[name="unpaid_amount"]').val('');
                modal.find('[name="is_paid"]').prop('checked', false);
                modal.find('[name="comment"]').val('');
            });

            var toast = $('.rap-report-success-toast');

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
