@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">
    function datetimepicker(id){
        date = new Date('2020-01-01');
        $('#'+id).datepicker({
            format: "yyyy-mm-dd",
            startView: 0,
            minViewMode: 0,
            startDate : date,
            autoclose: true
        });
    }
    $(function(){
        datetimepicker('date');
    });
</script>
@endsection

@section('content')
<div class="app-page-title row">
    <div class="col page-title-wrapper">
        <div class="page-title-heading">
            <h1>{{ $title_shown }}</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="main-card mb-3 card">
            <div class="card-body">
                {!! Form::open(['url'=>$action, 'method'=>$method, 'class'=>'needs-validation', 'novalidate', 'files'=>true]) !!}
                    {{-- @php $current_field = 'name'; 
                        $secondary_field = 'charge_type_id';
                        $row_data = [];
                        $expense_type = \App\Models\Expense_Type::where([['delstatus','<','1'],['status','>','0']])->get();
                        foreach($expense_type as $ds) $row_data[$ds->id] = $ds->name;
                        $multi_data = array(
                        ['type'=>'text','name'=>$current_field,'value'=>$form_data->$current_field ?? '','field_attributes'=>[]],
                        ['type'=>'select','name'=>$secondary_field, 'value'=>$form_data->$secondary_field ?? '','field_attributes'=>[],'remove_blank_field'=>false, 'data'=>$row_data]);
                    @endphp
                    {!! Form::bsMultiInput($multi_data, 'Name') !!} --}}

                    @php
                    $current_field='name';
                    $secondary_field='expense_type_id';
                    $row_data=[];
                    $expense_type= \App\Models\Expense_Type::where([['delstatus','<','1'],['status','>','0']])->get();
                    @endphp
                    <div class="position-relative form-group-multi-input form-group row">
                        <label for="name" class="form-control-label col-md-3 col-form-label">Name</label>
                        <div class="col-md-9">
                            <div class="input-group ">
                                <input class="form-control {{$errors->has($current_field) ? 'is-invalid':''}}" name="{{$current_field}}" type="text" value="{{$form_data->$current_field ?? ''}}" id="{{$current_field}}">
                               
                                <select class="custom-select {{$errors->has($secondary_field) ? 'is-invalid':''}}" name="{{ $secondary_field }}">
                                    <option selected="selected" value="">- -Select Option- -</option>
                                    @foreach($expense_type as $ds)
                                    <option {{ !empty($form_data->$secondary_field) && ($form_data->$secondary_field == $ds->id)?'selected ':'' }} value="{{ $ds->id }}">{{ $ds->name }}</option>
                                    @endforeach
                                </select>
                               
                                <div class="invalid-feedback">@if($errors->has($secondary_field)) {{ $errors->first($secondary_field) }} @endif  </div>
                           </div>
                        </div>
                    </div>


                    {{-- @php $current_field = 'expense_type_id';
                    $row_data = [];
                    $data_select = DB::table('expense_type')->get();
                    foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
                    @endphp
                    {!! Form::bsSelect($current_field, __('Expense Type'), $row_data, $form_data->$current_field ?? '', ['']); !!} --}}

                    @php $current_field = 'date'; @endphp
                    {!! Form::bsInput('text', $current_field, __('Date'), $form_data->$current_field ?? '', [ 'autocomplete'=>'off','required' ],  []); !!}
                    
                    @php 
                    $current_field = 'amount';
                    @endphp
                    {!! Form::bsInput('number', $current_field, __('Amount'), $form_data->$current_field ?? '', [ 'autocomplete'=>'off','required' ],  []); !!}

                    @php $current_field = 'image'; @endphp
                    {!! Form::bsInput('file',$current_field, __('Image'), $form_data->$current_field ?? '',[]); !!}

                    @if($mode=="edit" && !empty($form_data->$current_field))
                    <div class="position-relative mb-2">
                    <img src="{{ asset('upload/expense/' .$form_data->image) }}" class="img-fluid position-relative" alt="image">
                    </div>
                    @endif

                    <button type="submit" class="mt-1 btn btn-primary">{!! __('admin.text_button_submit') !!}</button>
                    <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger">{!! __('admin.text_button_back') !!}</a>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection