@php
    $modalHasErrors = $errors->any() && old('_modal_id') === $modalId;
    $fieldValue = fn (string $field, mixed $default = '') => $modalHasErrors ? old($field, $default) : $default;
    $defaultCashRegisterId = request('cash_register_id') ?: $cashRegisters->first()?->id;
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true" data-has-errors="{{ $modalHasErrors ? '1' : '0' }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $modalHasErrors ? old('_form_action', $action) : $action }}">
                @csrf
                <input type="hidden" name="_modal_id" value="{{ $modalId }}">
                <input type="hidden" name="_form_action" value="{{ $modalHasErrors ? old('_form_action', $action) : $action }}">
                @if ($method !== 'POST')
                    @method($method)
                @endif
                @foreach (request()->only(['cash_register_id', 'date_from', 'date_to', 'cash_company_id', 'cash_flow_category_id', 'direction', 'per_page', 'page']) as $name => $value)
                    <input type="hidden" name="filter_{{ $name }}" value="{{ $value }}">
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
                    @if ($modalHasErrors)
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
                            <div class="form-group">
                                <label for="{{ $modalId }}_cash_register_id">Касса</label>
                                <select id="{{ $modalId }}_cash_register_id" name="cash_register_id" class="form-control @error('cash_register_id') {{ $modalHasErrors ? 'is-invalid' : '' }} @enderror" required>
                                    @foreach ($cashRegisters as $cashRegister)
                                        <option value="{{ $cashRegister->id }}" @selected((int) $fieldValue('cash_register_id', $defaultCashRegisterId) === $cashRegister->id)>{{ $cashRegister->name }}</option>
                                    @endforeach
                                </select>
                                @if ($modalHasErrors)
                                    @error('cash_register_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_transaction_date">Дата</label>
                                <input type="date" id="{{ $modalId }}_transaction_date" name="transaction_date" value="{{ $fieldValue('transaction_date', $defaultTransactionDate ?? '') }}" class="form-control @error('transaction_date') {{ $modalHasErrors ? 'is-invalid' : '' }} @enderror" required>
                                @if ($modalHasErrors)
                                    @error('transaction_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_income_amount">Сумма поступления KZT</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_income_amount" name="income_amount" value="{{ $fieldValue('income_amount') }}" class="form-control cash-transaction-amount-input @error('income_amount') {{ $modalHasErrors ? 'is-invalid' : '' }} @enderror">
                                @if ($modalHasErrors)
                                    @error('income_amount')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_expense_amount">Сумма расхода KZT</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_expense_amount" name="expense_amount" value="{{ $fieldValue('expense_amount') }}" class="form-control cash-transaction-amount-input @error('expense_amount') {{ $modalHasErrors ? 'is-invalid' : '' }} @enderror">
                                @if ($modalHasErrors)
                                    @error('expense_amount')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_cash_company_id">Компания</label>
                                <select id="{{ $modalId }}_cash_company_id" name="cash_company_id" class="form-control @error('cash_company_id') {{ $modalHasErrors ? 'is-invalid' : '' }} @enderror">
                                    <option value="">Не указано</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" @selected((int) $fieldValue('cash_company_id') === $company->id)>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                                @if ($modalHasErrors)
                                    @error('cash_company_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_cash_flow_category_id">ДДС</label>
                                <select id="{{ $modalId }}_cash_flow_category_id" name="cash_flow_category_id" class="form-control @error('cash_flow_category_id') {{ $modalHasErrors ? 'is-invalid' : '' }} @enderror">
                                    <option value="">Не указано</option>
                                    @foreach ($cashFlows as $cashFlow)
                                        <option value="{{ $cashFlow->id }}" @selected((int) $fieldValue('cash_flow_category_id') === $cashFlow->id)>{{ $cashFlow->name }}</option>
                                    @endforeach
                                </select>
                                @if ($modalHasErrors)
                                    @error('cash_flow_category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_has_supporting_document">Наличие СЗ</label>
                                <select id="{{ $modalId }}_has_supporting_document" name="has_supporting_document" class="form-control @error('has_supporting_document') {{ $modalHasErrors ? 'is-invalid' : '' }} @enderror">
                                    <option value="">Не указано</option>
                                    <option value="1" @selected($fieldValue('has_supporting_document') === '1')>Есть</option>
                                    <option value="0" @selected($fieldValue('has_supporting_document') === '0')>Нет</option>
                                </select>
                                @if ($modalHasErrors)
                                    @error('has_supporting_document')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                @endif
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
