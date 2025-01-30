<?php
namespace App\Http\Controllers\Admin;

use App\Models\Expense as DefaultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ExpenseController extends Controller{
    public $module = array(
        'module_view' => 'expense',
        'module_route' => 'expense',
        'permission_group' => 'expense',
        'main_heading' => 'Expense',
        'default_perpage' => 10
    );

    public function __construct(Request $request){
        $module = $this->module;
        $folder = $this->folder;

        $module['main_view'] = $folder['folder_name'].'.'.$module['module_view'];
        $module['main_route'] = $folder['route_folder_name'].'.'.$module['module_route'];

        $this->module = $module;

        $this->middleware('permission:'.$module['permission_group'].'.manage', ['only' => ['index']]);
        $this->middleware('permission:'.$module['permission_group'].'.add', ['only' => ['create','store']]);
        $this->middleware('permission:'.$module['permission_group'].'.edit', ['only' => ['edit','update']]);
        $this->middleware('permission:'.$module['permission_group'].'.delete', ['only' => ['destroy']]);

        $mode_construct = '';
        if(!empty($request->post('combined_action'))) $mode_construct = $request->post('combined_action');
        else if(!empty($request->segment(3))) $mode_construct = $request->segment(3);

        if(in_array($mode_construct, array('activate', 'deactivate'))) $this->middleware('permission:'.$module['permission_group'].'.status', ['only' => ['action', 'bulk']]);
        if(in_array($mode_construct, array('delete'))) $this->middleware('permission:'.$module['permission_group'].'.delete', ['only' => ['action', 'bulk']]);
    }

    public function index(Request $request, DefaultModel $model){
        $carbon = new Carbon();
        $module = $this->module;
        $perpage = $request->perpage ?? $module['default_perpage'];
        if(!$request->perpage && !empty($request->cookie('perpage'))) $perpage = $request->cookie('perpage');
        
        $model_get = $model;
        if($model->getDelStatusColumn()) $model_get = $model_get->where($model->getDelStatusColumn(), '<', '1');
        
        if($model->getSortOrderColumn()) $model_get = $model_get->orderBy($model->getSortOrderColumn(), 'ASC');
        else $model_get = $model_get->latest();
        
        $query = $request->get('query') ?? '';
        if($query!='') $model_get = $model_get->where('name', 'LIKE', '%'.$query.'%'); 

        $data = $model_get->paginate($perpage)->onEachSide(2);

        $title_shown = 'Manage '.$module['main_heading'].'s';
        $folder = $this->folder;

        return view($module['main_view'].'.index', compact('data', 'model', 'carbon', 'module', 'perpage', 'folder', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function create(DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $action = URL::route($module['main_route'].'.store');
        $title_shown = 'Add '.$module['main_heading'];
        $method = 'POST';
        $mode = 'insert';

        return view($module['main_view'].'.cred', compact('module', 'model', 'action', 'method', 'mode', 'folder', 'title_shown'));
    }

    public function store(Request $request, DefaultModel $model){
        $module = $this->module;
        // dd($request->input());
        $request->validate([
            'name' =>[
            function($attribute, $value, $fail) use($request){
                if(!empty($request->input('expense_type_id'))&& !empty($request->input('name'))){
                    $fail("Either name or Expense Type is required you can't fill both of them");
                } else if(empty($request->input('expense_type_id')) && empty($request->input('name'))){
                    $fail("Either name or Expense Type is required");
                }
            }
            ],
            'expense_type_id' =>[
            function($attribute, $value, $fail) use($request){
                if(!empty($request->input('expense_type_id'))&& !empty($request->input('name'))){
                    $fail("Either name or Expense Type is required you can't fill both of them");
                } else if(empty($request->input('expense_type_id')) && empty($request->input('name'))){
                    $fail("Either name or Expense Type is required");
                }
            }
            ],

            'date'=>'required|date',
            'amount'=>'required|integer',
            'image' => 'image|mimes:jpg,bmp,png,jpeg,gif,avif,webp|max:2048'
        ]);
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        $user = $auth_user->id;
        $imageName = null;
        if(!empty($request->hasFile('image'))){
            $imageName= time().'.'.$request->image->getClientOriginalExtension();
            $image = $request->file('image');
            // dd($image);
            $image->move(public_path('upload/expense/'), $imageName);
        }
        if(!in_array(1,$roles)){
            $request->merge(['organization_id'=>$auth_user->organization_id, 'user_id'=>$user]);
        } else{
            $request->merge(['user_id'=>$user]);
        }

        $model->create(array_merge($request->all(),['image'=>$imageName]));

        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' created successfully.');
    }

    public function show($id, DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = $model->find($id);
        $title_shown = 'Show '.$module['main_heading'];
        $mode = 'show';
        
        return view($module['main_view'].'.show', compact('form_data', 'model', 'module', 'folder', 'title_shown', 'mode', 'id'));
    }

    public function edit($id, DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = $model->find($id);
        $action = URL::route($module['main_route'].'.update', $id);
        $title_shown = 'Edit '.$module['main_heading'];
        $method = 'PUT';
        $mode = 'edit';

        return view($module['main_view'].'.cred')->with(compact('form_data', 'model', 'module', 'action', 'method', 'mode', 'folder', 'title_shown', 'id'));
    }

    public function update(Request $request, $id, DefaultModel $model){
        $module = $this->module;
        $request->validate([
            'name' => empty($request->input('expense_type_id'))?'required':'nullable',
            'expense_type_id' =>empty($request->input('name'))? 'required':'nullable',
            'date'=>'required|date',
            'amount'=>'required|integer',
            'image' => 'image|mimes:jpg,bmp,png,jpeg,gif,avif,webp|max:2048'
        ]);

        $modelfind = $model->find($id);
        if(!empty($request->hasFile('image'))){
            $prev_img = public_path('upload/expense'.$modelfind->image);
            unlink($prev_img); // unlinking existing image
            $imageName=time().'.'.$request->image->getClientOriginalExtension();
            $image = $request->file('image');
            $image->move(public_path('upload/expense/'), $imageName);
            $request->merge(['image'=>$imageName]);
        }
        $modelfind->update($request->all());
    
        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' updated successfully');
    }

    public function action($mode, $id, DefaultModel $model){
        $module = $this->module;
        $err_type = $mode=='delete' ? 'info' : 'success';

        switch($mode){
            case 'activate':
                $model->postActivate($id);
            break;

            case 'deactivate':
                $model->postDeactivate($id);
            break;

            case 'delete':
                $model->postDelete($id);
            break;
        }

        return redirect()->route($module['main_route'].'.index')->with($err_type, $module['main_heading'].' '.$mode.'d successfully');
    }

    public function bulk(Request $request, DefaultModel $model){
        $module = $this->module;
        $token = $request->session()->token();
        $post = $request->post();

        if(!empty($post['btn_apply'])){
            if($token==$post['_token']){
                if(empty($post['combined_action'])){
                    return redirect()->route($module['main_route'].'.index')->with('error', 'Please select one action to perform.');
                } else if(is_null($request->post('row_check'))){
                    return redirect()->route($module['main_route'].'.index')->with('error', 'Please select atleast one checkbox to perform action.');
                } else{
                    $msg_type = $post['combined_action']=='delete' ? 'info' : 'success';

                    foreach($request->post('row_check') as $check_id){
                        switch($post['combined_action']){
                            case 'activate':
                                $model->postActivate($check_id);
                            break;

                            case 'deactivate':
                                $model->postDeactivate($check_id);
                            break;

                            case 'delete':
                                $model->postDelete($check_id);
                            break;
                        }
                    }

                    return redirect()->route($module['main_route'].'.index')->with($msg_type, $module['main_heading']."s ".ucfirst(strtolower($post['combined_action'])).( (substr($post['combined_action'], -1)=='e') ? 'd' : 'ed' )." Successfully.");
                }
            }
        }
    }
}
