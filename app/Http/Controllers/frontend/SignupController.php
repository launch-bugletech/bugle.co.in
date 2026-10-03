<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerForm;
use App\Models\User;
use Spatie\Permission\Models\Role;
use DB;
use Illuminate\Support\Facades\Auth;
use Hash;
use App\Models\EmailVerifications;
use Illuminate\Support\Facades\Session;
use Mail;
use Carbon\Carbon;
class SignupController extends Controller
{
    public function create()
    {
        $pagename = "Sign Up";
        $roles = Role::select('name','id')->get();

        return view('frontend.signup.create',compact('pagename','roles'));
    } 

    public function store(CustomerForm  $request){
        if($request->ajax()){
            return true;
        }

        $user = new User();  

        $user->username = $request->username;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role_id = $request->role_id;
        $user->password = Hash::make($request->password);
        $user->assignRole($request->role_id);
    
       if($user->save()){
        return redirect('/')->with('success','Your Account has been created successfully Please wait for admin approval ');
       }
           
    }
 
    // public function otpVerify($user)
    // {
    //     //dd($user);
    //     return view('frontend.auth.otp');
    // }
    
    // public function sendOtp($user)
    // {
    //     $otp = rand(100000, 999999);
    //     $time = time();
    
    //     // Find existing verification record for the previous day
    //     $previousVerification = EmailVerifications::where('email', $user->email)
    //         ->whereDate('created_at', Carbon::yesterday())
    //         ->first();
    
    //     if ($previousVerification) {
    //         // Update the existing record for the previous day
    //         $previousVerification->otp = $otp;
    //         $previousVerification->is_verify = 'No'; // Set is_verify to 'No' for the previous day's record
    //         $previousVerification->created_at = Carbon::yesterday(); // Update the creation date to yesterday
    //         $previousVerification->save();
    //     } else {
    //         // Create a new record for today if no record exists for the previous day
    //         EmailVerifications::create([
    //             'email' => $user->email,
    //             'otp' => $otp,
    //             'is_verify' => 'No',
    //             'created_at' => Carbon::createFromTimestamp($time),
    //         ]);
    //     }
    
    //     $data['email'] = $user->email;
    //     $data['title'] = 'Mail Verification';
    //     $data['body'] = 'Your OTP is:- ' . $otp;
    
    //     Mail::send('auth.mailVerification', ['data' => $data], function ($message) use ($data) {
    //         $message->to($data['email'])->subject($data['title']);
    //     });
    // }
    
    

    // public function otpVerify($id)
    // {
    //     $user = User::where('id',$id)->first();
    //     if(!$user || $user->is_verified == 1)
    //     {
    //         return redirect('/');
    //     }
    //     $email = $user->email;

    //     $this->sendOtp($user);
    //     return view('frontend.auth.otp',compact('email'));
    // }
    public function loadLogin()
    {
        if(Auth::user()){
            return redirect('/dashboard');
        }
        return view('auth.login');
    }
//     public function verifiedOtp(Request $request)
//     {
//         $user = User::where('email',$request->email)->first();
//         $otpData = EmailVerifications::where('otp',$request->otp)->first();
      
//         if(!$otpData){
//             return response()->json(['success' => false,'msg'=> 'You entered wrong OTP']);
//         }
//         else
//         {
//             $currentTime = time();
//             $time = $otpData->created_at;
//             if ($currentTime >= $time->timestamp && $time->timestamp >= $currentTime - (90 + 5)) 
//              {
//                 $otpData->is_verify = 'Yes';
//                  $otpData->save();
//                 User::where('id', $user->id)->update(['is_verified' => 1]);
//                 Auth::login($user); 
//                  User::where('id', $user->id)->update(['is_verified' => null]);
                
//                 return response()->json(['success' => true,'msg'=> 'Mail has been verified']);

//             }
//             else{
//                 return response()->json(['success' => false,'msg'=> 'Your OTP has been Expired']);
//             }

//         }
//    }

public function userLogin(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::attempt($request->only('email', 'password'))) {
        return redirect('/dashboard');
    }

    return back()->with('error', 'Email or password is incorrect');
}


    //  public function userLogin(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'string|required|email',
    //         'password' => 'string|required'
    //     ]);

    //     $userCredential = $request->only('email','password');
    //     $userData = User::where('email',$request->email)->first();
    //     if($userData && $userData->is_verified == 0)
    //     {
    //         $this->sendOtp($userData);
    //         return redirect("/verification/".$userData->id);
    //     }
    //     else if(Auth::attempt($userCredential))
    //     {
    //         dd($userCredential);
    //         return redirect('/dashboard');
    //     }
    //     else
    //     {
    //         return back()->with('error','Username & Password is incorrect');
    //     }
    // }
    public function loadDashboard()
    {
        if(Auth::user()){
            return view('dashboard');
        }
        return redirect('/');
    }
    
}