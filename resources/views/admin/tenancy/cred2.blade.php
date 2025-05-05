@php
Form::macro('variantData1', function($dataArray,$mode)
{
    $rowIndex = $dataArray['id'] ?? 0 ;
    if(!empty($dataArray['photo'])){
        $photo = "<div><a target='_blank' href='".asset('upload/tenant/' . $dataArray['photo'])."'><img src =".asset('upload/tenant/' . $dataArray['photo'])."><span>".$dataArray['photo_name']."</span></a></div>";
    } else{
        $photo = "";
    }
    if(!empty($dataArray['document'])){
        $doc = "<div> <a target='_blank' href='".asset('upload/tenant/' . $dataArray['document'])."'>View ".$dataArray['document_name']."</a></div>";
    } else{
        $doc = "";
    }
    $dataArray['tenant_master_id'] = $dataArray['tenant_master_id'] ?? '';
    $dataArray['name'] = $dataArray['name'] ?? '';
    $dataArray['age'] = $dataArray['age'] ?? '';
    $dataArray['gender'] = $dataArray['gender'] ?? '';
    if($mode =='insert'){
        $req_field = 'required';
    } else{
        $req_field = '';
    }

    return "<div class='row py-1 row_data[$rowIndex]'>"
    .Form::hidden('row_data['.$rowIndex.'][tenant_master_id]', $dataArray['tenant_master_id'] , ['class' => 'form-control']).
    "<div class='col-sm'>".Form::text('row_data['.$rowIndex.'][name]', $dataArray['name'] , ['class' => 'form-control name', 'placeholder'=>'Name', 'required'])."</div>
    <div class='col-sm'>".Form::input('number', 'row_data['.$rowIndex.'][age]', $dataArray['age'], ['class'=>'form-control age', 'placeholder'=>'Age', 'required'],[])."</div>
    <div class='col-sm'>".Form::select('row_data['.$rowIndex.'][gender]', [''=>'--Select Gender--', 'male'=>'Male', 'female'=>'Female', 'other'=>'Others'], $dataArray['gender'],['class'=> 'custom-select gender', 'placeholder'=>' Select Gender', 'required'] )."</div>
        <div class='col-sm-auto pt-2'>
            <i class='text-danger fa-lg fas fa-trash-alt del_row'></i>
        </div>
    </div>";
});

Form::macro('variantData2', function($dataArray,$mode)
{
    $rowIndex = $dataArray['id'] ?? 0 ;
    if(!empty($dataArray['photo'])){
        $photo = "<div><a target='_blank' href='".asset('upload/tenant/' . $dataArray['photo'])."'><img src =".asset('upload/tenant/' . $dataArray['photo'])."><span>".$dataArray['photo_name']."</span></a></div>";
    } else{
        $photo = "";
    }
    if(!empty($dataArray['document'])){
        $doc = "<div> <a target='_blank' href='".asset('upload/tenant/' . $dataArray['document'])."'>View ".$dataArray['document_name']."</a></div>";
    } else{
        $doc = "";
    }
    $dataArray['tenant_master_id'] = $dataArray['tenant_master_id'] ?? '';
    $dataArray['name'] = $dataArray['name'] ?? '';
    $dataArray['age'] = $dataArray['age'] ?? '';
    $dataArray['gender'] = $dataArray['gender'] ?? '';
    if($mode =='insert'){
        $req_field = 'required';
    } else{
        $req_field = '';
    }

    return "<div class='row py-1 row_data[$rowIndex]'>"
    .Form::hidden('police_verification['.$rowIndex.'][tenant_member]', $dataArray['id'] , ['class' => 'form-control']).
    Form::rawLabel($dataArray['name'], $dataArray['name'], ['class' => 'form-control-label col-md-3 col-form-label']).
    "<div class='col-md-9'>".Form::file('police_verification['.$rowIndex.'][police_verification]' , ['class' => 'form-control name', 'placeholder'=>'Police Verification Copy', $req_field])."<div class=''><a target='_blank' href='". asset('upload/tenant/'.$dataArray['police_verification'])."'><i class='text-danger far fa-lg fa-file-pdf'> </i><span> View ".$dataArray['police_verification_name']."</span> </a>
    </div></div>
    </div>";
});

$multi_data=''; 
$multi_data2='';
 if($mode=='insert'){
 $multi_data=Form::variantData1([],$mode, 0);
}
 else{
    if(isset($dataArray) && is_array($dataArray) && count($dataArray) > 0){
        foreach($dataArray as $data){
            $multi_data = $multi_data.Form::variantData1($data, $mode);
        }
    }
}

if($mode=='insert'){
 // $multi_data=Form::variantData2([],$mode, 0);
}
 else{
    if(isset($data2Array) && is_array($data2Array) && count($data2Array) > 0){
        foreach($data2Array as $data){
            $multi_data2 = $multi_data2.Form::variantData2($data, $mode);
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
<script>
function ajax_member(id){
    $.ajax({
        url: '{{ route("supanel.tenancy.get_member") }}',
        method: "POST",
        data: {'_token': '{!! csrf_token() !!}', 'member_id' : id},
        success: function(response){
            $('#mem_name span').text(response);
        },
        error: function(error) {
            console.error('Error fetching folder content:', error);
        }
    });
}

$(document).ready(function(){
    var counter = 0;
    var rowCount = $('.temp1 .row').length;

    // Define the add_row function to create a new row
    function add_row() {
        @if($mode=='edit')
            rowCount = @php echo \App\Models\Tenant_Variant::orderBy('id','desc')->first()->id+1; @endphp ?? rowCount;
        @endif
        var newRow = '<div class="row py-1 row_data['+ rowCount +']">' +
            '<input type="hidden"  name="row_data[' + rowCount + '][tenant_master_id]" class="form-control">' +
            '<div class="col-sm">' +
            '<input type="text" placeholder="Name" name="row_data[' + rowCount + '][name]" class="form-control name">' +
            '</div>' +
            '<div class="col-sm">'+
            '<input type="number" placeholder="Age" name="row_data['+ rowCount +'][age]" class="form-control age">'+
            '</div>'+
            '<div class="col-sm"><select class="custom-select" required name="row_data['+ rowCount +'][gender]">'+
            '<option value="" selected="selected"> --Select Gender-- </option>'+
            '<option value="male"> Male </option>'+
            '<option value="female"> Female </option>'+
            '<option value="other"> Others </option>'+
            '</select></div>'+
            '<div class="col-sm-auto pt-2">' +
            '<i class="text-danger fa-lg fas fa-trash-alt del_row pt-3"></i>' +
            '</div>' +
            '</div>';
        $('.temp1').append(newRow);
        rowCount++; // Increment the row count for the next row
    }

    function police_verification(val) {
        @if($mode=='edit')
            rowCount = @php echo \App\Models\Tenant_Variant::orderBy('id','desc')->first()->id+1; @endphp ?? rowCount;
        @endif
        var value = $('select[name="tenant_member[]"] option[value='+val+']').text()

        var newRow = '<div class="row py-1 police_verification['+ val +']">' +
            '<input type="hidden"  name="police_verification[' + val + '][tenant_member]" value="'+val+'" class="form-control">' +
            '<label for="police_verification[' + val + '][police_verification]" class="form-control-label col-md-3 col-form-label">'+value+'<span class="req"></span></label><div class="col-md-9">'+
            '<input type="file" placeholder="Police Verification Copy" name="police_verification[' + val + '][police_verification]" class="form-control police_verification"></div>' +
            '</div>';
        $('.temp2').append(newRow);
        // rowCount++; // Increment the row count for the next row
    }

    $('#start_date').datetimepicker({
      	useCurrent : true,
        showClose : true,
        format : "YYYY-MM-DD",
        icons:{
        	time : "fa fa-clock",
          date : "fa fa-calendar-day",
          up : "fa fa-chevron-up",
          down : "fa fa-chevron-down",
          previous : 'fa fa-chevron-left',
          next :'fa fa-chevron-right',
          today :'fa fa-screenshot',
          clear : 'fa fa-trash',
          close : 'fa fa-remove'
      	}
      });

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

    $('#member_id').on('change', function(){
        val = $(this).val();
        ajax_member(val);
    });

    $('#type').on('change', function(){
        mode = '@php echo $mode; @endphp';
        req = (mode=='insert')? 'req':'';
        type = $(this).val();
        // alert(type);
        @if($mode!='edit')
        $('select[name="tenant_member[]"]').val([]).trigger('change');
        @else
            if(counter>0){
                $('select[name="tenant_member[]"]').val([]).trigger('change');
            }
        @endif
        if(type == 'family'){
            $('select[name="tenant_member[]"]').prop('multiple', false);
            $('.photo').find('label').html("Family Group Photo<span class="+req+"></span>");
            $('.addon').show();
            $('.addon').find('input').attr('required', true);
            rowCount=0;
            @if($mode != 'edit' )
            add_row();
            @else
            if(counter>0) add_row();
            @endif
        } else if(type=="individual"){
            $('select[name="tenant_member[]"]').prop('multiple', true);
            $('.photo').find('label').html("Photo<span class="+req+"></span>");
            $('.addon').hide();
            $('.addon').find('input').removeAttr('required');
            $('.temp1').text('');
        } else{
            $('select[name="tenant_member[]"]').prop('multiple', false);
            $('.photo').find('label').html("Photo<span class="+req+"></span>");
            $(".addon").hide();
            $('.addon').find('input').removeAttr('required');
            $('.temp1').text('');
        }
        counter++;
    });
    $('#type').trigger('change');
    $('select[name="tenant_member[]"]').on('change', function(){
        val = $(this).val();
        if(val!='' && val!=null){
            $('.addon_pol').show();
            $('.temp2').text('');
            if(Array.isArray(val)){
                for(let v of val){
                    police_verification(v);
                }
            } else{
                police_verification(val);
            }
        } else{
            $('.addon_pol').hide();
            $('.temp2').text('');
        }
    });

});

</script>
@endsection

@section('content')
{!! Form::open(['url'=>$action, 'method'=>$method, 'class'=>'needs-validation', 'novalidate', 'files'=>true]) !!}
    <div class="row">
        <div class="offset-xl-2 col-xl-8 offset-lg-1 col-lg-10 col-md-12">
            <div class="main-card mb-3 card">
                <div class="card-header fixed-header bg-primary d-flex justify-content-between align-items-center">
                    <div>{{ $title_shown }}</div>
                    <div>
                        <button type="submit" class="mt-1 btn btn-dark btn-sm">{!! __('admin.text_button_submit') !!}</button>
                        <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger btn-sm">{!! __('admin.text_button_back') !!}</a>
                    </div>
                </div>

                <div class="card-body"> 
                    <div class="row">

                    @php
                    $auth_user = Illuminate\Support\Facades\Auth::user();
                    $roles = $auth_user->roles()->pluck('id')->toArray();
                    @endphp
                        @if(in_array(1, $roles) && $mode!='edit')
                        <div class="col-md-6">
                            @php 
                            $current_field = 'organization_id';
                            $row_data=[];
                            $data_select=\App\Models\Organization::select('id','name')->get();
                            foreach($data_select as $ds) $row_data[$ds->id]=$ds->name;
                            @endphp
                            {!! Form::bsSelect($current_field, __('Organization'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'required'], ['vertical'=> true]); !!}
                        </div>
                        @endif    


                        <div class="col-md-6">
                            @php 
                            $current_field = 'type';
                            $row_data = ['family'=>'Family', 'individual'=>'Individual'];
                            @endphp
                            {!! Form::bsSelect($current_field, __('Type'), $row_data, $form_data->$current_field ?? '', ['required', 'data-toggle'=>'select'], ['vertical'=>true]); !!}
                        </div>

                        {{-- <div class="col-md-6">
                            @php $current_field = 'name'; @endphp
                            {!! Form::bsText($current_field, __('admin.text_name'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div> --}}

                        {{-- <div class="col-md-6">
                            @php $current_field = 'mobile_number'; @endphp
                            {!! Form::bsInput('tel',$current_field, __('Mobile Number'), $form_data->$current_field ?? '', ['required', 'autocomplete'=>'tel'], ['vertical'=>true ]); !!}
                        </div> --}}

                        {{-- <div class="col-sm-6">
                            @php $current_field = 'email'; @endphp
                            {!! Form::bsEmail($current_field, 'Email', $form_data->$current_field ?? '', [], ['vertical'=>true]); !!}
                        </div> --}}

                        {{-- <div class="col-md-6">
                            @php $current_field = 'start_date'; @endphp
                            {!! Form::bsInput('number', $current_field, __('Start Date'), $form_data->$current_field ?? '', ['required'],['vertical'=>true] ); !!}
                        </div> --}}

                        <div class="col-md-6">
                            @php 
                            $current_field = 'start_date';
                            $now=Carbon\Carbon::now();
                            $add_perm = ['vertical'=>true];
                            @endphp
                            {!! Form::bsInput('text', $current_field, __('Start Date'), $form_data->$current_field ?? $now->toDateTimeString(), ['required'], $add_perm); !!}
                        </div>

                        <div class="col-md-6">
                            @php 
                            $current_field = 'member_id';
                            $row_data = [];

                            $picked_member = \App\Models\Tenant_Master::where([['status','>','0'], ['delstatus','<','1'],['member_id','!=',null]])->pluck('member_id')->toArray();
                            if($mode=='insert'){
                                $data_select = \App\Models\Members::where([['delstatus','<','1'],['status','>','0']])->whereNotIn('id',$picked_member)->get();
                            }
                            if($mode == 'edit'){
                                $data_select = \App\Models\Members::where([['status','>','0'], ['delstatus','<','1']])->whereNotIn('id',$picked_member)->orwhere([['status','>','0'], ['delstatus','<','1'], ['id','=',$form_data->$current_field]])->get();
                            }
                            foreach($data_select as $ds) $row_data[$ds->id] = $ds->unit_number;
                            @endphp
                            {!! Form::bsSelect($current_field, __('Unit Number'), $row_data, $form_data->$current_field ?? '', ['required', 'data-toggle'=>'select'], ['vertical'=>true]); !!}
                            <div id="mem_name" class ="file-div">
                                <span> </span>
                            </div>
                        </div>

                        <div class="col-md-6 tenant_select">
                            @php 
                            $current_field = 'tenant_member[]';
                            $row_data = [];
                            $tenant_data =[];
                            
                            if($mode =="insert"){
                                $data_select = \App\Models\Tenant_Variant::where([['delstatus','<','1'],['status','>','0'], ['tenant_master_id','=', null],['isfamily','=','0']])->get();
                            }
                            if($mode =="edit"){
                                $data_select = \App\Models\Tenant_Variant::where([['delstatus','<','1'],['status','>','0'], ['tenant_master_id','=', null]])->orwhere([['delstatus','<','1'],['status','>','0'], ['tenant_master_id','=', $id], ['isfamily','=','0']])->get();
                                $tenant_model = \App\Models\Tenant_Variant::where([['delstatus','<','1'],['status','>','0'], ['tenant_master_id', '=',$id],['isfamily','=','0']])->get();
                                foreach($tenant_model as $tm) $tenant_data[] = $tm->id;
                            }
                            foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
                            @endphp
                            {!! Form::bsSelect($current_field, __('Tenant(s)'), $row_data, $tenant_data ?? '', ['required',  'multiple','data-toggle'=>'select-multiple'], ['vertical'=>true, 'remove_blank_field'=>true]); !!}
                        </div>
                        
                        {{-- <div class="col-md-6">
                            @php 
                            $current_field = 'gender';
                            $row_data = ['male'=>'Male', 'female'=>'Female', 'other'=>'Others'];
                            
                            @endphp
                            {!! Form::bsSelect($current_field, __('Gender'), $row_data, $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div> --}}

                        {{-- <div class="col-md-6 photo">
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
                            <div>
                                <a target="_blank" href= "{{ asset('upload/tenant/'.$form_data->$current_field)}}"><img src = "{{ asset('upload/tenant/'.$form_data->$current_field)}}"><span>{{$form_data->photo_name}}</span></a> 
                            </div>
                            @endif
                        </div> --}}

                        {{-- <div class="col-md-6">
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
                                <div>
                                    <a target="_blank" href="{{ asset('upload/tenant/'.$form_data->$current_field) }}"> View {{$form_data->document_name}} </a>
                                </div>
                            @endif
                        </div> --}}

                        {{-- <div class="col-md-6">
                            @php $current_field = 'name'; @endphp
                            {!! Form::bsTextArea($current_field, __('admin.text_name'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div> --}}                    

                        <div class="addon col-md-12 py-2 my-2" style="display:none;">
                            <div class="card-header" style=" color: white; background-color: #f5365c;"> Add Family Member </div>
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

                        <!-- Agreement Copies -->
                        <div class="col-md-6">
                            @php
                                $current_field = 'rent_agreement';
                                if($mode=='insert'){
                                    $req_field = ['required'];
                                } else{
                                    $req_field = [];
                                }
                            @endphp
                            {!! Form::bsInput('file',$current_field, __('Rent Agreement Copy'), $form_data->$current_field ?? '', $req_field, ['vertical'=>true]); !!}
                            @if($mode =="edit")
                                <div class="file-div">
                                    <a target="_blank" href="{{ asset('upload/tenant/'.$form_data->$current_field) }}"><i class="text-danger far fa-lg fa-file-pdf"> </i><span> View {{$form_data->rent_agreement_name}}</span> </a>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            @php
                                $current_field = 'undertaking';
                                if($mode=='insert'){
                                    $req_field = ['required'];
                                } else{
                                    $req_field = [];
                                }
                            @endphp
                            {!! Form::bsInput('file',$current_field, __('Undertaking Copy'), $form_data->$current_field ?? '', $req_field, ['vertical'=>true]); !!}
                            @if($mode =="edit")
                                <div>
                                    <a target="_blank" href="{{ asset('upload/tenant/'.$form_data->$current_field) }}"><i class="text-danger far fa-lg fa-file-pdf"> </i> <span> View {{ $form_data->undertaking_name }} </span></a>
                                </div>
                            @endif
                        </div>

                        @if($mode != 'insert')
                        <div class="col-md-6">
                            @php
                                $current_field = 'acceptance';
                                if($mode=='insert'){
                                    $req_field = ['required'];
                                } else{
                                    $req_field = [];
                                }
                            @endphp
                            {!! Form::bsInput('file',$current_field, __('Acceptance Copy'), $form_data->$current_field ?? '', $req_field, ['vertical'=>true]); !!}
                            @if($mode =="edit" && !empty($form_data->current_field))
                                <div>
                                    <a target="_blank" href="{{ asset('upload/tenant/'.$form_data->$current_field) }}"> <i class="text-danger far fa-lg fa-file-pdf"> </i> <span> View {{$form_data->acceptance_name}} </span> </a>
                                </div>
                            @endif
                        </div>
                        @endif

                        <div class="addon_pol col-md-12 py-2 my-2" style="@if($mode=='insert')display:none; @endif">
                            <div class="card-header" style=" color: white; background-color: #f5365c;"> Police Verification Copy(s) </div>
                            <div class="card-body" style="background-color: #fafafa;">                           
                                <div class="temp2">
                                    @if($mode=='edit') {!! $multi_data2 !!} @endif
                                </div> 
                            </div>
                        </div>

                        {{-- <div class="col-md-6">
                            @php
                                $current_field = 'police_verification';
                                if($mode=='insert'){
                                    $req_field = ['required'];
                                } else{
                                    $req_field = [];
                                }
                            @endphp
                            {!! Form::bsInput('file',$current_field, __('Police Verification Copy'), $form_data->$current_field ?? '', $req_field, ['vertical' => true]); !!}
                            @if($mode =="edit")
                                <div>
                                    <a target="_blank" href="{{ asset('upload/tenant/'.$form_data->$current_field) }}"><i class="text-danger far fa-lg fa-file-pdf"> </i> <span> View {{$form_data->police_verification_name}} </span> </a>
                                </div>
                            @endif
                        </div> --}}

                        {{-- <div class="col-md-6">
                            @php
                            $current_field = 'address';
                            @endphp

                            {!! Form::bsTextArea($current_field, __('Address'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection