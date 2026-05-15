@extends('layouts.adminlte')

@section('title', 'Дт и Кт | ' . config('app.name'))
@section('page-title', 'Дт и Кт')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
    $filterKeys = ['date_from', 'date_to', 'section', 'group_name', 'company', 'amount_from', 'amount_to', 'per_page', 'page'];
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
                    <h3>{{ $money($creditorAmount) }}</h3>
                    <p>Кредиторская</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-circle-up"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $money($debtorAmount) }}</h3>
                    <p>Дебиторская</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-circle-down"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $money($totalAmount) }}</h3>
                    <p>Сумма по фильтру</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
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
        <div class="card-header js-filter-header" data-toggle-target="#debtCreditFilters">
            <h3 class="filter-title"><i class="fas fa-sliders-h text-muted"></i> Фильтры</h3>
            <button type="button" class="btn btn-tool js-filter-toggle collapsed" data-toggle="collapse" data-target="#debtCreditFilters">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <form method="GET" action="{{ route('debt-credit-reports.index') }}" id="debtCreditFilters" class="collapse">
            <div class="card-body">
                <div class="row filter-panel">
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
                        <div class="form-group">
                            <label for="section">Тип</label>
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
                            <label for="group_name">Группа</label>
                            <select id="group_name" name="group_name" class="form-control">
                                <option value="">Все</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group }}" @selected(($filters['group_name'] ?? '') === $group)>{{ $group }}</option>
                                @endforeach
                            </select>
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
                </div>
                <div class="row align-items-end filter-panel">
                    <div class="col-md-2">
                        <div class="form-group mb-md-0">
                            <label for="amount_from">Сумма от</label>
                            <input type="number" step="0.01" min="0" id="amount_from" name="amount_from" value="{{ $filters['amount_from'] ?? '' }}" class="form-control debt-credit-amount-input">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-md-0">
                            <label for="amount_to">Сумма до</label>
                            <input type="number" step="0.01" min="0" id="amount_to" name="amount_to" value="{{ $filters['amount_to'] ?? '' }}" class="form-control debt-credit-amount-input">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-1"></i> Применить</button>
                            <a href="{{ route('debt-credit-reports.index') }}" class="btn btn-default"><i class="fas fa-times mr-1"></i> Сбросить</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Записи Дт и Кт: {{ $totalCount }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#createDebtCreditModal">
                    <i class="fas fa-plus mr-1"></i> Новая запись
                </button>
                <form method="GET" action="{{ route('debt-credit-reports.index') }}" class="d-inline-block">
                    @foreach (request()->only(['date_from', 'date_to', 'section', 'group_name', 'company', 'amount_from', 'amount_to']) as $name => $value)
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
                        <th>№</th>
                        <th>Дата</th>
                        <th>Тип</th>
                        <th>Группа</th>
                        <th>Контрагент</th>
                        <th class="text-right">Сумма</th>
                        <th>Компания</th>
                        <th>Примечание</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->sort_order ?: $entries->firstItem() + $loop->index }}</td>
                            <td>{{ $entry->report_date->format('d.m.Y') }}</td>
                            <td>{{ $sections[$entry->section] ?? $entry->section }}</td>
                            <td class="text-wrap" style="min-width: 220px;">{{ $entry->group_name ?: '-' }}</td>
                            <td>{{ $entry->counterparty }}</td>
                            <td class="text-right font-weight-bold">{{ $money($entry->amount) }}</td>
                            <td>{{ $entry->company ?: '-' }}</td>
                            <td class="text-wrap" style="min-width: 220px;">{{ $entry->note ?: '-' }}</td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-outline-primary js-edit-debt-credit" data-toggle="modal" data-target="#editDebtCreditModal" data-action="{{ route('debt-credit-reports.update', $entry) }}" data-report-date="{{ $entry->report_date->format('Y-m-d') }}" data-section="{{ $entry->section }}" data-sort-order="{{ $entry->sort_order }}" data-group-name="{{ $entry->group_name }}" data-counterparty="{{ $entry->counterparty }}" data-amount="{{ $entry->amount }}" data-company="{{ $entry->company }}" data-note="{{ $entry->note }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('debt-credit-reports.destroy', $entry) }}" class="d-inline" onsubmit="return confirm('Удалить запись Дт и Кт?')">
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
                        <tr><td colspan="9" class="text-center text-muted py-4">Записей пока нет</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($entries->hasPages())
            <div class="card-footer">{{ $entries->links() }}</div>
        @endif
    </div>

    @include('debt-credit-reports.partials.form-modal', [
        'modalId' => 'createDebtCreditModal',
        'title' => 'Новая запись Дт и Кт',
        'action' => route('debt-credit-reports.store'),
        'method' => 'POST',
        'filterKeys' => $filterKeys,
        'sections' => $sections,
        'defaultReportDate' => '2026-04-30',
    ])
    @include('debt-credit-reports.partials.form-modal', [
        'modalId' => 'editDebtCreditModal',
        'title' => 'Редактировать запись Дт и Кт',
        'action' => '#',
        'method' => 'PUT',
        'filterKeys' => $filterKeys,
        'sections' => $sections,
    ])
@endsection

@push('scripts')
    <style>
        .debt-credit-amount-input {
            -moz-appearance: textfield;
        }

        .debt-credit-amount-input::-webkit-outer-spin-button,
        .debt-credit-amount-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
    <script>
        $(function () {
            $('.js-edit-debt-credit').on('click', function () {
                var button = $(this);
                var modal = $('#editDebtCreditModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="report_date"]').val(button.attr('data-report-date'));
                modal.find('[name="section"]').val(button.attr('data-section'));
                modal.find('[name="sort_order"]').val(button.attr('data-sort-order'));
                modal.find('[name="group_name"]').val(button.attr('data-group-name'));
                modal.find('[name="counterparty"]').val(button.attr('data-counterparty'));
                modal.find('[name="amount"]').val(button.attr('data-amount'));
                modal.find('[name="company"]').val(button.attr('data-company'));
                modal.find('[name="note"]').val(button.attr('data-note'));
            });

            $('#createDebtCreditModal').on('show.bs.modal', function () {
                var modal = $(this);

                modal.find('[name="report_date"]').val('2026-04-30');
                modal.find('[name="section"]').val('creditor');
                modal.find('[name="sort_order"]').val('');
                modal.find('[name="group_name"]').val('');
                modal.find('[name="counterparty"]').val('');
                modal.find('[name="amount"]').val('');
                modal.find('[name="company"]').val('');
                modal.find('[name="note"]').val('');
            });

            $('.js-per-page-select').on('change', function () {
                $(this).closest('form').trigger('submit');
            });
        });
    </script>
@endpush
