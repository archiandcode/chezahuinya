@extends('layouts.adminlte')

@section('title', 'Остатки по торговым | ' . config('app.name'))
@section('page-title', 'Остатки по торговым')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
    $filterKeys = ['date_from', 'date_to', 'product_group', 'quantity_from', 'quantity_to', 'amount_from', 'amount_to', 'per_page', 'page'];
    $activeFilters = collect([
        'date_from' => ['label' => 'Дата от', 'value' => $filters['date_from'] ?? null],
        'date_to' => ['label' => 'Дата до', 'value' => $filters['date_to'] ?? null],
        'product_group' => ['label' => 'Группа', 'value' => $filters['product_group'] ?? null],
        'quantity_from' => ['label' => 'Кол-во от', 'value' => $filters['quantity_from'] ?? null],
        'quantity_to' => ['label' => 'Кол-во до', 'value' => $filters['quantity_to'] ?? null],
        'amount_from' => ['label' => 'Сумма от', 'value' => $filters['amount_from'] ?? null],
        'amount_to' => ['label' => 'Сумма до', 'value' => $filters['amount_to'] ?? null],
    ])->filter(fn ($filter) => filled($filter['value']));
@endphp

@section('content')
    @if (session('toast_success'))
        <div class="trading-stock-balance-success-toast" role="status" aria-live="polite">
            <div class="trading-stock-balance-success-toast__content">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('toast_success') }}</span>
            </div>
            <div class="trading-stock-balance-success-toast__timer"></div>
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
                    <h3>{{ $money($totalQuantity) }}</h3>
                    <p>Кол-во по фильтру</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $money($totalCostAmount) }}</h3>
                    <p>Себестоимость</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $groupsCount }}</h3>
                    <p>Групп товара</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
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
        <div class="card-header js-filter-header" data-toggle-target="#tradingStockBalanceFilters">
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
                    data-target="#tradingStockBalanceFilters"
                    aria-expanded="{{ request('filter_expanded') === '1' ? 'true' : 'false' }}"
                    aria-controls="tradingStockBalanceFilters"
                    title="Свернуть / развернуть"
                >
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <form method="GET" action="{{ route('trading-stock-balances.index') }}" id="tradingStockBalanceFilters" class="collapse {{ request('filter_expanded') === '1' ? 'show' : '' }}">
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
                                <label for="product_group">Группа товара</label>
                                <select id="product_group" name="product_group" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($productGroups as $productGroup)
                                        <option value="{{ $productGroup }}" @selected(($filters['product_group'] ?? '') === $productGroup)>{{ $productGroup }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="quantity_from">Кол-во от</label>
                                <input type="number" step="0.01" min="0" id="quantity_from" name="quantity_from" value="{{ $filters['quantity_from'] ?? '' }}" class="form-control trading-stock-balance-number-input">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="quantity_to">Кол-во до</label>
                                <input type="number" step="0.01" min="0" id="quantity_to" name="quantity_to" value="{{ $filters['quantity_to'] ?? '' }}" class="form-control trading-stock-balance-number-input">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount_from">Сумма от</label>
                                <input type="number" step="0.01" min="0" id="amount_from" name="amount_from" value="{{ $filters['amount_from'] ?? '' }}" class="form-control trading-stock-balance-number-input">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount_to">Сумма до</label>
                                <input type="number" step="0.01" min="0" id="amount_to" name="amount_to" value="{{ $filters['amount_to'] ?? '' }}" class="form-control trading-stock-balance-number-input">
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-end filter-panel">
                        <div class="col-md-12">
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter mr-1"></i> Применить
                                </button>
                                <a href="{{ route('trading-stock-balances.index', request()->filled('per_page') ? ['per_page' => request('per_page')] : []) }}" class="btn btn-default">
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
            <h3 class="card-title">Остатки по торговым: {{ $totalCount }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#createTradingStockBalanceModal">
                    <i class="fas fa-plus mr-1"></i> Новая запись
                </button>
                <form method="GET" action="{{ route('trading-stock-balances.index') }}" class="d-inline-block">
                    @foreach (request()->only(['date_from', 'date_to', 'product_group', 'quantity_from', 'quantity_to', 'amount_from', 'amount_to', 'filter_expanded']) as $name => $value)
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
                        <th style="width: 70px;">№ пп</th>
                        <th>Дата</th>
                        <th>Группа товара</th>
                        <th class="text-right">Кол-во</th>
                        <th class="text-right">Сумма себестоимости</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($balances as $balance)
                        <tr>
                            <td>{{ $balance->sort_order ?: $balances->firstItem() + $loop->index }}</td>
                            <td>{{ $balance->balance_date->format('d.m.Y') }}</td>
                            <td>{{ $balance->product_group }}</td>
                            <td class="text-right font-weight-bold">{{ $money($balance->quantity) }}</td>
                            <td class="text-right font-weight-bold">{{ $money($balance->cost_amount) }}</td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary js-edit-trading-stock-balance"
                                    data-toggle="modal"
                                    data-target="#editTradingStockBalanceModal"
                                    data-action="{{ route('trading-stock-balances.update', $balance) }}"
                                    data-balance-date="{{ $balance->balance_date->format('Y-m-d') }}"
                                    data-sort-order="{{ $balance->sort_order }}"
                                    data-product-group="{{ $balance->product_group }}"
                                    data-quantity="{{ $balance->quantity }}"
                                    data-cost-amount="{{ $balance->cost_amount }}"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('trading-stock-balances.destroy', $balance) }}" class="d-inline" onsubmit="return confirm('Удалить запись остатка по торговым?')">
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
                            <td colspan="6" class="text-center text-muted py-4">Записей пока нет</td>
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

    @include('trading-stock-balances.partials.form-modal', [
        'modalId' => 'createTradingStockBalanceModal',
        'title' => 'Новая запись остатка по торговым',
        'action' => route('trading-stock-balances.store'),
        'method' => 'POST',
        'filterKeys' => $filterKeys,
        'defaultBalanceDate' => $defaultBalanceDate,
    ])

    @include('trading-stock-balances.partials.form-modal', [
        'modalId' => 'editTradingStockBalanceModal',
        'title' => 'Редактировать остаток по торговым',
        'action' => '#',
        'method' => 'PUT',
        'filterKeys' => $filterKeys,
    ])
@endsection

@push('scripts')
    <style>
        .trading-stock-balance-number-input {
            -moz-appearance: textfield;
        }

        .trading-stock-balance-number-input::-webkit-outer-spin-button,
        .trading-stock-balance-number-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .trading-stock-balance-success-toast {
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

        .trading-stock-balance-success-toast__content {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1rem;
        }

        .trading-stock-balance-success-toast__timer {
            height: 3px;
            background: #28a745;
            animation: tradingStockBalanceSuccessToastTimer 4s linear forwards;
        }

        .trading-stock-balance-success-toast.is-hiding {
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity .2s ease, transform .2s ease;
        }

        @keyframes tradingStockBalanceSuccessToastTimer {
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
            $('#tradingStockBalanceFilters').on('submit', function () {
                $(this).find('[name="filter_expanded"]').val($(this).hasClass('show') ? '1' : '0');
            });

            if (window.history.replaceState) {
                var url = new URL(window.location.href);

                if (url.searchParams.has('filter_expanded')) {
                    url.searchParams.delete('filter_expanded');
                    window.history.replaceState({}, document.title, url.toString());
                }
            }

            $('.js-edit-trading-stock-balance').on('click', function () {
                var button = $(this);
                var modal = $('#editTradingStockBalanceModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="balance_date"]').val(button.attr('data-balance-date'));
                modal.find('[name="sort_order"]').val(button.attr('data-sort-order'));
                modal.find('[name="product_group"]').val(button.attr('data-product-group'));
                modal.find('[name="quantity"]').val(button.attr('data-quantity'));
                modal.find('[name="cost_amount"]').val(button.attr('data-cost-amount'));
            });

            $('#createTradingStockBalanceModal').on('show.bs.modal', function () {
                var modal = $(this);

                modal.find('[name="balance_date"]').val('{{ $defaultBalanceDate }}');
                modal.find('[name="sort_order"]').val('');
                modal.find('[name="product_group"]').val('');
                modal.find('[name="quantity"]').val('');
                modal.find('[name="cost_amount"]').val('');
            });

            var toast = $('.trading-stock-balance-success-toast');

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
