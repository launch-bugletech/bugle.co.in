<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CreditcardForm;
use App\Models\CustomerDraft;
use App\Models\Customer;
use App\Models\DraftProduct;
use App\Models\ProductMaster;
use DB;
use Illuminate\Support\Facades\Auth;


class ReportController extends Controller
{
    public function index()
    {   
        $pagename = "Report";
        return view('frontend.report.index',compact('pagename'));
    } 
    public function orderHistory(Request $request)
    {
        $pagename = "Order History";
         $user_id = Auth::user()->id;
         $order_nu = CustomerDraft::select('customer_draft_id')->where('customer_id',$user_id)->get();
         // dd($order_nu);
        $query = CustomerDraft::select('customer_draft.customer_draft_id', 'customer_draft.created_at', 'customer.representative_name as customer_name', 'customer_draft.total_price')
        ->leftjoin('customer', 'customer.user_id', 'customer_draft.customer_id')
        ->where('customer_draft.customer_id', $user_id);

        if ($request->filled('customer_draft_id')) {
            $query->where('customer_draft.customer_draft_id', $request->input('customer_draft_id'));
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('customer_draft.created_at', [$request->input('from_date'), $request->input('to_date')]);
        }

        $order_history = $query->latest()->get();

       
        return view('frontend.report.order_history',compact('order_history','order_nu','pagename'));
    }
    public function salesOrders($id)
    {
        $pagename = "Sales Orders";
        $customer_id = Auth::user()->id;
        $sales_data = DraftProduct::select('draft_product.product_id','product_item.product_item_name','product_item.description','draft_product.quantity','product_item.product_item_sku','customer_draft.total_price','draft_product.hinge_side','draft_product.finish_side')
        ->leftjoin('product_item','product_item.product_id','=','draft_product.product_id')
        ->leftjoin('customer_draft_style','customer_draft_style.draft_style_id','draft_product.draft_style_id')
        ->leftjoin('customer_draft','customer_draft.customer_draft_id','draft_product.customer_draft_id')
        ->where('draft_product.customer_draft_id',$id)
        ->get();

        $pdf_data = CustomerDraft::select('customer.company_name','customer.representative_name','customer.address as customer_address','customer.contact_number as customer_no','customer.email','customer_draft.client_name','customer_draft.address as client_address','customer_draft.contact_no as client_no','customer_draft.po_number','customer_draft.service_type','customer_draft.designer','customer_draft.configuration','customer_draft.created_at')
        ->leftjoin('customer','customer.user_id','customer_draft.customer_id')
        ->where('customer_draft.customer_draft_id',$id)
        ->first();
        
        return view('frontend.report.sales_orders',compact('sales_data','pdf_data','pagename'));
    }
    public function inventoryLevels()
    {
        $pagename = "Inventory Levels";
        $product = ProductMaster::select('product_name')->get();
        return view('frontend.report.inventory_levels',compact('product','pagename'));
    }
    public function salesTest()
    {$pagename = "Report";
        $customer_draft_Id = '82';
        
         $user = Auth::user()->id;
        $customer_draft = CustomerDraft::where('customer_draft_id',$customer_draft_Id)->first();

        $customerDraftStyles = DB::table('customer_draft_style')
            ->where('customer_draft_id', $customer_draft_Id)
            ->get();

        $products = [];

        foreach ($customerDraftStyles as $customerDraftStyle) {
            $products[$customerDraftStyle->door_style_Id] = DB::table('draft_product')
                ->select('draft_product.*', 'product_master.*','product_item.description as product_description','product_item.hinge_side as is_hinge_side', 'product_item.finish_side as is_finish_side', 'product_item.product_item_price as product_item_price', 'product_item.cut_depth', 'product_item.cut_depth_price','door_style.name','door_style.image')
                ->leftJoin('product_item', 'draft_product.product_id', '=', 'product_item.product_id')
                ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
                ->leftJoin('customer_draft_style','customer_draft_style.draft_style_id','=','draft_product.draft_style_id')
                ->leftJoin('door_style','door_style.doorStyle_id','=','customer_draft_style.door_style_Id')
                ->where('draft_product.customer_draft_id', $customer_draft_Id)
                ->where('draft_product.customer_id', $user)
                ->where('customer_draft_style.door_style_Id', $customerDraftStyle->door_style_Id)
                ->get();
        }
        return view('frontend.report.test',compact('customer_draft','products','customer_draft_Id','pagename'));
    }



 
}

