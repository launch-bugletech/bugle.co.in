@extends(backendView('layouts.app'))

@section('title', 'Task Board')

@section('content')
    <div class="container-xxl">

        <div class="row mb-3 align-items-center">
            <div class="col">
                <h3 class="fw-bold">Task Board</h3>
                @if(!auth()->user()->canManageTasks())
                    <small class="text-muted">My Tasks</small>
                @endif
            </div>

            {{-- ================= FILTER BAR ================= --}}
            <div class="row mb-3 g-2 align-items-end" id="filterBar">
                @if(auth()->user()->canManageTasks())
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-semibold text-muted mb-1">
                            <i class="fa fa-user"></i> Employee
                        </label>
                        <select id="filterEmployee" class="form-select form-select-sm">
                            <option value="">All Employees</option>
                            @foreach(\App\Models\User::role(['Employee', 'Admin'])->get() as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->username }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-3">
                    <label class="form-label form-label-sm fw-semibold text-muted mb-1">
                        <i class="fa fa-folder"></i> Project
                    </label>
                    <select id="filterProject" class="form-select form-select-sm">
                        <option value="">All Projects</option>
                        @foreach(\App\Models\Project::where('status', 'active')->get() as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label form-label-sm fw-semibold text-muted mb-1">
                        <i class="fa fa-flag"></i> Priority
                    </label>
                    <select id="filterPriority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-sm btn-secondary w-100" id="clearFiltersBtn">
                        <i class="fa fa-times"></i> Clear Filters
                    </button>
                </div>

                <div class="col-12" id="activeFilterBadges"></div>
            </div>

            <div class="col text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                    <i class="fa fa-plus"></i> New Task
                </button>
            </div>
        </div>

        {{-- ================= KANBAN BOARD (last 7 days) ================= --}}
        @php
            /*
             * The controller passes $tasks as a Laravel Collection keyed by status
             * (the result of $allTasks->groupBy('status')).
             * Use ->get() to safely retrieve each bucket — returns an empty Collection
             * if a status has no tasks, so no null errors.
             *
             * created_at is already a Carbon instance on Eloquent models (cast
             * automatically), so we can call ->gte() / ->lt() directly.
             */
            $cutoff         = \Carbon\Carbon::now()->subDays(7)->startOfDay();
            $recentByStatus = [];
            $allOldTasks    = collect();

            foreach (['new', 'in_progress', 'completed'] as $statusKey) {
                // $tasks is a Collection from groupBy() — use ->get(), not array access
                $bucket = $tasks->get($statusKey, collect());

               if ($statusKey === 'completed') {
    // Completed: split by age — recent stay in kanban, old go to the list below
    $recent = $bucket->filter(fn($t) => $t->created_at->gte($cutoff));
    $old    = $bucket->filter(fn($t) => $t->created_at->lt($cutoff));
    $recentByStatus[$statusKey] = $recent;
    $allOldTasks = $allOldTasks->merge($old);
} else {
    // New / In Progress: always show ALL tasks in kanban, regardless of age
    $recentByStatus[$statusKey] = $bucket;
}
            }

            $allOldTasks = $allOldTasks->sortByDesc('created_at')->values();
        @endphp

        <div class="row kanban-board g-3">
            @foreach(['new' => 'New', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $key => $label)
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header fw-bold text-center bg-light">
                            {{ $label }}
                            <span class="badge bg-secondary ms-2 col-count" data-col="{{ $key }}">
                                {{ $recentByStatus[$key]->count() }}
                            </span>
                        </div>
                        <div class="card-body task-column p-2" data-status="{{ $key }}" style="min-height: 400px;">
                            @foreach($recentByStatus[$key] as $task)
                                @php
                                    $assignedIds   = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];
                                    $employees     = \App\Models\User::whereIn('id', $assignedIds)->get();
                                    $employeeNames = $employees->pluck('username')->implode(', ');
                                    $timeLogs      = $task->time_logs ?? [];
                                    $totalSeconds  = collect($timeLogs)->sum(fn($l) => isset($l['end']) ? $l['end'] - $l['start'] : 0);
                                    $runningLog    = collect($timeLogs)->first(fn($l) => !isset($l['end']) && $l['user_id'] == auth()->id());
                                @endphp

                                <div class="card mb-2 task-card"
                                     data-id="{{ $task->id }}"
                                     data-position="{{ $task->position }}"
                                     data-priority="{{ $task->priority }}"
                                     data-project-id="{{ $task->project_id ?? '' }}"
                                     data-assigned-ids="{{ implode(',', $assignedIds) }}">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold task-title">{{ $task->title }}</div>
                                                <small class="text-muted task-description">{{ $task->description }}</small>
                                            </div>
                                            <div class="d-flex gap-1 flex-wrap justify-content-end">
                                                @if(auth()->user()->canManageTasks())
                                                    <button class="btn btn-sm btn-primary edit-task-btn"
                                                            data-id="{{ $task->id }}"
                                                            data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}"
                                                            data-description="{{ htmlspecialchars($task->description ?? '', ENT_QUOTES) }}"
                                                            data-assigned='@json($task->assigned_to)'
                                                            data-priority="{{ $task->priority }}"
                                                            data-project="{{ $task->project_id ?? '' }}"
                                                            data-due="{{ $task->due_date ?? '' }}"
                                                            data-status="{{ $task->status }}"
                                                            data-is-admin="true">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-task-btn" data-id="{{ $task->id }}">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                @elseif(in_array(auth()->id(), $assignedIds))
                                                    <button class="btn btn-sm btn-primary edit-task-btn"
                                                            data-id="{{ $task->id }}"
                                                            data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}"
                                                            data-description="{{ htmlspecialchars($task->description ?? '', ENT_QUOTES) }}"
                                                            data-assigned='@json($task->assigned_to)'
                                                            data-priority="{{ $task->priority }}"
                                                            data-project="{{ $task->project_id ?? '' }}"
                                                            data-due="{{ $task->due_date ?? '' }}"
                                                            data-status="{{ $task->status }}"
                                                            data-assignee-name="{{ $employeeNames }}"
                                                            data-is-admin="false">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                @endif

                                                <button class="btn btn-sm btn-info remark-btn text-white"
                                                        data-id="{{ $task->id }}"
                                                        data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}">
                                                    <i class="fa fa-comments"></i>
                                                </button>

                                                @if(in_array(auth()->id(), $assignedIds) || auth()->user()->canManageTasks())
                                                    <button class="btn btn-sm timer-btn {{ $runningLog ? 'btn-danger' : 'btn-success' }}"
                                                            data-id="{{ $task->id }}"
                                                            data-running="{{ $runningLog ? 'true' : 'false' }}"
                                                            data-started-at="{{ $runningLog ? $runningLog['start'] : '' }}"
                                                            data-total="{{ $totalSeconds }}"
                                                            title="{{ $runningLog ? 'Stop Timer' : 'Start Timer' }}">
                                                        <i class="fa {{ $runningLog ? 'fa-stop-circle' : 'fa-play-circle' }}"></i>
                                                        <span class="timer-display">{{ gmdate('H:i:s', $totalSeconds) }}</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            <span class="badge task-priority
                                                @if($task->priority == 'high') bg-danger
                                                @elseif($task->priority == 'medium') bg-warning text-dark
                                                @else bg-secondary @endif">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                            @if($task->project)
                                                <span class="badge bg-dark text-white task-project">{{ $task->project->name }}</span>
                                            @endif
                                            @if($task->due_date)
                                                <span class="badge bg-light text-dark task-due">
                                                    Due: {{ \Carbon\Carbon::parse($task->due_date)->format('d M') }}
                                                </span>
                                            @endif
                                            <span class="badge bg-info text-dark task-assignee">Assign to: {{ $employeeNames }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


        {{-- ================= OLDER TASKS LIST (> 7 days) ================= --}}
        <div class="mt-5" id="olderTasksSection" style="{{ $allOldTasks->count() === 0 ? 'display:none' : '' }}">
            <div class="d-flex align-items-center justify-content-between mb-3 w-100">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="fw-bold mb-0">
                        <i class="fa fa-archive text-muted me-1"></i> Completed Older Tasks
                    </h5>
                    <span class="badge bg-secondary" id="oldTaskCount">{{ $allOldTasks->count() }}</span>
                    <small class="text-muted">(completed tasks older than 7 days)</small>
                </div>
                <button class="btn btn-sm btn-secondary flex-shrink-0" id="toggleOldTasks">
                    <i class="fa fa-chevron-down me-1"></i> Show
                </button>
            </div>
            <div id="oldTasksTable" class="d-none">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="oldTasksDataTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Priority</th>
                                        <th>Project</th>
                                        <th>Assigned To</th>
                                        <th>Due Date</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allOldTasks as $task)
                                        @php
                                            $assignedIds   = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];
                                            $employees     = \App\Models\User::whereIn('id', $assignedIds)->get();
                                            $employeeNames = $employees->pluck('username')->implode(', ');


                                        @endphp
                                        <tr class="old-task-row"
                                            data-priority="{{ $task->priority }}"
                                            data-project-id="{{ $task->project_id ?? '' }}"
                                            data-assigned-ids="{{ implode(',', $assignedIds) }}">

                                            <td>
                                                <div class="fw-semibold">{{ $task->title }}</div>
                                                @if($task->description)
                                                    <small class="text-muted">{{ Str::limit($task->description, 60) }}</small>
                                                @endif
                                            </td>

                                            <td>
                                                <span class="badge
                                                    @if($task->priority == 'high') bg-danger
                                                    @elseif($task->priority == 'medium') bg-warning text-dark
                                                    @else bg-secondary @endif">
                                                    {{ ucfirst($task->priority) }}
                                                </span>
                                            </td>

                                            <td>{{ optional($task->project)->name ?? '—' }}</td>

                                            <td><span class="badge bg-info text-dark">{{ $employeeNames ?: '—' }}</span></td>

                                            <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : '—' }}</td>

                                            <td>
                                                <span class="text-muted small">
                                                    {{ \Carbon\Carbon::parse($task->created_at)->format('d M Y') }}
                                                </span>
                                            </td>

                                            <td class="text-end">
                                                <div class="d-flex gap-1 justify-content-end flex-wrap">
                                                    @if(auth()->user()->canManageTasks())
                                                        {{-- NOTE: no data-bs-toggle/target here — JS opens the modal manually --}}
                                                        <button class="btn btn-sm btn-primary edit-task-btn"
                                                                data-id="{{ $task->id }}"
                                                                data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}"
                                                                data-description="{{ htmlspecialchars($task->description ?? '', ENT_QUOTES) }}"
                                                                data-assigned='@json($task->assigned_to)'
                                                                data-priority="{{ $task->priority }}"
                                                                data-project="{{ $task->project_id ?? '' }}"
                                                                data-due="{{ $task->due_date ?? '' }}"
                                                                data-status="{{ $task->status }}"
                                                                data-is-admin="true">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger delete-task-btn" data-id="{{ $task->id }}">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    @elseif(in_array(auth()->id(), $assignedIds))
                                                        <button class="btn btn-sm btn-primary edit-task-btn"
                                                                data-id="{{ $task->id }}"
                                                                data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}"
                                                                data-description="{{ htmlspecialchars($task->description ?? '', ENT_QUOTES) }}"
                                                                data-assigned='@json($task->assigned_to)'
                                                                data-priority="{{ $task->priority }}"
                                                                data-project="{{ $task->project_id ?? '' }}"
                                                                data-due="{{ $task->due_date ?? '' }}"
                                                                data-status="{{ $task->status }}"
                                                                data-assignee-name="{{ $employeeNames }}"
                                                                data-is-admin="false">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                    @endif

                                                    <button class="btn btn-sm btn-info remark-btn text-white"
                                                            data-id="{{ $task->id }}"
                                                            data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}">
                                                        <i class="fa fa-comments"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fa fa-inbox fa-lg d-block mb-2 opacity-50"></i>
                                                No older tasks found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /container-xxl --}}


    {{-- ================= CREATE TASK MODAL ================= --}}
    <div class="modal fade" id="createTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('tasks.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Task Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>

                        @if(auth()->user()->canManageTasks())
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Assign To <span class="text-danger">*</span></label>
                                <select name="assigned_to[]" class="form-select assign-select-create" multiple required>
                                    @foreach(\App\Models\User::role(['Employee', 'Admin'])->get() as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="assigned_to[]" value="{{ auth()->id() }}">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Assigned To</label>
                                <input type="text" class="form-control bg-light" value="{{ auth()->user()->username }}" disabled>
                                <small class="text-muted"><i class="fa fa-info-circle"></i> Task will be assigned to you automatically</small>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">Select project</option>
                                @foreach(\App\Models\Project::where('status', 'active')->get() as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>
                        </div>
                        <input type="hidden" name="status" value="new">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Task</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= EDIT TASK MODAL ================= --}}
    <div class="modal fade" id="editTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editTaskForm">
                @csrf
                <input type="hidden" name="task_id" id="edit-task-id">
                <input type="hidden" id="edit-is-admin" value="{{ auth()->user()->canManageTasks() ? 'true' : 'false' }}">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Task Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assign To <span class="text-danger">*</span></label>
                            <select name="assigned_to[]" id="edit-assigned" class="form-select assign-select-edit" multiple required
                                    @if(!auth()->user()->canManageTasks()) disabled @endif>
                                @foreach(\App\Models\User::role(['Employee', 'Admin'])->get() as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->username }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Project</label>
                            <select name="project_id" id="edit-project" class="form-select">
                                <option value="">Select project</option>
                                @foreach(\App\Models\Project::where('status', 'active')->get() as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" id="edit-priority" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" id="edit-due" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit-status" class="form-select">
                                <option value="new">New</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveTaskBtn">
                            <i class="fa fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= REMARK MODAL ================= --}}
    <div class="modal fade" id="remarkModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-comments"></i>
                        Remarks — <span id="remarkTaskTitle" class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="remarksList" style="max-height:350px; overflow-y:auto;" class="mb-4">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <p class="text-muted small mt-1">Loading remarks...</p>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <h6 class="fw-bold mb-3"><i class="fa fa-plus-circle text-primary"></i> Add Remark</h6>
                        <input type="hidden" id="currentTaskId">
                        <div class="mb-3">
                            <textarea id="remarkText" class="form-control" rows="3" placeholder="Write your remark here..."></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Attachment (optional)</label>
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <button type="button" class="btn btn-sm btn-secondary attach-type-btn active" data-type="none">✕ None</button>
                                <button type="button" class="btn btn-sm btn-primary attach-type-btn" data-type="file">📎 File</button>
                                <button type="button" class="btn btn-sm btn-success attach-type-btn" data-type="link">🔗 Link</button>
                            </div>
                        </div>
                        <div id="fileUploadSection" class="mb-3 d-none">
                            <input type="file" id="remarkAttachment" class="form-control"
                                   accept=".jpg,.jpeg,.png,.gif,.pdf,.xlsx,.xls,.csv,.doc,.docx,.txt">
                            <small class="text-muted">Supported: Images, PDF, Excel, Word, Text (max 10MB)</small>
                            <div id="filePreview" class="mt-2 d-none">
                                <div class="d-flex align-items-center gap-2 p-2 border rounded bg-light">
                                    <i class="fa fa-file fa-lg text-primary" id="filePreviewIcon"></i>
                                    <span id="filePreviewName" class="small fw-semibold"></span>
                                    <button type="button" class="btn btn-sm btn-link text-danger ms-auto p-0" id="clearFileBtn">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="linkSection" class="mb-3 d-none">
                            <input type="url" id="remarkLink" class="form-control" placeholder="https://docs.google.com/... or any URL">
                            <small class="text-muted">Paste Google Docs, Drive, Sheets, or any link</small>
                        </div>
                        <button type="button" class="btn btn-primary" id="submitRemarkBtn">
                            <i class="fa fa-paper-plane"></i> Add Remark
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= TIMER DETAIL MODAL ================= --}}
    <div class="modal fade" id="timerDetailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-clock-o text-success"></i>
                        Time Log — <span id="timerTaskTitle" class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="display-4 fw-bold text-success" id="timerTotalDisplay">00:00:00</div>
                        <small class="text-muted">Total time spent on this task</small>
                    </div>
                    <div id="timerLogsList">
                        <p class="text-muted text-center">No time logs yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* ── Remarks ── */
        .remark-item { padding:.75rem; border-radius:8px; background:#f9fafb; border:1px solid #e5e7eb; transition:all .2s; }
        .remark-item:hover { background:#f3f4f6; }
        .attach-type-btn.active { font-weight:700; }

        /* ── Kanban ── */
        .task-card { cursor:grab; margin-bottom:8px; transition:all .3s ease; }
        .task-card.dragging { cursor:grabbing; opacity:.5; }
        .task-placeholder { border:2px dashed #007bff; background:#e7f3ff; min-height:80px; margin-bottom:8px; border-radius:4px; }
        .task-column { min-height:400px; transition:background-color .2s ease; }
        .task-card:hover { box-shadow:0 4px 6px rgba(0,0,0,.1); transform:translateY(-2px); }
        .task-title { word-wrap:break-word; font-size:.95rem; }
        .task-description { display:block; margin-top:4px; font-size:.85rem; }

        /* ── Select2 ── */
        .assign-select-create, .assign-select-edit { min-height:120px; border-radius:12px; padding:10px; transition:.3s ease; }
        .select2-container--default .select2-selection--multiple { border-radius:12px; min-height:45px; padding:5px; }
        .select2-selection__choice { background-color:#0d6efd !important; border:none !important; color:white !important; padding:3px 8px; border-radius:20px !important; }

        /* ── Timer ── */
        .timer-btn { font-size:.75rem; white-space:nowrap; }
        .timer-btn .timer-display { font-family:'Courier New',monospace; font-weight:600; letter-spacing:.5px; }
        .timer-btn.btn-success { animation:timerPulse 2s infinite; }
        @keyframes timerPulse { 0%,100%{box-shadow:0 0 0 0 rgba(25,135,84,.4)} 50%{box-shadow:0 0 0 6px rgba(25,135,84,0)} }
        .timer-log-item { padding:.5rem .75rem; border-radius:6px; background:#f8f9fa; border:1px solid #e9ecef; margin-bottom:.5rem; font-size:.85rem; }

        /* ── Filter Bar ── */
        #filterBar .form-select-sm { border-radius:8px; font-size:.85rem; }
        #activeFilterBadges .badge { font-size:.8rem; padding:5px 10px; border-radius:20px; }
        .task-card.filter-hidden { display:none !important; }
        .filter-empty-msg { text-align:center; color:#9ca3af; font-size:.85rem; padding:1.5rem 0; }

        /* ── Older Tasks Table ── */
        #oldTasksTable .table th { font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; }
        #oldTasksTable .table td { font-size:.875rem; vertical-align:middle; }
        .old-task-row.filter-hidden { display:none !important; }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function () {

        // ── Select2 Init ────────────────────────────────────────────
        $('.assign-select-create').select2({
            placeholder: "Select employees...",
            width: '100%',
            dropdownParent: $('#createTaskModal')
        });
        $('.assign-select-edit').select2({
            placeholder: "Select employees...",
            width: '100%',
            dropdownParent: $('#editTaskModal')
        });

        const isAdmin = {{ auth()->user()->canManageTasks() ? 'true' : 'false' }};

        // Keep a reference to the Bootstrap modal instance (created once)
        const editModalEl  = document.getElementById('editTaskModal');
        const editModalObj = new bootstrap.Modal(editModalEl);

        // ── Edit Task Button ─────────────────────────────────────────
        // Single delegated handler covers BOTH kanban cards AND old-tasks table rows
        $(document).on('click', '.edit-task-btn', function (e) {
            e.preventDefault();
            const btn = $(this);

            // Populate simple fields first
            $('#edit-task-id').val(btn.data('id'));
            $('#edit-title').val(btn.attr('data-title'));
            $('#edit-description').val(btn.attr('data-description') || '');
            $('#edit-priority').val(btn.attr('data-priority'));
            $('#edit-due').val(btn.attr('data-due') || '');
            $('#edit-status').val(btn.attr('data-status'));

            // Project — plain <select> (no Select2), safe to set directly
            const projectId = btn.attr('data-project') || '';
            $('#edit-project').val(projectId);

            // Assigned users — Select2 needs val() then trigger('change')
            try {
                const raw          = btn.attr('data-assigned');
                const assignedUsers = raw ? JSON.parse(raw) : [];
                // Normalise to strings so Select2 option matching works
                const stringIds    = assignedUsers.map(String);
                $('#edit-assigned').val(stringIds).trigger('change');
            } catch (err) {
                console.error('edit-task-btn: could not parse data-assigned', err);
                $('#edit-assigned').val([]).trigger('change');
            }

            // Open the modal programmatically (works whether triggered from
            // a kanban card button OR the older-tasks table button)
            editModalObj.show();
        });

        // ── Save Task ────────────────────────────────────────────────
        $('#saveTaskBtn').on('click', function (e) {
            e.preventDefault();
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('tasks.update') }}",
                method: 'POST',
                data: $('#editTaskForm').serialize(),
                success: function (response) {
                    if (response.success) {
                        updateTaskCard(response.task, response.employee_name);
                        editModalObj.hide();
                        $('#editTaskForm')[0].reset();
                        $('.assign-select-edit').val(null).trigger('change');
                        showSuccess('Task updated successfully');
                    }
                },
                error: function (xhr) {
                    showError(xhr.responseJSON?.error || 'Something went wrong');
                },
                complete: function () {
                    btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Changes');
                }
            });
        });

        // ── Update Task Card in DOM ───────────────────────────────────
        function updateTaskCard(task, employeeNames) {
            const card      = $('.task-card[data-id="' + task.id + '"]');
            const oldStatus = card.closest('.task-column').data('status');

            card.find('.task-title').text(task.title);
            card.find('.task-description').text(task.description || '');
            card.find('.task-assignee').text('Assign to: ' + (employeeNames || '-'));

            // Priority badge
            const priorityBadge = card.find('.task-priority');
            priorityBadge.removeClass('bg-danger bg-warning bg-secondary text-dark');
            if (task.priority === 'high')        priorityBadge.addClass('bg-danger');
            else if (task.priority === 'medium') priorityBadge.addClass('bg-warning text-dark');
            else                                 priorityBadge.addClass('bg-secondary');
            priorityBadge.text(task.priority.charAt(0).toUpperCase() + task.priority.slice(1));

            // Due date badge
            if (task.due_date) {
                const formatted = new Date(task.due_date)
                    .toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
                if (card.find('.task-due').length) {
                    card.find('.task-due').text('Due: ' + formatted);
                } else {
                    card.find('.task-priority').after(
                        '<span class="badge bg-light text-dark task-due">Due: ' + formatted + '</span>'
                    );
                }
            } else {
                card.find('.task-due').remove();
            }

            // Project badge
            if (task.project_name) {
                if (card.find('.task-project').length) {
                    card.find('.task-project').text(task.project_name);
                } else {
                    card.find('.task-priority').after(
                        '<span class="badge bg-dark text-white task-project ms-1">' + task.project_name + '</span>'
                    );
                }
            } else {
                card.find('.task-project').remove();
            }

            // Move card to new column if status changed
            if (oldStatus !== task.status) {
                const newColumn = $('.task-column[data-status="' + task.status + '"]');
                newColumn.append(card);
                updateColumnCounts();
            }

            // Also refresh the matching row in the older-tasks table (if present)
            const oldRow = $('#oldTasksDataTable').find('tr').filter(function () {
                return $(this).find('.edit-task-btn').data('id') == task.id;
            });
            if (oldRow.length) {
                oldRow.find('td:first .fw-semibold').text(task.title);
                const statusMap = {
                    new:         { label: 'New',         cls: 'bg-secondary' },
                    in_progress: { label: 'In Progress',  cls: 'bg-primary'   },
                    completed:   { label: 'Completed',    cls: 'bg-success'   },
                };
                const s = statusMap[task.status] || { label: task.status, cls: 'bg-secondary' };
                oldRow.find('td:nth-child(2) .badge').attr('class', 'badge ' + s.cls).text(s.label);
            }
        }

        // ── Sortable Kanban ──────────────────────────────────────────
        $('.task-column').sortable({
            connectWith: '.task-column',
            items: '.task-card',
            placeholder: 'task-placeholder',
            forcePlaceholderSize: true,
            tolerance: 'pointer',
            cursor: 'grabbing',
            start: function (event, ui) {
                ui.placeholder.height(ui.item.height());
                ui.item.addClass('dragging');
            },
            stop: function (event, ui) {
                ui.item.removeClass('dragging');
            },
            update: function (event, ui) {
                if (this === ui.item.parent()[0]) {
                    const taskId     = ui.item.data('id');
                    const newStatus  = ui.item.closest('.task-column').data('status');
                    const newPos     = ui.item.index() + 1;

                    ui.item.closest('.task-column').children('.task-card').each(function (i) {
                        $(this).attr('data-position', i + 1);
                    });
                    ui.item.css('opacity', '.6');

                    $.ajax({
                        url: "{{ route('tasks.updateStatus') }}",
                        method: 'POST',
                        data: { _token: "{{ csrf_token() }}", task_id: taskId, status: newStatus, position: newPos },
                        success: function (res) {
                            if (res.success) {
                                ui.item.css('opacity', '1');
                                updateColumnCounts();
                                showSuccess('Task status updated');
                            } else {
                                $('.task-column').sortable('cancel');
                                showError('Failed to update task status');
                            }
                        },
                        error: function (xhr) {
                            $('.task-column').sortable('cancel');
                            showError(xhr.responseJSON?.error || 'Something went wrong');
                        }
                    });
                }
            }
        }).disableSelection();

        // ── Column Count Helper ──────────────────────────────────────
        function updateColumnCounts() {
            $('.task-column').each(function () {
                const status = $(this).data('status');
                const count  = $(this).find('.task-card:not(.filter-hidden)').length;
                $('.col-count[data-col="' + status + '"]').text(count);
            });
        }

        // ── Delete Task ──────────────────────────────────────────────
        $(document).on('click', '.delete-task-btn', function (e) {
            e.preventDefault();
            const taskId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This task will be deleted permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(result => {
                if (!result.isConfirmed) return;
                $.post("{{ route('tasks.delete') }}", { _token: "{{ csrf_token() }}", task_id: taskId }, function (res) {
                    if (res.success) {
                        // Remove from kanban
                        $('.task-card[data-id="' + taskId + '"]').fadeOut(300, function () {
                            $(this).remove();
                            updateColumnCounts();
                        });
                        // Remove from older-tasks table
                        $('#oldTasksDataTable').find('.edit-task-btn[data-id="' + taskId + '"]')
                            .closest('tr').fadeOut(300, function () { $(this).remove(); });
                        showSuccess('Task deleted');
                    }
                });
            });
        });

        // ── Notification Helpers ─────────────────────────────────────
        function showSuccess(msg) {
            Swal.fire({ icon:'success', title:'Success!', text:msg, timer:1500, showConfirmButton:false, toast:true, position:'top-end' });
        }
        function showError(msg) {
            Swal.fire({ icon:'error', title:'Error', text:msg });
        }

        // ── Reset Modals ─────────────────────────────────────────────
        $('#createTaskModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $('.assign-select-create').val(null).trigger('change');
        });
        editModalEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('editTaskForm').reset();
            $('.assign-select-edit').val(null).trigger('change');
        });

        // ── Toggle Older Tasks ───────────────────────────────────────
        $('#toggleOldTasks').on('click', function () {
            const table = $('#oldTasksTable');
            const open  = table.hasClass('d-none');
            table.toggleClass('d-none', !open);
            $(this).html(open
                ? '<i class="fa fa-chevron-up me-1"></i> Hide'
                : '<i class="fa fa-chevron-down me-1"></i> Show'
            );
        });

    }); // end document.ready


    // ════════════════════════════════════════════════════════════════
    // ── REMARKS ──────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════
    const remarkModal = new bootstrap.Modal(document.getElementById('remarkModal'));

    $(document).on('click', '.remark-btn', function () {
        const taskId    = $(this).data('id');
        const taskTitle = $(this).data('title');

        $('#currentTaskId').val(taskId);
        $('#remarkTaskTitle').text(taskTitle);
        $('#remarkText').val('');
        $('#remarkAttachment').val('');
        $('#remarkLink').val('');
        $('#filePreview').addClass('d-none');
        $('.attach-type-btn').removeClass('active');
        $('.attach-type-btn[data-type="none"]').addClass('active');
        $('#fileUploadSection, #linkSection').addClass('d-none');

        remarkModal.show();
        loadRemarks(taskId);
    });

    function loadRemarks(taskId) {
        $('#remarksList').html(`
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <p class="text-muted small mt-1">Loading...</p>
            </div>`);

        $.get(`{{ url('/admin/admin/tasks') }}/${taskId}/remarks`, function (res) {
            if (!res.success || res.remarks.length === 0) {
                $('#remarksList').html(`
                    <div class="text-center py-4 text-muted">
                        <i class="fa fa-comments fa-2x mb-2 d-block opacity-50"></i>
                        No remarks yet. Be the first to add one!
                    </div>`);
                return;
            }
            let html = '';
            res.remarks.forEach(r => { html += buildRemarkHtml(r); });
            $('#remarksList').html(html);
            $('#remarksList').scrollTop($('#remarksList')[0].scrollHeight);
        }).fail(() => {
            $('#remarksList').html('<p class="text-danger text-center">Failed to load remarks.</p>');
        });
    }

    function buildRemarkHtml(r) {
        let attachmentHtml = '';
        if (r.attachment_type === 'image') {
            attachmentHtml = `<div class="mt-2"><a href="${r.attachment_path}" target="_blank"><img src="${r.attachment_path}" alt="${r.attachment_name}" class="img-thumbnail" style="max-height:150px;cursor:pointer;"></a></div>`;
        } else if (r.attachment_type === 'pdf') {
            attachmentHtml = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-danger mt-2"><i class="fa fa-file-pdf-o"></i> ${r.attachment_name}</a>`;
        } else if (r.attachment_type === 'excel') {
            attachmentHtml = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-success mt-2"><i class="fa fa-file-excel-o"></i> ${r.attachment_name}</a>`;
        } else if (r.attachment_type === 'word') {
            attachmentHtml = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-primary mt-2"><i class="fa fa-file-word-o"></i> ${r.attachment_name}</a>`;
        } else if (r.attachment_type === 'link') {
            attachmentHtml = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-info mt-2"><i class="fa fa-external-link"></i> Open Link</a>`;
        } else if (r.attachment_type === 'text') {
            attachmentHtml = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-secondary mt-2"><i class="fa fa-file-text-o"></i> ${r.attachment_name}</a>`;
        }

        return `
            <div class="remark-item d-flex gap-2 mb-3" data-remark-id="${r.id}">
                <div class="remark-avatar flex-shrink-0"
                     style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);
                            color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">
                    ${r.user_initials}
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="fw-semibold small">${r.user_name}</span>
                            <span class="text-muted small ms-2">${r.created_at}</span>
                        </div>
                        <button class="btn btn-sm btn-link text-danger p-0 delete-remark-btn" data-id="${r.id}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    ${r.remark ? `<p class="mb-1 small">${r.remark}</p>` : ''}
                    ${attachmentHtml}
                </div>
            </div>`;
    }

    $(document).on('click', '.attach-type-btn', function () {
        $('.attach-type-btn').removeClass('active');
        $(this).addClass('active');
        const type = $(this).data('type');
        $('#fileUploadSection').toggleClass('d-none', type !== 'file');
        $('#linkSection').toggleClass('d-none', type !== 'link');
        if (type !== 'file') { $('#remarkAttachment').val(''); $('#filePreview').addClass('d-none'); }
        if (type !== 'link') { $('#remarkLink').val(''); }
    });

    $('#remarkAttachment').on('change', function () {
        const file = this.files[0];
        if (!file) { $('#filePreview').addClass('d-none'); return; }
        const ext   = file.name.split('.').pop().toLowerCase();
        const icons = {
            pdf: 'fa-file-pdf-o text-danger',
            xlsx: 'fa-file-excel-o text-success', xls: 'fa-file-excel-o text-success', csv: 'fa-file-excel-o text-success',
            doc: 'fa-file-word-o text-primary', docx: 'fa-file-word-o text-primary',
            jpg: 'fa-file-image-o text-warning', jpeg: 'fa-file-image-o text-warning',
            png: 'fa-file-image-o text-warning', gif: 'fa-file-image-o text-warning',
        };
        $('#filePreviewIcon').attr('class', `fa ${icons[ext] || 'fa-file text-secondary'} fa-lg`);
        $('#filePreviewName').text(file.name);
        $('#filePreview').removeClass('d-none');
    });

    $('#clearFileBtn').on('click', function () {
        $('#remarkAttachment').val('');
        $('#filePreview').addClass('d-none');
    });

    $('#submitRemarkBtn').on('click', function () {
        const taskId     = $('#currentTaskId').val();
        const remarkText = $('#remarkText').val().trim();
        const activeType = $('.attach-type-btn.active').data('type');
        const file       = $('#remarkAttachment')[0].files[0];
        const link       = $('#remarkLink').val().trim();

        if (!remarkText && activeType === 'none') { alert('Please add a remark or select an attachment.'); return; }
        if (activeType === 'file' && !file)        { alert('Please select a file to upload.'); return; }
        if (activeType === 'link' && !link)         { alert('Please enter a link.'); return; }

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        if (remarkText)               formData.append('remark', remarkText);
        if (activeType === 'file' && file)  formData.append('attachment', file);
        if (activeType === 'link' && link)  formData.append('link', link);

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Adding...');

        $.ajax({
            url: `{{ url('/admin/admin/tasks') }}/${taskId}/remarks`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    const newHtml = buildRemarkHtml(res.remark);
                    if ($('#remarksList .text-center').length) {
                        $('#remarksList').html(newHtml);
                    } else {
                        $('#remarksList').append(newHtml);
                    }
                    $('#remarksList').scrollTop($('#remarksList')[0].scrollHeight);
                    $('#remarkText').val('');
                    $('#remarkAttachment').val('');
                    $('#remarkLink').val('');
                    $('#filePreview').addClass('d-none');
                    $('.attach-type-btn').removeClass('active');
                    $('.attach-type-btn[data-type="none"]').addClass('active');
                    $('#fileUploadSection, #linkSection').addClass('d-none');
                }
            },
            error: function (xhr) { alert(xhr.responseJSON?.message || 'Failed to add remark.'); },
            complete: function () { btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Add Remark'); }
        });
    });

    $(document).on('click', '.delete-remark-btn', function () {
        const remarkId = $(this).data('id');
        const item     = $(this).closest('.remark-item');

        Swal.fire({
            title: 'Delete remark?', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Delete'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `{{ url('/admin/admin/tasks/remarks') }}/${remarkId}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) { item.fadeOut(300, function () { $(this).remove(); }); }
                }
            });
        });
    });


    // ════════════════════════════════════════════════════════════════
    // ── TASK TIMER ───────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════
    const timerIntervals = {};

    $('.timer-btn').each(function () {
        const btn       = $(this);
        const isRunning = btn.data('running') === true || btn.data('running') === 'true';
        const startedAt = parseInt(btn.data('started-at')) || 0;
        const base      = parseInt(btn.data('total')) || 0;
        if (isRunning && startedAt) {
            resumeCountup(btn, btn.data('id'), base, startedAt);
        }
    });

    $(document).on('click', '.timer-btn', function () {
        const btn       = $(this);
        const taskId    = btn.data('id');
        const isRunning = btn.data('running') === true || btn.data('running') === 'true';

        btn.prop('disabled', true);

        if (!isRunning) {
            $.ajax({
                url: `/admin/admin/tasks/${taskId}/timer/start`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        const base = parseInt(btn.data('total')) || 0;
                        btn.data('running', 'true').data('started-at', res.started_at);
                        btn.removeClass('btn-success').addClass('btn-danger');
                        btn.find('i').removeClass('fa-play-circle').addClass('fa-stop-circle');
                        btn.attr('title', 'Stop Timer');
                        resumeCountup(btn, taskId, base, res.started_at);
                    } else {
                        alert(res.message || 'Could not start timer.');
                    }
                },
                error: function () { alert('Failed to start timer.'); },
                complete: function () { btn.prop('disabled', false); }
            });
        } else {
            $.ajax({
                url: `/admin/admin/tasks/${taskId}/timer/stop`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        clearInterval(timerIntervals[taskId]);
                        delete timerIntervals[taskId];
                        btn.data('running', 'false').data('total', res.total_seconds);
                        btn.removeClass('btn-danger').addClass('btn-outline-success');
                        btn.find('i').removeClass('fa-stop-circle').addClass('fa-play-circle');
                        btn.attr('title', 'Start Timer');
                        btn.find('.timer-display').text(formatSeconds(res.total_seconds));
                    } else {
                        alert(res.message || 'Could not stop timer.');
                    }
                },
                error: function () { alert('Failed to stop timer.'); },
                complete: function () { btn.prop('disabled', false); }
            });
        }
    });

    function resumeCountup(btn, taskId, baseSeconds, startedAt) {
        clearInterval(timerIntervals[taskId]);
        timerIntervals[taskId] = setInterval(() => {
            const elapsed = Math.floor(Date.now() / 1000) - startedAt;
            btn.find('.timer-display').text(formatSeconds(baseSeconds + elapsed));
        }, 1000);
    }

    function formatSeconds(total) {
        total = Math.max(0, Math.floor(total));
        return [Math.floor(total / 3600), Math.floor((total % 3600) / 60), total % 60]
            .map(n => String(n).padStart(2, '0')).join(':');
    }


    // ════════════════════════════════════════════════════════════════
    // ── CLIENT-SIDE FILTERS ──────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════
    const activeFilters = { employee: '', project: '', priority: '' };

    function applyFilters() {
        const { employee, project, priority } = activeFilters;
        const visibleCount = { new: 0, in_progress: 0, completed: 0 };

        $('.task-card').each(function () {
            const card   = $(this);
            const column = card.closest('.task-column').data('status');

            // Employee
            let employeeMatch = true;
            if (employee) {
                const editBtn = card.find('.edit-task-btn');
                if (editBtn.length) {
                    try {
                        const ids = JSON.parse(editBtn.attr('data-assigned') || '[]');
                        employeeMatch = ids.map(String).includes(String(employee));
                    } catch (e) { employeeMatch = false; }
                } else {
                    const ids = String(card.data('assigned-ids') || '').split(',');
                    employeeMatch = ids.includes(String(employee));
                }
            }

            // Project
            let projectMatch = true;
            if (project) {
                projectMatch = String(card.data('project-id')) === String(project);
            }

            // Priority
            let priorityMatch = true;
            if (priority) {
                priorityMatch = card.data('priority') === priority;
            }

            if (employeeMatch && projectMatch && priorityMatch) {
                card.removeClass('filter-hidden');
                visibleCount[column] = (visibleCount[column] || 0) + 1;
            } else {
                card.addClass('filter-hidden');
            }
        });

        // Empty messages per column
        $('.task-column').each(function () {
            const col     = $(this);
            const status  = col.data('status');
            col.find('.filter-empty-msg').remove();
            if ((visibleCount[status] || 0) === 0 && (activeFilters.employee || activeFilters.project || activeFilters.priority)) {
                col.append('<div class="filter-empty-msg"><i class="fa fa-inbox fa-lg d-block mb-1"></i>No tasks match filters</div>');
            }
        });

        // Also filter the older-tasks table
        applyOldTaskFilters();
        renderFilterBadges();
    }

    function applyOldTaskFilters() {
        const { employee, project, priority } = activeFilters;

        $('.old-task-row').each(function () {
            const row = $(this);

            let employeeMatch = true;
            if (employee) {
                const ids = String(row.data('assigned-ids') || '').split(',');
                employeeMatch = ids.includes(String(employee));
            }

            let projectMatch = true;
            if (project) {
                projectMatch = String(row.data('project-id')) === String(project);
            }

            let priorityMatch = true;
            if (priority) {
                priorityMatch = row.data('priority') === priority;
            }

            row.toggleClass('filter-hidden', !(employeeMatch && projectMatch && priorityMatch));
        });
    }

    function renderFilterBadges() {
        const badges = [];
        const { employee, project, priority } = activeFilters;

        if (employee) {
            const name = $('#filterEmployee option:selected').text();
            badges.push(`<span class="badge bg-primary me-1"><i class="fa fa-user me-1"></i>${name}<a href="#" class="text-white ms-1 clear-badge" data-filter="employee">×</a></span>`);
        }
        if (project) {
            const name = $('#filterProject option:selected').text();
            badges.push(`<span class="badge bg-dark me-1"><i class="fa fa-folder me-1"></i>${name}<a href="#" class="text-white ms-1 clear-badge" data-filter="project">×</a></span>`);
        }
        if (priority) {
            const colors = { high: 'danger', medium: 'warning text-dark', low: 'secondary' };
            badges.push(`<span class="badge bg-${colors[priority] || 'secondary'} me-1"><i class="fa fa-flag me-1"></i>${priority.charAt(0).toUpperCase() + priority.slice(1)}<a href="#" class="text-white ms-1 clear-badge" data-filter="priority">×</a></span>`);
        }

        $('#activeFilterBadges').html(badges.length
            ? '<small class="text-muted me-1">Active:</small>' + badges.join('')
            : '');
    }

    $('#filterEmployee').on('change', function () { activeFilters.employee = $(this).val(); applyFilters(); });
    $('#filterProject').on('change',  function () { activeFilters.project  = $(this).val(); applyFilters(); });
    $('#filterPriority').on('change', function () { activeFilters.priority = $(this).val(); applyFilters(); });

    $('#clearFiltersBtn').on('click', function () {
        activeFilters.employee = activeFilters.project = activeFilters.priority = '';
        $('#filterEmployee, #filterProject, #filterPriority').val('');
        applyFilters();
    });

    $(document).on('click', '.clear-badge', function (e) {
        e.preventDefault();
        const key = $(this).data('filter');
        activeFilters[key] = '';
        $('#filter' + key.charAt(0).toUpperCase() + key.slice(1)).val('');
        applyFilters();
    });

    </script>
@endpush