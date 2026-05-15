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
                                <label for="{{ $modalId }}_report_year">Год</label>
                                <input type="number" min="2000" max="2100" id="{{ $modalId }}_report_year" name="report_year" value="{{ old('report_year', $defaultYear ?? '') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="{{ $modalId }}_period_label">Период</label>
                                <input type="text" id="{{ $modalId }}_period_label" name="period_label" value="{{ old('period_label') }}" class="form-control" maxlength="255" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_period_date">Дата периода</label>
                                <input type="date" id="{{ $modalId }}_period_date" name="period_date" value="{{ old('period_date') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="{{ $modalId }}_metric">Показатель</label>
                                <select id="{{ $modalId }}_metric" name="metric" class="form-control" required>
                                    @foreach ($metrics as $metricValue => $metricLabel)
                                        <option value="{{ $metricValue }}">{{ $metricLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="{{ $modalId }}_amount">Сумма</label>
                                <input type="number" step="0.01" id="{{ $modalId }}_amount" name="amount" value="{{ old('amount') }}" class="form-control buffet-amount-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="{{ $modalId }}_sort_order">№</label>
                                <input type="number" min="0" max="65535" id="{{ $modalId }}_sort_order" name="sort_order" value="{{ old('sort_order') }}" class="form-control">
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
