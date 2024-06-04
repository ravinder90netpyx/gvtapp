<?php
namespace App\Http\Controllers\Admin;

use App\Models\Settings as DefaultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;

class GeneralConfigController extends Controller{
    public $module = array(
        'module_view' => 'general_configs',
        'module_route' => 'general_configs',
        'permission_group' => 'general_settings',
        'main_heading' => 'General Config',
        'table_name' => 'settings',
        'default_perpage' => 10
    );

    public function __construct(Request $request){
        parent::__construct();

        $module = $this->module;
        $folder = $this->folder;

        $module['main_view'] = $folder['folder_name'].'.'.$module['module_view'];
        $module['main_route'] = $folder['route_folder_name'].'.'.$module['module_route'];

        $module['table_column_index'] = 'id';
        $module['table_column_status'] = 'status';
        $module['table_column_delstatus'] = 'delstatus';
        $module['table_column_created'] = 'created_at';
        $module['table_column_updated'] = 'updated_at';

        $this->module = $module;

        $this->middleware('permission:'.$module['permission_group'].'.manage_general_config', ['only' => ['index', 'store']]);
    }

    public function index(Request $request, DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $title_shown = 'Manage Settings';

        return view($module['main_view'].'.index', compact('module', 'folder', 'title_shown', 'model'));
    }

    public function store(Request $request, DefaultModel $model){
        $module = $this->module;
        $request->validate([
            'whatsapp_settings.frequency_minute' => array('required', 'integer', 'min:1', 'max:1000'),
            'whatsapp_settings.jobs_per_schedule' => array('required', 'integer', 'min:1', 'max:100000'),
            'config.admin_mail' => array('required', 'regex:/^([a-z][a-z0-9_.]+@([a-z0-9-]+\.)+[a-z]{2,6}(,)*)+$/')
        ]);

        $group = 'whatsapp_settings';
        if(!empty($request->input($group))){
            $fields = $request->input($group);
            foreach($fields as $fieldk=>$field){
                $model->insOrUpd(['group'=>$group, 'key'=>$fieldk], ['value'=>$field]);
            }
        }

        // $group = 'config';
        // if(!empty($request->input($group))){
        //     $fields = $request->input($group);
        //     foreach($fields as $fieldk=>$field){
        //         $model->insOrUpd(['group'=>$group, 'key'=>$fieldk], ['value'=>$field]);
        //     }
        // }
        #echo "<pre>"; print_r($request->input()); echo "<pre>"; exit;
    
        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' updated successfully.');
    }
}
