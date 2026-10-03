<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerDraft;
use Illuminate\Support\Facades\Auth; 

class DashboardController extends Controller
{
   public function index(){
      $user =Auth::user();
      $pagename = "Dashboard";
      if($user){
        $customer_draft = CustomerDraft::where('customer_id', $user->id)
                          // ->whereIn('draft_status', ['Save', 'Quotation','Pending'])
                          ->get();
        return view('frontend.dashboard',compact('customer_draft','pagename'));
      }else{
        $customer_draft = "";
        return view('frontend.dashboard',compact('customer_draft','pagename'));
      }
    
   }
}

