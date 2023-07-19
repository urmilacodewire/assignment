<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Video;
use DataTables;

class VideocamController extends Controller
{
   
    public function index(Request $request)
    {
        if ($request->ajax()) {
  
            $data = Video::all();
  
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->editColumn('video', function($row) {
                        $url = url('webcamVideo/'.$row->video);
                        return $video = '<video width="250" height="150" controls>  <source src="'.$url.'" >  </video>';
                        })
                    ->addColumn('action', function($row){
   
                           $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm editvideo" id="editvideo">Edit</a>';
   
                           $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteVideo" id="deleteVideo">Delete</a>';
    
                            return $btn;
                    })
                    ->rawColumns(['video','action'])
                    ->make(true);
        }
        
        return view('index');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        // $request->validate([
        //                     'title'=> 'required',
        //                     'name'=> 'required',
        //                     'video'=> 'required|mimes:webm' 
        // ]);

        if($request->isMethod('post')){
            $file = '';
            if($request->hasFile('video')){
                $temp_video = $request->file('video');
                if($temp_video->isValid()){
                    $file = time().'cam-video'.rand('1000','9999').'.'.$request->video->getClientOriginalExtension();
                    $request->video->move(public_path('webcamVideo'),$file);
                }
            }else{
                $data['video'] = "" ;
            }
            Video::updateOrCreate(
                ['id'=>$request->videoId],
                [
                    'title'=> $request->title,
                    'name'=> $request->name,
                    'video'=> $file,
                ]);
            $data['message'] = "Video is uploaded successfully.";    
        }else{
            $data['message'] = "Video is not uploaded.";
        }
        return response()->json($data);
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $video = Video::find($id);
        return response()->json($video);
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        $videodata = Video::findOrFail($id);
        Storage::delete("public/webcamVideo/". $videodata->video);
        //unlink($storagePath.$file);
        $videodata->delete();
        $data['message'] = "Video is Deleted successfully.";   
        return response()->json($data); 
    }
}
