<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
class CmsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Session::put('page','cms-pages');
        $CmsPages = CmsPage::get()->toArray();
        // dd($CmsPages);
        return view('admin.pages.cms_pages')->with(compact('CmsPages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CmsPage $cmsPage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id=null)
    {
        Session::put('page','cms-pages');
        if($id==""){
            $title = "Add CMS Page";
            $cmspage = new CmsPage;
            $message = "CMS Page added succesfully";
        }else{
            $title = "Edit CMS Page";
            $cmspage = CmsPage::find($id);
            $message = "CMS Page updated succesfully";
        }
        if($request->isMethod('post')){
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;

            //cms pages validations
            $rules =[
                'title' => 'required',
                'url' => 'required',
                'description' => 'required',
            ];
            $customMessages =[
                'title.required' => 'Page Title is required',
                'url.required' => 'Page URL is required',
                'description.required' => 'Page Description is required',
            ];
            $this->validate($request,$rules,$customMessages);

            $cmspage->title = $data['title'];
            $cmspage->url = $data['url'];
            $cmspage->description = $data['description'];
            $cmspage->meta_title = $data['meta_title'];
            $cmspage->meta_description = $data['meta_description'];
            $cmspage->meta_keywords = $data['meta_keywords'];
            $cmspage->status = 1;
            $cmspage->save();
            return redirect('admin/cms-pages')->with('success_message',$message);
        }
        return view('admin.pages.add_edit_cmspage')->with(compact('title','cmspage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CmsPage $cmsPage)
    {
        if($request->ajax()){
            $data = $request->all();
            
            // Check if the 'status' key exists in the $data array
            if(isset($data['status'])) {
                // Check the value of 'status' key and toggle it
                $status = ($data['status'] == "Active") ? 0 : 1;
                
                // Update the status of the CMS page
                CmsPage::where('id', $data['page_id'])->update(['status' => $status]);
                
                // Return JSON response with updated status and page ID
                return response()->json(['status' => $status, 'page_id' => $data['page_id']]);
            } else {
                // If 'status' key is not found, return an error response
                return response()->json(['error' => 'Status key not found in request.'], 400);
            }
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //Delete CMS Page
        CmsPage::where('id',$id)->delete();
        return redirect()->back()->with('success_message','CMS Page delete successfully!');
    }
}
