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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_construction_section_id">Раздел</label>
                                <select id="{{ $modalId }}_construction_section_id" name="construction_section_id" class="form-control" required>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}" @selected((int) old('construction_section_id', request('construction_section_id') ?: $sections->first()?->id) === $section->id)>{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_payment_date">Дата</label>
                                <input type="date" id="{{ $modalId }}_payment_date" name="payment_date" value="{{ old('payment_date', $defaultPaymentDate ?? '') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_supplier">Поставщик</label>
                                <input type="text" id="{{ $modalId }}_supplier" name="supplier" value="{{ old('supplier') }}" class="form-control" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_amount">Сумма</label>
                                <input type="number" step="0.01" min="0.01" id="{{ $modalId }}_amount" name="amount" value="{{ old('amount') }}" class="form-control construction-payment-amount-input" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="{{ $modalId }}_contract">Договор</label>
                                <input type="text" id="{{ $modalId }}_contract" name="contract" value="{{ old('contract') }}" class="form-control" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="{{ $modalId }}_payment_source">С какой кассы и/или р/с</label>
                                <input type="text" id="{{ $modalId }}_payment_source" name="payment_source" value="{{ old('payment_source') }}" class="form-control" maxlength="255">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label for="{{ $modalId }}_purpose">Назначение</label>
                        <textarea id="{{ $modalId }}_purpose" name="purpose" class="form-control" rows="3">{{ old('purpose') }}</textarea>
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
