@php 
$val = $form_data->attendes_id ?? '';
@endphp
<div class="position-relative form-group">
    <label for="series_id" class="form-control-label">Series<span class="req"></span></label>
<select id="series_id" class="custom-select" required name="series_id">
            <option value="" selected="selected"> --Select Series-- </option>
            @foreach($row_data as $key=>$val)
            <option value="{{ $key }}" ((!empty($val)) ? 'selected':'')>{{ $val }}</option>
            @endforeach
            </select></div>