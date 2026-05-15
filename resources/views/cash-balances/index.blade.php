@extends('layouts.adminlte')

@section('title', 'Остатки ДС | ' . config('app.name'))
@section('page-title', 'Остатки ДС')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
    $filterKeys = ['date_from', 'date_to', 'company', 'amount_from', 'amount_to', 'per_page', 'page'];
    $activeFilters = collect([
        'date_from' => ['label' => 'Дата от', 'value' => $filters['date_from'] ?? null],
        'date_to' => ['label' => 'Дата до', 'value' => $filters['date_to'] ?? null],
        'company' => ['label' => 'Компания', 'value' => $filters['company'] ?? null],
        'amount_from' => ['label' => 'Остаток от', 'value' => $filters['amount_from'] ?? null],
        'amount_to' => ['label' => 'Остаток до', 'value' => $filters['amount_to'] ?? null],
    ])->filter(fn ($filter) => filled($filter['value']));
@endphp

@section('content')
    @if (session('toast_success'))
        <div class="cash-balance-success-toast" role="status" aria-live="polite">
            <div class="cash-balance-success-toast__content">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('toast_success') }}</span>
            </div>
            <div class="cash-balance-success-toast__timer"></div>
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
                    <h3>{{ $money($totalBalance) }}</h3>
                    <p>Остаток по фильтру</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $money($totalCustodyAssets) }}</h3>
                    <p>Активы кастоди</p>
                </div>
                <div class="icon">
                    <i class="fas fa-university"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $money($totalOwnAssets) }}</h3>
                    <p>Собственные активы</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $totalCount }}</h3>
                    <p>Записей по фильтру</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card filter-card">
        <div class="card-header js-filter-header" data-toggle-target="#cashBalanceFilters">
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
                    data-target="#cashBalanceFilters"
                    aria-expanded="{{ request('filter_expanded') === '1' ? 'true' : 'false' }}"
                    aria-controls="cashBalanceFilters"
                    title="Свернуть / развернуть"
                >
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <form method="GET" action="{{ route('cash-balances.index') }}" id="cashBalanceFilters" class="collapse {{ request('filter_expanded') === '1' ? 'show' : '' }}">
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
                        <i class="fas fa-list-ul text-muted"></i>
                        Параметры
                    </div>
                    <div class="row filter-panel">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="company">Компания</label>
                                <select id="company" name="company" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company }}" @selected(($filters['company'] ?? '') === $company)>{{ $company }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount_from">Остаток от</label>
                                <input type="number" step="0.01" min="0" id="amount_from" name="amount_from" value="{{ $filters['amount_from'] ?? '' }}" class="form-control cash-balance-amount-input">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount_to">Остаток до</label>
                                <input type="number" step="0.01" min="0" id="amount_to" name="amount_to" value="{{ $filters['amount_to'] ?? '' }}" class="form-control cash-balance-amount-input">
                            </div>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-1"></i> Применить
                        </button>
                        <a href="{{ route('cash-balances.index', request()->filled('per_page') ? ['per_page' => request('per_page')] : []) }}" class="btn btn-default">
                            <i class="fas fa-times mr-1"></i> Сбросить
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Остатки ДС: {{ $totalCount }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#createCashBalanceModal">
                    <i class="fas fa-plus mr-1"></i> Новая запись
                </button>
                <form method="GET" action="{{ route('cash-balances.index') }}" class="d-inline-block">
                    @foreach (request()->only(['date_from', 'date_to', 'company', 'amount_from', 'amount_to', 'filter_expanded']) as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <select name="per_page" class="form-control form-control-sm d-inline-block w-auto js-per-page-select" aria-label="На странице">
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
                        <th style="width: 70px;">№ пп</th>
                        <th>Дата</th>
                        <th>Компания</th>
                        <th class="text-right">Остаток</th>
                        <th class="text-right">Активы (кастоди)</th>
                        <th class="text-right">Собственные активы</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($balances as $balance)
                        <tr>
                            <td>{{ $balance->sort_order ?: $balances->firstItem() + $loop->index }}</td>
                            <td>{{ $balance->balance_date->format('d.m.Y') }}</td>
                            <td>{{ $balance->company }}</td>
                            <td class="text-right font-weight-bold">{{ $money($balance->balance_amount) }}</td>
                            <td class="text-right">{{ $money($balance->custody_assets_amount) }}</td>
                            <td class="text-right">{{ $money($balance->own_assets_amount) }}</td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary js-edit-cash-balance"
                                    data-toggle="modal"
                                    data-target="#editCashBalanceModal"
                                    data-action="{{ route('cash-balances.update', $balance) }}"
                                    data-balance-date="{{ $balance->balance_date->format('Y-m-d') }}"
                                    data-sort-order="{{ $balance->sort_order }}"
                                    data-company="{{ $balance->company }}"
                                    data-balance-amount="{{ $balance->balance_amount }}"
                                    data-custody-assets-amount="{{ $balance->custody_assets_amount }}"
                                    data-own-assets-amount="{{ $balance->own_assets_amount }}"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('cash-balances.destroy', $balance) }}" class="d-inline" onsubmit="return confirm('Удалить запись остатка ДС?')">
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
                            <td colspan="7" class="text-center text-muted py-4">Записей пока нет</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($balances->hasPages())
            <div class="card-footer">
                {{ $balances->links() }}
            </div>
        @endif
    </div>

    @include('cash-balances.partials.form-modal', [
        'modalId' => 'createCashBalanceModal',
        'title' => 'Новая запись остатка ДС',
        'action' => route('cash-balances.store'),
        'method' => 'POST',
        'filterKeys' => $filterKeys,
        'defaultBalanceDate' => '2026-04-30',
    ])

    @include('cash-balances.partials.form-modal', [
        'modalId' => 'editCashBalanceModal',
        'title' => 'Редактировать остаток ДС',
        'action' => '#',
        'method' => 'PUT',
        'filterKeys' => $filterKeys,
    ])
@endsection

@push('scripts')
    <style>
        .cash-balance-amount-input {
            -moz-appearance: textfield;
        }

        .cash-balance-amount-input::-webkit-outer-spin-button,
        .cash-balance-amount-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .cash-balance-success-toast {
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

        .cash-balance-success-toast__content {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1rem;
        }

        .cash-balance-success-toast__timer {
            height: 3px;
            background: #28a745;
            animation: cashBalanceSuccessToastTimer 4s linear forwards;
        }

        .cash-balance-success-toast.is-hiding {
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity .2s ease, transform .2s ease;
        }

        @keyframes cashBalanceSuccessToastTimer {
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
            $('#cashBalanceFilters').on('submit', function () {
                $(this).find('[name="filter_expanded"]').val($(this).hasClass('show') ? '1' : '0');
            });

            if (window.history.replaceState) {
                var url = new URL(window.location.href);

                if (url.searchParams.has('filter_expanded')) {
                    url.searchParams.delete('filter_expanded');
                    window.history.replaceState({}, document.title, url.toString());
                }
            }

            $('.js-edit-cash-balance').on('click', function () {
                var button = $(this);
                var modal = $('#editCashBalanceModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="balance_date"]').val(button.attr('data-balance-date'));
                modal.find('[name="sort_order"]').val(button.attr('data-sort-order'));
                modal.find('[name="company"]').val(button.attr('data-company'));
                modal.find('[name="balance_amount"]').val(button.attr('data-balance-amount'));
                modal.find('[name="custody_assets_amount"]').val(button.attr('data-custody-assets-amount'));
                modal.find('[name="own_assets_amount"]').val(button.attr('data-own-assets-amount'));
            });

            $('#createCashBalanceModal').on('show.bs.modal', function () {
                var modal = $(this);

                modal.find('[name="balance_date"]').val('2026-04-30');
                modal.find('[name="sort_order"]').val('');
                modal.find('[name="company"]').val('');
                modal.find('[name="balance_amount"]').val('');
                modal.find('[name="custody_assets_amount"]').val('');
                modal.find('[name="own_assets_amount"]').val('');
            });

            var toast = $('.cash-balance-success-toast');

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
