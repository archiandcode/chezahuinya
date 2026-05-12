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
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">&nbsp;</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
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
