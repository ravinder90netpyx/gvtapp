@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
@endsection

@section('content')
{!! Form::open(['url'=>$action, 'method'=>$method, 'class'=>'needs-validation', 'novalidate', 'files'=>true]) !!}
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
                            {!! Form::bsText($current_field, __('Name'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'age'; @endphp
                            {!! Form::bsInput('number', $current_field, __('Age'), $form_data->$current_field ?? '', ['required'],['vertical'=>true] ); !!}
                        </div>

                        <div class="col-md-6">
                            @php 
                            $current_field = 'gender';
                            $row_data = ['male'=>'Male', 'female'=>'Female', 'other'=>'Others'];
                            
                            @endphp
                            {!! Form::bsSelect($current_field, __('Gender'), $row_data, $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'mobile_number'; @endphp
                            {!! Form::bsInput('tel',$current_field, __('Mobile Number'), $form_data->$current_field ?? '', ['required', 'autocomplete'=>'tel'], ['vertical'=>true ]); !!}
                        </div>

                        <div class="col-sm-6">
                            @php $current_field = 'email'; @endphp
                            {!! Form::bsEmail($current_field, 'Email', $form_data->$current_field ?? '', [], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6 photo">
                            @php
                                $current_field = 'photo';
                                if($mode=='insert'){
                                    $req_field = ['required'];
                                } else{
                                    $req_field = [];
                                }
                            @endphp
                            {!! Form::bsInput('file',$current_field, __('Photo'), $form_data->$current_field ?? '', $req_field, ['vertical'=>true]); !!}
                            @if($mode =="edit")
                            <div class ="file-div">
                                <a target="_blank" href= "{{ asset('upload/tenant/'.$form_data->$current_field)}}"><img src = "{{ asset('upload/tenant/'.$form_data->$current_field)}}"><span>{{$form_data->photo_name}}</span></a> 
                            </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            @php
                                $current_field = 'document';
                                if($mode=='insert'){
                                    $req_field = ['required'];
                                } else{
                                    $req_field = [];
                                }
                            @endphp
                            {!! Form::bsInput('file',$current_field, __('Unique Identifiction Document'), $form_data->$current_field ?? '', $req_field, ['vertical'=>true]); !!}
                            @if($mode =="edit")
                                <div class="file-div">
                                    <a target="_blank" href="{{ asset('upload/tenant/'.$form_data->$current_field) }}"><i class="text-danger far fa-lg fa-file-pdf"> </i><span> View {{$form_data->document_name}}</span> </a>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'locality'; @endphp

                            {!! Form::bsText($current_field, __('Locality/Area'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'city'; @endphp

                            {!! Form::bsText($current_field, __('City'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'state'; 
                            $row_data = [];
                            foreach($indiaStates as $ind) $row_data[$ind] = $ind;
                            @endphp

                            {!! Form::bsSelect($current_field, __('State'), $row_data, $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'pincode'; @endphp

                            {!! Form::bsInput('number', $current_field, __('Pincode'), $form_data->$current_field ?? '', ['required', 'pattern'=>'[1-9][0-9]{5}$'],['vertical'=>true] ); !!}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection