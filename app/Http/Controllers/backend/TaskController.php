<?php

namespace App\Http\Controllers\backend;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskAssignedMail;
use App\Mail\TaskUpdatedMail;
use App\Mail\TaskStatusChangedMail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\TaskRemark;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskCreatedNotification;


class TaskController extends BaseController
{
    public static function categoryLabels(): array
    {
        return [
            'social_media' => 'Social Media',
            'blog_post'   => 'Blog Posts',
            'design'       => 'Design',
            'development'  => 'Development',
            'client_work'  => 'Client Work',
            'meetings'     => 'Meetings',
        ];
    }

    // ════════════════════════════════════════════════════════════════
    // BOARD
    // ════════════════════════════════════════════════════════════════

    public function board(Request $request)
    {
        $pagename    = 'Tasks';
        $currentUser = auth()->user();

        $tasksQuery = $currentUser->canManageTasks()
            ? Task::with(['project', 'assignedUsers'])
            : Task::with(['project', 'assignedUsers'])
                  ->whereJsonContains('assigned_to', (string) $currentUser->id);

        if ($request->filled('priority')) {
            $tasksQuery->whereIn('priority', (array) $request->priority);
        }
        if ($request->filled('project_id')) {
            $tasksQuery->where('project_id', $request->project_id);
        }
        if ($request->filled('assigned_to')) {
            $tasksQuery->where(function ($q) use ($request) {
                $q->whereJsonContains('assigned_to', (string) $request->assigned_to)
                  ->orWhereJsonContains('assigned_to', (int) $request->assigned_to);
            });
        }
        if ($request->filled('due_date_range')) {
            switch ($request->due_date_range) {
                case 'today':
                    $tasksQuery->whereDate('due_date', Carbon::today());
                    break;
                case 'week':
                    $tasksQuery->whereBetween('due_date', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]);
                    break;
                case 'overdue':
                    $tasksQuery->where('due_date', '<', Carbon::today())
                               ->where('status', '!=', 'completed');
                    break;
            }
        }
        if ($request->filled('task_type')) {
            $tasksQuery->where('task_type', $request->task_type);
        }
        if ($request->filled('quick_filter')) {
            switch ($request->quick_filter) {
                case 'recurring':
                    $tasksQuery->whereIn('task_type', ['daily', 'weekly', 'monthly']);
                    break;
                case 'mine':
                    $tasksQuery->where(function ($q) use ($currentUser) {
                        $q->whereJsonContains('assigned_to', (string) $currentUser->id)
                          ->orWhereJsonContains('assigned_to', (int) $currentUser->id);
                    });
                    break;
                case 'overdue':
                    $tasksQuery->where('due_date', '<', Carbon::today())
                               ->where('status', '!=', 'completed');
                    break;
            }
        }
        if ($request->filled('task_category')) {
            $tasksQuery->where('task_category', $request->task_category);
        }

        $allTasks = $tasksQuery->orderBy('position')->get();
        $tasks    = $allTasks->groupBy('status');
        $projects = Project::where('status', 'active')->get();
        $users    = User::role(['Employee', 'Admin'])->get();

        $stats = [
            'total'       => $allTasks->count(),
            'new'         => $allTasks->where('status', 'new')->count(),
            'in_progress' => $allTasks->where('status', 'in_progress')->count(),
            'completed'   => $allTasks->where('status', 'completed')->count(),
            'recurring'   => $allTasks->whereIn('task_type', ['daily', 'weekly', 'monthly'])->count(),
        ];

        $categoryQuery = $currentUser->canManageTasks()
            ? Task::selectRaw('task_category, COUNT(*) as count')
            : Task::selectRaw('task_category, COUNT(*) as count')
                  ->whereJsonContains('assigned_to', (string) $currentUser->id);

        $categoryCounts = $categoryQuery
            ->whereNotNull('task_category')
            ->groupBy('task_category')
            ->pluck('count', 'task_category');

        $recurringQuery = $currentUser->canManageTasks()
            ? Task::selectRaw('task_type, COUNT(*) as count')
            : Task::selectRaw('task_type, COUNT(*) as count')
                  ->whereJsonContains('assigned_to', (string) $currentUser->id);

        $recurringCounts = $recurringQuery
            ->whereIn('task_type', ['daily', 'weekly', 'monthly'])
            ->groupBy('task_type')
            ->pluck('count', 'task_type');

        $categories = self::categoryLabels();

        // ── Return JSON for AJAX filter requests ──
        if ($request->expectsJson() || $request->ajax()) {
            $tasksForJson = [];
            foreach (['new', 'in_progress', 'completed'] as $status) {
                $tasksForJson[$status] = ($tasks->get($status) ?? collect())->map(function ($task) {
                    $assignedIds = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];
                    $employees   = \App\Models\User::whereIn('id', $assignedIds)->get();
                    return [
                        'id'            => $task->id,
                        'title'         => $task->title,
                        'description'   => $task->description,
                        'priority'      => $task->priority,
                        'task_type'     => $task->task_type ?? 'one_time',
                        'task_category' => $task->task_category,
                        'status'        => $task->status,
                        'position'      => $task->position,
                        'due_date'      => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                        'project_id'    => $task->project_id,
                        'project_name'  => $task->project->name ?? null,
                        'assigned_to'   => $assignedIds,
                        'employees'     => $employees->map(fn($e) => [
                            'id'       => $e->id,
                            'username' => $e->username,
                            'initials' => strtoupper(substr($e->username, 0, 2)),
                        ]),
                        'time_logs'     => $task->time_logs ?? [],
                        'total_seconds' => $task->total_time_spent,
                        'is_overdue'    => $task->is_overdue,
                        'is_due_today'  => $task->is_due_today,
                    ];
                })->values();
            }

            return response()->json([
                'success'         => true,
                'tasks'           => $tasksForJson,
                'stats'           => $stats,
                'categoryCounts'  => $categoryCounts,
                'recurringCounts' => $recurringCounts,
            ]);
        }

        return view('backend.tasks.board', compact(
            'tasks', 'pagename', 'projects', 'users',
            'stats', 'categoryCounts', 'categories', 'recurringCounts'
        ));
    }

    // ════════════════════════════════════════════════════════════════
    // EXPORT — EXCEL
    // ════════════════════════════════════════════════════════════════

    public function exportExcel(Request $request)
    {
        $currentUser = auth()->user();

        $tasksQuery = $currentUser->canManageTasks()
            ? Task::with(['project', 'assignedUsers'])
            : Task::with(['project', 'assignedUsers'])
                  ->whereJsonContains('assigned_to', (string) $currentUser->id);

        // Apply same filters as board
        if ($request->filled('priority'))      $tasksQuery->whereIn('priority', (array) $request->priority);
        if ($request->filled('project_id'))    $tasksQuery->where('project_id', $request->project_id);
        if ($request->filled('task_type'))     $tasksQuery->where('task_type', $request->task_type);
        if ($request->filled('task_category')) $tasksQuery->where('task_category', $request->task_category);
        if ($request->filled('status'))        $tasksQuery->where('status', $request->status);
        if ($request->filled('assigned_to')) {
            $tasksQuery->where(function ($q) use ($request) {
                $q->whereJsonContains('assigned_to', (string) $request->assigned_to)
                  ->orWhereJsonContains('assigned_to', (int) $request->assigned_to);
            });
        }

        $tasks = $tasksQuery->orderBy('status')->orderBy('position')->get();

        $filename = 'tasks_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new class($tasks) implements \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithStyles,
            \Maatwebsite\Excel\Concerns\WithColumnWidths,
            \Maatwebsite\Excel\Concerns\ShouldAutoSize
        {
            private $tasks;

            public function __construct($tasks) { $this->tasks = $tasks; }

            public function collection()
            {
                return $this->tasks->map(function ($task) {
                    $assignedIds = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];
                    $employees   = \App\Models\User::whereIn('id', $assignedIds)->get();

                    return [
                        'id'          => $task->id,
                        'title'       => $task->title,
                        'description' => $task->description ?? '-',
                        'status'      => ucfirst(str_replace('_', ' ', $task->status)),
                        'priority'    => ucfirst($task->priority),
                        'task_type'   => ucfirst(str_replace('_', ' ', $task->task_type ?? 'one_time')),
                        'category'    => $task->task_category ? ucfirst(str_replace('_', ' ', $task->task_category)) : '-',
                        'project'     => $task->project->name ?? '-',
                        'assigned_to' => $employees->pluck('username')->implode(', '),
                        'due_date'    => $task->due_date ? $task->due_date->format('d M Y') : '-',
                        'created_at'  => $task->created_at->format('d M Y'),
                    ];
                });
            }

            public function headings(): array
            {
                return ['#', 'Title', 'Description', 'Status', 'Priority', 'Type', 'Category', 'Project', 'Assigned To', 'Due Date', 'Created'];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => [
                        'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0D6EFD']],
                        'alignment' => ['horizontal' => 'center'],
                    ],
                ];
            }

            public function columnWidths(): array
            {
                return [
                    'A' => 6,  'B' => 30, 'C' => 40, 'D' => 14,
                    'E' => 12, 'F' => 12, 'G' => 16, 'H' => 20,
                    'I' => 25, 'J' => 14, 'K' => 14,
                ];
            }
        }, $filename);
    }

    // ════════════════════════════════════════════════════════════════
    // EXPORT — PDF
    // ════════════════════════════════════════════════════════════════

    public function exportPdf(Request $request)
    {
        $currentUser = auth()->user();

        $tasksQuery = $currentUser->canManageTasks()
            ? Task::with(['project', 'assignedUsers'])
            : Task::with(['project', 'assignedUsers'])
                  ->whereJsonContains('assigned_to', (string) $currentUser->id);

        if ($request->filled('priority'))      $tasksQuery->whereIn('priority', (array) $request->priority);
        if ($request->filled('project_id'))    $tasksQuery->where('project_id', $request->project_id);
        if ($request->filled('task_type'))     $tasksQuery->where('task_type', $request->task_type);
        if ($request->filled('task_category')) $tasksQuery->where('task_category', $request->task_category);
        if ($request->filled('status'))        $tasksQuery->where('status', $request->status);
        if ($request->filled('assigned_to')) {
            $tasksQuery->where(function ($q) use ($request) {
                $q->whereJsonContains('assigned_to', (string) $request->assigned_to)
                  ->orWhereJsonContains('assigned_to', (int) $request->assigned_to);
            });
        }

        $tasks = $tasksQuery->orderBy('status')->orderBy('position')->get()->map(function ($task) {
            $assignedIds     = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];
            $employees       = \App\Models\User::whereIn('id', $assignedIds)->get();
            $task->emp_names = $employees->pluck('username')->implode(', ');
            return $task;
        });

        $stats = [
            'total'       => $tasks->count(),
            'new'         => $tasks->where('status', 'new')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed'   => $tasks->where('status', 'completed')->count(),
        ];

        $pdf = Pdf::loadView('backend.tasks.export-pdf', compact('tasks', 'stats'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('tasks_' . Carbon::now()->format('Y-m-d_His') . '.pdf');
    }

    // ════════════════════════════════════════════════════════════════
    // STORE
    // ════════════════════════════════════════════════════════════════

    public function store(Request $request)
    {
        $isAdmin = auth()->user()->canManageTasks();

        $rules = [
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'priority'      => 'required|in:low,medium,high,urgent',
            'due_date'      => 'nullable|date',
            'status'        => 'required|in:new,in_progress,completed',
            'project_id'    => 'nullable|exists:projects,id',
            'task_category' => 'nullable|in:social_media,blog_post,design,development,client_work,meetings',
            'task_type'     => 'nullable|in:daily,weekly,monthly,one_time',
        ];

        if ($isAdmin) {
            $rules['assigned_to']    = 'required|array';
            $rules['assigned_to.*']  = 'exists:users,id';
        }

        $validated = $request->validate($rules);

        $assignedTo = $isAdmin
            ? array_map('strval', $validated['assigned_to'])
            : [(string) auth()->id()];

        $maxPosition = Task::where('status', $validated['status'])->max('position') ?? 0;

        $task = Task::create([
            'project_id'    => $validated['project_id'] ?? null,
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'assigned_to'   => $assignedTo,
            'priority'      => $validated['priority'],
            'status'        => $validated['status'],
            'due_date'      => $validated['due_date'] ?? null,
            'task_category' => $validated['task_category'] ?? null,
            'task_type'     => $validated['task_type'] ?? 'one_time',
            'position'      => $maxPosition + 1,
        ]);
     
$admins = \App\Models\User::role('Admin')->get();
Notification::send($admins, new TaskCreatedNotification($task));
        $assignedUsers = User::whereIn('id', $assignedTo)->get();
        foreach ($assignedUsers as $user) {
            try { Mail::to($user->email)->send(new TaskAssignedMail($task)); }
            catch (\Exception $e) { \Log::error('Failed to send email: ' . $e->getMessage()); }
        }

        try { Mail::to('dev@bugletech.com')->send(new TaskAssignedMail($task)); }
        catch (\Exception $e) { \Log::error('Failed to send dev notification: ' . $e->getMessage()); }

        return $request->expectsJson()
            ? response()->json(['success' => true, 'task' => $task])
            : redirect()->route('tasks.board')->with('success', 'Task created');
    }

    // ════════════════════════════════════════════════════════════════
    // UPDATE STATUS (drag & drop)
    // ════════════════════════════════════════════════════════════════

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'task_id'  => 'required|exists:tasks,id',
            'status'   => 'required|in:new,in_progress,completed',
            'position' => 'required|integer|min:1',
        ]);

        $task        = Task::findOrFail($validated['task_id']);
        $assignedIds = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];
        $isAssigned  = in_array((string) auth()->id(), $assignedIds) || in_array(auth()->id(), $assignedIds);

        if (!$isAssigned && !auth()->user()->canManageTasks()) {
            return response()->json(['success' => false, 'error' => 'You are not assigned to this task'], 403);
        }

        $oldStatus = $task->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus) {
            Task::where('status', $oldStatus)->where('position', '>', $task->position)->decrement('position');
            Task::where('status', $newStatus)->where('position', '>=', $validated['position'])->increment('position');
            $task->status   = $newStatus;
            $task->position = $validated['position'];
        } else {
            $old = $task->position;
            $new = $validated['position'];
            if ($new < $old)
                Task::where('status', $newStatus)->whereBetween('position', [$new, $old - 1])->increment('position');
            elseif ($new > $old)
                Task::where('status', $newStatus)->whereBetween('position', [$old + 1, $new])->decrement('position');
            $task->position = $new;
        }

        $task->save();

        if (!auth()->user()->canManageTasks() && $newStatus === 'completed') {
            try { Mail::to('jobs@bugletech.com')->send(new TaskStatusChangedMail($task)); }
            catch (\Exception $e) { \Log::error($e->getMessage()); }
        }

        return response()->json([
            'success' => true,
            'task'    => $task->load(['project', 'assignedUsers']),
            'message' => 'Task status updated successfully',
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // UPDATE (edit modal)
    // ════════════════════════════════════════════════════════════════

    public function update(Request $request)
    {
        $rules = [
            'task_id'       => 'required|exists:tasks,id',
            'project_id'    => 'nullable|exists:projects,id',
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'priority'      => 'required|in:low,medium,high,urgent',
            'task_type'     => 'required|in:daily,weekly,monthly,one_time',
            'due_date'      => 'nullable|date',
            'status'        => 'required|in:new,in_progress,completed',
            'task_category' => 'nullable|in:social_media,blog_post,design,development,client_work,meetings',
        ];

        if (auth()->user()->canManageTasks()) {
            $rules['assigned_to']   = 'required|array';
            $rules['assigned_to.*'] = 'exists:users,id';
        }

        $validated   = $request->validate($rules);
        $task        = Task::findOrFail($validated['task_id']);
        $isAdmin     = auth()->user()->canManageTasks();
        $assignedIds = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];
        $isAssigned  = in_array((string) auth()->id(), $assignedIds) || in_array(auth()->id(), $assignedIds);

        if (!$isAdmin && !$isAssigned) {
            return response()->json(['success' => false, 'error' => 'You can only update tasks assigned to you'], 403);
        }

        $oldStatus = $task->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus) {
            Task::where('status', $oldStatus)->where('position', '>', $task->position)->decrement('position');
            $task->position = (Task::where('status', $newStatus)->max('position') ?? 0) + 1;
        }

        $updateData = [
            'title'         => $validated['title'],
            'project_id'    => $validated['project_id'] ?? null,
            'description'   => $validated['description'],
            'priority'      => $validated['priority'],
            'task_type'     => $validated['task_type'] ?? 'one_time',
            'due_date'      => $validated['due_date'],
            'status'        => $newStatus,
            'position'      => $task->position,
            'task_category' => $validated['task_category'] ?? null,
        ];

        if ($isAdmin && isset($validated['assigned_to'])) {
            $updateData['assigned_to'] = array_map('strval', $validated['assigned_to']);
        }

        $task->update($updateData);

        $assignedUserIds = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];
        $assignedUsers   = User::whereIn('id', $assignedUserIds)->get();
        $employeeNames   = $assignedUsers->pluck('username')->implode(', ');

        if ($isAdmin) {
            foreach ($assignedUsers as $user) {
                try { Mail::to($user->email)->send(new TaskUpdatedMail($task)); }
                catch (\Exception $e) { \Log::error('Failed to send email: ' . $e->getMessage()); }
            }
        } else {
            try { Mail::to('jobs@bugletech.com')->send(new TaskStatusChangedMail($task)); }
            catch (\Exception $e) { \Log::error('Failed to send email: ' . $e->getMessage()); }
        }

        $task = $task->fresh()->load(['project', 'assignedUsers']);

        return response()->json([
            'success'       => true,
            'task'          => [
                'id'           => $task->id,
                'title'        => $task->title,
                'description'  => $task->description,
                'status'       => $task->status,
                'priority'     => $task->priority,
                'task_type'    => $task->task_type,
                'due_date'     => $task->due_date,
                'project_id'   => $task->project_id,
                'project_name' => $task->project->name ?? null,
            ],
            'employee_name' => $employeeNames,
            'message'       => 'Task updated successfully',
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // DELETE
    // ════════════════════════════════════════════════════════════════

    public function delete(Request $request)
    {
        abort_unless(auth()->user()->canManageTasks(), 403, 'Only admins can delete tasks');

        $validated = $request->validate(['task_id' => 'required|exists:tasks,id']);
        $task      = Task::findOrFail($validated['task_id']);
        $status    = $task->status;
        $position  = $task->position;

        $task->delete();

        Task::where('status', $status)->where('position', '>', $position)->decrement('position');

        return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
    }

    // ════════════════════════════════════════════════════════════════
    // QUICK ADD
    // ════════════════════════════════════════════════════════════════

    public function quickAdd(Request $request)
    {
        abort_unless(auth()->user()->canManageTasks(), 403, 'Only admins can create tasks');

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high,urgent',
            'task_type'   => 'nullable|in:daily,weekly,monthly,one_time',
            'due_date'    => 'nullable|date',
            'assigned_to' => 'required|exists:users,id',
        ]);

        try {
            $maxPosition = Task::where('status', 'new')->max('position') ?? 0;

            $task = Task::create([
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'assigned_to' => [(string) $validated['assigned_to']],
                'priority'    => $validated['priority'],
                'task_type'   => $validated['task_type'] ?? 'one_time',
                'status'      => 'new',
                'due_date'    => $validated['due_date'],
                'position'    => $maxPosition + 1,
            ]);

            $assignedUser = User::find($validated['assigned_to']);
            if ($assignedUser) {
                try { Mail::to($assignedUser->email)->send(new TaskAssignedMail($task)); }
                catch (\Exception $e) { \Log::error('Failed to send email: ' . $e->getMessage()); }
            }

            $task->load(['project', 'assignedUsers']);

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'task'    => [
                    'id'                 => $task->id,
                    'title'              => $task->title,
                    'description'        => $task->description,
                    'status'             => $task->status,
                    'priority'           => $task->priority,
                    'task_type'          => $task->task_type,
                    'due_date'           => $task->due_date,
                    'employee_name'      => $assignedUser->username ?? 'Unknown',
                    'employee_initials'  => $this->getInitials($assignedUser->username ?? ''),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Quick add task error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error creating task: ' . $e->getMessage()], 500);
        }
    }

    // ════════════════════════════════════════════════════════════════
    // QUICK UPDATE STATUS
    // ════════════════════════════════════════════════════════════════

    public function quickUpdateStatus(Request $request, $id)
    {
        $validated = $request->validate(['status' => 'required|in:new,in_progress,completed']);

        try {
            $task        = Task::findOrFail($id);
            $assignedIds = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];
            $isAssigned  = in_array((string) auth()->id(), $assignedIds) || in_array(auth()->id(), $assignedIds);

            if (!$isAssigned && !auth()->user()->canManageTasks()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $oldStatus = $task->status;
            $newStatus = $validated['status'];

            if ($oldStatus !== $newStatus) {
                Task::where('status', $oldStatus)->where('position', '>', $task->position)->decrement('position');
                $task->position = (Task::where('status', $newStatus)->max('position') ?? 0) + 1;
            }

            $task->status = $newStatus;
            $task->save();

            if (!auth()->user()->canManageTasks() && $oldStatus !== $newStatus) {
                try { Mail::to('jobs@bugletech.com')->send(new TaskStatusChangedMail($task)); }
                catch (\Exception $e) { \Log::error('Failed to send email: ' . $e->getMessage()); }
            }

            return response()->json([
                'success' => true,
                'message' => 'Task status updated',
                'task'    => $task->load(['project', 'assignedUsers']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ════════════════════════════════════════════════════════════════
    // BULK UPDATE
    // ════════════════════════════════════════════════════════════════

    public function bulkUpdate(Request $request)
    {
        abort_unless(auth()->user()->canManageTasks(), 403);

        $validated = $request->validate([
            'task_ids'    => 'required|array',
            'task_ids.*'  => 'exists:tasks,id',
            'action'      => 'required|in:status,priority,task_type,assign,delete',
            'value'       => 'nullable',
        ]);

        try {
            switch ($validated['action']) {
                case 'status':
                    Task::whereIn('id', $validated['task_ids'])->update(['status' => $validated['value']]);
                    break;
                case 'priority':
                    Task::whereIn('id', $validated['task_ids'])->update(['priority' => $validated['value']]);
                    break;
                case 'task_type':
                    Task::whereIn('id', $validated['task_ids'])->update(['task_type' => $validated['value']]);
                    break;
                case 'assign':
                    $assignedTo = is_array($validated['value'])
                        ? array_map('strval', $validated['value'])
                        : [(string) $validated['value']];
                    Task::whereIn('id', $validated['task_ids'])->update(['assigned_to' => json_encode($assignedTo)]);
                    break;
                case 'delete':
                    Task::whereIn('id', $validated['task_ids'])->delete();
                    break;
            }

            return response()->json(['success' => true, 'message' => 'Bulk operation completed successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ════════════════════════════════════════════════════════════════
    // STATISTICS
    // ════════════════════════════════════════════════════════════════

    public function statistics()
    {
        $user  = auth()->user();
        $tasks = $user->canManageTasks()
            ? Task::all()
            : Task::whereJsonContains('assigned_to', (string) $user->id)->get();

        return response()->json([
            'success'    => true,
            'statistics' => [
                'total'       => $tasks->count(),
                'new'         => $tasks->where('status', 'new')->count(),
                'in_progress' => $tasks->where('status', 'in_progress')->count(),
                'completed'   => $tasks->where('status', 'completed')->count(),
                'overdue'     => $tasks->where('due_date', '<', Carbon::today())->where('status', '!=', 'completed')->count(),
                'due_today'   => $tasks->filter(fn($t) => $t->due_date && $t->due_date->isToday())->count(),
                'by_priority' => [
                    'urgent' => $tasks->where('priority', 'urgent')->count(),
                    'high'   => $tasks->where('priority', 'high')->count(),
                    'medium' => $tasks->where('priority', 'medium')->count(),
                    'low'    => $tasks->where('priority', 'low')->count(),
                ],
                'by_type' => [
                    'daily'    => $tasks->where('task_type', 'daily')->count(),
                    'weekly'   => $tasks->where('task_type', 'weekly')->count(),
                    'monthly'  => $tasks->where('task_type', 'monthly')->count(),
                    'one_time' => $tasks->where('task_type', 'one_time')->count(),
                ],
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // RECENT TASKS (dashboard widget)
    // ════════════════════════════════════════════════════════════════

    public function recentTasks()
    {
        $user = Auth::user();

        if ($user->role_id == 1) {
            return response()->json(['success' => false, 'message' => 'Not allowed']);
        }

        $task = Task::where(function ($query) use ($user) {
            $query->whereJsonContains('assigned_to', $user->id)
                  ->orWhereJsonContains('assigned_to', (string) $user->id);
        })->latest()->first(['id', 'title', 'status']);

        return response()->json(['success' => true, 'task' => $task]);
    }

    // ════════════════════════════════════════════════════════════════
    // DAILY VIEW
    // ════════════════════════════════════════════════════════════════

    public function daily(Request $request)
    {
        $date = $request->date
            ? Carbon::parse($request->date)->toDateString()
            : Carbon::today()->toDateString();

        $tasks = Task::with(['project', 'assignedUsers'])
            ->whereDate('created_at', $date)
            ->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
            ->orderBy('status')
            ->get();

        $tasks = $tasks->map(function ($task) {
            $assignedUsers        = $task->assignedUsers;
            $task->employee_names = $assignedUsers->pluck('username')->implode(', ');
            $task->first_employee = $assignedUsers->first();
            return $task;
        });

        $employeeTasks = $tasks->filter(fn($t) => $t->assignedUsers->contains(fn($u) => $u->role_id != 1));
        $adminTasks    = $tasks->filter(fn($t) => $t->assignedUsers->isEmpty() || $t->assignedUsers->contains(fn($u) => $u->role_id == 1));

        $mapTask = fn($task) => [
            'id'                 => $task->id,
            'title'              => $task->title,
            'project_name'       => $task->project->name ?? null,
            'description'        => $task->description,
            'status'             => $task->status,
            'priority'           => $task->priority,
            'task_type'          => $task->task_type,
            'due_date'           => $task->due_date,
            'created_at'         => $task->created_at->format('d M Y, h:i A'),
            'employee_name'      => $task->employee_names,
            'employee_initials'  => strtoupper(substr($task->employee_names, 0, 1)),
        ];

        return response()->json([
            'success'        => true,
            'date'           => $date,
            'employee_tasks' => $employeeTasks->map($mapTask)->values(),
            'admin_tasks'    => $adminTasks->map($mapTask)->values(),
            'employee_stats' => [
                'new'         => $employeeTasks->where('status', 'new')->count(),
                'in_progress' => $employeeTasks->where('status', 'in_progress')->count(),
                'completed'   => $employeeTasks->where('status', 'completed')->count(),
            ],
            'admin_stats' => [
                'new'         => $adminTasks->where('status', 'new')->count(),
                'in_progress' => $adminTasks->where('status', 'in_progress')->count(),
                'completed'   => $adminTasks->where('status', 'completed')->count(),
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // REMARKS — LIST
    // ════════════════════════════════════════════════════════════════

    public function getRemarks($taskId)
    {
        Task::findOrFail($taskId);

        $remarks = TaskRemark::with('user')
            ->where('task_id', $taskId)
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'remark'          => $r->remark,
                'attachment_path' => $r->attachment_path
                    ? asset('storage/' . $r->attachment_path)
                    : null,
                'attachment_name' => $r->attachment_name,
                'attachment_type' => $r->attachment_type,
                'user_name'       => $r->user->username ?? 'Unknown',
                'user_initials'   => strtoupper(substr($r->user->username ?? 'U', 0, 2)),
                'created_at'      => $r->created_at->format('d M Y, h:i A'),
                // Permission flags — used by JS to show/hide edit & delete buttons
                'can_edit'        => $r->user_id === auth()->id() || auth()->user()->canManageTasks(),
                'can_delete'      => auth()->user()->canManageTasks(),
            ]);

        return response()->json(['success' => true, 'remarks' => $remarks]);
    }

    // ════════════════════════════════════════════════════════════════
    // REMARKS — ADD
    // ════════════════════════════════════════════════════════════════

    public function addRemark(Request $request, $taskId)
    {
        Task::findOrFail($taskId);

        $validated = $request->validate([
            'remark'     => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,xlsx,xls,csv,doc,docx,txt|max:10240',
            'link'       => 'nullable|url',
        ]);

        if (empty($validated['remark']) && !$request->hasFile('attachment') && empty($validated['link'])) {
            return response()->json(['success' => false, 'message' => 'Please add a remark, attachment, or link.'], 422);
        }

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;

        if (!empty($validated['link'])) {
            $attachmentPath = $validated['link'];
            $attachmentName = $validated['link'];
            $attachmentType = 'link';
        }

        if ($request->hasFile('attachment')) {
            $file      = $request->file('attachment');
            $extension = strtolower($file->getClientOriginalExtension());
            $path      = $file->store('task-remarks/' . $taskId, 'public');

            $attachmentPath = $path;
            $attachmentName = $file->getClientOriginalName();
            $attachmentType = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image'
                : ($extension === 'pdf' ? 'pdf'
                    : (in_array($extension, ['xlsx', 'xls', 'csv']) ? 'excel'
                        : (in_array($extension, ['doc', 'docx']) ? 'word' : 'text')));
        }

        $remark = TaskRemark::create([
            'task_id'         => $taskId,
            'user_id'         => auth()->id(),
            'remark'          => $validated['remark'] ?? null,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
        ]);

        $remark->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Remark added successfully',
            'remark'  => [
                'id'              => $remark->id,
                'remark'          => $remark->remark,
                'attachment_path' => $attachmentPath && $attachmentType !== 'link'
                    ? asset('storage/' . $attachmentPath)
                    : $attachmentPath,
                'attachment_name' => $remark->attachment_name,
                'attachment_type' => $remark->attachment_type,
                'user_name'       => $remark->user->username ?? 'Unknown',
                'user_initials'   => strtoupper(substr($remark->user->username ?? 'U', 0, 2)),
                'created_at'      => $remark->created_at->format('d M Y, h:i A'),
                'can_edit'        => true,  // just created by current user
                'can_delete'      => auth()->user()->canManageTasks(),
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // REMARKS — UPDATE
    // ════════════════════════════════════════════════════════════════

    public function updateRemark(Request $request, $remarkId)
    {
        $remark = TaskRemark::findOrFail($remarkId);

        // Only the author or an admin can edit
        if ($remark->user_id !== auth()->id() && !auth()->user()->canManageTasks()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate(['remark' => 'required|string|max:2000']);

        $remark->update(['remark' => $validated['remark']]);

        return response()->json([
            'success' => true,
            'message' => 'Remark updated successfully',
            'remark'  => $remark->remark,
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // REMARKS — DELETE
    // ════════════════════════════════════════════════════════════════

    public function deleteRemark($remarkId)
    {
        $remark = TaskRemark::findOrFail($remarkId);

        // Only admins can delete (as requested)
        if (!auth()->user()->canManageTasks()) {
            return response()->json(['success' => false, 'message' => 'Only admins can delete remarks'], 403);
        }

        if ($remark->attachment_path && $remark->attachment_type !== 'link') {
            Storage::disk('public')->delete($remark->attachment_path);
        }

        $remark->delete();

        return response()->json(['success' => true, 'message' => 'Remark deleted']);
    }

    // ════════════════════════════════════════════════════════════════
    // TIMER
    // ════════════════════════════════════════════════════════════════

   public function startTimer(Request $request, $taskId)
{
    $task    = Task::findOrFail($taskId);
    $logs    = $task->time_logs ?? [];
    $running = collect($logs)->first(fn($l) => !isset($l['end']) && $l['user_id'] == auth()->id());

    if ($running) {
        return response()->json(['success' => false, 'message' => 'Timer already running']);
    }

    $startedAt = now()->timestamp;
    $logs[]    = ['start' => $startedAt, 'user_id' => auth()->id(), 'end' => null];

    $task->time_logs = $logs;

    // ── Auto-move to in_progress when timer starts ──────────────
    $statusChanged = false;
    if ($task->status === 'new') {
        $oldPosition = $task->position;

        // Shift remaining 'new' tasks down
        Task::where('status', 'new')
            ->where('position', '>', $oldPosition)
            ->decrement('position');

        // Place at end of in_progress
        $task->position = (Task::where('status', 'in_progress')->max('position') ?? 0) + 1;
        $task->status   = 'in_progress';
        $statusChanged  = true;
    }

    $task->save();

    return response()->json([
        'success'        => true,
        'started_at'     => $startedAt,
        'status_changed' => $statusChanged,
        'new_status'     => $task->status,
        'new_position'   => $task->position,
    ]);
}

    public function stopTimer(Request $request, $taskId)
    {
        $task    = Task::findOrFail($taskId);
        $logs    = $task->time_logs ?? [];
        $updated = false;

        foreach ($logs as &$log) {
            if (!isset($log['end']) && $log['user_id'] == auth()->id()) {
                $log['end'] = now()->timestamp;
                $updated    = true;
                break;
            }
        }

        if (!$updated) {
            return response()->json(['success' => false, 'message' => 'No running timer found']);
        }

        $task->time_logs = $logs;
        $task->save();

        return response()->json(['success' => true, 'total_seconds' => $task->total_time_spent]);
    }

    public function getTimerStatus($taskId)
    {
        $task    = Task::findOrFail($taskId);
        $logs    = $task->time_logs ?? [];
        $running = collect($logs)->first(fn($l) => !isset($l['end']) && $l['user_id'] == auth()->id());

        return response()->json([
            'success'       => true,
            'is_running'    => (bool) $running,
            'started_at'    => $running['start'] ?? null,
            'total_seconds' => $task->total_time_spent,
        ]);
    }

    // ── Private Helpers ──────────────────────────────────────────────

    private function getInitials($name): string
    {
        if (empty($name)) return 'NA';
        $words = explode(' ', $name);
        return count($words) >= 2
            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
            : strtoupper(substr($name, 0, 2));
    }
}