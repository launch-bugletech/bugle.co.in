<?php

namespace App\Http\Controllers\frontend;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Customer;
use App\Models\StateMaster;
use App\Models\AddDraft;
use App\Models\DoorStyle;
use App\Models\User;
use App\Models\CustomerDraft;
use App\Models\CustomerDraftStyle;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerForm;
use App\Http\Requests\ContactForm;
use Illuminate\Support\Facades\Mail;
use Hash;
use DB;
use Illuminate\Mail\Message;
use Swift_Message;

use Illuminate\Support\Facades\Redirect;

class CustomerController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function create()
    {
        $pagename = 'Create Customer';
        $state = StateMaster::pluck('state_name')->toArray();
        // dd($stateNames);
        return view('frontend.customer.create',compact('pagename','state'));
    }
    public function store(CustomerForm  $request){
        if($request->ajax()){
            return true;
        }

        $user = new User();  

        $user->name = $request->company_name;
        $user->role_id = 2;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->save();
        $randomNumber = 'D' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        while (Customer::where('dealer_number', $randomNumber)->exists()) {
            $randomNumber = 'D' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        }
      
        $customer = new Customer(); 
        $customer->user_id =  $user->id;
        $customer->dealer_number =$randomNumber;
        $customer->company_name = $request->company_name;
        $customer->address = $request->address;
        $customer->representative_name = $request->representative_name;
        $customer->showroom = $request->showroom;
        $customer->contact_number = $request->contact_number;
        $customer->domenstic_lines = $request->domenstic_lines;
        $customer->email = $request->email;
        $customer->import_lines = $request->import_lines;
        $customer->fax = $request->fax;
        $customer->date_business_started = $request->date_business_started;
        $customer->showroom_sq = $request->showroom_sq;
        $customer->annual_cabinet_sales = $request->annual_cabinet_sales;
        $customer->city = $request->city;
        $customer->state = $request->state;
        if($request->hasfile('company_logo'))
        {
            $file = $request->file('company_logo');
            $extension = $file->getClientOriginalExtension();
            $filename = rand().'.'.$extension;

            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                return back()->withErrors(['company_logo' => 'Only JPG and PNG files are allowed.']);
            }
        
            $file->move('public/img/companyLogo',$filename);
            $customer->company_logo=$filename;

        }

        $customer->owner_name = $request->owner_name;
        $customer->owner_address = $request->owner_address;
        $customer->owner_phone = $request->owner_phone;
        $customer->owner_city = $request->owner_city;
        $customer->owner_state = $request->owner_state;
        $customer->owner_zip = $request->owner_zip;
        $customer->owner_email = $request->owner_email;

        $customer->reference_com_name = $request->reference_com_name;
        $customer->reference_address = $request->reference_address;
        $customer->reference_contact_number = $request->reference_contact_number;
        $customer->reference_city = $request->reference_city;
        $customer->reference_state = $request->reference_state;
        $customer->reference_zip = $request->reference_zip;
        $customer->reference_email = $request->reference_email;
        $customer->account_type = $request->account_type;

        $customer->tex_exempt = $request->tex_exempt;
        if($request->tex_exempt == "Yes")
        {
        $customer->tex_id = $request->tex_id;
        if($request->hasfile('tex_exempt_form'))
        {
            $file = $request->file('tex_exempt_form');
            $extension = $file->getClientOriginalExtension();
            $filename1 = rand().'.'.$extension;
            $file->move('public/img/texExemptForm',$filename1);
            $customer->tex_exempt_form=$filename1;

        }
     }
        $customer->created_by =$user->id ;
        // Auth::login($user);

      
            $data_array = [
                'email' => $request->email,
                'company_name' => $request->company_name,
                'customer_name' => $request->representative_name,
            ];
         try {
            Mail::send('email.register', $data_array, function ($message) use ($data_array) {
                $message->from('orders@designmykitchen.cloud', 'Deluxewood Cabinetry')
                        ->to($data_array['email'])
                        ->subject('Registration Confirmation and Status Update');
            });
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

       if($customer->save()){
        return redirect('/')->with('success','Your Account has been created successfully Please wait for admin approval ');
       }
           
        }

     public function contact()
     {
        $pagename = 'Contact Us';
        return view('frontend.customer.contact',compact('pagename'));
     }
    
    public function contactStore(ContactForm $request)
    {
        if ($request->ajax()) {
            return true;
        }

        $name = $request->contact_name;
        $subject = $request->subject;
        $phone = $request->contact_no;
        $msg = $request->message;

        $data_array['name'] = $name;
        $data_array['subject'] = $subject;
        $data_array['phone'] = $phone;
        $data_array['msg'] = $msg;

        try {
            Mail::send('email.contact', $data_array, function ($message) use ($data_array) {
                $message->from('orders@designmykitchen.cloud', 'Deluxewood Cabinetry')
                        ->to('pl.ipopsolution@gmail.com')
                        ->subject('Contact Us');
            });
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return redirect('/contact-us')->with('success', 'Message Sent Successfully');
    }
     public function about()
     {
        $pagename = 'About Us';
        return view('frontend.customer.about',compact('pagename'));
     }
     public function termCondition()
     {
         $pagename = 'Terms And Conditions';
        return view('frontend.customer.terms_condition',compact('pagename'));
     }
      public function accessDenied()
     {
         $pagename = 'Access Denied';
        return view('frontend.customer.access_denied',compact('pagename'));
     }
     public function draft_product(Request $request,$draft_style_id)
     {  
         $pagename = 'Draft Product';
        $draft_style = AddDraft::find($draft_style_id);
        $img=DoorStyle::select('image','name')->where('doorStyle_id',$draft_style->door_style_Id)->first();
        $nm=$img->name;
         $img_nm=$img->image;
           
        $products = DB::table('product_master')
        ->leftJoin('product_door_style', 'product_master.product_id', '=', 'product_door_style.product_id')
        ->leftJoin('product_item', 'product_master.product_id', '=', 'product_item.product_id')
        ->where('product_door_style.door_style_id', $draft_style->door_style_Id)
        ->where('product_item.product_item_sku', 'LIKE','%'.$request->search.'%')
        ->select('product_master.*','product_item.*')
        ->get();
    
           return view('frontend.draft.add_draft',compact('draft_style','nm','img_nm','products','draft_style_id','pagename'));
     }

     public function addCart(Request $request,$customer_draft_Id)
     {
         $pagename = 'Add Cart';
        $user = Auth::user()->id;
        $customer_draft = CustomerDraft::where('customer_draft_id',$customer_draft_Id)->first();

        $product = DB::table('draft_product')
        ->leftJoin('product_item', 'draft_product.product_id', '=', 'product_item.product_id')
        ->leftJoin('product_master', 'product_master.product_id', '=', 'draft_product.product_id')
        ->where('draft_product.customer_draft_id', $customer_draft_Id)
        ->where('draft_product.customer_id', $user)
        ->select('draft_product.*','product_master.*','product_item.*')
        ->get();
       //dd($product);
        
        return view('frontend.draft.add_cart',compact('customer_draft','product','pagename'));
     }
      
}

