<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CreditcardForm;
use App\Models\CustomerDraft;
use App\Models\DraftProduct;
use DB;
use Illuminate\Support\Facades\Auth;


class TrackingStatusController extends Controller
{
    public function index()
    {
        $pagename = "Tarcking Status";
        $validStatuses = ['Quotation', 'Inprogress', 'Ready', 'Picked', 'Delivered', 'Return'];
        $user = Auth::user()->id;
        
        $tracking_data = CustomerDraft::select('customer.representative_name','customer_draft.customer_draft_id','customer_draft.draft_status',
           DB::raw('GROUP_CONCAT(door_style.name) as door_style_names'))
          ->leftJoin('customer', 'customer.user_id', '=', 'customer_draft.customer_id')
          ->leftJoin('customer_draft_style', 'customer_draft_style.customer_draft_Id', '=', 'customer_draft.customer_draft_id')
          ->leftJoin('door_style', 'door_style.doorStyle_id', '=', 'customer_draft_style.door_style_Id')
          ->where('customer_draft.customer_id', $user)
          ->whereIn('draft_status', $validStatuses)
          ->groupBy('customer.representative_name', 'customer_draft.customer_draft_id', 'customer_draft.draft_status')
          ->get();

          return view('frontend.tracking_status.index',compact('tracking_data','pagename'));
    } 
    public function fetchStyles(Request $request)
    {
        $selectedStatus = $request->input('draft_status');
        $validStatuses = ['Quotation', 'Inprogress', 'Ready', 'Picked', 'Delivered', 'Return'];
        
        $tracking_data = CustomerDraft::select('customer.representative_name','customer_draft.customer_draft_id','customer_draft.draft_status',
           DB::raw('GROUP_CONCAT(door_style.name) as door_style_names'))
          ->leftJoin('customer', 'customer.user_id', '=', 'customer_draft.customer_id')
          ->leftJoin('customer_draft_style', 'customer_draft_style.customer_draft_Id', '=', 'customer_draft.customer_draft_id')
          ->leftJoin('door_style', 'door_style.doorStyle_id', '=', 'customer_draft_style.door_style_Id')
          ->whereIn('draft_status', $validStatuses)
          ->when($selectedStatus, function ($query) use ($selectedStatus) {
            return $query->where('draft_status', $selectedStatus);
            })
          ->groupBy('customer.representative_name', 'customer_draft.customer_draft_id', 'customer_draft.draft_status')
          ->get();
        
         $view = view('frontend.tracking_status.table_rows',['tracking_data' => $tracking_data])->render();

        return response()->json( array('success' => true, 'html'=>$view));
    }
    public function view($id)
    {
        $pagename = "Tarcking Status View";
        $total = CustomerDraft::select('total_price','original_price')->where('customer_draft_id',$id)->first();
        
        $tracking_view = CustomerDraft::select('customer_draft.customer_draft_id', 'customer_draft.draft_status', 'customer_draft_style.door_style_Id', 'door_style.name as style_name', 'door_style.image as door_image')
        ->leftJoin('customer_draft_style', 'customer_draft_style.customer_draft_Id', '=', 'customer_draft.customer_draft_Id')
        ->leftJoin('door_style', 'door_style.doorStyle_id', '=', 'customer_draft_style.door_style_Id')
        ->where('customer_draft.customer_draft_Id', $id)
        ->get();

        $customerDraftId = $id; 

        $matchedDraftProducts = [];

        foreach ($tracking_view as $trackingItem) {
            $doorStyleId = $trackingItem->door_style_Id;

            $draft_products = DB::table('product_master')
                ->leftJoin('product_door_style', 'product_master.product_id', '=', 'product_door_style.product_id')
                ->leftJoin('product_item', 'product_master.product_id', '=', 'product_item.product_id')
                ->leftJoin('draft_product', 'product_master.product_id', '=', 'draft_product.product_id')
                ->where('draft_product.customer_draft_Id', $customerDraftId)
                ->where('product_door_style.door_style_id', $doorStyleId)
                ->select('product_master.*', 'product_item.*', 'draft_product.quantity','draft_product.hinge_side as h_side','draft_product.finish_side as f_side','draft_product.quantity')
                ->get(); 

            $matchedDraftProducts[$trackingItem->customer_draft_id][$doorStyleId] = $draft_products;
        }

         
         return view('frontend.tracking_status.view', compact('tracking_view', 'matchedDraftProducts','total','pagename'));
    }

    public function addCart(Request $request,$customer_draft_Id)
    {
        $pagename = "Add Cart";
        $user = Auth::user()->id;
        $customer_draft = CustomerDraft::where('customer_draft_id',$customer_draft_Id)->first();

        $customerDraftStyles = DB::table('customer_draft_style')
            ->where('customer_draft_id', $customer_draft_Id)
            ->get();

        $products = [];

        foreach ($customerDraftStyles as $customerDraftStyle) {
            $products[$customerDraftStyle->door_style_Id] =     DB::table('draft_product')
            ->leftJoin('product_item', 'draft_product.product_id', '=', 'product_item.product_id')
            ->leftJoin('product_item_hinge_side', 'draft_product.product_id', '=', 'product_item_hinge_side.product_id')
            ->leftJoin('product_item_finish_side', 'draft_product.product_id', '=', 'product_item_finish_side.product_id')
            ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
            ->leftJoin('customer_draft_style','customer_draft_style.draft_style_id','=','draft_product.draft_style_id')
            ->leftJoin('door_style','door_style.doorStyle_id','=','customer_draft_style.door_style_Id')
            ->where('draft_product.customer_draft_id', $customer_draft_Id)
            ->where('customer_draft_style.door_style_Id', $customerDraftStyle->door_style_Id)
            ->where('draft_product.customer_id', $user)
            ->select('draft_product.*','product_master.*',
                     'product_item.product_item_sku as product_item_sku',
                     'product_item.description as product_description','product_item.hinge_side as is_hinge_side','product_item.finish_side as is_finish_side','product_item.product_item_price as product_item_price',
                     'product_item.cut_depth', 'product_item.cut_depth_price','door_style.name','door_style.image',
                     'product_item_hinge_side.right_hinge_side_price', 'product_item_hinge_side.left_hinge_side_price', 'product_item_hinge_side.hinge_side_none',
                     'product_item_finish_side.right_finish_side_price','product_item_finish_side.left_finish_side_price','product_item_finish_side.both_finish_side_price','product_item_finish_side.finish_side_none')
            ->orderBy('draft_product.created_at', 'desc')
            ->get();
             
            $pdf_data = CustomerDraft::select('customer.company_name','customer.representative_name','customer.address as customer_address','customer.contact_number as customer_no','customer.email','customer_draft.client_name','customer_draft.address as client_address','customer_draft.contact_no as client_no','customer_draft.po_number','customer_draft.service_type','customer_draft.designer','customer_draft.configuration','customer_draft.created_at')
            ->leftjoin('customer','customer.user_id','customer_draft.customer_id')
            ->where('customer_draft.customer_draft_id',$customer_draft_Id)
            ->first();
            // DB::table('draft_product')
            //     ->select('draft_product.*', 'product_master.*','product_item.description as product_description','product_item.hinge_side as is_hinge_side', 'product_item.finish_side as is_finish_side', 'product_item.product_item_price as product_item_price',
            //      'product_item.cut_depth', 'product_item.cut_depth_price','door_style.name','door_style.image')
            //     ->leftJoin('product_item', 'draft_product.product_id', '=', 'product_item.product_id')
            //     ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
            //     ->leftJoin('customer_draft_style','customer_draft_style.draft_style_id','=','draft_product.draft_style_id')
            //     ->leftJoin('door_style','door_style.doorStyle_id','=','customer_draft_style.door_style_Id')
            //     ->where('draft_product.customer_draft_id', $customer_draft_Id)
            //     ->where('draft_product.customer_id', $user)
            //     ->where('customer_draft_style.door_style_Id', $customerDraftStyle->door_style_Id)
            //     ->get();
                

     
        }
        $newdata_arr = $products;
        $without_arr = $products;
        
        
        return view('frontend.draft.add_cart',compact('customer_draft','products','customer_draft_Id','newdata_arr','without_arr','pagename','pdf_data'));
    }



 
}

