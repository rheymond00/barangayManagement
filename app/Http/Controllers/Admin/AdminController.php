<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\Admin;
use App\Models\AdminsRole;
use Image;
use Illuminate\Support\Facades\Storage;


class AdminController extends Controller
{
    protected $guard;

    public function __construct()
    {
        $this->guard = Auth::guard('admin');
    }

    public function dashboard()
    {
        Session::put('page','dashboard');
        return view('admin.dashboard');
    }

    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $rules = [
                'email' => 'required|email|max:255',
                'password' => 'required|max:30'
            ];

            $customMessages = [
                'email.required' => "Email is required",
                'email.email' => "Valid Email is required",
                'password.required' => "Passwrod is required",

            ];

            $this->validate($request,$rules,$customMessages);

            if (Auth::guard('admin')->attempt(['email'=>$data['email'],'password'=>$data['password']])) {

                // remember Admin email and password with cookies
                if(isset($data['remember'])&&!empty($data['remember'])){
                    setcookie("email",$data['email'],time()+3600);
                    setcookie("password",$data['password'],time()+3600);
                }else{
                    setcookie("email"."");
                    setcookie("password"."");
                }

                // User authentication successful
                return redirect('admin/dashboard');
            } else {
                // User authentication failed
                return redirect()->back()->with("error_message", "Invalid Email or Password!");
            }
        }

        return view('admin.login');
    }

    public function logout(){
        Auth::guard('admin')->logout();
        return redirect('admin/login');
    }

    public function updatePassword(Request $request){
        Session::put('page','update-password');
        if($request->isMethod('post')){
            $data = $request->all();
            //Check if the current password is correct.
            if(Hash::check($data['current_pwd'],Auth::guard('admin')->user()->password)){
                 //Check if new password and confirm password are matching.
                if($data['new_pwd']==$data['verify_pwd']){
                    //Update new password.
                    Admin::where('id',Auth::guard('admin')->user()->id)->update(['password'=>bcrypt($data['new_pwd'])]);
                    return redirect()->back()->with('success_message','Paasword has been updated Succesfully!');
                }else{
                return redirect()->back()->with('error_message','New Password and Verify Password did not match!');
                }
            }else{
                return redirect()->back()->with('error_message','Your current password is Incorrect!');
            }
        }
        return view('admin.update_password');
    }

    public function checkCurrentPassword(Request $request){
        $data = $request->all();
        if(Hash::check($data['current_pwd'],Auth::guard('admin')->user()->password)){
            return "true";
        }else{
            return "false";
        }
    }

    public function updateDetails(Request $request)
{
    Session::put('page','update-details');
    if ($request->isMethod('post')) {
        $data = $request->all();

        $rules = [
            'admin_name' => 'required|regex:/^[\pL\s\-]+$/u|max:255',
            'admin_mobile' => 'required|numeric|digits:11',
            'admin_image' => 'image',
        ];

        $customMessages = [
            'admin_name.required' => "Name is required",
            'admin_name.regex' => "Valid Name is required",
            'admin_mobile.required' => "Mobile is required",
            'admin_mobile.numeric' => "Valid mobile number is required",
            'admin_mobile.digits' => "11 Digits mobile number is required",
            'admin_image.image' => "Valid image is required",
        ];

        $this->validate($request, $rules, $customMessages);

        // Check if a new image is uploaded
        if ($request->hasFile('admin_image')) {
            $image_tmp = $request->file('admin_image');

            // Use a unique name for the image
            $imageName = rand(111, 99999) . '.' . $image_tmp->getClientOriginalExtension();

            // Save the image to the public directory
            $image_tmp->move(public_path('admin/images/photos'), $imageName);
        } else {
            // No new image uploaded, use the current image
            $imageName = !empty($data['current_image']) ? $data['current_image'] : "";
        }

        // Update Admin Details
        Admin::where('email', Auth::guard('admin')->user()->email)->update([
            'name' => $data['admin_name'],
            'mobile' => $data['admin_mobile'],
            'image' => $imageName,
        ]);

        return redirect()->back()->with('success_message', 'Admin Details has been updated successfully!');
    }

    return view('admin.update_details');
}

public function subadmins(){
    Session::put('page','subadmins');
    $subadmins = Admin::where('type','subadmin')->get();
    return view('admin.subadmins.subadmins')->with(compact('subadmins'));
}
public function updateSubadminStatus(Request $request, Admin $subadmin){
        if($request->ajax()){
            $data = $request->all();
            
            // Check if the 'status' key exists in the $data array
            if(isset($data['status'])) {
                // Check the value of 'status' key and toggle it
                $status = ($data['status'] == "Active") ? 0 : 1;
                
                // Update the status of the Sub Admin
                Admin::where('id', $data['subadmin_id'])->update(['status' => $status]);
                
                // Return JSON response with updated status and subadmin ID
                return response()->json(['status' => $status, 'subadmin_id' => $data['subadmin_id']]);
            } else {
                // If 'status' key is not found, return an error response
                return response()->json(['error' => 'Status key not found in request.'], 400);
            }
        }
    }

    public function addEditSubadmin(Request $request, $id=null)
    {
        // Session::put('page','cms-pages');
        if($id==""){
            $title = "Add Subadmin";
            $subadmindata = new Admin;
            $message = "Subadmin added succesfully!";
        }else{
            $title = "Edit Subadmin";
            $subadmindata = Admin::find($id);
            $message = "Subadmin updated succesfully!";
        }
        if($request->isMethod('post')){
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;

            if($id==""){
                $subadminCount = Admin::where('email',$data['email'])->count();
                if($subadminCount>0){
                    return redirect()->back()->with('error_message','Subadmin alerady exists!');
                }
            }
            //Subadmin validations
            $rules =[
                'name' => 'required',
                'mobile' => 'required|numeric',
                'subadmin_image' => 'image'
            ];
            $customMessages =[
                'name.required' => 'Name is required',
                'mobile.required' => 'Mobile is required',
                'mobile.numeric' => 'Valid Mobile is required',
                'subadmin_image.image' => 'Valid Image is required',
            ];
            $this->validate($request,$rules,$customMessages);

             // Check if a new image is uploaded
            if ($request->hasFile('subadmin_image')) {
                $image_tmp = $request->file('subadmin_image');

                // Use a unique name for the image
                $imageName = rand(111, 99999) . '.' . $image_tmp->getClientOriginalExtension();

                // Save the image to the public directory
                $image_tmp->move(public_path('admin/images/photos'), $imageName);
            } else {
                // No new image uploaded, use the current image
                $imageName = !empty($data['current_image']) ? $data['current_image'] : "";
            }

            $subadmindata->image = $imageName;
            $subadmindata->name = $data['name'];
            $subadmindata->mobile = $data['mobile'];
            if($id==""){
                $subadmindata->email = $data['email'];
                $subadmindata->type = 'subadmin';
            }
            if($data['password']!=""){
                $subadmindata->password = bcrypt($data['password']);
            }
            $subadmindata->save();
            return redirect('admin/subadmins')->with('success_message',$message);
        }
        return view('admin.subadmins.add_edit_subadmin')->with(compact('title','subadmindata'));
    }

    public function deleteSubadmin($id)
    {
        //Delete CMS Page
        Admin::where('id',$id)->delete();
        return redirect()->back()->with('success_message','Subadmin deleted successfully!');
    }

    public function updateRole($id,Request $request){
        $title = "Update Subadmin Roles/Permision";
       

        if ($request->isMethod('post')){
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;  

            // Delete all erlier roles for Subadmin
            AdminsRole::where('subadmin_id',$id)->delete();

            // Add new rules for Subadmin
            if(isset($data['cms_pages']['view'])){
                $cms_pages_view = $data['cms_pages']['view'];
            }else{
                $cms_pages_view = 0;
            }

            if(isset($data['cms_pages']['edit'])){
                $cms_pages_edit = $data['cms_pages']['edit'];
            }else{
                $cms_pages_edit = 0;
            }

            if(isset($data['cms_pages']['full'])){
                $cms_pages_full = $data['cms_pages']['full'];
            }else{
                $cms_pages_full = 0;
            }

            $role = new AdminsRole;
            $role->subadmin_id = $id;
            $role->module = 'cms_pages';
            $role->view_access = $cms_pages_view;
            $role->edit_access = $cms_pages_edit;
            $role->full_access = $cms_pages_full;
            $role->save();

            $message = "Subadmin Roles updated successfully!";
            return redirect()->back()->with('success_message',$message);
        }
        return view('admin.subadmins.update_roles')->with(compact('title','id'));
    }





}

