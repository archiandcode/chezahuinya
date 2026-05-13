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
                                <label for="{{ $modalId }}_report_date">Дата</label>
                                <input type="date" id="{{ $modalId }}_report_date" name="report_date" value="{{ old('report_date', $defaultReportDate ?? '') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_report_company_id">Компания</label>
                                <select id="{{ $modalId }}_report_company_id" name="report_company_id" class="form-control js-entry-company" required>
                                    <option value="">Выберите</option>
                                    @foreach ($companies->groupBy('category') as $category => $groupedCompanies)
                                        <optgroup label="{{ $category ?: 'Без категории' }}">
                                            @foreach ($groupedCompanies as $company)
                                                <option value="{{ $company->id }}" @selected((int) old('report_company_id') === $company->id)>{{ $company->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_report_company_account_id">Счет</label>
                                <select id="{{ $modalId }}_report_company_account_id" name="report_company_account_id" class="form-control js-entry-account">
                                    <option value="">Без счета</option>
                                    @foreach ($accounts as $account)
                                        <option
                                            value="{{ $account->id }}"
                                            data-company-id="{{ $account->report_company_id }}"
                                            @selected((int) old('report_company_account_id') === $account->id)
                                        >
                                            {{ $account->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_daily_report_type_id">Тип</label>
                                <select id="{{ $modalId }}_daily_report_type_id" name="daily_report_type_id" class="form-control" required>
                                    <option value="">Выберите</option>
                                    @foreach ($types->groupBy('direction') as $direction => $groupedTypes)
                                        <optgroup label="{{ $directionLabels[$direction] ?? $direction }}">
                                            @foreach ($groupedTypes as $type)
                                                <option value="{{ $type->id }}" @selected((int) old('daily_report_type_id') === $type->id)>{{ $type->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_amount">Сумма</label>
                                <input type="number" step="0.01" min="0.01" id="{{ $modalId }}_amount" name="amount" value="{{ old('amount') }}" class="form-control daily-report-amount-input" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_counterparty">Контрагент</label>
                                <input type="text" id="{{ $modalId }}_counterparty" name="counterparty" value="{{ old('counterparty') }}" class="form-control" maxlength="255">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label for="{{ $modalId }}_comment">Комментарий</label>
                        <textarea id="{{ $modalId }}_comment" name="comment" class="form-control" rows="3">{{ old('comment') }}</textarea>
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
