@php
    $bsmodal=$bsmodal ?? true;
    $disable=($mode == 'show') ? "disabled": '';

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
        if($mode=='show') {
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
    {!! Form::bsInput('text', $current_field, __('Date'), $form_data->$current_field ?? $now->toDateTimeString(), ['required', $disable], $add_perm); !!}
</div>

<div class="col-md-6">
    @if($bsmodal==true)
        @php $current_field = 'member_mob';

        @endphp
        {!! Form::bsInput('search', $current_field, __('Search Member'), $form_data->$current_field ?? '', ['required', 'disabled'], ['vertical'=>true ]); !!}
    @else
        @php $current_field = 'member_id';
            $row_data=[];
            $data_select=\App\Models\Members::where([['delstatus','<','1'],['status','>','0'], ['orga']])->get();
            foreach($data_select as $ds) $row_data[$ds->id]= $ds->name;
        @endphp
        {!! Form::bsSelect($current_field, __('Member'), $row_data, $form_data->$current_field ?? '', ['required', $disable], ['vertical'=>true]); !!}
    @endif
</div>

<div class="col-md-6 charge" @if($mode!='show') style="display:none;" @endif>
    @php $current_field = 'charge';
        $add_perm = ['vertical'=>true];
        $apm = ['addon_check'=>'unpaid', 'addon_check_title' => 'Not Fully Paid'];
        $add_perm = array_merge($add_perm, $apm);
    @endphp
    {!! Form::bsInput('number', $current_field, __('Paid Money'), $form_data->$current_field ?? '', ['disabled'], $add_perm); !!}
</div>

<div class="col-md-6">
    @php $current_field = 'from_month'; @endphp
    {!! Form::bsInput('text', $current_field, __('From'), $form_data->$current_field ?? '', [ 'required', $disable ],  ['vertical'=>true]); !!}
</div>

<div class="col-md-6">
    @php $current_field = 'to_month'; @endphp
    
    {!! Form::bsInput('text', $current_field, __('To'), $form_data->$current_field ?? '', [ 'required', $disable ],  ['vertical'=>true]); !!}
</div>


<div class="col-md-6 ">
    @php $current_field = 'payment_mode';
        $row_data = \App\Models\Journal_Entry::getPaymentMode();
    @endphp
    {!! Form::bsSelect($current_field, __('Mode'), $row_data, $form_data->$current_field ?? '', ['required', 'placeholder'=>'Select Mode' , $disable], ['vertical'=>true]); !!}
</div>

{!! Form::hidden('member_id', '', ['required', $disable,'id'=>'member_id']); !!}

@if($mode=="edit")
{!! Form::hidden('edit_id', $id, ['required', $disable, 'id'=>'member_id']); !!}
@endif