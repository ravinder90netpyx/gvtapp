@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
<script type="text/javascript">
$(function(){
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
    $crModel = new App\Models\Role_Has_Permissions;
    $role_has_permissions = $crModel->where([ ['role_id', '=', $id] ])->get();
    foreach($role_has_permissions as $rp){
        $rp_arr[] = $rp->permission_id;
    }
}

$hidecheck_arr = ['general_settings'];
@endphp

<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-body">
                <form method="POST" action="{{ $action }}" class="needs-validation" novalidate>
                    {{csrf_field()}}
                    @method($method)

                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="position-relative form-group">
                                <label for="name">{{ __('admin.text_name') }}<span class="req"></span></label>
                                <input type="text" name="name" class="form-control" value="{{ $form_data->name ?? '' }}" required />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div> 
 
                    <div class="row">
                        <?php
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
                        ?>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex">
                            <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger">{!! __('admin.text_button_back') !!}</a>
                            <button type="submit" class="mt-1 btn btn-primary ml-auto">{!! __('admin.text_button_submit') !!}</button>
                            
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection