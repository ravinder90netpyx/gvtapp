@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
@endsection

@section('content')
{!! Form::open(['url'=>$action, 'method'=>$method, 'class'=>'needs-validation', 'novalidate']) !!}
    <div class="row">
        <div class="offset-xl-2 col-xl-8 offset-lg-1 col-lg-10 col-md-12">
            <div class="main-card mb-3 card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <div>{{ $title_shown }}</div>
                    <div>
                        <button type="submit" class="mt-1 btn btn-dark btn-sm">{!! __('admin.text_button_submit') !!}</button>
                        <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger btn-sm">{!! __('admin.text_button_back') !!}</a>
                    </div>
                </div>

                <div class="card-body"> 
                   <div class="row">    
                        
                        <div class="col-md-6">
                            @php $current_field = 'name'; @endphp
                            {!! Form::bsText($current_field, __('admin.text_name'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'name'; @endphp
                            {!! Form::bsPassword($current_field, 'Password', $form_data->$current_field ?? '', ['required'], ['vertical'=>true, 'reveal'=>true]); !!}
                        </div>
                        
                        <div class="col-md-6">
                            @php 
                            $current_field = 'name';
                            $row_data = [];
                            $data_select = DB::table('roles')->get();
                            foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
                            @endphp
                            {!! Form::bsSelect($current_field, __('admin.text_name'), $row_data, $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'name'; @endphp
                            {!! Form::bsTextArea($current_field, __('admin.text_name'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>
                    </div>  
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection