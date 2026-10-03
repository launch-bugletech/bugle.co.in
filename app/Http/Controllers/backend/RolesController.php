<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\RolesForm;
use App\Models\User;
use DB;
use Auth;


class RolesController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:show_role|add_role|edit_role|delete_role', ['only' => ['index','store']]);
         $this->middleware('permission:add_role', ['only' => ['create','store']]);
         $this->middleware('permission:edit_role', ['only' => ['edit','update']]);
         $this->middleware('permission:delete_role', ['only' => ['destroy']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
  
    public function index(Request $request)
    {

      $pagename = "Roles";
      $roles = Role::with('permissions')->get();
      $permissions = Permission::all();
      $options = array_column($permissions->toArray(), "name");

      // $role=Role::find(2);
      // dd($role->hasPermissionTo("add_role"));
      
      $can= [
            'add' => auth()->user()->can('add_role'),
            'edit' => auth()->user()->can('edit_role'),
            'delete' => auth()->user()->can('delete_role'),
            'show' => auth()->user()->can('show_role')
        ];

      return view('backend.roles.index',compact('pagename','permissions','roles','options','can'));
    } 
    public function create()
    {

      $pagename = "Roles Create";
      $permission = Permission::get();

      return view('backend.roles.create',compact('pagename','permission'));  
    }
    public function store(Request $request)
    {
      if($request->ajax()){
          return true;
      }
      
      $this->validate($request, [
          'name' => 'required|unique:roles,name',
          'permission' => 'required',
      ]);

      $role = Role::create(['name' => $request->input('name')]);
      $role->syncPermissions($request->input('permission'));
    
      return redirect()->route('roles.index')
                        ->with('success','Role created successfully');
    }
    public function show($id)
    {
      $pagename = "Roles View";
      $role = Role::find($id);
      $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
          ->where("role_has_permissions.role_id",$id)
          ->get();
        
        return view('backend.roles.show',compact('role','rolePermissions','pagename'));
    }
     public function edit($id)
    {
      $pagename = "Roles Edit";
      $role = Role::find($id);
      $permission = Permission::get();
      
      $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
          ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
          ->all();
         
        return view('backend.roles.edit',compact('role','permission','rolePermissions','pagename'));
    }
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);
       
        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();
    
        $role->syncPermissions($request->input('permission'));
    
        return redirect()->route('roles.index')
                        ->with('success','Role updated successfully');
    }
    public function destroy($id)
    {
        DB::table("roles")->where('id',$id)->delete();
        return redirect()->route('roles.index')
                        ->with('success','Role deleted successfully');
    }
    
    
}