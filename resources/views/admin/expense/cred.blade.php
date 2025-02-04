@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.1/themes/base/jquery-ui.min.css" /> --}}
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap-autocomplete@2.3.7/dist/latest/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript">
    function datetimepicker(id){
        date = new Date('2020-01-01');
        $('#'+id).datepicker({
            useCurrent: true,
            format: "yyyy-mm-dd",
            startView: 0,
            minViewMode: 0,
            startDate : date,
            autoclose: true
        });
    }

    function name_autocomplete(id){
        $('#'+id).autoComplete({
            resolver: 'custom',
            minLength:1,
            events: {
                search: function(query, callback){
                    $.ajax({
                        url:'{{route("supanel.expense.ajax_name")}}',
                        type: 'POST',
                        dataType: 'json',
                        data: {'_token':'{!! csrf_token() !!}', 'query':query},
                        success: function(data){
                            callback(data);
                        },
                        error: function(error){
                            console.error(error);
                        }
                    });
                }
            }
        });
    }
    $(function(){
        datetimepicker('date');
        $('#name').on('input', function(){
            name_autocomplete('name');
        });
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
                    @php
                    $current_field='name';
                    $secondary_field='expense_type_id';
                    $row_data=[];
                    $expense_type= \App\Models\Expense_Type::where([['delstatus','<','1'],['status','>','0']])->get();
                    @endphp

                    {!! Form::bsInput('search', $current_field, __('Name'), $form_data->$current_field ?? '', ['required']); !!}

                    @php $current_field = 'date';
                    $now = \Carbon\Carbon::now()->format('Y-m-d');
                    @endphp
                    {!! Form::bsInput('text', $current_field, __('Date'), $form_data->$current_field ?? $now, [ 'autocomplete'=>'off','required' ],  []); !!}
                    
                    @php 
                    $current_field = 'amount';
                    @endphp
                    {!! Form::bsInput('number', $current_field, __('Amount'), $form_data->$current_field ?? '', [ 'autocomplete'=>'off','required' ],  []); !!}

                    @php
                    $current_field = 'remarks';
                    @endphp
                    {!! Form::bsTextArea($current_field, __('Remarks'), $form_data->$current_field ?? '', ['']); !!}

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