<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerDraftForm;
use App\Http\Requests\CreditcardForm;
use App\Models\CustomerDraft;
use App\Models\StateMaster;
use App\Models\DoorStyle;
use App\Models\ProductMaster;
Use App\Models\CustomerDraftStyle;
Use App\Models\ProductDoorStyle;
Use App\Models\ItemModification;
Use App\Models\ItemAccessories;
use App\Models\DraftProduct;
use App\Models\Customer;
use App\Models\DraftProductModification;
use App\Models\DraftProductAccessories;
use App\Models\CustomerGroup;
use App\Models\ModificationValue;
use App\Models\CreditCard;
use App\Models\User;
use App\Models\Substations;
use App\Models\MaterialRequest;
use Illuminate\Support\Facades\Auth; 
use DB;

class DraftController extends Controller
{


    public function add_material(){
        $pagename = "Material Request";
        $user_id=Auth::user()->id;
         $substations = Substations::all();
         $products = ProductMaster::all();
         $materialRequest = new MaterialRequest();
         $materialRequest->requested_by = $user_id;
         $materialRequest->created_by = $user_id;
         $materialRequest->save();

         $material_request_id = $materialRequest->material_request_id;

        $material_request_sustation = DB::table('material_request_substation')->where('material_request_id', '=', $material_request_id) ->pluck('substation_id')
        ->toArray();

         return view('frontend.draft.add_material_request',compact('pagename','material_request_id','material_request_sustation'));
    }
   public function customerDraft(){
    
      $pagename = "customer Draft";
      $user_id=Auth::user()->id;
      $state = StateMaster::select('*')->get();
      $customer = Customer:: where('user_id',$user_id)->first();
      $CustomerGroup = CustomerGroup::where('customer_group_id',$customer->customer_group_id)->first();
     return view('frontend.draft.customer_draft',compact('state','CustomerGroup','pagename'));

   }
   public function store(CustomerDraftForm $request)
   {
    if($request->ajax()){
            return true;
      }
      $user_id=Auth::user()->id;
    
     $cus_draft = new CustomerDraft();
     $cus_draft->customer_id = $user_id;
     $cus_draft->po_number = $request->po_number;
     $cus_draft->order_tag = $request->order_tag;
     $cus_draft->showroom = $request->showroom;
     $cus_draft->designer = $request->designer;
     $cus_draft->discount = $request->discount;
     $cus_draft->client_name = $request->client_name;
     $cus_draft->client_email = $request->client_email;
     $cus_draft->address = $request->address;
     $cus_draft->contact_no = $request->contact_no;
     $cus_draft->city = $request->city;
     $cus_draft->state = $request->state;
     $cus_draft->zip_code = $request->zip_code;

     if ($request->hasfile('pro_kitchen_pdf')) {

            $file = $request->file('pro_kitchen_pdf');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . rand(1, 100) . '.' . $extension;
            $file->move('public/img/draft', $filename);
            $cus_draft->pro_kitchen_pdf = $filename;
        }
     $cus_draft->save();

        return redirect('/door-style/'.$cus_draft->customer_draft_id)->with('success','Customer Draft Add Successfully');
   }
   public function doorStyle($id)
   {
       $pagename = "Door Style";
       $door_style = DoorStyle::select('*')->get();
      
       return view('frontend.draft.door_style',compact('door_style','id','pagename'));
   }
   public function CustomerDraftStyle(Request $request)
   {
      $customerDraftStyle=CustomerDraftStyle::where('customer_draft_id',$request->draft_id)->where('door_style_Id',$request->doorStyle_id)->first();
      if($customerDraftStyle )
      {
         $customerDraftStyle->door_style_Id = $request->doorStyle_id;
         $customerDraftStyle->customer_draft_Id = $request->draft_id;
         $customerDraftStyle->save();
      }
      else
      {
       
         $customerDraftStyle = new CustomerDraftStyle();  
         $customerDraftStyle->door_style_Id = $request->doorStyle_id;
         $customerDraftStyle->customer_draft_Id = $request->draft_id;
         $customerDraftStyle->save();
      }
    
     return redirect('new-draft/'.$request->draft_id);
   }
   public function delete_draft_style($draft_style_id){

    $draft_style = CustomerDraftStyle::where('draft_style_id',$draft_style_id)->first();
    $customer_draft_Id = $draft_style->customer_draft_Id; 
    $draft_style->delete();
    DB::table('draft_product')
    ->where('draft_style_id', $draft_style_id)
    ->where('customer_draft_Id', $customer_draft_Id)
    ->delete();
    $draft_style_count = CustomerDraftStyle::where('customer_draft_Id', $customer_draft_Id)->count();
   
    if($draft_style_count == 0){
        return redirect('door-style/'.$customer_draft_Id);
    }else{
        return redirect('new-draft/'.$customer_draft_Id);

    }
    

   }

   public function change_draft_style_get($draft_style_id){
    $pagename = "Door Style";
    $draft_style = CustomerDraftStyle::where('draft_style_id',$draft_style_id)->first();

    $door_style = DoorStyle::select('*')->get();
      $id=$draft_style->customer_draft_Id;
       return view('frontend.draft.change_door_style',compact('door_style','id','draft_style_id','pagename'));
    }

    public function change_draft_style_post($id , $draft_style_id,Request $request){
        $product_id = ProductDoorStyle::where('door_style_id',$request->doorStyle_id)->pluck('product_id')->toArray();
       
        $draft_products = DraftProduct::where('customer_draft_Id',$id)->where('draft_style_id',$draft_style_id)->get();
          
        foreach ($draft_products as $draft_product) {
           
            if (!in_array($draft_product->product_id, $product_id)) {
                $draft_product->delete();
            }
        }
        $draft_style = CustomerDraftStyle::where('draft_style_id',$draft_style_id)->first();
        $draft_style->door_style_Id = $request->doorStyle_id;
        $draft_style->save();
        return redirect('new-draft/'.$id);


    }
   
  
   public function showProduct(Request $request,$customer_draft_Id)
   {
     $pagename="add draft";
      $draft_style = CustomerDraftStyle::where('customer_draft_Id',$customer_draft_Id)->get();
      $user = Auth::user()->id;

    //   $product = DB::table('draft_product')
    //   ->leftJoin('product_item', 'draft_product.product_id', '=', 'product_item.product_id')
    //   ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
    //   ->where('draft_product.customer_draft_id', $customer_draft_Id)
    //   ->where('draft_product.customer_id', $user)
    //   ->select('draft_product.*','product_master.*',
    //            'product_item.product_item_sku as product_item_sku',
    //            'product_item.hinge_side as is_hinge_side','product_item.finish_side as is_finish_side','product_item.product_item_price as product_item_price')
    //   ->get();
        $customerDraft = CustomerDraft::where('customer_draft_Id',$customer_draft_Id)->first();
        $serviceType = $customerDraft->service_type;
        $configuration = $customerDraft->configuration;
    
         return view('frontend.draft.add_draft',compact('draft_style','customer_draft_Id','serviceType', 'configuration','pagename'));
   }
    public function updateServieConfiguration(Request $request)
    {
        $customer_draft_id = $request->input('customer_draft_id');
        $toggle_group = $request->input('toggle_group');
        $new_value = $request->input('new_value');
    
        $customerDraft = CustomerDraft::where('customer_draft_Id', $customer_draft_id)->first();
        
        if ($toggle_group === 'service_type') {
            $customerDraft->service_type = $new_value;
        } elseif ($toggle_group === 'configuration') {
            $customerDraft->configuration = $new_value;
        }
    
        $customerDraft->save();
    
        return response()->json(['success' => true]);
    }

   public function getProductsBySku(Request $request)
    {
        $sku = $request->input('sku');
        $doorStyleId = $request->input('door_style_id');
         $user_id = Auth::user()->id;
        // Fetch products based on SKU and door_style_id using your existing query
        $products = DB::table('product_master')
            ->leftJoin('product_door_style', 'product_master.product_id', '=', 'product_door_style.product_id')
            ->leftJoin('product_item', 'product_master.product_id', '=', 'product_item.product_id')
            ->leftJoin('product_item_hinge_side',  'product_master.product_id', '=', 'product_item_hinge_side.product_id')
            ->leftJoin('product_item_finish_side',  'product_master.product_id', '=', 'product_item_finish_side.product_id')
            ->where('product_door_style.door_style_id', $doorStyleId)
            ->where('product_master.availability','Listed')
            ->where('product_item.product_item_sku', 'LIKE', '%' . $sku . '%')
            ->select('product_master.*', 'product_item.*',  'product_item_hinge_side.right_hinge_side_price', 'product_item_hinge_side.left_hinge_side_price', 'product_item_hinge_side.hinge_side_none',
            'product_item_finish_side.right_finish_side_price','product_item_finish_side.left_finish_side_price','product_item_finish_side.both_finish_side_price','product_item_finish_side.finish_side_none')
            ->get();

            $draftStyleId = $request->input('draft_style_id');
            $customerDraftId = $request->input('customer_draft_Id');

        return response()->json($products);
    }
    public function get_draft_products(Request $request){

        $customerDraftId = $request->input('customer_draft_Id');
        $draft_style_id = $request->input('draft_style_id');
        $user = Auth::user()->id;
       
        $product = DB::table('draft_product')
        ->leftJoin('product_item', 'draft_product.product_id', '=', 'product_item.product_id')
        ->leftJoin('product_item_hinge_side', 'draft_product.product_id', '=', 'product_item_hinge_side.product_id')
        ->leftJoin('product_item_finish_side', 'draft_product.product_id', '=', 'product_item_finish_side.product_id')
        ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
        ->where('draft_product.customer_draft_id', $customerDraftId)
        ->where('draft_product.draft_style_id', $draft_style_id)
        ->where('draft_product.customer_id', $user)
        ->select('draft_product.*','product_master.*',
                 'product_item.product_item_sku as product_item_sku',
                 'product_item.description as description','product_item.hinge_side as is_hinge_side','product_item.finish_side as is_finish_side','product_item.product_item_price as product_item_price',
                 'product_item_hinge_side.right_hinge_side_price', 'product_item_hinge_side.left_hinge_side_price', 'product_item_hinge_side.hinge_side_none',
                 'product_item_finish_side.right_finish_side_price','product_item_finish_side.left_finish_side_price','product_item_finish_side.both_finish_side_price','product_item_finish_side.finish_side_none')
        ->orderBy('draft_product.created_at', 'desc')
        ->get();
        foreach ($product as $item) {
            $modificationIdExists = DB::table('item_modification')
                ->where('product_id', $item->product_id)
                ->exists();
    
            $accessoriesIdExists = DB::table('item_accessories')
                ->where('product_id', $item->product_id)
                ->exists();
    
            $item->modification_id_exists = $modificationIdExists;
            $item->accessories_id_exists = $accessoriesIdExists;
        }
        $responseArray = array(
            'status' => 'success',
            'products' => $product // An array of product data
        );
    
            // dd($product);
            return response()->json($responseArray);
    }
    public function store_draft_product(Request $request){
        $user = Auth::user()->id;
        $draftStyleId = $request->input('draft_style_id');
        $customerDraftId = $request->input('customer_draft_Id');
        $product_id = $request->input('product_id');

        $draft_count = DraftProduct::where('draft_style_id',$draftStyleId)->where('product_id',$product_id)->count();

        if($draft_count == 0){
            DB::table('draft_product')->insert([
                'customer_id'=>$user,
               'draft_style_id' => $draftStyleId,
               'customer_draft_Id' => $customerDraftId,
               'product_id' => $product_id,
               // Add any other columns you want to insert here
           ]);
           $message = "Add to draft successfully..!";
        }else{
            $draft_product = DraftProduct::where('draft_style_id',$draftStyleId)->where('customer_draft_Id',$customerDraftId)->where('product_id',$product_id)->first();
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
    public function getModificationsByProduct(Request $request)
    {
        $productId = $request->input('product_id');
        $draftProductId = $request->input('draft_product_id');
    
        // Retrieve the product by ID
        $product = ProductMaster::find($productId);
    
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
    
        // Retrieve the selected modification IDs for the specific draft product
        $selectedModificationIds = DraftProductModification::where('draft_product_id', $draftProductId)
            ->pluck('modification_id')
            ->toArray();
    
        $modifications = ItemModification::select('item_modification.*', 'modificationmaster.modification_nm', 'modificationmaster.modification_info', 'modificationmaster.info_type', 'modificationmaster.message_label',  'modificationmaster.integer_lable')
            ->leftjoin('modificationmaster', 'modificationmaster.modification_id', 'item_modification.modification_id')
            ->where('product_id', $productId)
            ->get();
         
    
        $response = [];
        foreach ($modifications as $modification) {
            $isSelected = in_array($modification->modification_id, $selectedModificationIds);
    
            $response[] = [
                'modification_id' => $modification->modification_id,
                'modification_nm' => $modification->modification_nm,
                'modification_info' => $modification->modification_info,
                'info_type' => $modification->info_type,
                'message_label' => $modification->message_label,
                'integer_lable' => $modification->integer_lable,
                'is_selected' => $isSelected,
            ];
        }
    
        return response()->json($response);
    }
    
   
    public function addModificationToDraftProduct(Request $request)
{
    $modificationId = $request->input('modification_id');
    $productId = $request->input('product_id');
    $draftStyleId = $request->input('draft_style_id');
    $draftProductId = $request->input('draftProductId');
    $modificationInfo = $request->input('modificationInfo');
    $infoType = $request->input('infoType');
    $integer_lable = $request->input('integer_lable');
    $message_lable = $request->input('message_lable');
  

    // Check if the modification is already added to the draft product
    $draftProductModification = DraftProductModification::where('draft_product_id', $draftProductId)
        ->where('modification_id', $modificationId)
        ->first();

    if (!$draftProductModification) {
        $draft_product_modification = new DraftProductModification();
        $draft_product_modification->draft_product_id = $draftProductId;
        $draft_product_modification->modification_id = $modificationId;

        $draft_product_modification->save();

        return response()->json(['success' => true, 'added' => true]);
    }

    return response()->json(['success' => true, 'added' => false]);
}

        public function deleteModificationFromDraftProduct(Request $request)
        {
            $modificationId = $request->input('modification_id');
            $productId = $request->input('product_id');
            $draftProductId = $request->input('draft_product_id');
              // Delete the modification from the draft_product_modification table
             DraftProductModification::where('draft_product_id', $draftProductId)
                ->where('modification_id', $modificationId)
                ->delete();
               

            return response()->json(['deleted' => true]);
        }


    public function getModificationValues(Request $request)
    {
        $modificationId = $request->input('modification_id');
        $draftProductId = $request->input('draft_product_id');

        // Get the size and message values from draft_product_modification table
        $modificationData = DraftProductModification::where('modification_id', $modificationId)
            ->where('draft_product_id', $draftProductId)
            ->select('size', 'message')
            ->first();
    
        $values = ModificationValue::where('modification_id', $modificationId)
            ->pluck('value')
            ->toArray();
          
        
        return response()->json([
            'values' => $values,
            'modificationData' => $modificationData,
        ]);
    }
    public function editModificationInfo(Request $request)
{
    $modificationId = $request->input('modification_id');
    $draftProductId = $request->input('draft_product_id');
    $size = $request->input('size');
    $message = $request->input('message');
    
    // Check if the record exists in the database
    $existingRecord = DraftProductModification::where([
        'modification_id' => $modificationId,
        'draft_product_id' => $draftProductId,
    ])->first();

    if($size != ""){ $existingRecord->size = $size; }else{ $existingRecord->size = null;}
    if($message != ""){$existingRecord->message= $message; }else{ $existingRecord->message = null;}
    $existingRecord->save();
   

    return response()->json(['success' => true]);
}

    
    public function getAccessoriesByProduct(Request $request)
    {
        $productId = $request->input('product_id');
        $draftProductId = $request->input('draft_product_id');

        $product = ProductMaster::find($productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

      
        $selectedAccessoriesIds = DraftProductAccessories::where('draft_product_id', $draftProductId)
        ->pluck('accessories_id')
        ->toArray();
        
     

       $accessories=  ItemAccessories::select('item_accessories.*','accessoriesmaster.accessories_nm')
        ->leftjoin('accessoriesmaster','accessoriesmaster.accessories_id','item_accessories.accessories_id')->where('product_id',$productId)->get();
     
        $response = [];
        foreach ($accessories as $accessories) {
            $isSelected = in_array($accessories->accessories_id, $selectedAccessoriesIds);
            $response[] = [
                'accessories_id' => $accessories->accessories_id,
                'accessories_nm' => $accessories->accessories_nm,
                'is_selected' => $isSelected,
            ];
        }

        return response()->json($response);
    }

    
    public function addAccessorieToDraftProduct(Request $request)
    {
        $accessoriesId = $request->input('accessorie_id');
        $draftProductId = $request->input('draftProductId');

        $draftProductAccessories = DraftProductAccessories::where('draft_product_id', $draftProductId)
        ->where('accessories_id', $accessoriesId)
        ->first();

        if (!$draftProductAccessories) {
            $draftProductAccessories = new DraftProductAccessories();
            $draftProductAccessories->draft_product_id = $draftProductId;
            $draftProductAccessories->accessories_id = $accessoriesId;
    
            $draftProductAccessories->save();
    
            return response()->json(['success' => true, 'added' => true]);
        }
    
        return response()->json(['success' => true, 'added' => false]);
    }
    public function deleteAccessorieFromDraftProduct(Request $request)
    {
        $accessoriesId = $request->input('accessorie_id');
        $productId = $request->input('product_id');
        $draftProductId = $request->input('draft_product_id');
          // Delete the modification from the draft_product_modification table
          DraftProductAccessories::where('draft_product_id', $draftProductId)
            ->where('accessories_id', $accessoriesId)
            ->delete();
           

        return response()->json(['deleted' => true]);
    }


    public function quantity_update(Request $request)
{
    $productId = $request->input('product_id');
    $draftStyleId = $request->input('draft_style_id');
    $newQuantity = $request->input('new_quantity');
   
    $draft_product = DraftProduct::where('product_id', $productId)->where('draft_style_id', $draftStyleId)->first();
   
    $draft_product->quantity = $newQuantity;
    $quantity =$draft_product->quantity;
    $customer_draft_id = $draft_product->customer_draft_Id;
    $draft_product->save();
      
    // return response()->json(['message' => 'Quantity updated successfully']);
    return $this->count_price($customer_draft_id, $quantity );
}

public function count_price($id , $quantity){
    $sub_total = 0;
    $totalModificationPrice = 0; 
    $customer_draft = CustomerDraft::where('customer_draft_id',$id)->first();
      $product = DB::table('draft_product')
           ->leftJoin('product_item', 'draft_product.product_id', '=', 'product_item.product_id')
           ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
           ->where('draft_product.customer_draft_id', $id)
           ->select('draft_product.*','product_master.*',
                    'product_item.description as product_description',
                    'product_item.hinge_side as is_hinge_side','product_item.finish_side as is_finish_side','product_item.product_item_price as product_item_price','product_item.cut_depth','product_item.cut_depth_price')
           ->get();
           // dd($product);
           foreach($product as $data){
               $price = $data->product_item_price;
               
               $hinge_price = DB::table('product_item_hinge_side')->where('product_id', $data->product_id)->first();
               if($data->hinge_side == "L"){
                  $price =$price + $hinge_price->left_hinge_side_price;
               }elseif($data->hinge_side == "R"){
                  $price =$price + $hinge_price->right_hinge_side_price;
               }elseif($data->hinge_side == "B"){
                  $price =$price + $hinge_price->both_hinge_side_price;
               }
               $finish_price = DB::table('product_item_finish_side')->where('product_id', $data->product_id)->first();
               if($data->finish_side == "L"){
                  $price =$price + $finish_price->left_finish_side_price;
               }elseif($data->finish_side == "R"){
                  $price =$price + $finish_price->right_finish_side_price;
               }elseif($data->finish_side == "B"){
                  $price =$price + $finish_price->both_finish_side_price;
               }
         
               $ModificationPrice = DB::table('draft_product')
               ->selectRaw('SUM(item_modification.modification_price ) AS total_modification_price')
               ->where('draft_product.draft_product_id', $data->draft_product_id)
               ->join('draft_product_modification', 'draft_product.draft_product_id', '=', 'draft_product_modification.draft_product_id')
               ->join('item_modification', function ($join) {
               $join->on('draft_product_modification.modification_id', '=', 'item_modification.modification_id')
                       ->on('draft_product.product_id', '=', 'item_modification.product_id');
               })->value('total_modification_price');
            
               $AccessoriesPrice = DB::table('draft_product')
               ->selectRaw('SUM(item_accessories.accessories_price ) AS total_accessories_price')
               ->where('draft_product.draft_product_id', $data->draft_product_id)
               ->join('draft_product_accessories', 'draft_product.draft_product_id', '=', 'draft_product_accessories.draft_product_id')
               ->join('item_accessories', function ($join) {
               $join->on('draft_product_accessories.accessories_id', '=', 'item_accessories.accessories_id')
                       ->on('draft_product.product_id', '=', 'item_accessories.product_id');
               })->value('total_accessories_price');
           
               if($data->cut_depth =="Yes"){
                   $price =$price + $data->cut_depth_price;
                }
    
               $sumOfProductPrice = DB::table('item_dimensions')->where('product_id', $data->product_id)->sum('item_price');
               
               $price =$price + $sumOfProductPrice;
               $totalModificationPrice = $totalModificationPrice + $AccessoriesPrice;
               $price =$price * $data->quantity;
               $sub_total = $sub_total +$price;
               $sub_total = $sub_total + $ModificationPrice +$AccessoriesPrice ;
            }
               $discountedPrice = $sub_total * (1 - ($customer_draft->discount / 100));
               $discount = $customer_draft->discount;
            
           
            $customer_draft= CustomerDraft::where('customer_draft_id',$id)->first();
            $customer_draft->total_price =$discountedPrice;
            $customer_draft->original_price =$sub_total;
            $customer_draft->save();

            $responseArray = array(
                'quantity' => $quantity, // Assuming $quantity is already set in your previous function
                'sub_total' => $sub_total,
                'totalModificationPrice' => $totalModificationPrice,
                'discount' => $discount,
                'discountedPrice' => $discountedPrice,
                );
                // return $responseArray;
            //     dd($responseArray);
            return response()->json($responseArray);     
   
               
          
       }
    
    public function quantity_plus(Request $request)
    {
       
        $productId = $request->input('product_id');
        $draftStyleId = $request->input('draft_style_id');
       
        $draft_product = DraftProduct::where('product_id', $productId)->where('draft_style_id', $draftStyleId)->first();
        $draft_product->quantity = $draft_product->quantity+1;
        $quantity =$draft_product->quantity;
        $customer_draft_id = $draft_product->customer_draft_Id;
        $draft_product->save();
        // dd($this->count_price($customer_draft_id, $quantity ));
    

          
        // return response()->json($quantity);
        return $this->count_price($customer_draft_id, $quantity );
    }
    public function quantity_minus(Request $request)
    {
       
        $productId = $request->input('product_id');
        $draftStyleId = $request->input('draft_style_id');
       
        $draft_product = DraftProduct::where('product_id', $productId)->where('draft_style_id', $draftStyleId)->first();
        $customer_draft_id = $draft_product->customer_draft_Id;
        if($draft_product->quantity >= 2)
        {
        $draft_product->quantity = $draft_product->quantity-1;
        $quantity =$draft_product->quantity;
        $draft_product->save();
        }else{
            $quantity =$draft_product->quantity;
        }
        
          
        // return response()->json($quantity);
        return $this->count_price($customer_draft_id, $quantity );
    }
    public function count_side_price($id , $side){
        $sub_total = 0;
        $totalModificationPrice = 0; 
        $customer_draft = CustomerDraft::where('customer_draft_id',$id)->first();
          $product = DB::table('draft_product')
               ->leftJoin('product_item', 'draft_product.product_id', '=', 'product_item.product_id')
               ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
               ->where('draft_product.customer_draft_id', $id)
               ->select('draft_product.*','product_master.*',
                        'product_item.description as product_description',
                        'product_item.hinge_side as is_hinge_side','product_item.finish_side as is_finish_side','product_item.product_item_price as product_item_price','product_item.cut_depth','product_item.cut_depth_price')
               ->get();
               // dd($product);
               foreach($product as $data){
                   $price = $data->product_item_price;
                   
                   $hinge_price = DB::table('product_item_hinge_side')->where('product_id', $data->product_id)->first();
                   if($data->hinge_side == "L"){
                      $price =$price + $hinge_price->left_hinge_side_price;
                   }elseif($data->hinge_side == "R"){
                      $price =$price + $hinge_price->right_hinge_side_price;
                   }elseif($data->hinge_side == "B"){
                      $price =$price + $hinge_price->both_hinge_side_price;
                   }
                   $finish_price = DB::table('product_item_finish_side')->where('product_id', $data->product_id)->first();
                   if($data->finish_side == "L"){
                      $price =$price + $finish_price->left_finish_side_price;
                   }elseif($data->finish_side == "R"){
                      $price =$price + $finish_price->right_finish_side_price;
                   }elseif($data->finish_side == "B"){
                      $price =$price + $finish_price->both_finish_side_price;
                   }
             
                   $ModificationPrice = DB::table('draft_product')
                   ->selectRaw('SUM(item_modification.modification_price ) AS total_modification_price')
                   ->where('draft_product.draft_product_id', $data->draft_product_id)
                   ->join('draft_product_modification', 'draft_product.draft_product_id', '=', 'draft_product_modification.draft_product_id')
                   ->join('item_modification', function ($join) {
                   $join->on('draft_product_modification.modification_id', '=', 'item_modification.modification_id')
                           ->on('draft_product.product_id', '=', 'item_modification.product_id');
                   })->value('total_modification_price');
                
                   $AccessoriesPrice = DB::table('draft_product')
                   ->selectRaw('SUM(item_accessories.accessories_price ) AS total_accessories_price')
                   ->where('draft_product.draft_product_id', $data->draft_product_id)
                   ->join('draft_product_accessories', 'draft_product.draft_product_id', '=', 'draft_product_accessories.draft_product_id')
                   ->join('item_accessories', function ($join) {
                   $join->on('draft_product_accessories.accessories_id', '=', 'item_accessories.accessories_id')
                           ->on('draft_product.product_id', '=', 'item_accessories.product_id');
                   })->value('total_accessories_price');
               
                   if($data->cut_depth =="Yes"){
                       $price =$price + $data->cut_depth_price;
                    }
        
                   $sumOfProductPrice = DB::table('item_dimensions')->where('product_id', $data->product_id)->sum('item_price');
                   
                   $price =$price + $sumOfProductPrice;
                   $totalModificationPrice = $totalModificationPrice + $AccessoriesPrice;
                   $price =$price * $data->quantity;
                   $sub_total = $sub_total +$price;
                   $sub_total = $sub_total + $ModificationPrice +$AccessoriesPrice ;
                }
                   $discountedPrice = $sub_total * (1 - ($customer_draft->discount / 100));
                   $discount = $customer_draft->discount;
                
               
                $customer_draft= CustomerDraft::where('customer_draft_id',$id)->first();
                $customer_draft->total_price =$discountedPrice;
                $customer_draft->original_price =$sub_total;
                $customer_draft->save();
    
                $responseArray = array(
                    'side' => $side, // Assuming $quantity is already set in your previous function
                    'sub_total' => $sub_total,
                    'totalModificationPrice' => $totalModificationPrice,
                    'discount' => $discount,
                    'discountedPrice' => $discountedPrice,
                    );
                    // return $responseArray;
                //     dd($responseArray);
                return response()->json($responseArray);     
       
                   
              
           }
    public function add_hinge(Request $request)
    {
        $hinge = $request->input('hinge');
        $productId = $request->input('product_id');
        $draftStyleId = $request->input('draft_style_id');
       
        $draft_product = DraftProduct::where('product_id', $productId)->where('draft_style_id', $draftStyleId)->first();
        $draft_product->hinge_side = $hinge;
        $customer_draft_id = $draft_product->customer_draft_Id;
        $draft_product->save();
          
        // return response()->json($hinge);
        return $this->count_side_price($customer_draft_id, $hinge );
    }
    public function add_finish_side(Request $request)
    {
        $finish_side = $request->input('finish');
        $productId = $request->input('product_id');
        $draftStyleId = $request->input('draft_style_id');
       
        $draft_product = DraftProduct::where('product_id', $productId)->where('draft_style_id', $draftStyleId)->first();
        $draft_product->finish_side = $finish_side;
        $customer_draft_id = $draft_product->customer_draft_Id;
        $draft_product->save();
          
        // return response()->json($finish_side);
        return $this->count_side_price($customer_draft_id, $finish_side );
    }
   public function draft_product_store(Request $request,$customer_draft_id){
   

    $CustomerDraft =CustomerDraft::where('customer_draft_id',$customer_draft_id)->first();
    $CustomerDraft->service_type= $request->service_type;
    $CustomerDraft->configuration= $request->configuration;
    $CustomerDraft->save();
    $DraftProduct_count = DraftProduct::where('customer_draft_Id', $customer_draft_id)->count();
   if($DraftProduct_count == 0){
    return redirect('new-draft/'.$customer_draft_id)->with('error', 'Error: Please Select Product');
   }
    else{
        return redirect('add-cart/'.$customer_draft_id);
     
       }
    }
    public function addCart(Request $request,$customer_draft_Id)
    {
      //$custmer_draft_Id = CustomerDraftStyle::find($custmer_draft_Id);
       $pagename = "Add Cart";
       $user = Auth::user()->id;
       $customer_draft = CustomerDraft::where('customer_draft_id',$customer_draft_Id)->first();
    //    $customer = Customer:: where('user_id',$user)->first();
    //    $CustomerGroup = CustomerGroup::where('customer_group_id',$customer->customer_group_id)->first();
      
       
      // dd($customer_draft);
       //dd($customer_draft);
       $product = DB::table('draft_product')
       ->leftJoin('product_item', 'draft_product.product_id', '=', 'product_item.product_id')
       ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
       ->where('draft_product.customer_draft_id', $customer_draft_Id)
       ->where('draft_product.customer_id', $user)
       ->select('draft_product.*','product_master.*',
                'product_item.description as product_description',
                'product_item.hinge_side as is_hinge_side','product_item.finish_side as is_finish_side','product_item.product_item_price as product_item_price','product_item.cut_depth','product_item.cut_depth_price')
       ->get();
        // dd($product);
      
       return view('frontend.draft.add_cart',compact('customer_draft','product','customer_draft_Id','pagename'));
    }
    
    public function draft_product_destroy($id)
    {
       $DraftProduct = DraftProduct::find($id);
       $customer_draft_Id = $DraftProduct->customer_draft_Id;
       $DraftProduct->delete();
       $draft_product_count = DB::table('draft_product')->where('customer_draft_Id', $customer_draft_Id)->count();
      
    //   dd($draft_product_count);
       if($draft_product_count == 0){
        // $json = array('status' => 'null'); 
        // echo json_encode($json);
        //    return redirect('new-draft/'.$customer_draft_Id);
      
        echo  $customer_draft_Id;
       }else{
        $customer_draft_Id =0;
        echo  $customer_draft_Id;
     
        // $json = array('status' => 'success'); 
        // echo json_encode($json);

       }
    //    echo $draft_product_count;
    // $responseData = [
    //     'status' => 'success',
    //     'count' => $draft_product_count,
    //     'customer_draft_id' => $customer_draft_Id,
    // ];
    
    // echo json_encode($responseData);
    
    }
    public function draft_destroy($id){
        $customer_draft=CustomerDraft::find($id);
         CustomerDraftStyle::where('customer_draft_Id',$id)->delete();
         DraftProduct::where('customer_draft_Id',$id)->delete();
        if($customer_draft->delete()){
     
            $json = array('status' => 'success'); 
        }
        else
        {
            $json = array('status' => 'fail');   
        }
       
         echo json_encode($json);
    }
    public function save_draft($status,$id,$total_price,$original_price){
        
        $customer_draft = CustomerDraft::where('customer_draft_id',$id)->first();
       

        $product = DB::table('draft_product')
        ->leftJoin('product_item', 'draft_product.product_id', '=', 'product_item.product_id')
        ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
        ->where('draft_product.customer_draft_id', $id)
        ->select('draft_product.*','product_master.*',
                 'product_item.description as product_description',
                 'product_item.hinge_side as is_hinge_side','product_item.finish_side as is_finish_side','product_item.product_item_price as product_item_price','product_item.cut_depth','product_item.cut_depth_price')
        ->get();
        // dd($product);
        foreach($product as $data){
            $price = $data->product_item_price;
            
            $hinge_price = DB::table('product_item_hinge_side')->where('product_id', $data->product_id)->first();
            if($data->hinge_side == "L"){
               $price =$price + $hinge_price->left_hinge_side_price;
            }elseif($data->hinge_side == "R"){
               $price =$price + $hinge_price->right_hinge_side_price;
            }elseif($data->hinge_side == "B"){
               $price =$price + $hinge_price->both_hinge_side_price;
            }
            $finish_price = DB::table('product_item_finish_side')->where('product_id', $data->product_id)->first();
            if($data->finish_side == "L"){
               $price =$price + $finish_price->left_finish_side_price;
            }elseif($data->finish_side == "R"){
               $price =$price + $finish_price->right_finish_side_price;
            }elseif($data->finish_side == "B"){
               $price =$price + $finish_price->both_finish_side_price;
            }
      
            $ModificationPrice = DB::table('draft_product')
            ->selectRaw('SUM(item_modification.modification_price ) AS total_modification_price')
            ->where('draft_product.draft_product_id', $data->draft_product_id)
            ->join('draft_product_modification', 'draft_product.draft_product_id', '=', 'draft_product_modification.draft_product_id')
            ->join('item_modification', function ($join) {
            $join->on('draft_product_modification.modification_id', '=', 'item_modification.modification_id')
                    ->on('draft_product.product_id', '=', 'item_modification.product_id');
            })->value('total_modification_price');
         
            $AccessoriesPrice = DB::table('draft_product')
            ->selectRaw('SUM(item_accessories.accessories_price ) AS total_accessories_price')
            ->where('draft_product.draft_product_id', $data->draft_product_id)
            ->join('draft_product_accessories', 'draft_product.draft_product_id', '=', 'draft_product_accessories.draft_product_id')
            ->join('item_accessories', function ($join) {
            $join->on('draft_product_accessories.accessories_id', '=', 'item_accessories.accessories_id')
                    ->on('draft_product.product_id', '=', 'item_accessories.product_id');
            })->value('total_accessories_price');
        
            if($data->cut_depth =="Yes"){
                $price =$price + $data->cut_depth_price;
             }
 
            $sumOfProductPrice = DB::table('item_dimensions')->where('product_id', $data->product_id)->sum('item_price');
            
            $price =$price + $sumOfProductPrice;
            $price =$price + $ModificationPrice +$AccessoriesPrice;
            $sub_total =$price * $data->quantity;
            // $sub_total = $sub_total + $ModificationPrice +$AccessoriesPrice ;
            $total = $sub_total * (1 - ($customer_draft->discount / 100));
             //dd($price * (1 - ($customer_draft->discount / 100)));
            $draft_product = DraftProduct::where('customer_draft_Id',$id)->where('product_id', $data->product_id)->first();
            $draft_product->unit_price =  $price;
            $draft_product->sub_total_price =  $sub_total;
            $draft_product->final_unit_price =  $price * (1 - ($customer_draft->discount / 100));
            $draft_product->total_price =  $total;
            $draft_product->save();

            
        }
           
        
        $customer_draft=CustomerDraft::where('customer_draft_id',$id)->first();
       
        $customer_draft->total_price =$total_price;
        $customer_draft->original_price =$original_price;
       
      
        // $DraftProduct = DraftProduct::where('customer_draft_id',$id)->first();
        // $DraftProduct->total_price =$price;
        // $DraftProduct->save();
        if($status == "save"){
            $customer_draft->draft_status ="Save";
            $customer_draft->save();
        return redirect('/dashboard')->with('success','Draft Save!');
        }elseif($status == "quickbooks"){
            $product = DraftProduct::where('customer_draft_Id',$id)->get();
            return redirect()->route('initiate-payment', ['id' => $id]);
        } else{
           $product = DraftProduct::where('customer_draft_Id',$id)->get();
            // return redirect('card-payment/'.$id);
            
            return redirect()->route('card-payment', ['id' => $id]);
            // return redirect('/credit-card/authentication/'.$id);
        }
    }


    public function card_payment($id)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        // $item = DraftProduct::where('customer_draft_Id',$id)->get();

        $user = Auth::user(); 
       
        $item = DB::table('draft_product')
        ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
        ->leftJoin('product_item', 'product_master.product_id', '=', 'product_item.product_id')
        ->select('product_master.product_name','draft_product.final_unit_price', 'product_item.product_item_price','draft_product.quantity')
        ->where('customer_draft_Id',$id)
        ->get();
        //  dd($draft_products);
    

        $lineItems = [];
      
        foreach ($item as $item) {
              $unit_price = $item->final_unit_price *100;
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item->product_name, 
                    ],
                    'unit_amount' => $unit_price ,
                ],
                'quantity' =>  $item->quantity,
            ];
        }
        $statuses = ['Quotation', 'Inprogress', 'Ready', 'Picked', 'Delivered', 'Return'];
        $customer_exists = CustomerDraft::where('customer_id', $user->id)
        ->whereIn('draft_status', $statuses)
        ->exists();
        

        if($customer_exists){
        $customer = User::find($user->id);
        $customer_stripe_id = $customer->customer_stripe_id ;
        // dd("exist");
       
    
     }else{
        $customer = \Stripe\Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'address' => [
                'line1' => '123 Main Street',
                'city' => 'San Francisco',
                'state' => 'CA',
                'country' => 'US',
            ],
        ]);
        
       

        
        
        $customer_stripe_id = $customer->id;
        $new_use = User::find($user->id);
        $new_use->customer_stripe_id = $customer_stripe_id;
        $new_use->save();
       

     }
     
      
     $session = \Stripe\Checkout\Session::create([
        'customer' => $customer_stripe_id,
        'line_items' => $lineItems, 
        'payment_method_types' => ['card'],
        'mode' => 'payment',
        'success_url' => route('success', ['id' => $id]) . '?payment_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => route('card-payment', ['id' => $id]),
        
       
    ]);
   
   
     $customer_draft=CustomerDraft::where('customer_draft_id',$id)->first();
       
     $customer_draft->draft_status ="Quotation";
     $customer_draft->save();
  

    //  return view('frontend.draft.checkout', ['session' => $session]);
        return redirect()->away($session->url);
    }
    public function bank_payment(){
        $pagename = "Bank Payment";
        return view('frontend.draft.bank_payment',compact('pagename'));
    }
    public function success(Request $request,$id,$payment_id = null)
    {   
        $pagename = "Payment Sucess";
        $payment_id = $request->get('payment_id');
       
        
        // dd($paymentMethodId);
        $customer_draft=CustomerDraft::where('customer_draft_id',$id)->first();
        $customer_draft->checkout_session_id =$payment_id;
        $customer_draft->save();
       
        return view('frontend.draft.success',compact('pagename'));
 
        // return "Thanks for you order You have just completed your payment. The seeler will reach out to you as soon as possible";
    }

   public function credit_card_authentication($customer_draft_Id){
    $pagename = "Credit Card Authentication";
    $customer_id = Auth::user()->id;
    $credit_card = CreditCard::where('customer_id',$customer_id)->get();
   
  
    return view('frontend.draft.credit_card_authentication',compact('customer_draft_Id','credit_card','pagename'));
   }

   
   public function store_credit_card($customer_draft_Id,CreditcardForm $request){
    if($request->ajax()){
        return true;
     }
    $customer_id = Auth::user()->id;

    $payment = new CreditCard();  
    $payment->customer_id = $customer_id;
    $payment->credit_card_number = $request->credit_card_number;
    $payment->credit_card_type = $request->credit_card_type;
    $payment->card_member_name = $request->card_member_name;
    $payment->expiration_date = $request->expiration_date;
    $payment->billing_address = $request->billing_address;
    $payment->phone = $request->phone;

    $payment->save();

   
    
     return redirect('payment/'.$customer_draft_Id);
   }
 
   public function order($customer_draft_Id){
    $pagename = "Order";
    $customer_id = Auth::user()->id;
    $credit_card = CreditCard::where('customer_id',$customer_id)->get();
    $customer_draft=CustomerDraft::where('customer_draft_id',$customer_draft_Id)->first();
  
    return view('frontend.draft.order',compact('customer_draft_Id','credit_card','customer_draft','pagename'));
   }

   public function AddServices(Request $request)
   {
      $service=CustomerDraft::where('customer_draft_id',$request->draft_id)->first();
      dd($request->draft_id);
      $service->service_type = $request->service;
      $service->configuration = $request->configuration;
      $service->save();
   }
   
}

