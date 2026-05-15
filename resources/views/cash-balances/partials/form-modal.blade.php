<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif
                @foreach (request()->only($filterKeys) as $name => $value)
                    <input type="hidden" name="filter_{{ $name }}" value="{{ $value }}">
                @endforeach

                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $modalId }}Label">{{ $title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_balance_date">Дата остатка</label>
                                <input type="date" id="{{ $modalId }}_balance_date" name="balance_date" value="{{ old('balance_date', $defaultBalanceDate ?? '') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_sort_order">№ пп</label>
                                <input type="number" min="0" max="65535" id="{{ $modalId }}_sort_order" name="sort_order" value="{{ old('sort_order') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="{{ $modalId }}_company">Компания</label>
                                <input type="text" id="{{ $modalId }}_company" name="company" value="{{ old('company') }}" class="form-control" maxlength="255" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-md-0">
                                <label for="{{ $modalId }}_balance_amount">Остаток</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_balance_amount" name="balance_amount" value="{{ old('balance_amount') }}" class="form-control cash-balance-amount-input">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-md-0">
                                <label for="{{ $modalId }}_custody_assets_amount">Активы (кастоди)</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_custody_assets_amount" name="custody_assets_amount" value="{{ old('custody_assets_amount') }}" class="form-control cash-balance-amount-input">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="{{ $modalId }}_own_assets_amount">Собственные активы</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_own_assets_amount" name="own_assets_amount" value="{{ old('own_assets_amount') }}" class="form-control cash-balance-amount-input">
                            </div>
                        </div>
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
