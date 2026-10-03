<?php

namespace App\Http\Controllers\frontend;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\ExpensesReportForm;
use App\Models\ExpensesReport;
use App\Models\Substations;
use App\Models\ExpensesMaster;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Hash;

class ExpensesReportController extends BaseController {
   
    public function index() {
        $pagename = "Expenses Report Index";
        $created_by = Auth::user()->id;
        $expenses = ExpensesReport::select('*')->where('created_by', $created_by)->get();
        //dd($expenses);

		return view('frontend.expenses_report.index',compact('pagename','expenses'));
	}
    
   
    public function create()
    {
        //dd('hiiiii');
        $pagename = 'Create Expenses Report';
        $expenses = ExpensesMaster::all();
        $project_name=Substations::all();
        return view('frontend.expenses_report.create',compact('pagename','expenses','project_name'));
    }

    public function store(ExpensesReportForm $request){
        //dd('hioiiii');
        if($request->ajax()){
            return true;
        }
        if(Auth::user() != ""){
            $created_by = Auth::user()->id;
        }else{
            $created_by =1;
        }
        $useremail = Auth::user();

        $expenses = new ExpensesReport(); 
        $expenses->project_name = $request->input('selectedProjectCode');
        $expenses->substation_code = $request->name_of_project;
        $expenses->expense_type = $request->input('selectedExpensesCode');
        $expenses->expense_code = $request->expenses_type;
        $expenses->amount = $request->amount;

        $expenses->description = $request->description;
        if($request->hasfile('image'))
        {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = rand().'.'.$extension;
            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png','pdf','xls','doc'])) {
                return back()->withErrors(['image' => 'Only JPG and PNG files are allowed.']);
            }
            $file->move('public/img/invoice',$filename);
            $expenses->invoice=$filename;

        }
        $expenses->created_by = $created_by;
        $expenses->save();

        $data_array = [
            'email' => $useremail->email,
            'name' => $useremail->name,
        ];

        try {
            Mail::send('email.expenses_record', $data_array, function ($message) use ($data_array) {
                $message->from('notifications@akuntha.in', 'Akuntha')
                        ->to('pl.ipopsolution@gmail.com')
                        ->subject('Expenses Request Receive !');
            });
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
      
      
        return redirect('expenses_report/index')->with('success','Expenses Added Successfully');
    
           
        }
        public function destroy($id)
     {
    
       $expenses = ExpensesReport::find($id);   
        if($expenses->delete()){
            $json = array('status' => 'success'); 
        }
        else
        {
            $json = array('status' => 'fail');   
        }
         echo json_encode($json);
       
    }

    public function edit($id) {
        $pagename = "Expenses Report Edit";
        $expenses = ExpensesReport::find($id);
       
        $project_name = Substations::select('substation_code','substation')->where('substation_code',$expenses->substation_code)->first();
        $expense_name=ExpensesMaster::select('expenses_code','expenses_type')->where('expenses_code',$expenses->expense_code)->first();
       
        $expensesAll = ExpensesMaster::all();//whereNotIn('expenses_code', $expense_name->expenses_code)->get();
        $project_nameAll=Substations::all();
        //dd($expense_name->expenses_code);
		return view('frontend.expenses_report.edit',compact('pagename','expenses','project_name','expense_name','project_nameAll','expensesAll'));
	}
	
    public function update(ExpensesReportForm  $request,$id){
        if($request->ajax()){
            return true;
        }
        if(Auth::user() != ""){
            $updated_by = Auth::user()->id;
        }else{
            $updated_by =1;
        }
         
      


        $expenses = ExpensesReport::find($id); 

        $expenses->project_name = $request->input('selectedProjectCode');
        $expenses->substation_code = $request->name_of_project;
        $expenses->expense_type = $request->input('selectedExpensesCode');
        $expenses->expense_code = $request->expenses_type;
        $expenses->amount = $request->amount;
        $expenses->description = $request->description;
        if($request->hasfile('image'))
        {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = rand().'.'.$extension;
            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png','pdf','xls','doc'])) {
                return back()->withErrors(['image' => 'Only JPG and PNG files are allowed.']);
            }
            $file->move('public/img/invoice',$filename);
            $expenses->invoice=$filename;

        }
        $expenses->updated_by= $updated_by;
        $expenses->update();
      
     
    
        return redirect('expenses_report/index')->with('success','Expenses Added Successfully');

    
           
        }
      
      
    
}
