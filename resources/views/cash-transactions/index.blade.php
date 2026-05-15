@extends('layouts.adminlte')

@section('title', 'Кассы | ' . config('app.name'))
@section('page-title', 'Кассы')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
    $cashRegisterNames = $cashRegisters->pluck('name', 'id');
    $companyNames = $companies->pluck('name', 'id');
    $cashFlowNames = $cashFlows->pluck('name', 'id');
    $activeFilters = collect([
        'cash_register_id' => ['label' => 'Касса', 'value' => $cashRegisterNames[(int) ($filters['cash_register_id'] ?? 0)] ?? null],
        'date_from' => ['label' => 'Дата от', 'value' => $filters['date_from'] ?? null],
        'date_to' => ['label' => 'Дата до', 'value' => $filters['date_to'] ?? null],
        'cash_company_id' => ['label' => 'Компания', 'value' => $companyNames[(int) ($filters['cash_company_id'] ?? 0)] ?? null],
        'cash_flow_category_id' => ['label' => 'ДДС', 'value' => $cashFlowNames[(int) ($filters['cash_flow_category_id'] ?? 0)] ?? null],
        'direction' => ['label' => 'Тип', 'value' => ['income' => 'Поступление', 'expense' => 'Расход'][$filters['direction'] ?? ''] ?? null],
        'has_supporting_document' => ['label' => 'СЗ', 'value' => ['yes' => 'Есть', 'no' => 'Нет'][$filters['has_supporting_document'] ?? ''] ?? null],
    ])->filter(fn ($filter) => filled($filter['value']));
@endphp

@section('content')
    @if (session('toast_success'))
        <div class="cash-success-toast" role="status" aria-live="polite">
            <div class="cash-success-toast__content">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('toast_success') }}</span>
            </div>
            <div class="cash-success-toast__timer"></div>
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

    @if ($errors->any() && ! old('_modal_id'))
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
                    <h3>{{ $money($openingBalance) }}</h3>
                    <p>Начальный остаток</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $money($totalIncome) }}</h3>
                    <p>Поступления по фильтру</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $money($totalExpense) }}</h3>
                    <p>Расходы по фильтру</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $money($currentBalance) }}</h3>
                    <p>Текущий остаток</p>
                </div>
                <div class="icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card filter-card">
        <div class="card-header js-filter-header" data-toggle-target="#cashFilters">
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
                    data-target="#cashFilters"
                    aria-expanded="{{ request('filter_expanded') === '1' ? 'true' : 'false' }}"
                    aria-controls="cashFilters"
                    title="Свернуть / развернуть"
                >
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <form method="GET" action="{{ route('cash-transactions.index') }}" id="cashFilters" class="collapse {{ request('filter_expanded') === '1' ? 'show' : '' }}">
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cash_register_id">Касса</label>
                                <select id="cash_register_id" name="cash_register_id" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($cashRegisters as $cashRegister)
                                        <option value="{{ $cashRegister->id }}" @selected((int) ($filters['cash_register_id'] ?? 0) === $cashRegister->id)>{{ $cashRegister->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cash_company_id">Компания</label>
                                <select id="cash_company_id" name="cash_company_id" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" @selected((int) ($filters['cash_company_id'] ?? 0) === $company->id)>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cash_flow_category_id">ДДС</label>
                                <select id="cash_flow_category_id" name="cash_flow_category_id" class="form-control">
                                    <option value="">Все</option>
                                    @foreach ($cashFlows as $cashFlow)
                                        <option value="{{ $cashFlow->id }}" @selected((int) ($filters['cash_flow_category_id'] ?? 0) === $cashFlow->id)>{{ $cashFlow->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="direction">Тип суммы</label>
                                <select id="direction" name="direction" class="form-control">
                                    <option value="">Все</option>
                                    <option value="income" @selected(($filters['direction'] ?? '') === 'income')>Поступление</option>
                                    <option value="expense" @selected(($filters['direction'] ?? '') === 'expense')>Расход</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="has_supporting_document">СЗ</label>
                                <select id="has_supporting_document" name="has_supporting_document" class="form-control">
                                    <option value="">Все</option>
                                    <option value="yes" @selected(($filters['has_supporting_document'] ?? '') === 'yes')>Есть</option>
                                    <option value="no" @selected(($filters['has_supporting_document'] ?? '') === 'no')>Нет</option>
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
                                <a href="{{ route('cash-transactions.index', request()->filled('per_page') ? ['per_page' => request('per_page')] : []) }}" class="btn btn-default">
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
            <h3 class="card-title">Операции кассы: {{ $totalCount }}</h3>
            <div class="card-tools">
                <a href="{{ route('cash-directories.index') }}" class="btn btn-default btn-sm mr-2">
                    <i class="fas fa-book mr-1"></i> Справочники
                </a>
                <button type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#createCashTransactionModal">
                    <i class="fas fa-plus mr-1"></i> Новая запись
                </button>
                <form method="GET" action="{{ route('cash-transactions.index') }}" class="d-inline-block">
                    @foreach (request()->only(['cash_register_id', 'date_from', 'date_to', 'cash_company_id', 'cash_flow_category_id', 'has_supporting_document', 'direction', 'filter_expanded']) as $name => $value)
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
                        <th class="text-right">Сумма поступления KZT</th>
                        <th class="text-right">Сумма расхода KZT</th>
                        <th class="text-right">Остаток KZT</th>
                        <th>Касса</th>
                        <th>Компания</th>
                        <th>ДДС</th>
                        <th>Наличие СЗ</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transactions->firstItem() + $loop->index }}</td>
                            <td>{{ $transaction->transaction_date->format('d.m.Y') }}</td>
                            <td class="text-right text-success">{{ $transaction->income_amount > 0 ? $money($transaction->income_amount) : '' }}</td>
                            <td class="text-right text-danger">{{ $transaction->expense_amount > 0 ? $money($transaction->expense_amount) : '' }}</td>
                            <td class="text-right font-weight-bold">{{ $money($balances[$transaction->id] ?? $openingBalance) }}</td>
                            <td>{{ $transaction->cashRegister?->name ?: '-' }}</td>
                            <td>{{ $transaction->cashCompany?->name ?: ($transaction->company ?: '-') }}</td>
                            <td>{{ $transaction->cashFlowCategory?->name ?: ($transaction->cash_flow ?: '-') }}</td>
                            <td>
                                @if (is_null($transaction->has_supporting_document))
                                    -
                                @elseif ($transaction->has_supporting_document)
                                    <span class="badge badge-success">Есть</span>
                                @else
                                    <span class="badge badge-secondary">Нет</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary js-edit-transaction"
                                    data-toggle="modal"
                                    data-target="#editCashTransactionModal"
                                    data-action="{{ route('cash-transactions.update', $transaction) }}"
                                    data-cash-register-id="{{ $transaction->cash_register_id }}"
                                    data-date="{{ $transaction->transaction_date->format('Y-m-d') }}"
                                    data-income="{{ $transaction->income_amount }}"
                                    data-expense="{{ $transaction->expense_amount }}"
                                    data-company-id="{{ $transaction->cash_company_id }}"
                                    data-cash-flow-category-id="{{ $transaction->cash_flow_category_id }}"
                                    data-has-document="{{ is_null($transaction->has_supporting_document) ? '' : (int) $transaction->has_supporting_document }}"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('cash-transactions.destroy', $transaction) }}" class="d-inline" onsubmit="return confirm('Удалить запись кассы?')">
                                    @csrf
                                    @method('DELETE')
                                    @foreach (request()->only(['cash_register_id', 'date_from', 'date_to', 'cash_company_id', 'cash_flow_category_id', 'has_supporting_document', 'direction', 'per_page', 'page']) as $name => $value)
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
        @if ($transactions->hasPages())
            <div class="card-footer">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    @include('cash-transactions.partials.form-modal', [
        'modalId' => 'createCashTransactionModal',
        'title' => 'Новая запись кассы',
        'action' => route('cash-transactions.store'),
        'method' => 'POST',
        'transaction' => null,
        'defaultTransactionDate' => now()->toDateString(),
        'cashRegisters' => $cashRegisters,
        'companies' => $companies,
        'cashFlows' => $cashFlows,
    ])

    @include('cash-transactions.partials.form-modal', [
        'modalId' => 'editCashTransactionModal',
        'title' => 'Редактировать запись кассы',
        'action' => '#',
        'method' => 'PUT',
        'transaction' => null,
        'cashRegisters' => $cashRegisters,
        'companies' => $companies,
        'cashFlows' => $cashFlows,
    ])

@endsection

@push('scripts')
    <style>
        .cash-transaction-amount-input {
            -moz-appearance: textfield;
        }

        .cash-transaction-amount-input::-webkit-outer-spin-button,
        .cash-transaction-amount-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .cash-success-toast {
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

        .cash-success-toast__content {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1rem;
        }

        .cash-success-toast__timer {
            height: 3px;
            background: #28a745;
            animation: cashSuccessToastTimer 4s linear forwards;
        }

        .cash-success-toast.is-hiding {
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity .2s ease, transform .2s ease;
        }

        @keyframes cashSuccessToastTimer {
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
            $('#cashFilters').on('submit', function () {
                $(this).find('[name="filter_expanded"]').val($(this).hasClass('show') ? '1' : '0');
            });

            if (window.history.replaceState) {
                var url = new URL(window.location.href);

                if (url.searchParams.has('filter_expanded')) {
                    url.searchParams.delete('filter_expanded');
                    window.history.replaceState({}, document.title, url.toString());
                }
            }

            $('.js-edit-transaction').on('click', function () {
                var button = $(this);
                var modal = $('#editCashTransactionModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="_form_action"]').val(button.attr('data-action'));
                modal.find('[name="cash_register_id"]').val(button.attr('data-cash-register-id'));
                modal.find('[name="transaction_date"]').val(button.attr('data-date'));
                modal.find('[name="income_amount"]').val(button.attr('data-income'));
                modal.find('[name="expense_amount"]').val(button.attr('data-expense'));
                modal.find('[name="cash_company_id"]').val(button.attr('data-company-id'));
                modal.find('[name="cash_flow_category_id"]').val(button.attr('data-cash-flow-category-id'));
                modal.find('[name="has_supporting_document"]').val(button.attr('data-has-document'));
            });

            $('#createCashTransactionModal').on('show.bs.modal', function () {
                var modal = $(this);

                if (modal.attr('data-has-errors') === '1') {
                    return;
                }

                modal.find('[name="cash_register_id"]').val('{{ request('cash_register_id') ?: $cashRegisters->first()?->id }}');
                modal.find('[name="transaction_date"]').val('{{ now()->toDateString() }}');
                modal.find('[name="income_amount"]').val('');
                modal.find('[name="expense_amount"]').val('');
                modal.find('[name="cash_company_id"]').val('');
                modal.find('[name="cash_flow_category_id"]').val('');
                modal.find('[name="has_supporting_document"]').val('');
            });

            $('.modal').on('hidden.bs.modal', function () {
                var modal = $(this);

                if (modal.attr('data-has-errors') !== '1') {
                    return;
                }

                modal.attr('data-has-errors', '0');
                modal.find('.alert-danger').remove();
                modal.find('.is-invalid').removeClass('is-invalid');
                modal.find('.invalid-feedback').remove();
            });

            var errorModalId = @json(old('_modal_id'));

            if (errorModalId) {
                $('#' + errorModalId).modal('show');
            }

            var toast = $('.cash-success-toast');

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
