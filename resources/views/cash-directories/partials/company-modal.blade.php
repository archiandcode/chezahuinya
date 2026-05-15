<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $modalId }}Label">{{ $title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="{{ $modalId }}_name">Компания</label>
                        <input type="text" id="{{ $modalId }}_name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="{{ $modalId }}_short_name">Короткое имя</label>
                        <input type="text" id="{{ $modalId }}_short_name" name="short_name" class="form-control">
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" id="{{ $modalId }}_is_active" name="is_active" value="1" class="custom-control-input" checked>
                        <label class="custom-control-label" for="{{ $modalId }}_is_active">Показывать в формах</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
