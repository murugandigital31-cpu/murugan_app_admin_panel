@foreach($choice_options as $key=>$choice)
    <div class="row g-1">
        <div class="col-md-3 col-sm-4">
            <input type="hidden" name="choice_no[]" value="{{$choice_no[$key] ??null}}">
            <input type="text" class="form-control" name="choice[]" value="{{$choice['title']}}"
                   placeholder="{{ translate('Choice Title') }}" readonly>
        </div>
        <div class="col-lg-9 col-sm-8">
            <input type="text" class="form-control choice-option-input" name="choice_options_{{$choice_no[$key] ?? null}}[]" data-role="tagsinput"
                   value="{{ implode(',', $choice['options']) }}" onchange="combination_update()">
        </div>
    </div>
@endforeach
