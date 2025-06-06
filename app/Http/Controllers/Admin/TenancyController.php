<?php
namespace App\Http\Controllers\Admin;

use App\Models\Tenant_Master as DefaultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;
use PDF;
use Illuminate\SUpport\Facades\Auth;
use Carbon\Carbon;
use App\Jobs\WhatsappAPI;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenancyController extends Controller{
    public $module = array(
        'module_view' => 'tenancy',
        'module_route' => 'tenancy',
        'permission_group' => 'tenancy',
        'main_heading' => 'Tenancy',
        'default_perpage' => 10
    );

    public $whatsapp_api = array(
        'whatsapp_api_url' => 'https://api.gupshup.io/wa/api/v1/template/msg',
        'channel' => 'whatsapp',
        'src_name' => 'GVTGH9',
        'type' => 'document',
        'template_id' => 'c376f4e4-2743-4eb9-8cdb-2648f7457d22',
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
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        if(!in_array(1, $roles)){
            $organization_id = $auth_user->organization_id;
            $model_get = $model_get->where('organization_id', $organization_id);
        }
        if($model->getDelStatusColumn()) $model_get = $model_get->where($model->getDelStatusColumn(), '<', '1');
        
        if($model->getSortOrderColumn()) $model_get = $model_get->orderBy($model->getSortOrderColumn(), 'ASC');
        else $model_get = $model_get->latest();
        
        $query = $request->get('query') ?? '';
        if($query!='') $model_get = $model_get->where('name', 'LIKE', '%'.$query.'%'); 

        $data = $model_get->paginate($perpage)->onEachSide(2);

        $title_shown = 'Manage Tenancies';
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

        return view($module['main_view'].'.cred2', compact('module', 'model', 'action', 'method', 'mode', 'folder', 'title_shown'));
    }

    public function store(Request $request, DefaultModel $model){
        $module = $this->module;
        $master = [];
        // $a = $request->input('document');
        // dd($request->input());
        $rules = [
            'member_id'=> 'required',
            'type' => 'required|in:family,individual',
            'tenant_member' => 'required',
            'start_date' => 'required',
            'rent_agreement' => 'mimes:pdf,txt,doc,docx|max:2048',
            // 'police_verification' => 'mimes:pdf,txt,doc,docx|max:2048',
            'undertaking' => 'file|mimes:pdf,txt,doc,docx|max:2048'
            // 'acceptance' => 'file|mimes:pdf,txt,doc,docx|max:2048'
        ];
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        if(in_array(1,$roles)){
            $rules['organization_id'] = 'required';
        }

        if($request->input('type') == 'family'){
            $rules['row_data.*.name'] = 'required';
            $rules['row_data.*.age'] = 'required|numeric';
            $rules['row_data.*.gender'] = 'required|in:male,female,others';
        }
        $rules['police_verification.*.police_verification'] = 'mimes:pdf,txt,doc,docx|max:2048';

        $request->validate($rules);
        // dd(1);
        if(in_array(1,$roles)){
            $master['organization_id'] = $request->input('organization_id');
        } else{
            $master['organization_id'] = $auth_user->organization_id;
        }
        $master['member_id'] = $request->input('member_id');
        $member = \App\Models\Members::where([['status','>','0'],['delstatus','<','1'],['id','=',$master['member_id']]])->first();
        $master['member_name'] = $member->name;
        $master['type'] = $request->input('type');
        $master['start_date'] = $request->input('start_date');
        // $master['photo'] = $request->input('photo');
        // $master['document'] = $request->input('document');
        // $master['rent_agreement'] = $request->input('rent_agreement');
        // $master['police_verification'] = $request->input('police_verification');
        // $master['undertaking'] = $request->input('undertaking');
        // $master['acceptance'] = $request->input('acceptance');
        
        if($request->hasFile('rent_agreement')){
            $image = $request->file('rent_agreement');
            $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->rent_agreement->getClientOriginalExtension();
            $image->move(public_path('upload/tenant'), $imageName);
            $master['rent_agreement'] = $imageName;
            $master['rent_agreement_name'] = $image->getClientOriginalName();
        }
        // if($request->hasFile('police_verification')){
        //     $image = $request->file('police_verification');
        //     $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->police_verification->getClientOriginalExtension();
        //     $image->move(public_path('upload/tenant'), $imageName);
        //     $master['police_verification'] = $imageName;
        //     $master['police_verification_name'] = $image->getClientOriginalName();
        // }
        if($request->hasFile('undertaking')){
            $image = $request->file('undertaking');
            $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->undertaking->getClientOriginalExtension();
            $image->move(public_path('upload/tenant'), $imageName);
            $master['undertaking'] = $imageName;
            $master['undertaking_name'] = $image->getClientOriginalName();
        }
        if($request->hasFile('acceptance')){
            $image = $request->file('acceptance');
            $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->acceptance->getClientOriginalExtension();
            $image->move(public_path('upload/tenant'), $imageName);
            $master['acceptance'] = $imageName;
            $master['acCeptance_name'] = $image->getClientOriginalName();
        }
        $master_data = $model->create($master);
        $variant = [];
        $variant['tenant_master_id'] = $master_data->id;
        
        
        foreach($request->input('tenant_member') as $tm){
            $ten_vari =[];
            $ten_vari['tenant_master_id'] = $master_data->id;
            if($request->hasFile("police_verification.$tm.police_verification")){
                $image = $request->file("police_verification.$tm.police_verification");
                $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->file("police_verification.$tm.police_verification")->getClientOriginalExtension();
                $image->move(public_path('upload/tenant'), $imageName);
                $ten_vari['police_verification'] = $imageName;
                $ten_vari['police_verification_name'] = $image->getClientOriginalName();
            }
            $tenant_member = \App\Models\Tenant_Variant::where('id',$tm)->update($ten_vari);
            
            $id_tenant = $tm;
        }
        if($request->input('type') == 'family'){
            foreach($request->input('row_data') as $index => $row_data){
                $variant['organization_id'] = $master['organization_id'];
                $variant['tenant_variant_id'] = $id_tenant;
                $variant['name'] =  $row_data['name'];
                $variant['age'] = $row_data['age'];
                $variant['gender'] = $row_data['gender'];
                $variant['isfamily'] = '1';
                // $variant['photo'] = $row_data['photo'];
                // $variant['document'] = $row_data['document'];
                
                // if($request->hasFile("row_data.$index.document")){
                //     $image = $request->file("row_data.$index.document");
                //     $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->file("row_data.$index.document")->getClientOriginalExtension();
                //     $image->move(public_path('upload/tenant'), $imageName);
                //     $variant['document'] = $imageName;
                //     $variant['document_name'] = $image->getClientOriginalName();
                // }
                $variant_data = \App\Models\Tenant_Variant::create($variant);
            }
        }
        $this->generate_file_($variant['tenant_master_id']);

        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' created successfully.');
    }

    public function show($id, DefaultModel $model){
        // $module = $this->module;
        // $folder = $this->folder;
        // $form_data = $model->find($id);
        // $title_shown = 'Show '.$module['main_heading'];
        // $mode = 'show';
        
        // return view($module['main_view'].'.show', compact('form_data', 'model', 'module', 'folder', 'title_shown', 'mode', 'id'));
    }

    public function edit($id, DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = $model->find($id);

        $tenant_data = \App\Models\Tenant_Variant::where([['delstatus','<','1'],['status','>','0'], ['tenant_master_id', '=',$id],['isfamily','=','0']])->pluck('id')->toArray();
        $form_data->tenant_member = $tenant_data;
        $action = URL::route($module['main_route'].'.update', $id);
        $title_shown = 'Edit '.$module['main_heading'];
        $method = 'PUT';
        $mode = 'edit';
        $dataArray = \App\Models\Tenant_Variant::where([['status','>','0'],['delstatus','<','1'],['tenant_master_id','=',$form_data->id],['isfamily','=','1']])->get()->toArray();
        $data2Array = \App\Models\Tenant_Variant::where([['status','>','0'],['delstatus','<','1'],['tenant_master_id','=',$form_data->id],['isfamily','=','0']])->get()->toArray();
        return view($module['main_view'].'.cred2')->with(compact('form_data', 'dataArray', 'model', 'module', 'action', 'data2Array', 'method', 'mode', 'folder', 'title_shown', 'id'));
    }

    public function update(Request $request, $id, DefaultModel $model){
        $module = $this->module;
        // dd($request->input());
        $rules = [
            'member_id' => 'required',
            'type' => 'required|in:family,individual',
            'start_date' => 'required',
            'tenant_member' => 'required'
        ];
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id','name')->toArray();

        if($request->input('type') == 'individual'){
            $rules['row_data.*.name'] = 'required';
            $rules['row_data.*.age'] = 'required|numeric';
            $rules['row_data.*.gender'] = 'required|in:male,female,others';
        }

        $request->validate($rules);

        $master['type'] = $request->input('type');
        $master['mobile_number'] = $request->input('mobile_number');
        $master['member_id'] = $request->input('member_id');
        $member = \App\Models\Members::where([['status','>','0'],['delstatus','<','1'],['id','=',$master['member_id']]])->first();
        $master['member_name'] = $member->name;

        // $master['photo'] = $request->input('photo');
        // $master['document'] = $request->input('document');
        // $master['rent_agreement'] = $request->input('rent_agreement');
        // $master['police_verification'] = $request->input('police_verification');
        // $master['undertaking'] = $request->input('undertaking');
        // $master['acceptance'] = $request->input('acceptance');
        $modelfind = $model->where('id',$id)->first();
        if($request->hasFile('rent_agreement')){
            $image = $request->file('rent_agreement');
            if ($modelfind->rent_agreement && file_exists(public_path('upload/tenant/' . $modelfind->rent_agreement))) {
                unlink(public_path('upload/tenant/' . $modelfind->rent_agreement));
            }
            $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->rent_agreement->getClientOriginalExtension();
            $image->move(public_path('upload/tenant'), $imageName);
            $master['rent_agreement'] = $imageName;
            $master['rent_agreement_name'] = $image->getClientOriginalName();
        }
        if($request->hasFile('police_verification')){
            $image = $request->file('police_verification');
            if ($modelfind->police_verification && file_exists(public_path('upload/tenant/' . $modelfind->police_verification))) {
                unlink(public_path('upload/tenant/' . $modelfind->police_verification));
            }
            $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->police_verification->getClientOriginalExtension();
            $image->move(public_path('upload/tenant'), $imageName);
            $master['police_verification'] = $imageName;
            $master['police_verification_name'] = $image->getClientOriginalName();
        }
        if($request->hasFile('undertaking')){
            $image = $request->file('undertaking');
            if ($modelfind->undertaking && file_exists(public_path('upload/tenant/' . $modelfind->undertaking))) {
                unlink(public_path('upload/tenant/' . $modelfind->undertaking));
            }
            $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->undertaking->getClientOriginalExtension();
            
            $image->move(public_path('upload/tenant'), $imageName);
            $master['undertaking'] = $imageName;
            $master['undertaking_name'] = $image->getClientOriginalName();
        }
        if($request->hasFile('acceptance')){
            $image = $request->file('acceptance');
            if ($modelfind->acceptance && file_exists(public_path('upload/tenant/' . $modelfind->acceptance))) {
                unlink(public_path('upload/tenant/' . $modelfind->acceptance));
            }
            $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->acceptance->getClientOriginalExtension();
            
            $image->move(public_path('upload/tenant'), $imageName);
            $master['acceptance'] = $imageName;
            $master['acceptance_name'] = $image->getClientOriginalName();
        }
        $master_data = $modelfind->update($master);
        $vari_model = \App\Models\Tenant_Variant::where([['status','>','0'], ['delstatus','<','1'], ['tenant_master_id', '=', $id]])->update(['tenant_master_id'=>null]);
        $variant = [];
        $variant['tenant_master_id'] = $id;
        foreach($request->input('tenant_member') as $tm){
            $ten_vari =[];
            $ten_vari['tenant_master_id'] = $id;
            if($request->hasFile("police_verification.$tm.police_verification")){
                $image = $request->file("police_verification.$tm.police_verification");
                $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->file("police_verification.$tm.police_verification")->getClientOriginalExtension();
                $image->move(public_path('upload/tenant'), $imageName);
                $ten_vari['police_verification'] = $imageName;
                $ten_vari['police_verification_name'] = $image->getClientOriginalName();
            }
            $tenant_member = \App\Models\Tenant_Variant::where('id',$tm)->update($ten_vari);
            
            $id_tenant = $tm;
        }
        $var_model = \App\Models\Tenant_Variant::where([['status','>','0'], ['delstatus','<','1'], ['isfamily','=','1'], ['tenant_master_id', '=',$id]])->update(['tenant_master_id' => null]);
        if($request->input('type') == 'family'){
            foreach($request->input('row_data') as $index => $row_data){
                if($index && $variant_model = \App\Models\Tenant_Variant::find($index)){
                    $variant['tenant_variant_id'] = $id_tenant;
                    $variant['name'] =  $row_data['name'];
                    $variant['age'] = $row_data['age'];
                    $variant['gender'] = $row_data['gender'];
                    $variant['isfamily'] = '1';
                    // $variant['photo'] = $row_data['photo'];
                    // $variant['document'] = $row_data['document'];
                    $variant_model->update($variant);
                } else{
                    $variant['tenant_variant_id'] = $id_tenant;
                    $variant['name'] =  $row_data['name'];
                    $variant['age'] = $row_data['age'];
                    $variant['gender'] = $row_data['gender'];
                    $variant['isfamily'] = '1';
                    // $variant['photo'] = $row_data['photo'];
                    // $variant['document'] = $row_data['document'];
                    $variant_data = \App\Models\Tenant_Variant::create($variant);
                }
            }
        }
        $this->generate_file_($id);
        // $modelfind = $model->find($id);
        // $modelfind->update($request->all());
    
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

    public function generate_file_($id){
        $module = $this->module;
        $model_var = \App\Models\Tenant_Variant::where([['status','>','0'], ['delstatus', '<','1'],['tenant_master_id','=',$id]])->get()->toArray();
        $model = \App\Models\Tenant_Master::find($id);
        $organization = \App\Models\Organization::find($model->organization_id);
        $member = \App\Models\Members::find($model->member_id);

        $data =[];
        $data['org_name'] = $organization->name;
        $data['unit_number'] = $member->unit_number;
        $data['owner_name'] = $model->member_name;
        $data['start_date'] = $model->start_date;
        $profile_data = [];
        $family_data = [];
        $pvc = 0;
        foreach($model_var as $mv){
            if($mv['isfamily']=='0'){
                $pddata['name'] = $mv['name'];
                $pddata['gender'] = $mv['gender'];
                $pddata['age'] = $mv['age'];
                $pddata['address'] = $mv['locality'].', '.$mv['city'].', '.$mv['state'].'.';
                $pddata['photo'] = $mv['photo'];
                $pddata['police_verification'] = !empty($mv['police_verification']) ? 'Submitted':'Pending';
                $profile_data[] = $pddata;
            } else{
                $famdata['name'] = $mv['name'];
                $famdata['age'] = $mv['age'];
                $famdata['gender'] = $mv['gender'];
                $family_data[] = $famdata;
            }
        }
        $data['profile_data'] = $profile_data;
        $data['family_data'] = $family_data;
        $data['rent_agreement'] = !empty($model->rent_agreement) ? 'Submitted':'Pending';
        $data['undertaking'] = !empty($model->undertaking) ? 'Submitted':'Pending';
        // $data['police_verification'] = ($pvc==0) ? 'Submitted':'Pending';
        $data['acceptance'] = !empty($model->acceptance) ? 'Submitted':'Pending';

        $pdf = PDF::loadView('include.make_tenant_doc', $data);

        $mpdf = $pdf->getMpdf();
         $width = 10; // Adjust the width of the watermark image
        $height = 10; // Adjust the height of the watermark image

        // Get the dimensions of the page
        $pageWidth = $mpdf->w;
        $pageHeight = $mpdf->h;

        // Calculate the position to center the image
        $x = ($pageWidth - $width) / 8;
        $y = ($pageHeight - $height) / 8;

        $mpdf->SetWatermarkImage(public_path('dashboard/img/Logo-GVT.png'), 0.1, '', array($x, $y), true);
        $now=Carbon::now();
        $file_name = $id.'-'.$now->format('Y-m-d-H-i-s');
        $mpdf->showWatermarkImage = true;
        $mpdf->Output(public_path("upload/tenant/{$file_name}.pdf"), \Mpdf\Output\Destination::FILE);
        $model1 = \App\Models\Tenant_Master::find($id);
        $model1->update(['pdf_file'=> $file_name]);
        // $name = $file_name;
        // return view('include.make_tenant_doc',compact('data','profile_data','family_data'));
        return $this->show_pdf($id);
    }

    public function show_pdf($id){
        $model = \App\Models\Tenant_Master::find($id);
        $name = $model->pdf_file;
        return view('include.show_tenant',compact('name'));
    }

    public function get_member(Request $request){
        $id = $request->input('member_id');
        $member = \App\Models\Members::where([['status','>','0'], ['delstatus','<','1'], ['id','=',$id]])->first();
        return $member->name;
    }

    public function send_tenancy_msg(Request $request){
        // id will come in request
        $mem_id = null;
        $id = $request->input('id');
        $api = $this->whatsapp_api;
        $model = \App\Models\Tenant_Master::find($id);
        $member = \App\Models\Members::find($model->member_id);
        $tenant = \App\Models\Tenant_Variant::where([['status','>','0'], ['delstatus', '<', '1'], ['tenant_master_id', '=', $id]])->pluck('name')->toArray();
        $destination = $member->mobile_number;
        $message = '';
        $names = implode(',',$tenant);

        $category = 'tenancy';
        $file_name = $model->pdf_file;
        $message = array(
            'type' => $api['type'],
            $api['type'] => array(
                'link' => url('/upload/tenant/'.$file_name.'.pdf'),
                // 'link' => 'https://gvtapp.netpyx.org/supanel/journal_entry/921/show',

                'filename' => 'Document'
            )
        );
        $helpers = new \App\Helpers\helpers();
        $org_id = $model->organization_id;
        $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','tenancy'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();

        $data = [
            'name' =>$names,
            'unit_number' => $member->unit_number
        ];
        
        $templ_json = $helpers->make_temp_json($temp->id, $data);
        $message = json_encode($message, true);

        dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json, $category, $mem_id) )->onConnection('sync');
        $model->counter++;
        $model->save();
    }
}
