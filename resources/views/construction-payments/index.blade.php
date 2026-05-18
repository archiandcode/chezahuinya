@extends('layouts.adminlte')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
    $filterKeys = ['construction_section_id', 'date_from', 'date_to', 'supplier', 'contract', 'payment_source', 'amount_from', 'amount_to', 'per_page', 'page'];
    $selectedSection = $sections->firstWhere('id', (int) ($filters['construction_section_id'] ?? 0));
    $pageTitle = $selectedSection ? $selectedSection->name : 'Стройка';
    $resetFilterParameters = array_filter([
        'construction_section_id' => $filters['construction_section_id'] ?? null,
        'per_page' => request('per_page'),
    ], fn ($value) => filled($value));
    $activeFilters = collect([
        'date_from' => ['label' => 'Дата от', 'value' => $filters['date_from'] ?? null],
        'date_to' => ['label' => 'Дата до', 'value' => $filters['date_to'] ?? null],
        'supplier' => ['label' => 'Поставщик', 'value' => $filters['supplier'] ?? null],
        'contract' => ['label' => 'Договор', 'value' => $filters['contract'] ?? null],
        'payment_source' => ['label' => 'Касса / р/с', 'value' => $filters['payment_source'] ?? null],
        'amount_from' => ['label' => 'Сумма от', 'value' => $filters['amount_from'] ?? null],
        'amount_to' => ['label' => 'Сумма до', 'value' => $filters['amount_to'] ?? null],
    ])->filter(fn ($filter) => filled($filter['value']));
@endphp

@section('title', $pageTitle . ' | ' . config('app.name'))
@section('page-title', $pageTitle)

@section('content')
    @if (session('toast_success'))
        <div class="construction-success-toast" role="status" aria-live="polite">
            <div class="construction-success-toast__content">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('toast_success') }}</span>
            </div>
            <div class="construction-success-toast__timer"></div>
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
                    <h3>{{ $money($totalAmount) }}</h3>
                    <p>Сумма по фильтру</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalCount }}</h3>
                    <p>Записей по фильтру</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $suppliersCount }}</h3>
                    <p>Поставщиков</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $sourcesCount }}</h3>
                    <p>Касс / р/с</p>
                </div>
                <div class="icon">
                    <i class="fas fa-university"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card filter-card">
        <div class="card-header js-filter-header" data-toggle-target="#constructionFilters">
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
                    data-target="#constructionFilters"
                    aria-expanded="{{ request('filter_expanded') === '1' ? 'true' : 'false' }}"
                    aria-controls="constructionFilters"
                    title="Свернуть / развернуть"
                >
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <form method="GET" action="{{ route('construction-payments.index') }}" id="constructionFilters" class="collapse {{ request('filter_expanded') === '1' ? 'show' : '' }}">
            <input type="hidden" name="filter_expanded" value="{{ request('filter_expanded') === '1' ? '1' : '0' }}">
            @if ($selectedSection)
                <input type="hidden" name="construction_section_id" value="{{ $selectedSection->id }}">
            @endif
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
                        <i class="fas fa-list-ul text-muted"></i>
                        Параметры
                    </div>
                    <div class="row filter-panel">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="supplier">Поставщик</label>
                                <select id="supplier" name="supplier" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier }}" @selected(($filters['supplier'] ?? '') === $supplier)>{{ $supplier }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="contract">Договор</label>
                                <select id="contract" name="contract" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($contracts as $contract)
                                        <option value="{{ $contract }}" @selected(($filters['contract'] ?? '') === $contract)>{{ $contract }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="payment_source">Касса / р/с</label>
                                <select id="payment_source" name="payment_source" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($paymentSources as $paymentSource)
                                        <option value="{{ $paymentSource }}" @selected(($filters['payment_source'] ?? '') === $paymentSource)>{{ $paymentSource }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-section">
                    <div class="filter-section-title">
                        <i class="fas fa-coins text-muted"></i>
                        Сумма
                    </div>
                    <div class="row filter-panel">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount_from">Сумма от</label>
                                <input type="number" step="0.01" min="0" id="amount_from" name="amount_from" value="{{ $filters['amount_from'] ?? '' }}" class="form-control construction-payment-amount-input">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount_to">Сумма до</label>
                                <input type="number" step="0.01" min="0" id="amount_to" name="amount_to" value="{{ $filters['amount_to'] ?? '' }}" class="form-control construction-payment-amount-input">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-footer-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter mr-1"></i> Применить
                    </button>
                    <a href="{{ route('construction-payments.index', $resetFilterParameters) }}" class="btn btn-default">
                        <i class="fas fa-times mr-1"></i> Сбросить
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Платежи стройки: {{ $totalCount }}</h3>
            <div class="card-tools">
                <a href="{{ route('construction-directories.index') }}" class="btn btn-default btn-sm mr-2">
                    <i class="fas fa-book mr-1"></i> Справочники
                </a>
                <button type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#createConstructionPaymentModal">
                    <i class="fas fa-plus mr-1"></i> Новая запись
                </button>
                <form method="GET" action="{{ route('construction-payments.index') }}" class="d-inline-block">
                    @foreach (request()->only(['construction_section_id', 'date_from', 'date_to', 'supplier', 'contract', 'payment_source', 'amount_from', 'amount_to', 'filter_expanded']) as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <select name="per_page" class="form-control form-control-sm d-inline-block w-auto js-per-page-select construction-per-page-select" aria-label="На странице">
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
                        <th>Поставщик</th>
                        <th class="text-right">Сумма</th>
                        <th>Договор</th>
                        <th>Назначение</th>
                        <th>С какой кассы и/или р/с</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payments->firstItem() + $loop->index }}</td>
                            <td>{{ $payment->payment_date->format('d.m.Y') }}</td>
                            <td>{{ $payment->supplier ?: '-' }}</td>
                            <td class="text-right font-weight-bold">{{ $money($payment->amount) }}</td>
                            <td>{{ $payment->contract ?: '-' }}</td>
                            <td class="text-wrap" style="min-width: 280px;">{{ $payment->purpose ?: '-' }}</td>
                            <td>{{ $payment->payment_source ?: '-' }}</td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary js-edit-construction-payment"
                                    data-toggle="modal"
                                    data-target="#editConstructionPaymentModal"
                                    data-action="{{ route('construction-payments.update', $payment) }}"
                                    data-construction-section-id="{{ $payment->construction_section_id }}"
                                    data-date="{{ $payment->payment_date->format('Y-m-d') }}"
                                    data-supplier="{{ $payment->supplier }}"
                                    data-amount="{{ $payment->amount }}"
                                    data-contract="{{ $payment->contract }}"
                                    data-purpose="{{ $payment->purpose }}"
                                    data-payment-source="{{ $payment->payment_source }}"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('construction-payments.destroy', $payment) }}" class="d-inline" onsubmit="return confirm('Удалить запись стройки?')">
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
                            <td colspan="8" class="text-center text-muted py-4">Записей пока нет</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())
            <div class="card-footer">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    @include('construction-payments.partials.form-modal', [
        'modalId' => 'createConstructionPaymentModal',
        'title' => 'Новая запись стройки',
        'action' => route('construction-payments.store'),
        'method' => 'POST',
        'filterKeys' => $filterKeys,
        'sections' => $sections,
        'defaultPaymentDate' => now()->toDateString(),
    ])

    @include('construction-payments.partials.form-modal', [
        'modalId' => 'editConstructionPaymentModal',
        'title' => 'Редактировать запись стройки',
        'action' => '#',
        'method' => 'PUT',
        'filterKeys' => $filterKeys,
        'sections' => $sections,
    ])
@endsection

@push('scripts')
    <style>
        .construction-payment-amount-input {
            -moz-appearance: textfield;
        }

        .construction-payment-amount-input::-webkit-outer-spin-button,
        .construction-payment-amount-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .filter-footer-actions {
            display: flex;
            justify-content: flex-end;
            gap: .5rem;
            margin-top: .75rem;
            padding-top: .75rem;
            border-top: 1px solid #edf1f5;
        }

        .construction-per-page-select {
            margin-right: .5rem;
        }

        .construction-success-toast {
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

        .construction-success-toast__content {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1rem;
        }

        .construction-success-toast__timer {
            height: 3px;
            background: #28a745;
            animation: constructionSuccessToastTimer 4s linear forwards;
        }

        .construction-success-toast.is-hiding {
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity .2s ease, transform .2s ease;
        }

        @media (max-width: 767.98px) {
            .filter-footer-actions {
                justify-content: stretch;
            }

            .filter-footer-actions .btn {
                flex: 1 1 0;
            }

            .construction-per-page-select {
                margin-top: .5rem;
                margin-right: 0;
            }
        }

        @keyframes constructionSuccessToastTimer {
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
            $('#constructionFilters').on('submit', function () {
                $(this).find('[name="filter_expanded"]').val($(this).hasClass('show') ? '1' : '0');
            });

            if (window.history.replaceState) {
                var url = new URL(window.location.href);

                if (url.searchParams.has('filter_expanded')) {
                    url.searchParams.delete('filter_expanded');
                    window.history.replaceState({}, document.title, url.toString());
                }
            }

            $('.js-edit-construction-payment').on('click', function () {
                var button = $(this);
                var modal = $('#editConstructionPaymentModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="construction_section_id"]').val(button.attr('data-construction-section-id'));
                modal.find('[name="payment_date"]').val(button.attr('data-date'));
                modal.find('[name="supplier"]').val(button.attr('data-supplier'));
                modal.find('[name="amount"]').val(button.attr('data-amount'));
                modal.find('[name="contract"]').val(button.attr('data-contract'));
                modal.find('[name="purpose"]').val(button.attr('data-purpose'));
                modal.find('[name="payment_source"]').val(button.attr('data-payment-source'));
            });

            $('#createConstructionPaymentModal').on('show.bs.modal', function () {
                var modal = $(this);

                modal.find('[name="construction_section_id"]').val('{{ request('construction_section_id') ?: $sections->first()?->id }}');
                modal.find('[name="payment_date"]').val('{{ now()->toDateString() }}');
                modal.find('[name="supplier"]').val('');
                modal.find('[name="amount"]').val('');
                modal.find('[name="contract"]').val('');
                modal.find('[name="purpose"]').val('');
                modal.find('[name="payment_source"]').val('');
            });

            var toast = $('.construction-success-toast');

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
