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
                        <label for="{{ $modalId }}_name">Название кассы</label>
                        <input type="text" id="{{ $modalId }}_name" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_currency">Валюта</label>
                                <input type="text" id="{{ $modalId }}_currency" name="currency" value="KZT" class="form-control directory-currency" maxlength="3" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="{{ $modalId }}_opening_balance">Начальный остаток</label>
                                <input type="number" step="0.01" id="{{ $modalId }}_opening_balance" name="opening_balance" value="0.00" class="form-control cash-register-amount-input" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="{{ $modalId }}_opening_balance_date">Дата начального остатка</label>
                        <input type="date" id="{{ $modalId }}_opening_balance_date" name="opening_balance_date" value="2026-01-05" class="form-control">
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" id="{{ $modalId }}_is_active" name="is_active" value="1" class="custom-control-input" checked>
                        <label class="custom-control-label" for="{{ $modalId }}_is_active">Показывать в меню и формах</label>
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
