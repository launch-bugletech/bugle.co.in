<?php

namespace App\Http\Controllers\frontend;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CustomerEditProfileForm;    
use DB;


use Hash;

class CustomerEditProfileController extends BaseController {
   
    
    public function edit() { 
        $pagename = "Customer Profile Edit";
        $id = Auth::user()->id;
        $user = User::select('users.*')->where('id',$id)->first();
            
		return view('frontend.customer_edit_profile.edit',compact('pagename','user'));
	}
	
    public function update(CustomerEditProfileForm  $request,$id)
    {
        if($request->ajax()){
            return true;
        }
      
        $user = User::find(Auth::user()->id); 
        $user->name = $request->name;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        // $user->save();
       
       if($user->save()){
      
        return redirect('/customer_edit_profile/edit')->with('success','Your Profile Updated  Successfully');
       }
}}
