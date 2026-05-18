@extends('layouts.adminlte')

@section('title', 'Справочники кассы | ' . config('app.name'))
@section('page-title', 'Справочники кассы')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ' ');
    $directionLabels = [
        'income' => 'Поступление',
        'expense' => 'Расход',
    ];
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

    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-cash-register"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Кассы</span>
                    <span class="info-box-number">{{ $cashRegisters->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-building"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Компании</span>
                    <span class="info-box-number">{{ $companies->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-list-ul"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Статьи ДДС</span>
                    <span class="info-box-number">{{ $cashFlows->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card directory-shell">
        <div class="card-body">
            <ul class="nav nav-pills directory-tabs" id="directoryTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="registers-tab" data-toggle="tab" href="#registers" role="tab">
                        <i class="fas fa-cash-register mr-1"></i> Кассы
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="companies-tab" data-toggle="tab" href="#companies" role="tab">
                        <i class="fas fa-building mr-1"></i> Компании
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="flows-tab" data-toggle="tab" href="#flows" role="tab">
                        <i class="fas fa-list-ul mr-1"></i> ДДС
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="registers" role="tabpanel">
                    <div class="directory-toolbar">
                        <div>
                            <h3>Кассы</h3>
                            <p>Список касс, которые отображаются в левом меню и используются в операциях.</p>
                        </div>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createRegisterModal">
                            <i class="fas fa-plus mr-1"></i> Новая касса
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Касса</th>
                                    <th>Валюта</th>
                                    <th class="text-right">Начальный остаток</th>
                                    <th>Дата остатка</th>
                                    <th>Статус</th>
                                    <th class="text-right">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cashRegisters as $cashRegister)
                                    <tr>
                                        <td class="font-weight-bold">{{ $cashRegister->name }}</td>
                                        <td>{{ $cashRegister->currency }}</td>
                                        <td class="text-right">{{ $money($cashRegister->opening_balance) }}</td>
                                        <td>{{ $cashRegister->opening_balance_date?->format('d.m.Y') ?: '-' }}</td>
                                        <td>
                                            <span class="badge {{ $cashRegister->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $cashRegister->is_active ? 'Активна' : 'Скрыта' }}
                                            </span>
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary js-edit-register"
                                                data-toggle="modal"
                                                data-target="#editRegisterModal"
                                                data-directory="{{ \Illuminate\Support\Js::encode([
                                                    'action' => route('cash-registers.update', $cashRegister),
                                                    'name' => $cashRegister->name,
                                                    'currency' => $cashRegister->currency,
                                                    'opening_balance' => $cashRegister->opening_balance,
                                                    'opening_balance_date' => $cashRegister->opening_balance_date?->format('Y-m-d'),
                                                    'is_active' => (bool) $cashRegister->is_active,
                                                ]) }}"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
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

                <div class="tab-pane fade" id="companies" role="tabpanel">
                    <div class="directory-toolbar">
                        <div>
                            <h3>Компании</h3>
                            <p>Единый список компаний для выбора в операциях кассы.</p>
                        </div>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createCompanyModal">
                            <i class="fas fa-plus mr-1"></i> Новая компания
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Компания</th>
                                    <th>Короткое имя</th>
                                    <th>Статус</th>
                                    <th class="text-right">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($companies as $company)
                                    <tr>
                                        <td class="font-weight-bold">{{ $company->name }}</td>
                                        <td>{{ $company->short_name ?: '-' }}</td>
                                        <td>
                                            <span class="badge {{ $company->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $company->is_active ? 'Активна' : 'Скрыта' }}
                                            </span>
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary js-edit-company"
                                                data-toggle="modal"
                                                data-target="#editCompanyModal"
                                                data-directory="{{ \Illuminate\Support\Js::encode([
                                                    'action' => route('cash-companies.update', $company),
                                                    'name' => $company->name,
                                                    'short_name' => $company->short_name,
                                                    'is_active' => (bool) $company->is_active,
                                                ]) }}"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
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

                <div class="tab-pane fade" id="flows" role="tabpanel">
                    <div class="directory-toolbar">
                        <div>
                            <h3>Статьи ДДС</h3>
                            <p>Категории движения денежных средств: поступления, расходы или общий тип.</p>
                        </div>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createFlowModal">
                            <i class="fas fa-plus mr-1"></i> Новая статья
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Статья</th>
                                    <th>Тип</th>
                                    <th>Статус</th>
                                    <th class="text-right">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cashFlows as $cashFlow)
                                    <tr>
                                        <td class="font-weight-bold">{{ $cashFlow->name }}</td>
                                        <td>{{ $directionLabels[$cashFlow->direction] ?? 'Любой тип' }}</td>
                                        <td>
                                            <span class="badge {{ $cashFlow->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $cashFlow->is_active ? 'Активна' : 'Скрыта' }}
                                            </span>
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary js-edit-flow"
                                                data-toggle="modal"
                                                data-target="#editFlowModal"
                                                data-directory="{{ \Illuminate\Support\Js::encode([
                                                    'action' => route('cash-flow-categories.update', $cashFlow),
                                                    'name' => $cashFlow->name,
                                                    'direction' => $cashFlow->direction,
                                                    'is_active' => (bool) $cashFlow->is_active,
                                                ]) }}"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
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

    @include('cash-directories.partials.register-modal', [
        'modalId' => 'createRegisterModal',
        'title' => 'Новая касса',
        'action' => route('cash-registers.store'),
        'method' => 'POST',
        'register' => null,
    ])

    @include('cash-directories.partials.register-modal', [
        'modalId' => 'editRegisterModal',
        'title' => 'Редактировать кассу',
        'action' => '#',
        'method' => 'PUT',
        'register' => null,
    ])

    @include('cash-directories.partials.company-modal', [
        'modalId' => 'createCompanyModal',
        'title' => 'Новая компания',
        'action' => route('cash-companies.store'),
        'method' => 'POST',
    ])

    @include('cash-directories.partials.company-modal', [
        'modalId' => 'editCompanyModal',
        'title' => 'Редактировать компанию',
        'action' => '#',
        'method' => 'PUT',
    ])

    @include('cash-directories.partials.flow-modal', [
        'modalId' => 'createFlowModal',
        'title' => 'Новая статья ДДС',
        'action' => route('cash-flow-categories.store'),
        'method' => 'POST',
    ])

    @include('cash-directories.partials.flow-modal', [
        'modalId' => 'editFlowModal',
        'title' => 'Редактировать статью ДДС',
        'action' => '#',
        'method' => 'PUT',
    ])
@endsection

@push('scripts')
    <style>
        .directory-tabs {
            gap: .5rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .directory-tabs .nav-link {
            display: inline-flex;
            align-items: center;
            min-height: 2.25rem;
            color: #495057;
            border: 1px solid #dee2e6;
            border-radius: .25rem;
            background: #fff;
        }

        .directory-tabs .nav-link.active {
            color: #fff;
            border-color: #007bff;
            background: #007bff;
        }

        .directory-toolbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .directory-toolbar h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .directory-toolbar p {
            margin: .25rem 0 0;
            color: #6c757d;
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

        @media (max-width: 767.98px) {
            .directory-toolbar {
                display: block;
            }

            .directory-toolbar .btn {
                width: 100%;
                margin-top: .75rem;
            }
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
            function directoryData(button) {
                return button.data('directory') || {};
            }

            $('.js-edit-register').on('click', function () {
                var button = $(this);
                var data = directoryData(button);
                var modal = $('#editRegisterModal');

                modal.find('form').attr('action', data.action || '#');
                modal.find('[name="name"]').val(data.name || '');
                modal.find('[name="currency"]').val(data.currency || 'KZT');
                modal.find('[name="opening_balance"]').val(data.opening_balance || '0.00');
                modal.find('[name="opening_balance_date"]').val(data.opening_balance_date || '');
                modal.find('[name="is_active"]').prop('checked', !! data.is_active);
            });

            $('.js-edit-company').on('click', function () {
                var button = $(this);
                var data = directoryData(button);
                var modal = $('#editCompanyModal');

                modal.find('form').attr('action', data.action || '#');
                modal.find('[name="name"]').val(data.name || '');
                modal.find('[name="short_name"]').val(data.short_name || '');
                modal.find('[name="is_active"]').prop('checked', !! data.is_active);
            });

            $('.js-edit-flow').on('click', function () {
                var button = $(this);
                var data = directoryData(button);
                var modal = $('#editFlowModal');

                modal.find('form').attr('action', data.action || '#');
                modal.find('[name="name"]').val(data.name || '');
                modal.find('[name="direction"]').val(data.direction || '');
                modal.find('[name="is_active"]').prop('checked', !! data.is_active);
            });

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
