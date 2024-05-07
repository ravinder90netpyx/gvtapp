@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
<script type="text/javascript">
$(function(){
    $('#{{ $module['table_column_prefix'].'role-type' }}').select2({
        //allowClear: true,
        closeOnSelect: false
    });

    $('.card-closest .card-body input[type="checkbox"]').click(function(e){
        $(this).closest('.card-closest').find('.card-header input[type="checkbox"]').prop('checked', true);
    });

    $('.card-closest .card-header input[type="checkbox"]').click(function(e){
        var is_checked = $(this).prop('checked');
        $(this).closest('.card-closest').find('.card-body input[type="checkbox"]').prop('checked', is_checked);
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

@php
if($mode=="edit" && !empty($form_data->created_by)){
    $roles = \App\Models\User::find($form_data->created_by)->roles()->pluck('id')->toArray();
} else{
    $roles = auth()->user()->roles()->pluck('id')->toArray();
}
if(!in_array('1', $roles)){
    $usm = new \App\Models\User;
    $rluserid = !empty($form_data->created_by) ? $form_data->created_by : auth()->user()->id;
    $allPermissions = $usm->find($rluserid)->getAllPermissions()->pluck('id')->toArray();
    #echo "<pre>"; print_r($allPermissions); echo "</pre>"; exit; 
    $permissions = DB::table('permissions')->where([ ['name', 'NOT LIKE', 'users.%'], ['name', 'NOT LIKE', 'user_roles.%'] ])->whereIn('id', $allPermissions)->get();
} else{
    $su_conditions = [];
    if( !( isset($id) && $id=='1' ) ) $su_conditions[] = ['name', 'NOT LIKE', 'organization.%'];
    $permissions = DB::table('permissions')->where($su_conditions)->get();
}

$pm_arr = [];
foreach($permissions as $permission){
    $pr_break = explode('.', $permission->name);
    $pr_group = trim($pr_break[0]);
    $pr_permission = trim($pr_break[1]);
    $pm_arr[$pr_group][$pr_permission] = $permission->id;
}

$rp_arr = [];
if($mode=='edit' && !empty($id)){
    $crModel = new App\Models\Model_Has_Permissions;
    $model_has_permissions = $crModel->where([ ['model_id', '=', $id], ['model_type', '=', 'App\Models\User'] ])->get();
    foreach($model_has_permissions as $rp){
        $rp_arr[] = $rp->permission_id;
    }
}

$hidecheck_arr = ['general_settings'];
@endphp

@php
/*$permissions = DB::table('permissions')->get();

$pm_arr = [];
foreach($permissions as $permission){
    $pr_break = explode('.', $permission->name);
    $pr_group = trim($pr_break[0]);
    $pr_permission = trim($pr_break[1]);
    $pm_arr[$pr_group][$pr_permission] = $permission->id;
}

$rp_arr = [];
if($mode=='edit' && !empty($id)){
    $crModel = new App\Models\Model_Has_Permissions;
    $model_has_permissions = $crModel->where([ ['model_type', '=', 'App\Models\User'], ['model_id', '=', $id] ])->get();
    foreach($model_has_permissions as $rp){
        $rp_arr[] = $rp->permission_id;
    }
}*/
@endphp
<form method="POST" action="{{ $action }}" class="needs-validation" novalidate>
    {{ csrf_field() }}
    @method($method)
    <div class="row">
        <div class="col-lg-12">
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            @php $current_field = $module['table_column_prefix'].'first_name'; @endphp
                            {!! Form::bsText($current_field, 'First Name', $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-sm-6">
                            @php $current_field = $module['table_column_prefix'].'last_name'; @endphp
                            {!! Form::bsText($current_field, 'Last Name', $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-sm-6">
                            @php $current_field = $module['table_column_prefix'].'uname'; @endphp
                            {!! Form::bsText($current_field, 'User Name', $form_data->$current_field ?? '', ['required', 'placeholder'=>__('admin.text_unique_msg')], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-sm-6">
                            @php $current_field = $module['table_column_prefix'].'email'; @endphp
                            {!! Form::bsEmail($current_field, 'Email', $form_data->$current_field ?? '', ['required', 'placeholder'=>__('admin.text_unique_msg')], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-sm-6">
                            @php $current_field = $module['table_column_prefix'].'phone'; @endphp
                            {!! Form::bsInput('tel', $current_field, 'Phone', $form_data->$current_field ?? '', ['required', 'placeholder'=>__('admin.text_unique_msg')], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-sm-6">
                            @php 
                            $current_field = $module['table_column_prefix'].'role-type[]';
                            $current_field_id = $module['table_column_prefix'].'role-type';
                            $row_data = [];

                            $current_user_id = auth()->user()->id;
                            $current_userrole_ids = Auth::user()->roles()->pluck('id')->toArray();
                            $inst = DB::table('roles');
                            if($mode=="edit"){
                                if(!empty($form_data->created_by)) $inst = $inst->where([ ['created_by', '=', $form_data->created_by] ]);
                            } else{
                                /*if(!in_array(1, $current_userrole_ids)) */$inst = $inst->where([ ['created_by', '=', $current_user_id] ]);
                            }

                            $data_select = $inst->get()->toArray();
                            foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;

                            $selected_role = '';
                            if($mode=='edit'){
                                $selected_role = [];
                                $crModel = new App\Models\Model_Has_Roles;
                                $cr_role = $crModel->where('model_id', $form_data->id)->get();
                                foreach($cr_role as $rl) $selected_role[] = $rl->role_id;
                            }
                            @endphp
                            {!! Form::bsSelect($current_field, 'Type', $row_data, $selected_role ?? '', ['required', 'multiple', 'id'=>$current_field_id], ['vertical'=>true, 'remove_blank_field'=>true]); !!}      
                        </div>

                        <div class="col-sm-6">
                            @php 
                            $current_field = $module['table_column_prefix'].'password';
                            $attribs = ['autocomplete'=>'new-password'];
                            if($mode=='insert') $attribs[] = 'required';
                            else $attribs['placeholder'] = 'Fill to change password';
                            @endphp
                            {!! Form::bsPassword($current_field, 'Password', '', $attribs, ['vertical'=>true]); !!}
                        </div>

                        @if( $mode=='insert' && in_array('1', $roles) )
                            <div class="col-sm-6">
                                @php 
                                $current_field = $module['table_column_prefix'].'organization_id';
                                if($mode=="edit") $row_selected_id = $form_data->$current_field;
                                $row_model = new \App\Models\Organization;
                                $row_data = $row_model->getOptionValues();
                                $row_params = ['required'];

                                if( !($mode=='insert' && in_array('1', $roles)) ) $row_params = array_merge($row_params, ['disabled'=>'disabled']);

                                if($mode=='insert' && !in_array('1', $roles)){
                                    #echo "<pre>"; print_r(auth()->user()->organization_id); echo "</pre>"; exit;
                                    $row_selected_id = auth()->user()->organization_id;
                                }
                                @endphp
                                {!! Form::bsSelect($current_field, 'Organization', $row_data, $row_selected_id ?? '', $row_params, ['vertical'=>true]); !!}
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        @php
                        foreach($pm_arr as $key=>$val){
                            $group_name = $key;
                            $group_heading = ucwords(str_replace('_', ' ', $key));
                            
                            $hide_maincheck = false;
                            if(in_array($group_name, $hidecheck_arr)) $hide_maincheck = true;

                            $group_id = (!$hide_maincheck) ? $val['manage'] : '';
                            #$rl_type = role_types($val['variable']);
                            $this_str = '<div class="col-md-6">
                                <div class="card card-closest elevation-5 border">
                                <div class="card-header bg-default">
                                    <div class="custom-control custom-control-alternative custom-checkbox">';
                                        if(!$hide_maincheck) $this_str .= '<input type="checkbox" class="custom-control-input" name="prev['.$group_name.'.manage]" id="prev_'.$group_name.'_manage" value="'.$group_id.'"'.( in_array($group_id, $rp_arr) ? ' checked' : '' ).' />';
                                        $this_str .= '<label class="'.( !$hide_maincheck ? 'custom-control-label ' : '' ).'text-white" for="prev_'.$group_name.'_manage">'.$group_heading.'</label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">';
                                        foreach($val as $tpk=>$tpr){
                                            $permission_name = $tpk;
                                            $permission_heading = ucwords(str_replace('_', ' ', $tpk));
                                            $permission_id = $tpr;
                                            if($permission_name!='manage'){
                                                $this_str .= '<div class="col-lg-3 col-sm-6 my-2">
                                                    <div class="custom-control custom-control-alternative custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="prev_'.$group_name.'_'.$permission_name.'" name="prev['.$group_name.'.'.$permission_name.']" value="'.$permission_id.'"'.( in_array($permission_id, $rp_arr) ? ' checked' : '' ).' />
                        
                                                        <label class="custom-control-label" for="prev_'.$group_name.'_'.$permission_name.'">'.$permission_heading.'</label>
                                                    </div>
                                                </div>';
                                            }
                                        }
                                    $this_str .= '</div>
                                </div>
                            </div>
                            </div>';

                            echo $this_str;
                        }
                        @endphp
                    </div>
                </div>

                <div class="card-footer bg-secondary">
                    <div class="d-flex">
                        <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger">{!! __('admin.text_button_back') !!}</a>
                        <button type="submit" class="mt-1 btn btn-primary ml-auto">{!! __('admin.text_button_submit') !!}</button>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</form>
@endsection