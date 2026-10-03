<?php

namespace App\Http\Controllers\frontend;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\MaterialRequestForm;
use App\Models\Substations;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestSubstation;
use App\Models\MaterialRequestProducts;
use App\Models\ProductMaster;
use App\Models\TempMaterialProduct;
use Illuminate\Support\Facades\Auth;
use Hash;
use DB;
use Session;
use Str;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class MaterialRequestController extends BaseController {
   
    public function index() {
        $pagename = "Material Request";
        $requested_by = Auth::user()->id;
    
        $MaterialRequest = MaterialRequest::where('requested_by',$requested_by)
        ->where('request_send','Yes')
        ->orderBy('created_at', 'desc')
        ->get();
        
       
		return view('frontend.material_request.index',compact('pagename','MaterialRequest'));
	}
    public function create_request(){
        $pagename = "Material Request";
        return view('frontend.material_request.create_request',compact('pagename'));
    }
    public function store_request(MaterialRequestForm $request){
        $user_id=Auth::user()->id;
        $materialRequest = new MaterialRequest();
        $materialRequest->po_number = $request->po_number;
        $materialRequest->order_tag = $request->order_tag;
        $materialRequest->note = $request->note;
        $materialRequest->personal_note = $request->personal_note;
        $materialRequest->requested_by = $user_id;
        $materialRequest->created_by = $user_id;
        $materialRequest->save();

        $material_request_id = $materialRequest->material_request_id;
        return redirect('/material-request/create-substation/'.$material_request_id)->with('success','Customer Draft Add Successfully');

    }
    public function create_substation($id){
        $pagename = "Material Request";
        $substations = Substations::all();
        return view('frontend.material_request.create_substation',compact('pagename','id','substations'));
    }
    public function store_substation(Request $request,$id){
        $request->validate([
            'substation_id' => 'required',
            
           ]);
        $user_id=Auth::user()->id;
        $materialRequestSubstation=MaterialRequestSubstation::where('material_request_id',$id)->where('substation_id',$request->substation_id)->first();
        if($materialRequestSubstation )
        {
            $materialRequestSubstation->material_request_id = $id;
            $materialRequestSubstation->substation_id = $request->substation_id;
           $materialRequestSubstation->save();
        }
        else
        {
         
           $materialRequestSubstation = new MaterialRequestSubstation();  
           $materialRequestSubstation->material_request_id = $id;
           $materialRequestSubstation->substation_id = $request->substation_id;
           $materialRequestSubstation->created_by = $user_id;
           $materialRequestSubstation->save();
        }
      
        return redirect('/material-request/create-product/'.$id);
        
    }
    public function create_product($id){
        $pagename = "Material Request";
        $mr_sustation =MaterialRequestSubstation::select('material_request_substation.*','substations.substation')->leftjoin('substations','substations.substation_id','material_request_substation.substation_id')->where('material_request_id', '=', $id)->get();
        return view('frontend.material_request.create_product',compact('pagename','mr_sustation','id'));
    }
    public function getProductsByName(Request $request)
    {
        $product_name = $request->input('product_name');
    
        $products = ProductMaster::where('product_name', 'LIKE', '%' . $product_name . '%')->get();
        return response()->json($products);
    }
    
    public function store_product(Request $request){
        $user_id = Auth::user()->id;
        $mr_substation_id = $request->input('mr_substation_id');
        $material_request_id = $request->input('material_request_id');
        $product_id = $request->input('product_id');

        $product_count = MaterialRequestProducts::where('material_request_substation_id',$mr_substation_id)->where('product_id',$product_id)->count();

        if($product_count == 0){
            DB::table('material_request_products')->insert([
                'created_by'=>$user_id,
               'material_request_substation_id' => $mr_substation_id,
               'material_request_id' => $material_request_id,
               'product_id' => $product_id,
               // Add any other columns you want to insert here
           ]);
           $message = "Add to draft successfully..!";
        }else{
            $draft_product = MaterialRequestProducts::where('material_request_substation_id',$mr_substation_id)->where('product_id',$product_id)->first();
            $draft_product->quantity = $draft_product->quantity+1;
            $draft_product->save();
            $message = "Draft Product quantity increased.";
        }
       
        // dd($product);
        $responseArray = array(
            'status' => 'success',
             'message' => $message,
            );
        return response()->json($responseArray);     
    }

    public function get_products(Request $request){
        
        $mr_substation_id = $request->input('mr_substation_id');
        $material_request_id = $request->input('material_request_id');
      
        $user = Auth::user()->id;
       
        $product = DB::table('material_request_products')
        ->leftJoin('product_master', 'product_master.product_id', '=', 'material_request_products.product_id')
        ->where('material_request_products.material_request_id', $material_request_id)
        ->where('material_request_products.material_request_substation_id', $mr_substation_id)
        ->select('material_request_products.*','product_master.*')
        ->orderBy('material_request_products.created_at', 'desc')
        ->get();
       
        $responseArray = array(
            'status' => 'success',
            'products' => $product // An array of product data
        );
    
            // dd($product);
            return response()->json($responseArray);
    }

    public function delete_substation($mr_substation_id){

        $mr_substation = MaterialRequestSubstation::where('material_request_substation_id',$mr_substation_id)->first();
        $material_request_id = $mr_substation->material_request_id; 
        $mr_substation->delete();
        DB::table('material_request_products')
        ->where('material_request_substation_id', $mr_substation_id)
        ->where('material_request_id', $material_request_id)
        ->delete();
        $mr_substation_count = MaterialRequestSubstation::where('material_request_id', $material_request_id)->count();
       
        if($mr_substation_count == 0){
            return redirect('material-request/create-substation/'.$material_request_id);
        }else{
            return redirect('material-request/create-product/'.$material_request_id);
    
        }
        
    
       }
    
    public function delete_product($id)
    {
       $product = MaterialRequestProducts::find($id);
       $material_request_id = $product->material_request_id;
       $product->delete();
       $product_count = DB::table('material_request_products')->where('material_request_id', $material_request_id)->count();
      
    
       if($product_count == 0){
       
        echo  $material_request_id;
       }else{
        $material_request_id =0;
        echo  $material_request_id;
      }
   
    }
    public function quantity_update(Request $request)
    {
        $productId = $request->input('product_id');
        $mr_substation_id = $request->input('mr_substation_id');
        $newQuantity = $request->input('new_quantity');
       
        $mr_Products = MaterialRequestProducts::where('product_id', $productId)->where('material_request_substation_id', $mr_substation_id)->first();
        $mr_Products->quantity = $newQuantity;
        $quantity =$mr_Products->quantity;
        $material_request_id = $mr_Products->material_request_id;
        $mr_Products->save();
         
          $responseArray = array(
                'quantity' => $quantity, 
                );
               
            return response()->json($responseArray);    
       
    }
    public function quantity_plus(Request $request)
    {
        $productId = $request->input('product_id');
        $mr_substation_id = $request->input('mr_substation_id');
       
        $mr_Products = MaterialRequestProducts::where('product_id', $productId)->where('material_request_substation_id', $mr_substation_id)->first();
        $mr_Products->quantity = $mr_Products->quantity+1;
        $quantity =$mr_Products->quantity;
        $material_request_id = $mr_Products->material_request_id;
        $mr_Products->save();
         
          $responseArray = array(
                'quantity' => $quantity, 
                );
               
            return response()->json($responseArray);    
       
    }
    public function quantity_minus(Request $request)
    {
       
        $productId = $request->input('product_id');
        $mr_substation_id = $request->input('mr_substation_id');
       
        $mr_Products = MaterialRequestProducts::where('product_id', $productId)->where('material_request_substation_id', $mr_substation_id)->first();
        $material_request_id = $mr_Products->material_request_id;
        if($mr_Products->quantity >= 2)
        {
        $mr_Products->quantity = $mr_Products->quantity-1;
        $quantity =$mr_Products->quantity;
        $mr_Products->save();
        }else{
            $quantity =$mr_Products->quantity;
        }
          
          $responseArray = array(
                'quantity' => $quantity, 
                );
               
            return response()->json($responseArray);    
       
    }
    public function change_unit(Request $request)
    {
        $productId = $request->input('product_id');
        $mr_substation_id = $request->input('mr_substation_id');
        $unit = $request->input('unit');
       
       
        $mr_Products = MaterialRequestProducts::where('product_id', $productId)->where('material_request_substation_id', $mr_substation_id)->first();
        $mr_Products->unit = $unit;
        $material_request_id = $mr_Products->material_request_id;
        $mr_Products->save();
         
          
            return $material_request_id;
       
    }
    public function create()
    {
        $pagename = 'Create Material Request';
        $guid = Str::uuid();
        $Substations = Substations::all();
        $product = ProductMaster::all();
        return view('frontend.material_request.create',compact('pagename','Substations','product','guid'));
    }

    public function store(Request  $request){
        

            $mr_Products =MaterialRequestProducts::where('material_request_id',$request->material_request_id)->count();
           
           if($mr_Products == 0){
            return redirect('material-request/create-product/'.$request->material_request_id)->with('error', 'Error: Please Select Product');
           }
            else{
                $material_request = MaterialRequest::find($request->material_request_id);
                $material_request->request_send = "Yes";
                $material_request->save();
                $data_array = [
                // 'email' => $userEmail->email,
                // 'name' => $userEmail->name,
                ];

                try {
                    Mail::send('email.material_request', $data_array, function ($message) use ($data_array) {
                        $message->from('notifications@akuntha.in', 'Akuntha')
                                ->to('pl.ipopsolution@gmail.com')
                                ->subject('Material Request Receive !');
                    });
                } catch (\Exception $e) {
                    dd($e->getMessage());
                }
                return redirect('/material-request/index');
             
               }
            
        return redirect('/material-request/index')->with('success','Material request send successfully');
        // return redirect('/admin/item-list/'.$id)->with('success','Item Add Successfully');
        
    
           
        }
        public function view($id){
            $pagename = "View Material Request";
            $material_request = MaterialRequest::find($id);
            $mr_sustation =MaterialRequestSubstation::select('material_request_substation.*','substations.substation')->leftjoin('substations','substations.substation_id','material_request_substation.substation_id')->where('material_request_id', '=', $id)->get();
            return view('frontend.material_request.view',compact('pagename','mr_sustation','id','material_request'));
        }
    public function destroy($id)
     {
        $product = MaterialRequest::find($id);   
       
        if($product->delete()){
                Session::flash('success', 'Material request deleted successfully'); 
            return response()->json(['message' => 'Material request deleted successfully']);
        }
        else
        {
            Session::flash('fail', 'Something went wrong. Please try again.'); 
            return response()->json(['message' => ' Something went wrong. Please try again.']);     
        }
         echo json_encode($json);
       
    }
    public function edit($id) {
        $pagename = "Material Request Edit";
        $mr_sustation =MaterialRequestSubstation::select('material_request_substation.*','substations.substation')->leftjoin('substations','substations.substation_id','material_request_substation.substation_id')->where('material_request_id', '=', $id)->get();
        return view('frontend.material_request.edit',compact('pagename','mr_sustation','id'));

	}
	
    public function update(MaterialRequestForm  $request,$id){
        if($request->ajax()){
            return true;
        }
        if(Auth::user() != ""){
            $updated_by = Auth::user()->id;
        }else{
            $updated_by = 1;
        }
        $MaterialRequest =  MaterialRequest::find($id); 
        $MaterialRequest->delivery_type = $request->delivery_type;
        if($request->delivery_type == "other")
        {
            $MaterialRequest->other = $request->other;
        }else
        {
            $MaterialRequest->substation_id = $request->substation_id;
        }
        $MaterialRequest->item_name = $request->item_name;
        $MaterialRequest->quantity = $request->quantity;
        $MaterialRequest->unit = $request->unit;
        $MaterialRequest->note = $request->note;
        $MaterialRequest->personal_note = $request->personal_note;
        $MaterialRequest->updated_by = $updated_by;
        $MaterialRequest->save();
        
        return redirect('/material-request/index')->with('success','Material request Updated Successfully');
    
           
        }

    public function marerialProductstore(Request $request)
    {
        $newguid = $request->guid;

        $materialProObj = new TempMaterialProduct();
        $materialProObj->guid = $newguid;
        $materialProObj->product_id = $request->product_id;
        $materialProObj->quantity = $request->quantity;
        $materialProObj->unit = $request->unit;
      

        if($materialProObj->save()){

          $tempMaterialProData = TempMaterialProduct::select('temp_material_products.*','product_master.product_name')
            ->leftjoin('product_master','product_master.product_id','temp_material_products.product_id')
            ->where('temp_material_products.guid',$newguid)->get();

             $view = view('frontend.material_request.material_product_view',['material_pro_data' => $tempMaterialProData])->render();

             return response()->json( array('success' => true, 'html'=>$view));

      } 
    } 
    public function marerialProductsdestroy($id,$guid)
   {
     $materialPro = TempMaterialProduct::find($id);
    
      if($materialPro->delete()){

          $tempMaterialProData = TempMaterialProduct::select('temp_material_products.*','product_master.product_name')
            ->leftjoin('product_master','product_master.product_id','temp_material_products.product_id')
            ->where('temp_material_products.guid',$guid)->get();

          $view = view('frontend.material_request.material_product_view',['material_pro_data' => $tempMaterialProData])->render();

         return response()->json( array('success' => true, 'html'=>$view)); 
      }else{
         $json = array('status' => 'fail'); 
         echo json_encode($json);  
      }
         
   }


}
