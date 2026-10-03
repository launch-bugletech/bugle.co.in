<?php

namespace App\Http\Controllers\backend;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectFile;


class ProjectController extends BaseController
{
    // List
   public function index()
{
    $pagename = 'Projects';

    $projects = Project::latest()->get();

    return view('backend.projects.index', compact('projects', 'pagename'));
}

   // Create Form
public function create()
{
    $pagename = 'Create Project';
    return view('backend.projects.create', compact('pagename'));
}

// Store
public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'description' => 'nullable',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'status' => 'required',
        'attachment.*' => 'nullable|file|mimes:pdf,xls,xlsx,doc,docx,png,jpg,jpeg'

    ]);

    $data = $request->except('attachment');

    if ($request->hasFile('attachment')) {
        $data['attachment'] = $request->file('attachment')
            ->store('projects', 'public');
    }

    Project::create($data);

    return redirect()->route('admin.projects.index')
        ->with('success', 'Project created successfully.');
}


    // Edit Form
   public function edit(Project $project)
{
    $pagename = 'Project Edit';

    return view('backend.projects.edit', compact('project', 'pagename'));
}


// Update
public function update(Request $request, Project $project)
{
    $request->validate([
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_date'  => 'nullable|date',
        'end_date'    => 'nullable|date',
        'status' => 'required|in:Active,On Hold,Completed,Maintenance',
        'attachment'  => 'nullable|file|mimes:pdf,xls,xlsx,doc,docx,png,jpg,jpeg'
    ]);

    $data = $request->only([
        'name',
        'description',
        'start_date',
        'end_date',
        'status'
    ]);

    // File Upload
    if ($request->hasFile('attachment')) {
        $data['attachment'] = $request->file('attachment')
            ->store('projects', 'public');
    }

    $project->update($data);

    return redirect()
        ->route('admin.projects.index') // ✅ corrected route
        ->with('success', 'Project updated successfully.');
}

    // Delete
    public function destroy(Project $project)
    {
        $project->delete();

       return redirect()->route('admin.projects.index')
    ->with('success', 'Project deleted successfully.');

    }
    // show 
    public function show(Project $project)
{
    $pagename = 'Project View';

    return view('backend.projects.show', compact('project', 'pagename'));
}


}
