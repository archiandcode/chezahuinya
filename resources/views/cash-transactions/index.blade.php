@extends('layouts.adminlte')

@section('title', 'Кассы | ' . config('app.name'))
@section('page-title', 'Кассы')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
@endphp

@section('content')
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

    <div class="card">
        <div class="card-header js-filter-header" data-toggle-target="#cashFilters">
            <h3 class="card-title">Фильтры</h3>
            <div class="card-tools">
                <button
                    type="button"
                    class="btn btn-tool js-filter-toggle collapsed"
                    data-toggle="collapse"
                    data-target="#cashFilters"
                    aria-expanded="false"
                    aria-controls="cashFilters"
                    title="Свернуть / развернуть"
                >
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <form method="GET" action="{{ route('cash-transactions.index') }}" id="cashFilters" class="collapse">
            <div class="card-body">
                <div class="row">
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
                    <div class="col-md-2">
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
                            <label for="cash_flow">ДДС</label>
                            <select id="cash_flow" name="cash_flow" class="form-control">
                                <option value="">Все</option>
                                @foreach ($cashFlows as $cashFlow)
                                    <option value="{{ $cashFlow }}" @selected(($filters['cash_flow'] ?? '') === $cashFlow)>{{ $cashFlow }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="direction">Тип суммы</label>
                            <select id="direction" name="direction" class="form-control">
                                <option value="">Все</option>
                                <option value="income" @selected(($filters['direction'] ?? '') === 'income')>Поступление</option>
                                <option value="expense" @selected(($filters['direction'] ?? '') === 'expense')>Расход</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
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
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <div class="form-group mb-md-0">
                            <label for="search">Поиск</label>
                            <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Компания или ДДС">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-md-0">
                            <label for="per_page">На странице</label>
                            <select id="per_page" name="per_page" class="form-control">
                                @foreach ([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 10) === $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-1"></i> Применить
                        </button>
                        <a href="{{ route('cash-transactions.index') }}" class="btn btn-default">
                            <i class="fas fa-times mr-1"></i> Сбросить
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Операции кассы: {{ $totalCount }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createCashTransactionModal">
                    <i class="fas fa-plus mr-1"></i> Новая запись
                </button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th class="text-right">Сумма поступления KZT</th>
                        <th class="text-right">Сумма расхода KZT</th>
                        <th class="text-right">Остаток KZT</th>
                        <th>Компания</th>
                        <th>ДДС</th>
                        <th>Наличие СЗ</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_date->format('d.m.Y') }}</td>
                            <td class="text-right text-success">{{ $transaction->income_amount > 0 ? $money($transaction->income_amount) : '' }}</td>
                            <td class="text-right text-danger">{{ $transaction->expense_amount > 0 ? $money($transaction->expense_amount) : '' }}</td>
                            <td class="text-right font-weight-bold">{{ $money($balances[$transaction->id] ?? $openingBalance) }}</td>
                            <td>{{ $transaction->company ?: '-' }}</td>
                            <td>{{ $transaction->cash_flow ?: '-' }}</td>
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
                                    data-date="{{ $transaction->transaction_date->format('Y-m-d') }}"
                                    data-income="{{ $transaction->income_amount }}"
                                    data-expense="{{ $transaction->expense_amount }}"
                                    data-company="{{ $transaction->company }}"
                                    data-cash-flow="{{ $transaction->cash_flow }}"
                                    data-has-document="{{ is_null($transaction->has_supporting_document) ? '' : (int) $transaction->has_supporting_document }}"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('cash-transactions.destroy', $transaction) }}" class="d-inline" onsubmit="return confirm('Удалить запись кассы?')">
                                    @csrf
                                    @method('DELETE')
                                    @foreach (request()->only(['date_from', 'date_to', 'company', 'cash_flow', 'has_supporting_document', 'direction', 'search', 'per_page', 'page']) as $name => $value)
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
    ])

    @include('cash-transactions.partials.form-modal', [
        'modalId' => 'editCashTransactionModal',
        'title' => 'Редактировать запись кассы',
        'action' => '#',
        'method' => 'PUT',
        'transaction' => null,
    ])

@endsection

@push('scripts')
    <style>
        .js-filter-toggle .fa-chevron-down {
            transition: transform .2s ease;
        }

        .js-filter-toggle[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }
    </style>
    <script>
        $(function () {
            $('.js-edit-transaction').on('click', function () {
                var button = $(this);
                var modal = $('#editCashTransactionModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="transaction_date"]').val(button.attr('data-date'));
                modal.find('[name="income_amount"]').val(button.attr('data-income'));
                modal.find('[name="expense_amount"]').val(button.attr('data-expense'));
                modal.find('[name="company"]').val(button.attr('data-company'));
                modal.find('[name="cash_flow"]').val(button.attr('data-cash-flow'));
                modal.find('[name="has_supporting_document"]').val(button.attr('data-has-document'));
            });
        });
    </script>
@endpush
