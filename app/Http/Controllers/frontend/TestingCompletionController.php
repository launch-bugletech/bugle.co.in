<?php

namespace App\Http\Controllers\frontend;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\TestingCompletionForm;
use App\Models\Substations;
use App\Models\AddEquipment;
use App\Models\AddTesting;
use App\Models\TestingCompletion;
use App\Models\TestingCompletionEquipment;
use App\Models\TestingCompletionTestingType;
use Illuminate\Support\Facades\Auth;
use Hash;
use DB;
use Session;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class TestingCompletionController extends BaseController {
   
    public function index() {
        $pagename = "Testing Completion Index";
        $created_by = Auth::user()->id;
        // $TestingCompletion = TestingCompletion::select('testing_completion.*','substations.substation')
        // ->leftjoin('substations','testing_completion.substation_id','substations.substation_id')
        // ->orderBy('created_at', 'desc')
        // ->where('testing_completion.created_by',$user_id)
        // ->get();
        $TestingCompletion = TestingCompletion::select('testing_completion.*','substations.substation')
        ->leftjoin('substations','substations.substation_id','testing_completion.substation_id')
        ->where('testing_completion.created_by',$created_by)
        ->where('testing_completion.request_send','Yes')
        ->orderBy('testing_completion.created_at', 'desc')
        ->get();
       //dd($TestingRequest);
		return view('frontend.testing_completion.index',compact('pagename','TestingCompletion'));
	}

    public function create()
    {
        $pagename = 'Create Completion Request';
        $Substations = Substations::all();
        $EquipmentAll = AddEquipment::all();
        $TestingAll = AddTesting::all();
       
        return view('frontend.testing_completion.create',compact('pagename','Substations','EquipmentAll','TestingAll'));
    }

    public function store(TestingCompletionForm  $request){
        if($request->ajax()){
            return true;
        }
        $useremail = Auth::user();
        if(Auth::user() != ""){
            $created_by = Auth::user()->id;
        }else{
            $created_by =1; 
        }
        $TestingCompletion= new TestingCompletion();
        // $TestingRequest->equipment_type = implode(',',$request->equipment_type);
        //dd($TestingRequest->equipment_type);
        // $TestingRequest->testing_type = implode(',',$request->testing_type);
        $TestingCompletion->substation_id = $request->substation_id;
        $TestingCompletion->testing_time = $request->testing_time;
        $TestingCompletion->testing_agency = $request->testing_agency;
        $TestingCompletion->comments = $request->comments;
        $TestingCompletion->testing_year =$request->input('testing_year');

        $TestingCompletion->created_by = $created_by;
        $TestingCompletion->save();
        
       
        $testing_completion_id = $TestingCompletion->testing_completion_id;
        return redirect('/testing-completion/add-equipment/'.$testing_completion_id)->with('success','Completion Testing Report added Successfully');

        // return redirect('/testing-request/index')->with('success','Testing Request send successfully');
        
        }
     public function add_equipment($id){
            $pagename = "Material Request";
            $equipment = AddEquipment::all();
            return view('frontend.testing_completion.add_equipment',compact('pagename','id','equipment'));
     }
     public function store_equipment(Request $request,$id){
       
        $request->validate([
            'add_equipment_id' => 'required',
            
           ]);
        $user_id=Auth::user()->id;
        $testingRequestEquipment=TestingCompletionEquipment::where('testing_completion_id',$id)
        ->where('add_equipment_id',$request->add_equipment_id)
        ->first();
        if($testingRequestEquipment)
        {
            $testingRequestEquipment->testing_completion_id = $id;
            $testingRequestEquipment->add_equipment_id = $request->add_equipment_id;
           $testingRequestEquipment->save();
        }
        else
        {
         
           $testingRequestEquipment = new TestingCompletionEquipment();  
           $testingRequestEquipment->testing_completion_id = $id;
           $testingRequestEquipment->add_equipment_id = $request->add_equipment_id;
           $testingRequestEquipment->created_by = $user_id;
           $testingRequestEquipment->save();
        }
      
        return redirect('/testing-completion/create-testing-type/'.$id);
        
    }
    public function create_testing_type($id)
    {
        $pagename = "Material Request";
        $tr_equipment = TestingCompletionEquipment::select('testing_completion_equipment.*','add_equipment.name_equipment')
        ->leftjoin('add_equipment','add_equipment.add_equipment_id','testing_completion_equipment.add_equipment_id')
        ->where('testing_completion_id', '=', $id)
        ->get();
        return view('frontend.testing_completion.create_testing_type',compact('pagename','id','tr_equipment'));
    }
    public function getEquipmentByName(Request $request)
    {
        $testing_name = $request->input('testing_name');
        $add_testing = AddTesting::where('testing_name', 'LIKE', '%' . $testing_name . '%')->get();
        return response()->json($add_testing);
    }
    public function store_testing_type(Request $request)
    {
        $user_id = Auth::user()->id;
        $tr_equipment_id = $request->input('tr_equipment_id');
        $testing_completion_id = $request->input('testing_completion_id');
        $add_testing_id = $request->input('add_testing_id');
        $type_count = TestingCompletionTestingType::where('testing_completion_equipment_id',$tr_equipment_id)
        ->where('add_testing_id',$add_testing_id)->count();
        if($type_count == 0){
        DB::table('testing_completion_testing_type')->insert([
        'created_by'=>$user_id,
        'testing_completion_equipment_id' => $tr_equipment_id,
        'testing_completion_id' => $testing_completion_id,
        'add_testing_id' => $add_testing_id,
        // Add any other columns you want to insert here
        ]);
        $message = "Add to testing type successfully..!";
        }
        $responseArray = array(
        'status' => 'success',
        'message' => $message,
        );
        return response()->json($responseArray);     
    }
    public function get_testing_type(Request $request)
    {
        $tr_equipment_id = $request->input('tr_equipment_id');
        $testing_completion_id = $request->input('testing_completion_id');
        $user = Auth::user()->id;
        $product = DB::table('testing_completion_testing_type')
        ->leftJoin('add_testing', 'add_testing.add_testing_id', '=', 'testing_completion_testing_type.add_testing_id')
        ->where('testing_completion_testing_type.testing_completion_id', $testing_completion_id)
        ->where('testing_completion_testing_type.testing_completion_equipment_id', $tr_equipment_id)
        ->select('testing_completion_testing_type.*','add_testing.*')
        ->orderBy('testing_completion_testing_type.created_at', 'desc')
        ->get();
        $responseArray = array(
        'status' => 'success',
        'products' => $product // An array of product data
        );
        return response()->json($responseArray);
    }
    public function delete_testing_type($id)
    {
        $testing_type = TestingCompletionTestingType::find($id);
        $testing_request_id = $testing_type->testing_completion_id;
        $testing_type->delete();
        $product_count = DB::table('testing_completion_testing_type')->where('testing_completion_id', $testing_request_id)->count();
        if($product_count == 0){
        echo  $testing_request_id;
        }else{
        $testing_request_id =0;
        echo  $testing_request_id;
        }
    }
     public function delete_equipment($tr_equipment_id)
    {
        $tr_equipment = TestingCompletionEquipment::where('testing_completion_equipment_id',$tr_equipment_id)->first();
        $testing_completion_id = $tr_equipment->testing_completion_id; 
        $tr_equipment->delete();
        DB::table('testing_completion_testing_type')
        ->where('testing_completion_equipment_id', $tr_equipment_id)
        ->where('testing_completion_id', $testing_completion_id)
        ->delete();
        $tr_equipment_count = TestingCompletionEquipment::where('testing_completion_id', $testing_completion_id)->count();
        if($tr_equipment_count == 0){
        return redirect('testing-completion/add-equipment/'.$testing_completion_id);
        }else{
        return redirect('testing-completion/create-testing-type/'.$testing_completion_id);
        }
    }
     public function storeRequest(Request  $request)
    {
        $tr_Products = TestingCompletionTestingType::where('testing_completion_id',$request->testing_completion_id)->count();
        if($tr_Products == 0){
        return redirect('testing-completion/create-testing-type/'.$request->testing_completion_id)
        ->with('error', 'Error: Please Select Type');
        }
        else{
        $testing_request = TestingCompletion::find($request->testing_completion_id);
        $testing_request->request_send = "Yes";
        $testing_request->save();
        // $data_array = [
        // ];
        // try {
        //     Mail::send('email.material_request', $data_array, function ($message) use ($data_array) {
        //         $message->from('notifications@akuntha.in', 'Akuntha')
        //                 ->to('pl.ipopsolution@gmail.com')
        //                 ->subject('Material Request Receive !');
        //     });
        // } catch (\Exception $e) {
        //     dd($e->getMessage());
        // }
        return redirect('/testing-completion/index');
        }
        return redirect('/testing-completion/index')->with('success','Testing completion send successfully');  
    }

    public function destroy($id)
     {
        $Completion = TestingCompletion::find($id);   
       
        if($Completion->delete()){
                Session::flash('success', 'Testing Completion deleted successfully'); 
            return response()->json(['message' => 'Testing Completion deleted successfully']);
        }
        else
        {
            Session::flash('fail', 'Something went wrong. Please try again.'); 
            return response()->json(['message' => ' Something went wrong. Please try again.']);     
        }
         echo json_encode($json);
       
    }
    public function edit($id) {
        $pagename = "Testing Completion Edit";
        // $TestingCompletion = TestingCompletion::select('testing_completion.*','substations.substation','substations.substation_id')
        // ->where('testing_completion_id',$id)
        // ->leftjoin('substations','testing_completion.substation_id','substations.substation_id')
        // ->orderBy('created_at', 'desc')
        // ->first();
        // $Substations = Substations::all();

        // $equipment= TestingRequest::where('testing_completion_id', $id)->first(); 
        // $allEquipment = AddEquipment::all();
      
        // $testing= TestingRequest::where('testing_request_id', $id)->first(); 
        // $allTesting = AddTesting::all();
        //$allTesting =explode(',', $TestingRequest->testing_type);
        $tr_equipment =TestingCompletionEquipment::select('testing_completion_equipment.*','add_equipment.name_equipment')
        ->leftjoin('add_equipment','add_equipment.add_equipment_id','testing_completion_equipment.add_equipment_id')
        ->where('testing_completion_id', '=', $id)->get();

		return view('frontend.testing_completion.edit',compact('pagename','id','tr_equipment'));
	}
	
    public function update(TestingCompletionForm  $request,$id){
        if($request->ajax()){
            return true;
        }
        if(Auth::user() != ""){
            $updated_by = Auth::user()->id;
        }else{
            $updated_by = 1;
        }
        $TestingCompletion =  TestingCompletion::find($id); 
        $TestingCompletion->substation_id = $request->substation_id;
        $TestingCompletion->testing_time = $request->testing_time;
        $TestingCompletion->testing_agency = $request->testing_agency;
        $TestingCompletion->comments = $request->comments;
        $TestingCompletion->testing_year =$request->input('testing_year');
        $TestingCompletion->updated_by = $updated_by;
        $TestingCompletion->save();
        
        return redirect('/testing-completion/index')->with('success','Testing Completion Updated Successfully');
    
           
        }
       
      
      
       
}
