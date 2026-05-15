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
                                <label for="{{ $modalId }}_section">Тип</label>
                                <select id="{{ $modalId }}_section" name="section" class="form-control" required>
                                    @foreach ($sections as $sectionValue => $sectionLabel)
                                        <option value="{{ $sectionValue }}">{{ $sectionLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_sort_order">№ строки</label>
                                <input type="number" min="0" max="65535" id="{{ $modalId }}_sort_order" name="sort_order" value="{{ old('sort_order') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="{{ $modalId }}_group_name">Группа</label>
                                <input type="text" id="{{ $modalId }}_group_name" name="group_name" value="{{ old('group_name') }}" class="form-control" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="{{ $modalId }}_counterparty">Контрагент</label>
                                <input type="text" id="{{ $modalId }}_counterparty" name="counterparty" value="{{ old('counterparty') }}" class="form-control" maxlength="255" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_amount">Сумма задолженности</label>
                                <input type="number" step="0.01" min="0" id="{{ $modalId }}_amount" name="amount" value="{{ old('amount') }}" class="form-control debt-credit-amount-input">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="{{ $modalId }}_company">Компания</label>
                                <input type="text" id="{{ $modalId }}_company" name="company" value="{{ old('company') }}" class="form-control" maxlength="255">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label for="{{ $modalId }}_note">Примечание</label>
                        <textarea id="{{ $modalId }}_note" name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
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
