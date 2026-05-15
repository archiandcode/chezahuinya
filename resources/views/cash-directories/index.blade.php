@include('cash-directories.redesigned')
@php return; @endphp

@extends('layouts.adminlte')

@section('title', 'Справочники кассы | ' . config('app.name'))
@section('page-title', 'Справочники кассы')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
@endphp

@section('content')
    @if (session('toast_success'))
        <div class="cash-directory-toast" role="status" aria-live="polite">
            <div class="cash-directory-toast__content">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('toast_success') }}</span>
            </div>
            <div class="cash-directory-toast__timer"></div>
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

    <div class="mb-3">
        <a href="{{ route('cash-transactions.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left mr-1"></i> Назад к кассам
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Кассы</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('cash-registers.store') }}" class="directory-form mb-3">
                @csrf
                <input type="text" name="name" class="form-control" placeholder="Название кассы" required>
                <input type="text" name="currency" class="form-control directory-currency" value="KZT" maxlength="3" required>
                <input type="number" step="0.01" name="opening_balance" class="form-control" placeholder="Начальный остаток" required>
                <input type="date" name="opening_balance_date" class="form-control" value="2026-01-05">
                <label class="directory-check">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Активна
                </label>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Добавить
                </button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Название</th>
                            <th>Валюта</th>
                            <th class="text-right">Начальный остаток</th>
                            <th>Дата остатка</th>
                            <th>Активна</th>
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cashRegisters as $cashRegister)
                            <tr>
                                <form method="POST" action="{{ route('cash-registers.update', $cashRegister) }}">
                                    @csrf
                                    @method('PUT')
                                    <td><input type="text" name="name" value="{{ $cashRegister->name }}" class="form-control" required></td>
                                    <td><input type="text" name="currency" value="{{ $cashRegister->currency }}" class="form-control directory-currency" maxlength="3" required></td>
                                    <td><input type="number" step="0.01" name="opening_balance" value="{{ $cashRegister->opening_balance }}" class="form-control text-right" required></td>
                                    <td><input type="date" name="opening_balance_date" value="{{ $cashRegister->opening_balance_date?->format('Y-m-d') }}" class="form-control"></td>
                                    <td class="text-center"><input type="checkbox" name="is_active" value="1" @checked($cashRegister->is_active)></td>
                                    <td class="text-right text-nowrap">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-save"></i>
                                        </button>
                                </form>
                                        <form method="POST" action="{{ route('cash-registers.destroy', $cashRegister) }}" class="d-inline" onsubmit="return confirm('Удалить кассу?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Касс пока нет</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Компании</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cash-companies.store') }}" class="directory-form mb-3">
                        @csrf
                        <input type="text" name="name" class="form-control" placeholder="Компания" required>
                        <input type="text" name="short_name" class="form-control" placeholder="Короткое имя">
                        <label class="directory-check">
                            <input type="checkbox" name="is_active" value="1" checked>
                            Активна
                        </label>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i> Добавить
                        </button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Компания</th>
                                    <th>Короткое имя</th>
                                    <th>Активна</th>
                                    <th class="text-right">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($companies as $company)
                                    <tr>
                                        <form method="POST" action="{{ route('cash-companies.update', $company) }}">
                                            @csrf
                                            @method('PUT')
                                            <td><input type="text" name="name" value="{{ $company->name }}" class="form-control" required></td>
                                            <td><input type="text" name="short_name" value="{{ $company->short_name }}" class="form-control"></td>
                                            <td class="text-center"><input type="checkbox" name="is_active" value="1" @checked($company->is_active)></td>
                                            <td class="text-right text-nowrap">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                        </form>
                                                <form method="POST" action="{{ route('cash-companies.destroy', $company) }}" class="d-inline" onsubmit="return confirm('Удалить компанию?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Компаний пока нет</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ДДС</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cash-flow-categories.store') }}" class="directory-form mb-3">
                        @csrf
                        <input type="text" name="name" class="form-control" placeholder="Статья ДДС" required>
                        <select name="direction" class="form-control">
                            <option value="">Любой тип</option>
                            <option value="income">Поступление</option>
                            <option value="expense">Расход</option>
                        </select>
                        <label class="directory-check">
                            <input type="checkbox" name="is_active" value="1" checked>
                            Активна
                        </label>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i> Добавить
                        </button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Статья</th>
                                    <th>Тип</th>
                                    <th>Активна</th>
                                    <th class="text-right">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cashFlows as $cashFlow)
                                    <tr>
                                        <form method="POST" action="{{ route('cash-flow-categories.update', $cashFlow) }}">
                                            @csrf
                                            @method('PUT')
                                            <td><input type="text" name="name" value="{{ $cashFlow->name }}" class="form-control" required></td>
                                            <td>
                                                <select name="direction" class="form-control">
                                                    <option value="">Любой тип</option>
                                                    <option value="income" @selected($cashFlow->direction === 'income')>Поступление</option>
                                                    <option value="expense" @selected($cashFlow->direction === 'expense')>Расход</option>
                                                </select>
                                            </td>
                                            <td class="text-center"><input type="checkbox" name="is_active" value="1" @checked($cashFlow->is_active)></td>
                                            <td class="text-right text-nowrap">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                        </form>
                                                <form method="POST" action="{{ route('cash-flow-categories.destroy', $cashFlow) }}" class="d-inline" onsubmit="return confirm('Удалить статью ДДС?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Статей ДДС пока нет</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .directory-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: .5rem;
            align-items: center;
        }

        .directory-check {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            min-height: calc(2.25rem + 2px);
            margin: 0;
            color: #495057;
            font-weight: 400;
        }

        .directory-currency {
            text-transform: uppercase;
        }

        .cash-directory-toast {
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

        .cash-directory-toast__content {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1rem;
        }

        .cash-directory-toast__timer {
            height: 3px;
            background: #28a745;
            animation: cashDirectoryToastTimer 4s linear forwards;
        }

        .cash-directory-toast.is-hiding {
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity .2s ease, transform .2s ease;
        }

        @keyframes cashDirectoryToastTimer {
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
            var toast = $('.cash-directory-toast');

            if (toast.length) {
                setTimeout(function () {
                    toast.addClass('is-hiding');

                    setTimeout(function () {
                        toast.remove();
                    }, 200);
                }, 4000);
            }
        });
    </script>
@endpush
