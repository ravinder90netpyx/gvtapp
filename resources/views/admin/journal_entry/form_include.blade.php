@php
    $bsmodal=$bsmodal ?? true;
    $disable=($mode == 'show') ? "disabled": '';
    $hide = "style='display:none;'";
    $auth_user = Auth::user();  
    $roles = $auth_user->roles()->pluck('name','id')->toArray();
    if(in_array('1', array_keys($roles))){
 @endphp
<div class="col-md-6">
    @php 
    $current_field = 'organization_id';
    $organizations = \App\Models\Organization::pluck('name', 'id');
    @endphp
    {!! Form::bsSelect($current_field, __('Organization Name'), $organizations, $form_data->$current_field ?? '', ['required', $disable], ['vertical'=>true]); !!}
</div>

@php } @endphp

<div class="col-md-6" id = "series_div_id">
    @php
        $row_data=[];
        if($mode=='show' || $mode == 'edit') {
            $data_select=\App\Models\Series::where([['delstatus','<','1'],['status','>','0']])->get();
            foreach($data_select as $ds) $row_data[$ds->id]= $ds->name;
        }
    @endphp
    @include($module['main_view'].'.series_select',['row_data'=>$row_data])
    {{-- @php $current_field = 'series_id';
        $row_data=[];
        if($mode!="edit" && in_array('1', array_keys($roles))){
            $data_select=\App\Models\Series::where([['delstatus','<','1'],['status','>','0']])->get();
            foreach($data_select as $ds) $row_data[$ds->id]= $ds->name;
        }
    @endphp
    {!! Form::bsSelect($current_field, __('Series'), $row_data, $form_data->$current_field ?? '', ['required', 'disabled'], ['vertical'=>true]); !!} --}}
</div>

<div class="col-md-6">
    @php $current_field = 'entry_year';
        $row_data=[];
        foreach(array_reverse($financial_years) as $fy){
            $row_data[$fy] = $fy;
        }
    @endphp
    {!! Form::bsSelect($current_field, __('Year'), $row_data, $form_data->$current_field ?? '', ['required', $disable], ['vertical'=>true, 'remove_blank_field'=>true]); !!}
</div>

<div class="col-md-6">
    @php 
    $current_field = 'entry_date';
    $now=Carbon\Carbon::now();
    $add_perm = ['vertical'=>true];
    $apm = ['addon_check'=>'back_date', 'addon_check_title' => 'Enter Previous Date'];
    $add_perm = array_merge($add_perm, $apm);
    @endphp
    {!! Form::bsInput('text', $current_field, __('Payment Date'), $form_data->$current_field ?? $now->toDateTimeString(), ['required', $disable], $add_perm); !!}
</div>

<div class="col-md-6">
    @php 
    $current_field = 'reciept_date';
    $add_perm = ['vertical'=>true];
    $add_perm = array_merge($add_perm);
    @endphp
    {!! Form::bsInput('text', $current_field, __('Reciept Date'), $form_data->$current_field ?? '', ['required', $disable], $add_perm); !!}
</div>

<div class="col-md-6">
    @php $current_field = 'charge_type_id';
        $row_data=[];
        $charge_type_model = \App\Models\ChargeType::where([['status','>','0'],['delstatus','<','1']])->get();
        foreach($charge_type_model as $ch){
            $row_data[$ch->id] = $ch->name;
        }
    @endphp
    {!! Form::bsSelect($current_field, __('Charge Type'), $row_data, $form_data->$current_field ?? '', ['required', $disable], ['vertical'=>true]); !!}
</div>

<div class="col-md-6">
    @if($bsmodal==true)
        @php $current_field = 'member_mob';
            $row_data = [];
            if($mode =="show"){
                $row_data[$form_data->$current_field] = $form_data['member_val'];
            }
            if($mode =='edit'){
                $member = \App\Models\Members::where([['organization_id','=',$form_data['organization_id']], ['delstatus','<','1'], ['status','>','0']])->get()->toArray();
                foreach($member as $mem){
                    $row_data[$mem['id']] = "Name : ".$mem['name']."; Unit No : ".$mem['unit_number']."; Mob No : ".$mem['mobile_number'];
                }
            }
        @endphp
        {{-- {!! Form::bsInput('search', $current_field, __('Search Member'), $form_data->$current_field ?? '', ['required', 'disabled'], ['vertical'=>true ]); !!} --}}
        {!! Form::bsSelect($current_field, __('Search Member'), $row_data, $form_data->$current_field ?? '', ['required', $disable], ['vertical'=>true]); !!}

    @else
        @php $current_field = 'member_id';
            $row_data=[];
            $data_select=\App\Models\Members::where([['delstatus','<','1'],['status','>','0'], ['organization_id']])->get();
            foreach($data_select as $ds) $row_data[$ds->id]= $ds->name;
        @endphp
        {!! Form::bsSelect($current_field, __('Member'), $row_data, $form_data->$current_field ?? '', ['required', $disable], ['vertical'=>true]); !!}
    @endif
</div>

<div class="col-md-6 fine_amount">
    @php 
    $current_field = 'fine_amt';
    $add_perm = ['vertical'=>true];
    $apm = ['addon_check'=>'fine_wave_off', 'addon_check_title' => 'Wave off Fine'];
    $add_perm = array_merge($add_perm, $apm);
    @endphp
    {!! Form::bsInput('number', $current_field, __('Fine Amount'), $form_data->$current_field ?? '', ['disabled'], $add_perm); !!}
</div>

<div class="col-md-6 charge" @if($mode == 'insert') style="display:none;" @endif>
    @php $current_field = 'charge';
        $add_perm = ['vertical'=>true];
        $apm = ['addon_check'=>'unpaid', 'addon_check_title' => 'Not Fully Paid'];
        $add_perm = array_merge($add_perm, $apm);
    @endphp
    {!! Form::bsInput('number', $current_field, __('Paid Money'), $form_data->$current_field ?? '', ['disabled'], $add_perm); !!}
</div>

@php $current_field = 'from_month'; @endphp
<div class="col-md-6 from_month" @if(empty($form_data->$current_field) && $mode =='edit') style='display:none;' @endif>
    {!! Form::bsInput('text', $current_field, __('From'), $form_data->$current_field ?? '', [ 'required', 'autocomplete'=>'off', $disable ],  ['vertical'=>true]); !!}
</div>

@php $current_field = 'to_month'; @endphp
<div class="col-md-6 to_month" @if(empty($form_data->$current_field) && $mode =='edit') style='display:none;' @endif >
    
    {!! Form::bsInput('text', $current_field, __('To'), $form_data->$current_field ?? '', [ 'required', 'autocomplete'=>'off', $disable ],  ['vertical'=>true]); !!}
</div>

<div class="col-md-6 custom_toggle">
    @php $current_field = 'custom_toggle'; @endphp
    {!! Form::bsToggle($current_field, 'Custom Month', '1', ( $form_data->$current_field ?? false ), [$disable], ['vertical'=>true]); !!}
</div>

@php $current_field = 'custom_month'; @endphp
<div class="col-md-6 custom_month" @if(empty($form_data->$current_field) || $mode == 'insert') style='display:none;' @endif>
    {!! Form::bsInput('text', $current_field, __('Custom Month'), $form_data->$current_field ?? '', [ 'autocomplete'=>'off', $disable ],  ['vertical'=>true]); !!}
</div>

<div class="col-md-6 ">
    @php $current_field = 'payment_mode';
        $row_data = \App\Models\Journal_Entry::getPaymentMode();
    @endphp
    {!! Form::bsSelect($current_field, __('Mode'), $row_data, $form_data->$current_field ?? '', ['required', 'placeholder'=>'Select Mode' , $disable], ['vertical'=>true]); !!}
</div>

<div class="col-md-6 ">
    @php $current_field = 'remarks'; @endphp
    {!! Form::bsTextArea($current_field, __('Remarks'), $form_data->$current_field ?? '', [$disable], ['vertical'=>true]); !!}
</div>

{!! Form::hidden('member_id', '', ['required', $disable,'id'=>'member_id']); !!}

@if($mode=="edit")
{!! Form::hidden('edit_id', $id, ['required', $disable, 'id'=>'member_id']); !!}
@endif