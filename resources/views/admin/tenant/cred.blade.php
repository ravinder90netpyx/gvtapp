@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
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
                    @php $current_field = 'name'; @endphp
                    {!! Form::bsText($current_field, __('admin.text_name'), $form_data->$current_field ?? '', ['required']); !!}

                    @php $current_field = 'age'; @endphp
                    {!! Form::bsInput('number', $current_field, __('Age'), $form_data->$current_field ?? '', ['required'],[] ); !!}
                    
                    @php 
                    $current_field = 'gender';
                    $row_data = ['male'=>'Male', 'female'=>'Female', 'other'=>'Others'];
                    @endphp
                    {!! Form::bsSelect($current_field, __('Gender'), $row_data, $form_data->$current_field ?? '', ['required']); !!}

                    @php
                        $current_field = 'photo';
                        if($mode=='insert'){
                            $req_field = ['required'];
                        } else{
                            $req_field = [];
                        }
                    @endphp
                    {!! Form::bsInput('file',$current_field, __('Photo'), $form_data->$current_field ?? '', $req_field, []); !!}
                    @if($mode =="edit")
                        <div>
                            <a target="_blank" href="{{ asset('upload/tenant/'.$form_data->$current_field) }}"> <img src = "{{ asset('upload/tenant/'.$form_data->$current_field)}}"> </a>
                        </div>
                    @endif

                    @php
                        $current_field = 'document';
                        if($mode=='insert'){
                            $req_field = ['required'];
                        } else{
                            $req_field = [];
                        }
                    @endphp
                    {!! Form::bsInput('file',$current_field, __('Unique Identifiction Document'), $form_data->$current_field ?? '', $req_field, []); !!}
                    @if($mode =="edit")
                        <div>
                            <a target="_blank" href="{{ asset('upload/tenant/'.$form_data->$current_field) }}"> View {{$form_data->document_name}} </a>
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