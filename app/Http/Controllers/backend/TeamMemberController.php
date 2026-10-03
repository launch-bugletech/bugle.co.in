<?php

namespace App\Http\Controllers\backend;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\TeamMemberForm;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Session;

class TeamMemberController extends BaseController
{
    // public function index()
    // {
    //     $pagename = "Team Member Index";

    //     $team_member = User::select(
    //         'users.*',
    //         'roles.name as role_name'
    //     )
    //         ->leftJoin('roles', 'roles.id', 'users.role_id')
    //         ->get();

    //     return view('backend.team_member.index', compact('pagename', 'team_member'));
    // }
 public function index()
{
    $pagename = 'Team Member Index';

    $query = User::select(
            'users.*',
            'roles.name as role_name'
        )
        ->leftJoin('roles', 'roles.id', '=', 'users.role_id');

    if (auth()->user()->role_id != 1) {
        // Normal user sees only their own record
        $query->where('users.id', auth()->id());
    }

    $team_member = $query->get();

    return view('backend.team_member.index', compact('team_member', 'pagename'));
}


    public function create()
    {
        $pagename = 'Create Team Member';
        $roles = Role::select('id', 'name')->get();

        return view('backend.team_member.create', compact('pagename', 'roles'));
    }
    public function store(TeamMemberForm $request)
    {
        // dd($request->all()); // debug input

        $user = new User();

        $user->username = $request->username;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role_id = $request->role_id;
        $user->department = $request->department;
        $user->location = $request->location;
        $user->date_of_join = $request->date_of_join;
        $user->date_of_left = $request->date_of_left;

        // ✅ Handle status properly
        $user->status = $request->has('status') && $request->status === 'active' ? 'active' : 'inactive';

        $user->password = Hash::make($request->password);

        if ($user->save()) {
            // Assign role using spatie/laravel-permission
            $user->assignRole($request->role_id);

            return redirect('/admin/employees')
                ->with('success', 'Employee Added Successfully');
        }

        return redirect('/admin/employees')
            ->with('error', 'Something went wrong. Please try again.');
    }


    public function edit($id)
    {
        $pagename = "Edit Team Member";
        $user = User::findOrFail($id);
        $roles = Role::select('id', 'name')->get();
        return view('backend.team_member.edit', compact('pagename', 'user', 'roles'));
    }

  public function update_emp(TeamMemberForm $request, $id)
{
    $user = User::findOrFail($id);
    
    // dd($request->all());
    $user->username = $request->username;
    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->role_id = $request->role_id;
    $user->department = $request->department;
    $user->location = $request->location;
    $user->date_of_join = $request->date_of_join;
    $user->date_of_left = $request->date_of_left;

    // Handle status properly
    $user->status = $request->has('status') && $request->status === 'active' ? 'active' : 'inactive';

    // Password update (only if entered)
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    if ($user->save()) {
        // Update role using spatie/laravel-permission
        $user->syncRoles([$request->role_id]);

        return redirect('/admin/employees')
            ->with('success', 'Employee Updated Successfully');
    }

    return redirect('/admin/employees')
        ->with('error', 'Something went wrong. Please try again.');
}

    public function view($id)
    {
        $pagename = "View Team Member";
        $user = User::findOrFail($id);
        $roles = Role::select('id', 'name')->get();

        return view('backend.team_member.view', compact('pagename', 'user', 'roles'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Remove all roles before deleting
        $user->syncRoles([]);
        // dd(465465,$user);
        if ($user->delete()) {
            return redirect('/admin/employees')
                ->with('success', 'Employee Deleted Successfully');
        }

        return redirect('/admin/employees')
            ->with('error', 'Something went wrong. Please try again.');
    }

    public function changeStatus($id)
    {
        $user = User::findOrFail($id);

        $user->status = ($user->status == 'active') ? 'inactive' : 'active';
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
            'current_status' => $user->status
        ]);
    }


}
