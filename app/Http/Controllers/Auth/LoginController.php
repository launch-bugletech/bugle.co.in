<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use App\Models\EmailVerifications;
use App\Models\User;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = RouteServiceProvider::HOME;
  
    protected function redirectTo()
    {
       
        $role_id = Auth::user()->role_id;
        if($role_id == 1 || $role_id == 3){
            return '/admin/home';
        }
        else
        {
            //dd("hiii"); 
           return '/dashboard';    
            // $user = Auth::user()->id;
            //dd($user);
          
        }  
    }

    

    protected function authenticated(Request $request, $user)
{
    // dd($user);
    // assuming status = 0 means inactive
    if ($user->status == 'inactive') {
        // dd($user->status);
        // logout immediately
        Auth::logout();

        // invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // redirect back with message for SweetAlert
        return redirect()->back()->with('inactive', 
            'Your account is inactive. Please contact admin.'
        );
    }
}

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
     public function logout(Request $request) {
       
      Auth::logout();
      return redirect('/');
    }
}
