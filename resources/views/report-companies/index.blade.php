@extends('layouts.adminlte')

@section('title', 'Компании | ' . config('app.name'))
@section('page-title', 'Справочник компаний')

@php
    $filterKeys = ['category', 'search', 'per_page', 'page'];
@endphp

@section('content')
    @if (session('toast_success'))
        <div class="company-success-toast" role="status" aria-live="polite">
            <div class="company-success-toast__content">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('toast_success') }}</span>
            </div>
            <div class="company-success-toast__timer"></div>
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
                <span class="info-box-icon bg-info"><i class="fas fa-building"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Компаний</span>
                    <span class="info-box-number">{{ $totalCompanies }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-credit-card"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Счетов</span>
                    <span class="info-box-number">{{ $totalAccounts }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-layer-group"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Категорий</span>
                    <span class="info-box-number">{{ $categories->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header js-filter-header" data-toggle-target="#companyFilters">
            <h3 class="card-title">Фильтры</h3>
            <div class="card-tools">
                <button
                    type="button"
                    class="btn btn-tool js-filter-toggle {{ request('filter_expanded') === '1' ? '' : 'collapsed' }}"
                    data-toggle="collapse"
                    data-target="#companyFilters"
                    aria-expanded="{{ request('filter_expanded') === '1' ? 'true' : 'false' }}"
                    aria-controls="companyFilters"
                    title="Свернуть / развернуть"
                >
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <form method="GET" action="{{ route('report-companies.index') }}" id="companyFilters" class="collapse {{ request('filter_expanded') === '1' ? 'show' : '' }}">
            <input type="hidden" name="filter_expanded" value="{{ request('filter_expanded') === '1' ? '1' : '0' }}">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <div class="form-group mb-md-0">
                            <label for="category">Категория</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">Все</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group mb-md-0">
                            <label for="search">Поиск</label>
                            <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Название, категория или счет">
                        </div>
                    </div>
                    <div class="col-md-3 text-md-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-1"></i> Применить
                        </button>
                        <a href="{{ route('report-companies.index', request()->filled('per_page') ? ['per_page' => request('per_page')] : []) }}" class="btn btn-default">
                            <i class="fas fa-times mr-1"></i> Сбросить
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Компании</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#createCompanyModal">
                    <i class="fas fa-plus mr-1"></i> Новая компания
                </button>
                <form method="GET" action="{{ route('report-companies.index') }}" class="d-inline-block">
                    @foreach (request()->only(['category', 'search', 'filter_expanded']) as $name => $value)
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
            <table class="table table-hover table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Компания</th>
                        <th>Короткое имя</th>
                        <th>Категория</th>
                        <th>Счета</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr>
                            <td>{{ $companies->firstItem() + $loop->index }}</td>
                            <td class="font-weight-bold">{{ $company->name }}</td>
                            <td>{{ $company->short_name ?: '-' }}</td>
                            <td>{{ $company->category ?: '-' }}</td>
                            <td style="min-width: 320px;">
                                @forelse ($company->accounts as $account)
                                    <div class="d-flex align-items-center justify-content-between border-bottom py-1">
                                        <div>
                                            <span>{{ $account->account_number }}</span>
                                            @if ($account->bank)
                                                <span class="badge badge-light ml-1">{{ $account->bank }}</span>
                                            @endif
                                        </div>
                                        <div class="text-nowrap ml-2">
                                            <button
                                                type="button"
                                                class="btn btn-xs btn-outline-primary js-edit-account"
                                                data-toggle="modal"
                                                data-target="#editAccountModal"
                                                data-action="{{ route('report-company-accounts.update', $account) }}"
                                                data-account-number="{{ $account->account_number }}"
                                                data-bank="{{ $account->bank }}"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ route('report-company-accounts.destroy', $account) }}" class="d-inline" onsubmit="return confirm('Удалить счет компании?')">
                                                @csrf
                                                @method('DELETE')
                                                @foreach (request()->only($filterKeys) as $name => $value)
                                                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                                @endforeach
                                                <button type="submit" class="btn btn-xs btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-muted">Счетов нет</span>
                                @endforelse
                            </td>
                            <td class="text-right text-nowrap">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-success js-create-account"
                                    data-toggle="modal"
                                    data-target="#createAccountModal"
                                    data-action="{{ route('report-companies.accounts.store', $company) }}"
                                    data-company="{{ $company->name }}"
                                >
                                    <i class="fas fa-credit-card"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary js-edit-company"
                                    data-toggle="modal"
                                    data-target="#editCompanyModal"
                                    data-action="{{ route('report-companies.update', $company) }}"
                                    data-name="{{ $company->name }}"
                                    data-short-name="{{ $company->short_name }}"
                                    data-category="{{ $company->category }}"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('report-companies.destroy', $company) }}" class="d-inline" onsubmit="return confirm('Удалить компанию? Вместе с ней удалятся ее счета, если компания не используется в отчетах.')">
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
                            <td colspan="6" class="text-center text-muted py-4">Компаний пока нет</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($companies->hasPages())
            <div class="card-footer">
                {{ $companies->links() }}
            </div>
        @endif
    </div>

    @include('report-companies.partials.company-modal', [
        'modalId' => 'createCompanyModal',
        'title' => 'Новая компания',
        'action' => route('report-companies.store'),
        'method' => 'POST',
        'filterKeys' => $filterKeys,
    ])

    @include('report-companies.partials.company-modal', [
        'modalId' => 'editCompanyModal',
        'title' => 'Редактировать компанию',
        'action' => '#',
        'method' => 'PUT',
        'filterKeys' => $filterKeys,
    ])

    @include('report-companies.partials.account-modal', [
        'modalId' => 'createAccountModal',
        'title' => 'Новый счет',
        'action' => '#',
        'method' => 'POST',
        'filterKeys' => $filterKeys,
    ])

    @include('report-companies.partials.account-modal', [
        'modalId' => 'editAccountModal',
        'title' => 'Редактировать счет',
        'action' => '#',
        'method' => 'PUT',
        'filterKeys' => $filterKeys,
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

        .company-success-toast {
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

        .company-success-toast__content {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1rem;
        }

        .company-success-toast__timer {
            height: 3px;
            background: #28a745;
            animation: companySuccessToastTimer 4s linear forwards;
        }

        .company-success-toast.is-hiding {
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity .2s ease, transform .2s ease;
        }

        @keyframes companySuccessToastTimer {
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
            $('#companyFilters').on('submit', function () {
                $(this).find('[name="filter_expanded"]').val($(this).hasClass('show') ? '1' : '0');
            });

            if (window.history.replaceState) {
                var url = new URL(window.location.href);

                if (url.searchParams.has('filter_expanded')) {
                    url.searchParams.delete('filter_expanded');
                    window.history.replaceState({}, document.title, url.toString());
                }
            }

            $('.js-edit-company').on('click', function () {
                var button = $(this);
                var modal = $('#editCompanyModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="name"]').val(button.attr('data-name'));
                modal.find('[name="short_name"]').val(button.attr('data-short-name'));
                modal.find('[name="category"]').val(button.attr('data-category'));
            });

            $('#createCompanyModal').on('show.bs.modal', function () {
                var modal = $(this);

                modal.find('[name="name"]').val('');
                modal.find('[name="short_name"]').val('');
                modal.find('[name="category"]').val('');
            });

            $('.js-create-account').on('click', function () {
                var button = $(this);
                var modal = $('#createAccountModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('.js-account-company').text(button.attr('data-company'));
                modal.find('[name="account_number"]').val('');
                modal.find('[name="bank"]').val('');
            });

            $('.js-edit-account').on('click', function () {
                var button = $(this);
                var modal = $('#editAccountModal');

                modal.find('form').attr('action', button.attr('data-action'));
                modal.find('[name="account_number"]').val(button.attr('data-account-number'));
                modal.find('[name="bank"]').val(button.attr('data-bank'));
            });

            var toast = $('.company-success-toast');

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
