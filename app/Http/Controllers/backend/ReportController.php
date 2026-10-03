<?php

namespace App\Http\Controllers\backend;

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
        return view('backend.report.index',compact('pagename'));
    } 
    public function orderHistory(Request $request)
    {
        $pagename = "Order History";
        $order_nu = CustomerDraft::select('customer_draft_id')->get();
        $query = CustomerDraft::select('customer_draft.customer_draft_id', 'customer_draft.created_at', 'customer.representative_name as customer_name', 'customer_draft.total_price')
        ->leftjoin('customer', 'customer.user_id', 'customer_draft.customer_id');
        

        if ($request->filled('customer_draft_id')) {
            $query->where('customer_draft.customer_draft_id', $request->input('customer_draft_id'));
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('customer_draft.created_at', [$request->input('from_date'), $request->input('to_date')]);
        }

        $order_history = $query->latest()->get();
        return view('backend.report.order_history',compact('order_nu','order_history','pagename'));  
    }
    public function salesPerformance($id)
    {
        $pagename = "Sales Performance";

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

        return view('backend.report.sales_performance',compact('sales_data','pagename','pdf_data'));
    }
    public function customerList()
    {
        $pagename = "Customer List";
        $customer_list = Customer::select('customer_id','company_name','representative_name','contact_number','email','address')->latest()->get();

        return view('backend.report.customer_list',compact('customer_list','pagename'));
    }

}