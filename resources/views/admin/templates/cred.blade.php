@php
    $auth_user = Illuminate\Support\Facades\Auth::user();
    $roles = $auth_user->roles()->pluck('id')->toArray();
        // dd($dataArray);
    Form::macro('variantData1', function($dataArray){
        // dd($dataArray);
        $rowIndex = $dataArray['id'] ?? 0 ;
        $dataArray['name'] = $dataArray['name'] ?? '';
        $dataArray['position'] = $dataArray['position'] ?? '';

        return "<div class='row py-1 params[$rowIndex]'>
        <div class='col-sm'>".Form::text('params['.$rowIndex.'][name]', $dataArray['name'] , ['class' => 'form-control label', 'placeholder' => "name"])."</div>
        <div class='col-sm'>".Form::number('params['.$rowIndex.'][position]', $dataArray['position'] , ['class' => 'form-control label', 'placeholder' => "Position"])."</div>
            <div class='col-sm-auto pt-2'>
                <i class='text-danger fa-lg fas fa-trash-alt del_row'></i>
            </div>
        </div>";
    });

    $multi_data='';
     if($mode=='insert'){
     $multi_data=Form::variantData1([], 0);
    }
     else{
        if(isset($dataArray) && is_array($dataArray) && count($dataArray) > 0){
        foreach($dataArray as $data){
            $multi_data = $multi_data.Form::variantData1($data);
        }
    }

    }

@endphp

@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
<script type="text/javascript">
    var rowCount = $('.temp1 .row').length;

function add_row() {
    var newRow = '<div class="row py-1 params['+ rowCount +']">' +
        '<div class="col-sm">' +
        '<input type="text" name="params[' + rowCount + '][name]" placeholder = "name" class="form-control label">' +
        '</div>' +
        '<div class="col-sm">' +
        '<input type="number" name="params[' + rowCount + '][position]" placeholder = "Position" class="form-control label">' +
        '</div>' +
        '<div class="col-sm-auto pt-2">' +
        '<i class="text-danger fa-lg fas fa-trash-alt del_row"></i>' +
        '</div>' +
        '</div>';
    $('.temp1').append(newRow);
    rowCount++; // Increment the row count for the next row
}

// Bind the click event to add a new row
$('.new_row').on('click', function() {
    add_row();
});

// Bind the click event to delete a row
$('.temp1').on('click', '.del_row', function() {
    if ($('.temp1 .row').length > 1) {
        $(this).closest('.row').remove();
    }
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
                {!! Form::open(['url'=>$action, 'method'=>$method, 'class'=>'needs-validation', 'novalidate']) !!}
                    @php 
                    $current_field = 'name';
                    $row_data = [];
                    $data_select = $model->template_name();
                    foreach($data_select as $k => $v) $row_data[$k] = $v;
                    @endphp
                    {!! Form::bsSelect($current_field, __('name'), $row_data, $form_data->$current_field ?? '', ['required']); !!}
                    
                    @if(in_array(1, $roles) && $mode!='edit')
                        @php 
                        $current_field = 'organization_id';
                        $row_data=[];
                        $data_select=\App\Models\Organization::select('id','name')->get();
                        foreach($data_select as $ds) $row_data[$ds->id]=$ds->name;
                        @endphp
                        {!! Form::bsSelect($current_field, __('Organization'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'required']); !!}
                    @endif

                    @php $current_field = 'template_id'; @endphp
                    {!! Form::bsText($current_field, __('Template Id'), $form_data->$current_field ?? '', ['required']); !!}
                    
                    <div class="card">
                        <div class="card-header" style=" color: white; background-color: #f5365c;"> Params </div>
                        <div class="card-body" style="background-color: #fafafa;">                           
                            <div class="temp1">
                                {!! $multi_data !!}
                            </div> 
                        </div>
                        <div class="card-footer" style="background-color: #d0cfcf;">
                           <div class="col-md-12" style="text-align: end;"><i class='fa-lg fas fa-plus-circle new_row' title ="Add row" style="padding: 5px; background-color: black; color: white; cursor: pointer;" aria-hidden='true'></i>
                           </div>
                        </div> 
                    </div>
                    <button type="submit" class="mt-1 btn btn-primary">{!! __('admin.text_button_submit') !!}</button>
                    <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger">{!! __('admin.text_button_back') !!}</a>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection