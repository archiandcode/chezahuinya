@extends('layouts.adminlte')

@section('title', 'Справочники стройки | ' . config('app.name'))
@section('page-title', 'Справочники стройки')

@section('content')
    @if (session('toast_success'))
        <div class="construction-directory-toast" role="status" aria-live="polite">
            <div class="construction-directory-toast__content">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('toast_success') }}</span>
            </div>
            <div class="construction-directory-toast__timer"></div>
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
                <span class="info-box-icon bg-info"><i class="fas fa-folder"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Разделы стройки</span>
                    <span class="info-box-number">{{ $sections->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card directory-shell">
        <div class="card-body">
            <ul class="nav nav-pills directory-tabs" id="constructionDirectoryTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="sections-tab" data-toggle="tab" href="#sections" role="tab">
                        <i class="fas fa-folder mr-1"></i> Разделы
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="sections" role="tabpanel">
                    <div class="directory-toolbar">
                        <div>
                            <h3>Разделы стройки</h3>
                            <p>Список разделов, которые отображаются в левом меню стройки.</p>
                        </div>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createConstructionSectionModal">
                            <i class="fas fa-plus mr-1"></i> Новый раздел
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Раздел</th>
                                    <th>Порядок</th>
                                    <th>Статус</th>
                                    <th class="text-right">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sections as $section)
                                    <tr>
                                        <td class="font-weight-bold">{{ $section->name }}</td>
                                        <td>{{ $section->sort_order }}</td>
                                        <td>
                                            <span class="badge {{ $section->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $section->is_active ? 'Активен' : 'Скрыт' }}
                                            </span>
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary js-edit-section"
                                                data-toggle="modal"
                                                data-target="#editConstructionSectionModal"
                                                data-directory="{{ \Illuminate\Support\Js::encode([
                                                    'action' => route('construction-sections.update', $section),
                                                    'name' => $section->name,
                                                    'sort_order' => $section->sort_order,
                                                    'is_active' => (bool) $section->is_active,
                                                ]) }}"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ route('construction-sections.destroy', $section) }}" class="d-inline" onsubmit="return confirm('Удалить раздел стройки?')">
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
                                        <td colspan="4" class="text-center text-muted py-4">Разделов пока нет</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('construction-directories.partials.section-modal', [
        'modalId' => 'createConstructionSectionModal',
        'title' => 'Новый раздел',
        'action' => route('construction-sections.store'),
        'method' => 'POST',
    ])

    @include('construction-directories.partials.section-modal', [
        'modalId' => 'editConstructionSectionModal',
        'title' => 'Редактировать раздел',
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

        .construction-directory-toast {
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

        .construction-directory-toast__content {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1rem;
        }

        .construction-directory-toast__timer {
            height: 3px;
            background: #28a745;
            animation: constructionDirectoryToastTimer 4s linear forwards;
        }

        .construction-directory-toast.is-hiding {
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

        @keyframes constructionDirectoryToastTimer {
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
            $('.js-edit-section').on('click', function () {
                var button = $(this);
                var data = button.data('directory') || {};
                var modal = $('#editConstructionSectionModal');

                modal.find('form').attr('action', data.action || '#');
                modal.find('[name="name"]').val(data.name || '');
                modal.find('[name="sort_order"]').val(data.sort_order || 0);
                modal.find('[name="is_active"]').prop('checked', !! data.is_active);
            });

            var toast = $('.construction-directory-toast');

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
