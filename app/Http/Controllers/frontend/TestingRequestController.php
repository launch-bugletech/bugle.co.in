<?php

namespace App\Http\Controllers\frontend;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\TestingRequestForm;
use App\Models\Substations;
use App\Models\AddEquipment;
use App\Models\AddTesting;
use App\Models\TestingRequest;
use App\Models\TestingRequestEquipment;
use App\Models\TestingRequestTestingType;
use Illuminate\Support\Facades\Auth;
use Hash;
use DB;
use Session;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
class TestingRequestController extends BaseController {

    public function index() 
    {
        $pagename = "Testing Request Index";
        // $user_id = Auth::user()->id;
        // $TestingRequest = TestingRequest::select('testing_request.*','substations.substation','name_equipment','testing_name')
        // ->leftjoin('substations','testing_request.substation_id','substations.substation_id')
        // ->leftjoin('add_equipment','testing_request.add_equipment_id','add_equipment.add_equipment_id')
        // ->leftjoin('add_testing','testing_request.add_testing_id','add_testing.add_testing_id')
        // ->orderBy('created_at', 'desc')
        // ->where('testing_request.created_by',$user_id)
        // ->get();
        $created_by = Auth::user()->id;
    
        $TestingRequest = TestingRequest::select('testing_request.*','substations.substation')
        ->leftjoin('substations','substations.substation_id','testing_request.substation_id')
        ->where('testing_request.created_by',$created_by)
        ->where('testing_request.request_send','Yes')
        ->orderBy('testing_request.created_at', 'desc')
        ->get();
        return view('frontend.testing_request.index',compact('pagename','TestingRequest'));
    }
    public function create()
    {
        $pagename = 'Create Testing Request';
        $Substations = Substations::all();
        $EquipmentAll = AddEquipment::all();
        $TestingAll = AddTesting::all();

        return view('frontend.testing_request.create',compact('pagename','Substations','EquipmentAll','TestingAll'));
    }
    public function store(TestingRequestForm  $request)
    {
        if($request->ajax()){
        return true;
        }
        $useremail = Auth::user();
        if(Auth::user() != ""){
        $created_by = Auth::user()->id;
        }else{
        $created_by =1; 
        }
        $TestingRequest = new TestingRequest();
        // $TestingRequest->equipment_type = implode(',',$request->equipment_type);
        //dd($TestingRequest->equipment_type);
        // $TestingRequest->testing_type = implode(',',$request->testing_type);
        $TestingRequest->substation_id = $request->substation_id;
        $TestingRequest->estimated_time = $request->estimated_time;
        $TestingRequest->estimated_time_from = $request->estimated_time_from;
        $TestingRequest->estimated_year = $request->estimated_year;
        $TestingRequest->created_by = $created_by;
        $TestingRequest->save();
        // $data_array['email'] = $useremail->email;
        // $mail = Mail::send('email.testing_request', $data_array, function ($message) use ($data_array) {
        //     $message->to('pl.ipopsolution@gmail.com')->subject('Testing Request Receive');
        // }); 
        // $data_array = [
        //     'email' => $useremail->email,
        //     'name' => $useremail->name,
        // ];
        // try {
        //     Mail::send('email.testing_request', $data_array, function ($message) use ($data_array) {
        //         $message->from('notifications@akuntha.in', 'Akuntha')
        //                 ->to('pl.ipopsolution@gmail.com')
        //                 ->subject('Testing Request Receive !');
        //     });
        // } catch (\Exception $e) {
        //     dd($e->getMessage());
        // }
        $testing_request_id = $TestingRequest->testing_request_id;
        return redirect('/testing-request/create-equipment/'.$testing_request_id)->with('success','Testing Request Add Successfully');
        // return redirect('/testing-request/index')->with('success','Testing Request send successfully');
        // return redirect('/admin/item-list/'.$id)->with('success','Item Add Successfully');
    }
    public function create_equipment($id)
    {
        $pagename = "Material Request";
        $equipment = AddEquipment::all();
        return view('frontend.testing_request.create_equipment',compact('pagename','id','equipment'));
    }

    public function store_equipment(Request $request,$id)
    {
        $request->validate([
        'add_equipment_id' => 'required',
        ], [
            'add_equipment_id.required' => 'Please select equipment',
        ]);
        $user_id=Auth::user()->id;
        $testingRequestEquipment=TestingRequestEquipment::where('testing_request_id',$id)
        ->where('add_equipment_id',$request->add_equipment_id)
        ->first();

        if($testingRequestEquipment)
        {
        $testingRequestEquipment->testing_request_id = $id;
        $testingRequestEquipment->add_equipment_id = $request->add_equipment_id;
        $testingRequestEquipment->save();
        }
        else
        {
        $testingRequestEquipment = new TestingRequestEquipment();  
        $testingRequestEquipment->testing_request_id = $id;
        $testingRequestEquipment->add_equipment_id = $request->add_equipment_id;
        $testingRequestEquipment->created_by = $user_id;
        $testingRequestEquipment->save();
        }

        return redirect('/testing-request/create-testing-type/'.$id);
    }

    public function create_testing_type($id)
    {
        $pagename = "Material Request";
        $tr_equipment = TestingRequestEquipment::select('testing_request_equipment.*','add_equipment.name_equipment')
        ->leftjoin('add_equipment','add_equipment.add_equipment_id','testing_request_equipment.add_equipment_id')
        ->where('testing_request_id', '=', $id)
        ->get();
        return view('frontend.testing_request.create_testing_type',compact('pagename','id','tr_equipment'));
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
        $testing_request_id = $request->input('testing_request_id');
        $add_testing_id = $request->input('add_testing_id');
        $type_count = TestingRequestTestingType::where('testing_request_equipment_id',$tr_equipment_id)->where('add_testing_id',$add_testing_id)->count();
        if($type_count == 0){
        DB::table('testing_request_testing_type')->insert([
        'created_by'=>$user_id,
        'testing_request_equipment_id' => $tr_equipment_id,
        'testing_request_id' => $testing_request_id,
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
        $testing_request_id = $request->input('testing_request_id');
        $user = Auth::user()->id;
        $product = DB::table('testing_request_testing_type')
        ->leftJoin('add_testing', 'add_testing.add_testing_id', '=', 'testing_request_testing_type.add_testing_id')
        ->where('testing_request_testing_type.testing_request_id', $testing_request_id)
        ->where('testing_request_testing_type.testing_request_equipment_id', $tr_equipment_id)
        ->select('testing_request_testing_type.*','add_testing.*')
        ->orderBy('testing_request_testing_type.created_at', 'desc')
        ->get();
        $responseArray = array(
        'status' => 'success',
        'products' => $product // An array of product data
        );
        return response()->json($responseArray);
    }

    public function delete_testing_type($id)
    {
        $testing_type = TestingRequestTestingType::find($id);
        $testing_request_id = $testing_type->testing_request_id;
        $testing_type->delete();
        $product_count = DB::table('testing_request_testing_type')->where('testing_request_id', $testing_request_id)->count();
        if($product_count == 0){
        echo  $testing_request_id;
        }else{
        $testing_request_id =0;
        echo  $testing_request_id;
        }
    }

    public function delete_equipment($tr_equipment_id)
    {
        $tr_equipment = TestingRequestEquipment::where('testing_request_equipment_id',$tr_equipment_id)->first();
        $testing_request_id = $tr_equipment->testing_request_id; 
        $tr_equipment->delete();
        DB::table('testing_request_testing_type')
        ->where('testing_request_equipment_id', $tr_equipment_id)
        ->where('testing_request_id', $testing_request_id)
        ->delete();
        $tr_equipment_count = TestingRequestEquipment::where('testing_request_id', $testing_request_id)->count();
        if($tr_equipment_count == 0){
        return redirect('testing-request/create-equipment/'.$testing_request_id);
        }else{
        return redirect('testing-request/create-testing-type/'.$testing_request_id);
        }
    }

    public function storeRequest(Request  $request)
    {
        $tr_Products = TestingRequestTestingType::where('testing_request_id',$request->testing_request_id)->count();
        if($tr_Products == 0){
        return redirect('testing-request/create-testing-type/'.$request->testing_request_id)
        ->with('error', 'Error: Please Select Type');
        }
        else{
        $testing_request = TestingRequest::find($request->testing_request_id);
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
        return redirect('/testing-request/index');
        }
        return redirect('/testing-request/index')->with('success','Testing request send successfully');  
    }

    public function destroy($id)
    {
        $product = TestingRequest::find($id);
        TestingRequestTestingType::where('testing_request_id',$id)->delete();
        TestingRequestEquipment::where('testing_request_id',$id)->delete();
        if($product->delete()){
        Session::flash('success', 'Testing Request deleted successfully'); 
        return response()->json(['message' => 'Testing Request deleted successfully']);
        }
        else
        {
        Session::flash('fail', 'Something went wrong. Please try again.'); 
        return response()->json(['message' => ' Something went wrong. Please try again.']);     
        }
        echo json_encode($json);
    }

    public function edit($id) 
    {
        $pagename = "Testing Request Edit";
        // $TestingRequest = TestingRequest::select('testing_request.*','substations.substation','substations.substation_id','name_equipment','testing_type')
        // ->where('testing_request_id',$id)
        // ->leftjoin('substations','testing_request.substation_id','substations.substation_id')
        // ->leftjoin('add_equipment','testing_request.add_equipment_id','add_equipment.add_equipment_id')
        // ->leftjoin('add_testing','testing_request.add_testing_id','add_testing.add_testing_id')
        // ->orderBy('created_at', 'desc')
        // ->first();
        // $Substations = Substations::all();
        // $equipment= TestingRequest::where('testing_request_id', $id)->first(); 
        // $allEquipment = AddEquipment::all();
        // $testing= TestingRequest::where('testing_request_id', $id)->first(); 
        // $allTesting = AddTesting::all();
        //$allTesting =explode(',', $TestingRequest->testing_type);
        $tr_equipment =TestingRequestEquipment::select('testing_request_equipment.*','add_equipment.name_equipment')
        ->leftjoin('add_equipment','add_equipment.add_equipment_id','testing_request_equipment.add_equipment_id')
        ->where('testing_request_id', '=', $id)->get();

        return view('frontend.testing_request.edit',compact('pagename','id','tr_equipment'));
    }

    public function update(TestingRequestForm  $request,$id)
    {
        if($request->ajax()){
        return true;
        }
        if(Auth::user() != ""){
        $updated_by = Auth::user()->id;
        }else{
        $updated_by = 1;
        }
        $TestingRequest =  TestingRequest::find($id); 
        $TestingRequest->equipment_type = implode(',',$request->equipment_type);
        $TestingRequest->testing_type = implode(',',$request->testing_type);
        $TestingRequest->substation_id = $request->substation_id;
        $TestingRequest->estimated_time = $request->estimated_time;
        $TestingRequest->estimated_time_from = $request->estimated_time_from;
        $TestingRequest->estimated_year = $request->estimated_year;
        $TestingRequest->updated_by = $updated_by;
        $TestingRequest->save();
        return redirect('/testing-request/index')->with('success','Testing Request Updated Successfully');
    }
}