<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        .js-filter-header {
            cursor: pointer;
        }

        .filter-card {
            border: 1px solid #dce3ea;
            box-shadow: 0 .125rem .375rem rgba(0, 0, 0, .04);
        }

        .filter-card .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: #fff;
            border-bottom-color: #e9eef3;
        }

        .filter-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .filter-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .filter-count {
            display: inline-flex;
            align-items: center;
            min-height: 1.75rem;
            padding: .25rem .625rem;
            color: #0f5132;
            background: #d1e7dd;
            border-radius: 999px;
            font-size: .8125rem;
            font-weight: 600;
        }

        .filter-summary {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            padding: .75rem 1rem 0;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            min-height: 1.875rem;
            padding: .25rem .625rem;
            color: #495057;
            background: #f4f6f9;
            border: 1px solid #dce3ea;
            border-radius: 999px;
            font-size: .8125rem;
            line-height: 1.2;
        }

        .filter-chip strong {
            margin-right: .25rem;
            color: #212529;
            font-weight: 600;
        }

        .filter-panel .form-group {
            margin-bottom: .875rem;
        }

        .filter-section {
            padding: .875rem 1rem .25rem;
            background: #fbfcfd;
            border: 1px solid #edf1f5;
            border-radius: .25rem;
        }

        .filter-section + .filter-section {
            margin-top: .75rem;
        }

        .filter-section-title {
            display: flex;
            align-items: center;
            gap: .375rem;
            margin-bottom: .625rem;
            color: #343a40;
            font-size: .875rem;
            font-weight: 700;
        }

        .filter-panel label {
            margin-bottom: .25rem;
            color: #5f6b76;
            font-size: .8125rem;
            font-weight: 600;
        }

        .filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: .5rem;
            padding-top: 1.5rem;
        }

        .js-filter-toggle .fa-chevron-down {
            transition: transform .2s ease;
        }

        .js-filter-toggle[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }

        .content .small-box {
            min-height: 120px;
        }

        .content .info-box {
            min-height: 90px;
        }

        @media (max-width: 767.98px) {
            .filter-card .card-header {
                align-items: flex-start;
            }

            .filter-meta {
                justify-content: flex-end;
            }

            .filter-actions {
                justify-content: stretch;
                padding-top: .25rem;
            }

            .filter-actions .btn {
                flex: 1 1 0;
            }
        }

    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link">
                        <i class="fas fa-sign-out-alt mr-1"></i> Выйти
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <div class="sidebar">
            <div class="user-panel pt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x text-white-50"></i>
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ auth()->user()->name }}</a>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Главная</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('cash-transactions.index') }}" class="nav-link {{ request()->routeIs('cash-transactions.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cash-register"></i>
                            <p>Кассы</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('construction-payments.index') }}" class="nav-link {{ request()->routeIs('construction-payments.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-hard-hat"></i>
                            <p>Стройка</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('daily-reports.index') }}" class="nav-link {{ request()->routeIs('daily-reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>Ежедневные отчеты</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('cash-balances.index') }}" class="nav-link {{ request()->routeIs('cash-balances.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-wallet"></i>
                            <p>Остатки ДС</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('trading-stock-balances.index') }}" class="nav-link {{ request()->routeIs('trading-stock-balances.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>Остатки по торговым</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('rap-reports.index') }}" class="nav-link {{ request()->routeIs('rap-reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>Отчет RAP</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('debt-credit-reports.index') }}" class="nav-link {{ request()->routeIs('debt-credit-reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-balance-scale"></i>
                            <p>Дт и Кт</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('buffet-reports.index') }}" class="nav-link {{ request()->routeIs('buffet-reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-utensils"></i>
                            <p>Буфет</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('report-companies.index') }}" class="nav-link {{ request()->routeIs('report-companies.*') || request()->routeIs('report-company-accounts.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>Компании</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">@yield('page-title', 'Панель')</h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    {{-- <footer class="main-footer">
        <strong>{{ config('app.name') }}</strong>
    </footer> --}}
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    $(function () {
        $('.js-filter-header').on('click', function (event) {
            if ($(event.target).closest('button, a, input, select, textarea, label').length) {
                return;
            }

            $($(this).attr('data-toggle-target')).collapse('toggle');
        });
    });
</script>
@stack('scripts')
</body>
</html>
