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
                                <label for="{{ $modalId }}_product_group">Группа товара</label>
                                <input type="text" id="{{ $modalId }}_product_group" name="product_group" value="{{ old('product_group') }}" class="form-control" maxlength="255" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-md-0">
                                <label for="{{ $modalId }}_quantity">Кол-во</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_quantity" name="quantity" value="{{ old('quantity') }}" class="form-control trading-stock-balance-number-input">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="{{ $modalId }}_cost_amount">Сумма себестоимости</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_cost_amount" name="cost_amount" value="{{ old('cost_amount') }}" class="form-control trading-stock-balance-number-input">
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
