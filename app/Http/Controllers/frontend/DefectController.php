<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Defect;
use App\Models\Substations;
use App\Models\DefectImages;
use App\Models\User;
use App\Http\Requests\DefectForm;
use Session;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class DefectController extends Controller
{
    public function index()
    {
        $pagename = "Defect";
        $user_id = Auth::user()->id;
        $defect = Defect::select('defects.*', 'substations.substation', 'defect_images.incident_image')
            ->leftJoin('substations', 'substations.substation_id', '=', 'defects.substation_id')
            ->leftJoin('defect_images', function ($join) {
                $join->on('defect_images.defect_id', '=', 'defects.defect_id')
                    ->whereRaw('defect_images.defect_image_id = (select min(defect_image_id) from defect_images as di where di.defect_id = defect_images.defect_id)');
            })
            ->where('defects.created_by',$user_id)
            ->latest()
            ->get();

        return view('frontend.defect.index',compact('pagename','defect'));
    } 

    public function create()
    {
        $pagename = 'Defect Create';
       
        $substations = Substations::select('substation_id','substation')->get();
        return view('frontend.defect.create',compact('pagename','substations'));
    }

    public function store(DefectForm $request){
        if($request->ajax()){
            return true;
         }

        $user = Auth::user()->id;
        $useremail = Auth::user();
        $defect = new Defect();  
        $defect->substation_id = $request->substation_id;
        $defect->incident_reason = $request->incident_reason;
        $defect->created_by = $user;
        $defect->save();

        $defectId = $defect->defect_id;

       if ($request->hasfile('incident_image')) {
            $i = 1;
            foreach ($request->file('incident_image') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename1 = rand() . '.' . $extension;
                $file->move('public/img/defect', $filename1);

                $images = new DefectImages();
                $images->incident_image = $filename1;
                $images->defect_id = $defectId;
                $images->save();
                $i++;
            }
        }

        // $data_array['email'] = $useremail->email;
        
        // $mail = Mail::send('email.defect_request', $data_array, function ($message) use ($data_array) {
            
        //     $message->to('pl.ipopsolution@gmail.com')->subject('Defect Request Receive');
        // }); 

        $data_array = [
            'email' => $useremail->email,
            'name' => $useremail->name,
        ];

        try {
            Mail::send('email.defect_request', $data_array, function ($message) use ($data_array) {
                $message->from('notifications@akuntha.in', 'Akuntha')
                        ->to('pl.ipopsolution@gmail.com')
                        ->subject('Defect Request Receive !');
            });
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

      //   $data = array('name'=>"Virat Gandhi");
   
      // Mail::send(['text'=>'email.defect_request'], $data, function($message) {
      //    $message->to('pl.ipopsolution@gmail.com', 'Tutorials Point')->subject
      //       ('Laravel Basic Testing Mail');
      //    $message->from('vorabhargav2002@gmail.com','Virat Gandhi');
      // });
         // $data_array['email'] = $useremail->email;

         //    $mail = Mail::send('email.defect_request', $data_array, function ($message) use ($data_array) {
         //        $message->from('vorabhargav2002@gmail.com', 'Bhargav'); 
         //        $message->to('orders@designmykitchen.cloud')->subject('Enquiry Brochure');
         //    });

        // $data_array = [
        //         'email' => $useremail->email,
               
        //     ];
        //  try {
        //     Mail::send('email.defect_request', $data_array, function ($message) use ($data_array) {
        //         $message->from('vorabhargav2002@gmail.com')
        //                 ->to('pl.ipopsolution@gmail.com')
        //                 ->subject('Registration Confirmation and Status Update');
        //     });
        // } catch (\Exception $e) {
        //     dd($e->getMessage());
        // }
         
         return redirect('defect/index')->with('success','Defect Added Successfully');
           
        }

       public function destroy($id)
    {
        $defect = Defect::find($id);

        if ($defect->delete()) {
            DB::table('defect_images')->where('defect_id', $id)->delete();
            Session::flash('success', 'Defect deleted successfully');
            return response()->json(['message' => 'Defect deleted successfully']);
        } else {
            Session::flash('fail', 'Something went wrong. Please try again.');
            return response()->json(['message' => 'Something went wrong. Please try again.']);
        }
    }
    
    public function edit($id)
    {

        $pagename = 'Defect Edit';
        $defect = Defect::find($id);
        $defectImg = DefectImages::select('incident_image','defect_image_id')->where('defect_id',$id)->get();
        $substations = Substations::select('substation_id','substation')->get();

        return view('frontend.defect.edit',compact('pagename','defect','substations','defectImg'));

    }
    public function update(DefectForm $request,$id)
    { 
        if($request->ajax())
        {
             return true;
        }
 
        $defect = Defect::find($id);

        $defect->substation_id = $request->substation_id;
        $defect->incident_reason = $request->incident_reason;
        $defect->save();

        // $defectId = $defect->defect_id;

       if ($request->hasfile('incident_image')) {
        foreach ($request->file('incident_image') as $file) {
            $extension = $file->getClientOriginalExtension();
            $filename1 = rand() . '.' . $extension;
            $file->move('public/img/defect', $filename1);

 
            $images = new DefectImages();
            $images->incident_image = $filename1;
            $images->defect_id = $defect->defect_id;
            $images->save();
           }
        }
       return redirect('defect/index')->with('success','Defect Updated Successfully');      
    }
    public function imageDelete($id)
    {
        if (!empty($id)) {

            DB::table('defect_images')->where('defect_image_id', $id)->delete();
            echo 'true';
        } else {
            echo 'false';
        }
    }
}