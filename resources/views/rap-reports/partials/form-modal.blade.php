<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
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
                                <label for="{{ $modalId }}_report_date">Дата отчета</label>
                                <input type="date" id="{{ $modalId }}_report_date" name="report_date" value="{{ old('report_date', $defaultReportDate ?? '') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_section">Раздел</label>
                                <select id="{{ $modalId }}_section" name="section" class="form-control" required>
                                    @foreach ($sections as $sectionValue => $sectionLabel)
                                        <option value="{{ $sectionValue }}">{{ $sectionLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="{{ $modalId }}_counterparty">Контрагент</label>
                                <input type="text" id="{{ $modalId }}_counterparty" name="counterparty" value="{{ old('counterparty') }}" class="form-control" maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_quantity">Количество</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_quantity" name="quantity" value="{{ old('quantity') }}" class="form-control rap-report-number-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_unit_price">Цена за 1 пачку</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_unit_price" name="unit_price" value="{{ old('unit_price') }}" class="form-control rap-report-number-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_sale_amount">Сумма реализации</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_sale_amount" name="sale_amount" value="{{ old('sale_amount') }}" class="form-control rap-report-number-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_sale_month">Месяц реализации</label>
                                <input type="text" id="{{ $modalId }}_sale_month" name="sale_month" value="{{ old('sale_month') }}" class="form-control" maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_invoice_date">Дата выписки СФ</label>
                                <input type="date" id="{{ $modalId }}_invoice_date" name="invoice_date" value="{{ old('invoice_date') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_actual_payment_date">Дата факт. оплаты</label>
                                <input type="date" id="{{ $modalId }}_actual_payment_date" name="actual_payment_date" value="{{ old('actual_payment_date') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_paid_amount">Сумма оплаты</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_paid_amount" name="paid_amount" value="{{ old('paid_amount') }}" class="form-control rap-report-number-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_planned_payment_date">Дата план. оплаты</label>
                                <input type="date" id="{{ $modalId }}_planned_payment_date" name="planned_payment_date" value="{{ old('planned_payment_date') }}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_unpaid_amount">Нет оплаты</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_unpaid_amount" name="unpaid_amount" value="{{ old('unpaid_amount') }}" class="form-control rap-report-number-input">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="custom-control custom-checkbox mt-3">
                                <input type="checkbox" id="{{ $modalId }}_is_paid" name="is_paid" value="1" class="custom-control-input">
                                <label class="custom-control-label" for="{{ $modalId }}_is_paid">Оплачен</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="{{ $modalId }}_comment">Коммент</label>
                                <textarea id="{{ $modalId }}_comment" name="comment" class="form-control" rows="2">{{ old('comment') }}</textarea>
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
