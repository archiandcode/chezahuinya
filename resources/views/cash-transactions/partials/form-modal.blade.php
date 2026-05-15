<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif
                @foreach (request()->only(['date_from', 'date_to', 'company', 'cash_flow', 'direction', 'per_page', 'page']) as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                @if (request()->filled('has_supporting_document'))
                    <input type="hidden" name="filter_has_supporting_document" value="{{ request('has_supporting_document') }}">
                @endif

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
                                <label for="{{ $modalId }}_transaction_date">Дата</label>
                                <input type="date" id="{{ $modalId }}_transaction_date" name="transaction_date" value="{{ old('transaction_date', $defaultTransactionDate ?? '') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_income_amount">Сумма поступления KZT</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_income_amount" name="income_amount" value="{{ old('income_amount') }}" class="form-control cash-transaction-amount-input">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_expense_amount">Сумма расхода KZT</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_expense_amount" name="expense_amount" value="{{ old('expense_amount') }}" class="form-control cash-transaction-amount-input">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_company">Компания</label>
                                <input type="text" id="{{ $modalId }}_company" name="company" value="{{ old('company') }}" class="form-control" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_cash_flow">ДДС</label>
                                <input type="text" id="{{ $modalId }}_cash_flow" name="cash_flow" value="{{ old('cash_flow') }}" class="form-control" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_has_supporting_document">Наличие СЗ</label>
                                <select id="{{ $modalId }}_has_supporting_document" name="has_supporting_document" class="form-control">
                                    <option value="">Не указано</option>
                                    <option value="1" @selected(old('has_supporting_document') === '1')>Есть</option>
                                    <option value="0" @selected(old('has_supporting_document') === '0')>Нет</option>
                                </select>
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
