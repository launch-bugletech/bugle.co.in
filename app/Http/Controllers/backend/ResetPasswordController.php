<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use DB;
// use App\Http\Requests\CustomerEditProfileForm;   
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use Hash;
class ResetPasswordController extends Controller
{
    public function edit()
    {
        $pagename = "Reset Password";
        $id = Auth::user()->id;
        $user = User::select('users.*','customer.*')->leftjoin('customer','customer.user_id','users.id')->where('id',$id)->first();

        return view('backend.reset_password.edit',compact('pagename'));
    }
    public function update(Request $request)
    {
   
       
     $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:8|confirmed',
    ]);
        $user = Auth::user();

        if (!(Hash::check($request->current_password, $user->password))) {
            return redirect()->back()->with("error","Your current password is not match with the password.");
        }

        if(strcmp($request->current_password, $request->new_password_confirmation) == 0){
            // Current password and new password same
            return redirect()->back()->with("error","New Password cannot be same as your current password.");
        }
       //dd($request->new_password, $request->new_password_confirmation);
        if(strcmp($request->new_password, $request->new_password_confirmation) == 0)
        {
            $validatedData = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:8|confirmed',
                'new_password_confirmation' => ['same:new_password'],
            ]);
    
            //Change Password
            $user = Auth::user();
            $user->update(['password' => Hash::make($request->new_password)]);
           
            return redirect("admin/home")->with("success","Your Password change Successfully  .");
        }
        else
        {     // New password and Condirm password are not same
            return redirect()->back()->with("error","New Password and Confirm Password are not same .");
        }
    }  
    public function edit_profile() { 
        $pagename = "Customer Profile Edit";
        $id = Auth::user()->id;
        $user = User::select('users.*')->where('id',$id)->first();
            
		return view('backend.reset_password.edit_profile',compact('pagename','user'));
	}
	
   public function update_profile(Request $request)
{
    // ✅ Validation inside controller
    $request->validate([
        'username'      => 'required',
        'first_name'    => 'required',
        'last_name'     => 'required',
        'email'         => 'required|email',
        'phone'         => 'nullable|digits:10',
        'date_of_join'  => 'required|date_format:d-m-Y',
        'date_of_left'  => 'nullable|date_format:d-m-Y|after_or_equal:date_of_join',
        'department'    => 'required|string',
        'location'      => 'nullable|string',
    ]);

    $user = User::findOrFail(Auth::user()->id);

    $user->username   = $request->username;
    $user->first_name = $request->first_name;
    $user->last_name  = $request->last_name;
    $user->email      = $request->email;
    $user->phone      = $request->phone;

    // ✅ Convert dd-mm-yyyy → Y-m-d before save
    $user->date_of_join = Carbon::createFromFormat('d-m-Y', $request->date_of_join)->format('Y-m-d');

    if ($request->filled('date_of_left')) {
        $user->date_of_left = Carbon::createFromFormat('d-m-Y', $request->date_of_left)->format('Y-m-d');
    }

    $user->department = $request->department;
    $user->location   = $request->location;

    $user->save();
    // dd(64534546);
    return redirect('/admin/edit_profile')
        ->with('success', 'Your Profile Updated Successfully');
}       
}