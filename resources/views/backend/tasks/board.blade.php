@extends(backendView('layouts.app'))

@section('title', 'Task Board')

@section('content')
    <div class="container-xxl">

        {{-- ═══════════════════════════════════════════════════════════
        TOP STATS BAR
        ════════════════════════════════════════════════════════════════ --}}
        <div class="row g-3 mb-3" id="statsBar">
            @php
                $statItems = [
                    ['label' => 'Total',       'value' => $stats['total'],       'color' => 'primary'],
                    ['label' => 'Assigned',    'value' => $stats['new'],         'color' => 'secondary'],
                    ['label' => 'In Progress', 'value' => $stats['in_progress'], 'color' => 'warning'],
                    ['label' => 'Completed',   'value' => $stats['completed'],   'color' => 'success'],
                ];
            @endphp
            @foreach($statItems as $stat)
                <div class="col">
                    <div class="card shadow-sm border-0 stat-card">
                        <div class="card-body py-3 px-3">
                            <div class="text-muted small">{{ $stat['label'] }}</div>
                            <div class="fs-4 fw-bold text-{{ $stat['color'] }} stat-value"
                                 data-stat="{{ strtolower(str_replace(' ','_',$stat['label'])) }}">
                                {{ $stat['value'] }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3">

            {{-- ═══════════════════════════════════════════════════════
            LEFT SIDEBAR
            ════════════════════════════════════════════════════════════ --}}
            <div class="col-md-2">
                <div class="sidebar-panel">

                    {{-- Quick Filters --}}
                    <div class="sidebar-section">
                        <div class="sidebar-label">QUICK FILTERS</div>
                        <a href="#" class="sidebar-item quick-filter active" data-filter="">
                            <i class="fa fa-th-large me-2"></i> All Tasks
                            <span class="ms-auto badge bg-secondary sidebar-total">{{ $stats['total'] }}</span>
                        </a>
                        <a href="#" class="sidebar-item quick-filter" data-filter="mine">
                            <i class="fa fa-user me-2"></i> Mine
                        </a>
                        <a href="#" class="sidebar-item quick-filter" data-filter="overdue">
                            <i class="fa fa-clock-o me-2"></i> Overdue
                        </a>
                    </div>

                    {{-- Categories --}}
                    <div class="sidebar-section mt-3">
                        <div class="sidebar-label">CATEGORIES</div>
                        @foreach($categories as $key => $label)
                            @php $count = $categoryCounts[$key] ?? 0; @endphp
                            <a href="#" class="sidebar-item category-filter" data-category="{{ $key }}">
                                <span class="category-dot cat-{{ $key }}"></span>
                                {{ $label }}
                                @if($count > 0)
                                    <span class="ms-auto text-muted small cat-count" data-cat="{{ $key }}">{{ $count }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    {{-- Recurring Types --}}
                    <div class="sidebar-section mt-3">
                        <div class="sidebar-label">RECURRING</div>
                        @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $type => $label)
                            @php $count = $recurringCounts[$type] ?? 0; @endphp
                            <a href="#" class="sidebar-item tasktype-filter" data-type="{{ $type }}">
                                <i class="fa fa-{{ $type === 'daily' ? 'sun-o' : ($type === 'weekly' ? 'calendar-o' : 'calendar') }} me-2"></i>
                                {{ $label }}
                                <span class="ms-auto text-muted small">{{ $count }}</span>
                            </a>
                        @endforeach
                    </div>

                    {{-- New Task Button --}}
                    <div class="mt-3">
                        <button class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                            <i class="fa fa-plus me-1"></i> New Task
                        </button>
                    </div>

                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════
            MAIN KANBAN AREA
            ════════════════════════════════════════════════════════════ --}}
            <div class="col-md-10" style="position:relative">

                {{-- ── TOP ACTION BAR ── --}}
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">

                    {{-- Filters --}}
                    <div class="d-flex gap-2 flex-nowrap align-items-center overflow-auto">

                        @if(auth()->user()->canManageTasks())
                        <select id="filterEmployee" class="form-select form-select-sm" style="min-width:160px">
                            <option value="">👤 All Employees</option>
                            @foreach(\App\Models\User::role(['Employee','Admin'])->get() as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->username }}</option>
                            @endforeach
                        </select>
                        @endif

                        <select id="filterProject" class="form-select form-select-sm" style="min-width:160px">
                            <option value="">📁 All Projects</option>
                            @foreach(\App\Models\Project::where('status','active')->get() as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>

                        <select id="filterPriority" class="form-select form-select-sm" style="min-width:130px">
                            <option value="">🚦 All Priorities</option>
                            <option value="urgent">🔴 Urgent</option>
                            <option value="high">🟠 High</option>
                            <option value="medium">🟡 Medium</option>
                            <option value="low">🟢 Low</option>
                        </select>

                        <select id="filterTaskType" class="form-select form-select-sm" style="min-width:130px">
                            <option value="">🔄 All Types</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="one_time">One-Time</option>
                        </select>

                        <button class="btn btn-sm btn-danger" id="clearFiltersBtn" title="Clear all filters">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    {{-- Export Buttons --}}
                    <div class="d-flex gap-2 flex-shrink-0">
                        <button class="btn btn-sm btn-success export-btn" id="exportExcelBtn" title="Export to Excel">
                            <i class="fa fa-file-excel-o me-1"></i> Excel
                        </button>
                        <button class="btn btn-sm btn-danger export-btn" id="exportPdfBtn" title="Export to PDF">
                            <i class="fa fa-file-pdf-o me-1"></i> PDF
                        </button>
                    </div>
                </div>

                {{-- Active filter badges --}}
                <div id="activeFilterBadges" class="mb-2 d-flex flex-wrap gap-1 align-items-center"></div>

                {{-- ═══════════════════════════════════════════════
                KANBAN COLUMNS
                ════════════════════════════════════════════════════ --}}
                @php
                    $cutoff         = \Carbon\Carbon::now()->subDays(7)->startOfDay();
                    $recentByStatus = [];
                    $allOldTasks    = collect();

                    foreach (['new', 'in_progress', 'completed'] as $statusKey) {
                        $bucket = $tasks->get($statusKey, collect());
                        if ($statusKey === 'completed') {
                            $recentByStatus[$statusKey] = $bucket->filter(fn($t) => $t->created_at->gte($cutoff));
                            $allOldTasks = $allOldTasks->merge($bucket->filter(fn($t) => $t->created_at->lt($cutoff)));
                        } else {
                            $recentByStatus[$statusKey] = $bucket;
                        }
                    }

                    $typeColors = [
                        'daily'    => 'type-daily',
                        'weekly'   => 'type-weekly',
                        'monthly'  => 'type-monthly',
                        'one_time' => 'type-onetime',
                    ];
                    $typeLabels = [
                        'daily'    => 'Daily',
                        'weekly'   => 'Weekly',
                        'monthly'  => 'Monthly',
                        'one_time' => 'One-Time',
                    ];
                    $categoryBadgeMap = [
                        'social_media' => ['label' => 'Social',  'cls' => 'cat-badge-social'],
                        'blog_post'   => ['label' => 'Blog',    'cls' => 'cat-badge-blog'],
                        'design'       => ['label' => 'Design',  'cls' => 'cat-badge-design'],
                        'development'  => ['label' => 'Dev',     'cls' => 'cat-badge-dev'],
                        'client_work'  => ['label' => 'Client',  'cls' => 'cat-badge-client'],
                        'meetings'     => ['label' => 'Meeting', 'cls' => 'cat-badge-meeting'],
                    ];
                @endphp

                <div class="row kanban-board g-3" id="kanbanBoard">
                    @foreach(['new' => 'Assigned', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $key => $label)
                        <div class="col-md-4">
                            <div class="kanban-column-card">
                                <div class="kanban-col-header {{ $key }}">
                                    <span class="col-label">{{ $label }}</span>
                                    <span class="badge col-count" data-col="{{ $key }}">
                                        {{ $recentByStatus[$key]->count() }}
                                    </span>
                                </div>

                                <div class="task-column p-2" data-status="{{ $key }}">
                                    @foreach($recentByStatus[$key] as $task)
                                        @php
                                            $assignedIds   = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];
                                            $employees     = \App\Models\User::whereIn('id', $assignedIds)->get();
                                            $employeeNames = $employees->pluck('username')->implode(', ');
                                            $timeLogs      = $task->time_logs ?? [];
                                            $totalSeconds  = collect($timeLogs)->sum(fn($l) => isset($l['end']) ? $l['end'] - $l['start'] : 0);
                                            $runningLog    = collect($timeLogs)->first(fn($l) => !isset($l['end']) && $l['user_id'] == auth()->id());
                                            $taskType      = $task->task_type ?? 'one_time';
                                            $isRecurring   = in_array($taskType, ['daily','weekly','monthly']);
                                            $isCompleted   = $key === 'completed';
                                            $category      = $task->task_category ?? null;
                                            $catBadge      = $category ? ($categoryBadgeMap[$category] ?? null) : null;

                                            // Due date label
                                            $due      = $task->due_date ? \Carbon\Carbon::parse($task->due_date) : null;
                                            $dueLabel = null;
                                            $dueClass = 'text-muted';
                                            if ($due) {
                                                $dueLabel = $due->isToday()    ? 'Today'
                                                    : ($due->isTomorrow()      ? 'Tomorrow'
                                                    : ($due->isCurrentWeek()   ? $due->format('D')
                                                    : $due->format('d M')));
                                                $dueClass = $task->getIsOverdueAttribute() ? 'text-danger fw-semibold' : 'text-muted';
                                            }
                                        @endphp

                                        <div class="card mb-2 task-card {{ $isCompleted ? 'task-done' : '' }}"
                                             data-id="{{ $task->id }}"
                                             data-position="{{ $task->position }}"
                                             data-priority="{{ $task->priority }}"
                                             data-task-type="{{ $taskType }}"
                                             data-project-id="{{ $task->project_id ?? '' }}"
                                             data-assigned-ids="{{ implode(',', $assignedIds) }}"
                                             data-category="{{ $category ?? '' }}">
                                            <div class="card-body p-2">

                                                {{-- ── Title row (no edit/delete here anymore) ── --}}
                                                <div class="d-flex justify-content-between align-items-start gap-1">
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold task-title {{ $isCompleted ? 'text-decoration-line-through text-muted' : '' }}">
                                                            {{ $task->title }}
                                                        </div>
                                                        @if($task->description)
                                                            <small class="text-muted task-description">{{ Str::limit($task->description, 60) }}</small>
                                                        @endif
                                                    </div>
                                                    {{-- Only remarks + timer in top-right --}}
                                                    <div class="d-flex gap-1 flex-shrink-0">
                                                        <!-- <button class="btn btn-sm btn-info rounded-pill remark-btn px-2"
                                                            title="Remarks"
                                                            data-id="{{ $task->id }}"
                                                            data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}">
                                                        <i class="fa fa-comments"></i>
                                                    </button> -->


                                                        @if(in_array(auth()->id(), $assignedIds) || auth()->user()->canManageTasks())
                                                            <button class="btn btn-xs {{ $runningLog ? 'btn-danger' : 'btn-success' }} timer-btn"
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

                                                {{-- ── Badge row ── --}}
                                                <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                                                    @if($catBadge)
                                                        <span class="badge task-cat-badge {{ $catBadge['cls'] }}">{{ $catBadge['label'] }}</span>
                                                    @endif
                                                    <span class="badge task-priority
                                                        @if($task->priority=='urgent') bg-danger
                                                        @elseif($task->priority=='high') bg-danger opacity-75
                                                        @elseif($task->priority=='medium') bg-warning text-dark
                                                        @else bg-secondary @endif">
                                                        {{ ucfirst($task->priority) }}
                                                    </span>
                                                    @if($isRecurring)
                                                        <span class="badge task-type-badge {{ $typeColors[$taskType] ?? 'type-onetime' }}">
                                                            <i class="fa fa-refresh me-1" style="font-size:.65rem"></i>{{ $typeLabels[$taskType] }}
                                                        </span>
                                                    @endif
                                                    @if($task->project)
                                                        <span class="badge bg-dark task-project">{{ $task->project->name }}</span>
                                                    @endif
                                                </div>

                                                {{-- ── Footer row: avatars | due date + edit + delete ── --}}
                                                <div class="mt-2 d-flex align-items-center justify-content-between">

                                                    {{-- Avatar stack --}}
                                                    <div class="avatar-stack">
                                                        @foreach($employees->take(3) as $emp)
                                                            <div class="avatar-circle" title="{{ $emp->username }}">
                                                                {{ strtoupper(substr($emp->username, 0, 2)) }}
                                                            </div>
                                                        @endforeach
                                                        @if($employees->count() > 3)
                                                            <div class="avatar-circle avatar-more">+{{ $employees->count() - 3 }}</div>
                                                        @endif
                                                    </div>

                                                    {{-- Due date + edit + delete --}}
                                                    <div class="d-flex align-items-center gap-1">

                                                        @if($isCompleted)
                                                            <span class="task-done-badge">
                                                                <i class="fa fa-check-circle me-1"></i>Completed
                                                            </span>
                                                        @elseif($dueLabel)
                                                            <span class="due-date-chip {{ $dueClass }}">
                                                                <i class="fa fa-calendar-o me-1" style="font-size:.65rem"></i>{{ $dueLabel }}
                                                            </span>
                                                        @endif

                                                        {{-- Edit button — visible to admin or assigned user --}}
                                                        @if(auth()->user()->canManageTasks())
                                                               <button class="btn btn-sm btn-primary rounded-pill px-2 edit-task-btn"

                                                                    title="Edit task"
                                                                    data-id="{{ $task->id }}"
                                                                    data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}"
                                                                    data-description="{{ htmlspecialchars($task->description ?? '', ENT_QUOTES) }}"
                                                                    data-assigned='@json($task->assigned_to)'
                                                                    data-priority="{{ $task->priority }}"
                                                                    data-task-type="{{ $taskType }}"
                                                                    data-project="{{ $task->project_id ?? '' }}"
                                                                    data-due="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}"
                                                                    data-status="{{ $task->status }}"
                                                                    data-category="{{ $category ?? '' }}"
                                                                    data-is-admin="true">
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                             <button class="btn btn-sm btn-danger rounded-pill px-2 delete-task-btn"

                                                                    title="Delete task"
                                                                    data-id="{{ $task->id }}">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        @elseif(in_array(auth()->id(), $assignedIds))
                                                               <button class="btn btn-sm btn-primary rounded-pill px-2 edit-task-btn"

                                                                    title="Edit task"
                                                                    data-id="{{ $task->id }}"
                                                                    data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}"
                                                                    data-description="{{ htmlspecialchars($task->description ?? '', ENT_QUOTES) }}"
                                                                    data-assigned='@json($task->assigned_to)'
                                                                    data-priority="{{ $task->priority }}"
                                                                    data-task-type="{{ $taskType }}"
                                                                    data-project="{{ $task->project_id ?? '' }}"
                                                                    data-due="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}"
                                                                    data-status="{{ $task->status }}"
                                                                    data-category="{{ $category ?? '' }}"
                                                                    data-assignee-name="{{ $employeeNames }}"
                                                                    data-is-admin="false">
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                        @endif

                                                    </div>
                                                </div>{{-- /footer --}}

                                            </div>
                                        </div>
                                    @endforeach

                                    @if($key === 'new')
                                        <div class="add-task-inline" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                                            <i class="fa fa-plus me-2 text-muted"></i>
                                            <span class="text-muted">Add task</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>{{-- /col-md-10 --}}
        </div>{{-- /row --}}
    </div>{{-- /container-xxl --}}


    {{-- ═══════════════════════════════════════════════════════════════
    CREATE TASK MODAL
    ════════════════════════════════════════════════════════════════════ --}}
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
                                    @foreach(\App\Models\User::role(['Employee','Admin'])->get() as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="assigned_to[]" value="{{ auth()->id() }}">
                            <div class="mb-3">
                                <label class="form-label">Assigned To</label>
                                <input type="text" class="form-control bg-light" value="{{ auth()->user()->username }}" disabled>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="task_category" class="form-select">
                                    <option value="">No Category</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Task Type</label>
                                <select name="task_type" class="form-select">
                                    <option value="one_time" selected>One-Time</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project</label>
                                <select name="project_id" class="form-select">
                                    <option value="">Select project</option>
                                    @foreach(\App\Models\Project::where('status','active')->get() as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
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

    {{-- ═══════════════════════════════════════════════════════════════
    EDIT TASK MODAL
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="editTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editTaskForm">
                @csrf
                <input type="hidden" name="task_id" id="edit-task-id">
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
                            <select name="assigned_to[]" id="edit-assigned" class="form-select assign-select-edit"
                                    multiple required @if(!auth()->user()->canManageTasks()) disabled @endif>
                                @foreach(\App\Models\User::role(['Employee','Admin'])->get() as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->username }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="task_category" id="edit-category" class="form-select">
                                    <option value="">No Category</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project</label>
                                <select name="project_id" id="edit-project" class="form-select">
                                    <option value="">Select project</option>
                                    @foreach(\App\Models\Project::where('status','active')->get() as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" id="edit-priority" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Task Type</label>
                                <select name="task_type" id="edit-task-type" class="form-select">
                                    <option value="one_time">One-Time</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" id="edit-due" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit-status" class="form-select">
                                <option value="new">Assigned</option>
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

    {{-- ═══════════════════════════════════════════════════════════════
    REMARK MODAL
    ════════════════════════════════════════════════════════════════════ --}}
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
                    <div id="remarksList" style="max-height:350px;overflow-y:auto;" class="mb-4">
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
                            <input type="url" id="remarkLink" class="form-control" placeholder="https://...">
                        </div>
                        <button type="button" class="btn btn-primary" id="submitRemarkBtn">
                            <i class="fa fa-paper-plane"></i> Add Remark
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TIMER DETAIL MODAL --}}
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        /* ── Sidebar ───────────────────────────────────────────── */
        .sidebar-panel { padding: 4px 0; }
        .sidebar-label  { font-size:.7rem;font-weight:700;letter-spacing:.08em;color:#9ca3af;padding:0 8px 4px;text-transform:uppercase; }
        .sidebar-item   { display:flex;align-items:center;padding:6px 10px;border-radius:8px;font-size:.85rem;color:#374151;text-decoration:none;transition:.15s;cursor:pointer; }
        .sidebar-item:hover,.sidebar-item.active { background:#f3f4f6;color:#111; }
        .sidebar-section { border-bottom:1px solid #f3f4f6;padding-bottom:8px; }

        /* Category dots */
        .category-dot      { width:8px;height:8px;border-radius:50%;margin-right:8px;flex-shrink:0; }
        .cat-social_media  { background:#8b5cf6; }
        .cat-blog_post    { background:#3b82f6; }
        .cat-design        { background:#ec4899; }
        .cat-development   { background:#10b981; }
        .cat-client_work   { background:#f59e0b; }
        .cat-meetings      { background:#6b7280; }

        /* ── Stats bar ─────────────────────────────────────────── */
        .stat-card { border-radius:10px; }

        /* ── Kanban ────────────────────────────────────────────── */
        .kanban-column-card  { border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;background:#fff; }
        .kanban-col-header   { display:flex;justify-content:space-between;align-items:center;padding:10px 14px;font-weight:600;font-size:.9rem;background:#f9fafb;border-bottom:1px solid #e5e7eb; }
        .kanban-col-header.new .col-label          { color:#374151; }
        .kanban-col-header.in_progress .col-label  { color:#d97706; }
        .kanban-col-header.completed .col-label    { color:#059669; }
        .kanban-col-header .col-count { background:#e5e7eb;color:#6b7280;font-size:.75rem; }

        .task-column { min-height:400px;transition:background .2s; }
        .task-card   { cursor:grab;border-radius:8px !important;border:1px solid #e5e7eb !important;transition:box-shadow .2s,transform .15s; }
        .task-card:hover    { box-shadow:0 4px 12px rgba(0,0,0,.08) !important;transform:translateY(-1px); }
        .task-card.dragging { cursor:grabbing;opacity:.5; }
        .task-placeholder   { border:2px dashed #93c5fd;background:#eff6ff;min-height:80px;margin-bottom:8px;border-radius:8px; }
        .task-done  { opacity:.7; }
        .task-title { font-size:.875rem;word-break:break-word; }
        .task-description { font-size:.8rem; }
        .filter-hidden { display:none !important; }

        /* ── Ghost action buttons (top-right of card) ──────────── */
        .btn-xs    { padding:2px 6px;font-size:.75rem;border-radius:5px; }
        .btn-ghost { background:transparent;border:none;color:#6b7280; }
        .btn-ghost:hover { background:#f3f4f6;color:#111; }

        /* ── Footer action buttons (edit / delete beside due date) */
        .btn-ghost-edit,
        .btn-ghost-delete {
            padding: 2px 5px;
            font-size: .72rem;
            border-radius: 5px;
            border: none;
            line-height: 1.4;
            transition: background .15s, color .15s, transform .1s;
        }
        .btn-ghost-edit {
            background: transparent;
            color: #6b7280;
        }
        .btn-ghost-edit:hover {
            background: #e0f2fe;
            color: #0284c7;
            transform: scale(1.1);
        }
        .btn-ghost-delete {
            background: transparent;
            color: #6b7280;
        }
        .btn-ghost-delete:hover {
            background: #fee2e2;
            color: #dc2626;
            transform: scale(1.1);
        }

        /* ── Due date chip ─────────────────────────────────────── */
        .due-date-chip {
            display: inline-flex;
            align-items: center;
            font-size: .72rem;
            padding: 2px 7px;
            border-radius: 20px;
            background: #f3f4f6;
            white-space: nowrap;
        }
        .due-date-chip.text-danger  { background:#fee2e2;color:#dc2626 !important; }
        .due-date-chip.text-muted   { background:#f3f4f6;color:#6b7280 !important; }

        /* ── Done badge ────────────────────────────────────────── */
        .task-done-badge {
            display: inline-flex;
            align-items: center;
            font-size: .72rem;
            padding: 2px 7px;
            border-radius: 20px;
            background: #d1fae5;
            color: #065f46;
            font-weight: 500;
        }

        /* ── Category badges ───────────────────────────────────── */
        .task-cat-badge    { font-size:.7rem;border-radius:12px;padding:2px 8px;font-weight:500; }
        .cat-badge-social  { background:#ede9fe;color:#5b21b6; }
        .cat-badge-blog    { background:#dbeafe;color:#1d4ed8; }
        .cat-badge-design  { background:#fce7f3;color:#9d174d; }
        .cat-badge-dev     { background:#d1fae5;color:#065f46; }
        .cat-badge-client  { background:#fef3c7;color:#92400e; }
        .cat-badge-meeting { background:#f3f4f6;color:#374151; }

        /* ── Task type badges ──────────────────────────────────── */
        .type-daily   { background:#dbeafe !important;color:#1d4ed8 !important; }
        .type-weekly  { background:#ede9fe !important;color:#5b21b6 !important; }
        .type-monthly { background:#d1fae5 !important;color:#065f46 !important; }
        .type-onetime { background:#f3f4f6 !important;color:#6b7280 !important; }

        /* ── Avatar stack ──────────────────────────────────────── */
        .avatar-stack  { display:flex; }
        .avatar-circle { width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid #fff;margin-left:-6px; }
        .avatar-stack .avatar-circle:first-child { margin-left:0; }
        .avatar-more { background:#e5e7eb;color:#6b7280; }

        /* ── Inline add task ───────────────────────────────────── */
        .add-task-inline { padding:8px 10px;border-radius:8px;cursor:pointer;border:1px dashed #d1d5db;font-size:.85rem;display:flex;align-items:center;margin-top:4px; }
        .add-task-inline:hover { background:#f9fafb; }

        /* ── Select2 ───────────────────────────────────────────── */
        .select2-container--default .select2-selection--multiple { border-radius:8px;min-height:42px; }
        .select2-selection__choice { background-color:#0d6efd !important;border:none !important;color:white !important;border-radius:12px !important; }

        /* ── Timer ─────────────────────────────────────────────── */
        .timer-btn .timer-display { font-family:'Courier New',monospace;font-weight:600;letter-spacing:.5px;font-size:.7rem; }

        /* ── Remarks ───────────────────────────────────────────── */
        .remark-item       { padding:.75rem;border-radius:8px;background:#f9fafb;border:1px solid #e5e7eb; }
        .attach-type-btn.active { font-weight:700; }
        .remark-edit-form  { background:#fff;border:1px solid #d1d5db;border-radius:6px;padding:.6rem; }

        /* ── Filter badge ──────────────────────────────────────── */
        .filter-badge { font-size:.8rem;padding:4px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px; }
        .filter-badge .remove-badge { cursor:pointer;opacity:.7;margin-left:2px; }
        .filter-badge .remove-badge:hover { opacity:1; }

        /* ── Export buttons ────────────────────────────────────── */
        .export-btn   { font-size:.8rem; }
        .export-btn i { font-size:.9rem; }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>"Select employees...", width:'100%', dropdownParent:$('#createTaskModal') });
        $('.assign-select-edit').select2({ placeholder:"Select employees...", width:'100%', dropdownParent:$('#editTaskModal') });

        const editModalEl  = document.getElementById('editTaskModal');
        const editModalObj = new bootstrap.Modal(editModalEl);

        // ════════════════════════════════════════════════════════════
        // FILTER STATE
        // ════════════════════════════════════════════════════════════
        const serverFilters = {
            assigned_to  : '',
            project_id   : '',
            priority     : '',
            task_type    : '',
            task_category: '',
            quick_filter : '',
        };

        let filterDebounce = null;

        function buildParams() {
            const p = {};
            Object.entries(serverFilters).forEach(([k,v]) => { if (v) p[k] = v; });
            return p;
        }

        function applyFilters(immediate) {
            clearTimeout(filterDebounce);
            filterDebounce = setTimeout(function () { fetchBoard(buildParams()); }, immediate ? 0 : 280);
        }

        function fetchBoard(params) {
            $('#boardLoadingOverlay').removeClass('d-none');
            $.ajax({
                url    : "{{ route('tasks.board') }}",
                method : 'GET',
                data   : params,
                headers: { 'X-Requested-With':'XMLHttpRequest', Accept:'application/json' },
                success: function (res) {
                    if (!res.success) return;
                    renderBoard(res.tasks);
                    updateStatBadges(res.stats);
                    renderFilterBadges();
                },
                error   : function () { showError('Failed to apply filters'); },
                complete: function () { $('#boardLoadingOverlay').addClass('d-none'); }
            });
        }

        // ════════════════════════════════════════════════════════════
        // EXPORT BUTTONS
        // ════════════════════════════════════════════════════════════
        $('#exportExcelBtn').on('click', function () {
            const params = $.param(buildParams());
            const url    = "{{ route('tasks.export.excel') }}" + (params ? '?' + params : '');
            const btn    = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Exporting...');
            const a = document.createElement('a'); a.href = url;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            setTimeout(function () { btn.prop('disabled', false).html('<i class="fa fa-file-excel-o me-1"></i> Excel'); }, 2000);
        });

        $('#exportPdfBtn').on('click', function () {
            const params = $.param(buildParams());
            const url    = "{{ route('tasks.export.pdf') }}" + (params ? '?' + params : '');
            const btn    = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Exporting...');
            const a = document.createElement('a'); a.href = url;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            setTimeout(function () { btn.prop('disabled', false).html('<i class="fa fa-file-pdf-o me-1"></i> PDF'); }, 2000);
        });

        // ════════════════════════════════════════════════════════════
        // RENDER BOARD FROM JSON
        // ════════════════════════════════════════════════════════════
        function renderBoard(tasksByStatus) {
            const currentUserId = {{ auth()->id() }};
            const isAdmin       = {{ auth()->user()->canManageTasks() ? 'true' : 'false' }};

            const typeColors  = { daily:'type-daily', weekly:'type-weekly', monthly:'type-monthly', one_time:'type-onetime' };
            const typeLabels  = { daily:'Daily', weekly:'Weekly', monthly:'Monthly', one_time:'One-Time' };
            const catBadgeMap = {
                social_media : { label:'Social',  cls:'cat-badge-social'  },
                blog_post   : { label:'Blog',    cls:'cat-badge-blog'    },
                design       : { label:'Design',  cls:'cat-badge-design'  },
                development  : { label:'Dev',     cls:'cat-badge-dev'     },
                client_work  : { label:'Client',  cls:'cat-badge-client'  },
                meetings     : { label:'Meeting', cls:'cat-badge-meeting' },
            };

            ['new','in_progress','completed'].forEach(function (status) {
                const col    = $('.task-column[data-status="' + status + '"]');
                const tasks  = tasksByStatus[status] || [];
                const addBtn = col.find('.add-task-inline');

                col.find('.task-card, .filter-empty-msg').remove();

                if (tasks.length === 0) {
                    const hasActiveFilter = Object.values(serverFilters).some(v => v);
                    if (hasActiveFilter) {
                        const msg = $('<div class="filter-empty-msg text-center text-muted py-4 small">'
                            + '<i class="fa fa-inbox fa-2x d-block mb-2 opacity-50"></i>No tasks match filters</div>');
                        addBtn.length ? addBtn.before(msg) : col.append(msg);
                    }
                    $('.col-count[data-col="' + status + '"]').text(0);
                    return;
                }

                tasks.forEach(function (task) {
                    const isCompleted  = status === 'completed';
                    const isRecurring  = ['daily','weekly','monthly'].includes(task.task_type);
                    const assignedIds  = (task.assigned_to || []).map(String);
                    const isAssigned   = assignedIds.includes(String(currentUserId));
                    const employees    = task.employees || [];
                    const totalSeconds = task.total_seconds || 0;
                    const timeLogs     = task.time_logs || [];
                    const runningLog   = timeLogs.find(l => !l.end && String(l.user_id) === String(currentUserId));
                    const timerRunning = !!runningLog;

                    // ── Due date chip ─────────────────────────────
                    let dueHtml = '';
                    if (isCompleted) {
                        dueHtml = '<span class="task-done-badge"><i class="fa fa-check-circle me-1"></i>Done</span>';
                    } else if (task.due_date) {
                        const due      = new Date(task.due_date + 'T00:00:00');
                        const today    = new Date(); today.setHours(0,0,0,0);
                        const tomorrow = new Date(today); tomorrow.setDate(today.getDate()+1);
                        const lbl = due < today
                            ? due.toLocaleDateString('en-GB',{day:'2-digit',month:'short'})
                            : due.getTime()===today.getTime()    ? 'Today'
                            : due.getTime()===tomorrow.getTime() ? 'Tomorrow'
                            : due.toLocaleDateString('en-GB',{day:'2-digit',month:'short'});
                        const cls = task.is_overdue ? 'text-danger' : 'text-muted';
                        dueHtml = '<span class="due-date-chip ' + cls + '">'
                            + '<i class="fa fa-calendar-o me-1" style="font-size:.65rem"></i>' + lbl + '</span>';
                    }

                    // ── Priority badge ────────────────────────────
                    let priCls = 'bg-secondary';
                    if (task.priority === 'urgent' || task.priority === 'high') priCls = 'bg-danger';
                    else if (task.priority === 'medium') priCls = 'bg-warning text-dark';

                    // ── Category badge ────────────────────────────
                    const cat     = catBadgeMap[task.task_category];
                    const catHtml = cat ? '<span class="badge task-cat-badge ' + cat.cls + '">' + cat.label + '</span>' : '';

                    // ── Type badge ────────────────────────────────
                    const typeHtml = isRecurring
                        ? '<span class="badge task-type-badge ' + (typeColors[task.task_type]||'type-onetime') + '">'
                          + '<i class="fa fa-refresh me-1" style="font-size:.65rem"></i>' + (typeLabels[task.task_type]||'') + '</span>'
                        : '';

                    // ── Project badge ─────────────────────────────
                    const projHtml = task.project_name
                        ? '<span class="badge bg-dark task-project">' + esc(task.project_name) + '</span>'
                        : '';

                    // ── Avatar stack ──────────────────────────────
                    let avatarHtml = '<div class="avatar-stack">';
                    employees.slice(0,3).forEach(function (e) {
                        avatarHtml += '<div class="avatar-circle" title="' + esc(e.username) + '">' + esc(e.initials) + '</div>';
                    });
                    if (employees.length > 3) avatarHtml += '<div class="avatar-circle avatar-more">+' + (employees.length-3) + '</div>';
                    avatarHtml += '</div>';

                    // ── Timer button (top-right) ──────────────────
                    const timerHtml = (isAssigned || isAdmin)
                        ? '<button class="btn btn-xs ' + (timerRunning ? 'btn-danger' : 'btn-success') + ' timer-btn"'
                          + ' data-id="' + task.id + '"'
                          + ' data-running="' + timerRunning + '"'
                          + ' data-started-at="' + (runningLog ? runningLog.start : '') + '"'
                          + ' data-total="' + totalSeconds + '">'
                          + '<i class="fa ' + (timerRunning ? 'fa-stop-circle' : 'fa-play-circle') + '"></i>'
                          + '<span class="timer-display">' + formatSeconds(totalSeconds) + '</span>'
                          + '</button>'
                        : '';

                    // ── Remark button (top-right) ─────────────────
                    const remarkBtn =
                        '<button class="btn btn-xs btn-ghost remark-btn"'
                        + ' data-id="' + task.id + '"'
                        + ' data-title="' + esc(task.title) + '"><i class="fa fa-comments"></i></button>';

                    // ── Edit & delete (footer, beside due date) ───
                    const assignedJson = JSON.stringify(task.assigned_to || []).replace(/'/g,"&#39;");
                    let footerActionBtns = '';
                    if (isAdmin) {
                        footerActionBtns =
                            '<button class="btn btn-ghost-edit edit-task-btn" title="Edit task"'
                            + ' data-id="' + task.id + '"'
                            + ' data-title="' + esc(task.title) + '"'
                            + ' data-description="' + esc(task.description||'') + '"'
                            + " data-assigned='" + assignedJson + "'"
                            + ' data-priority="' + task.priority + '"'
                            + ' data-task-type="' + (task.task_type||'one_time') + '"'
                            + ' data-project="' + (task.project_id||'') + '"'
                            + ' data-due="' + (task.due_date ? task.due_date.substring(0,10) : '') + '"'
                            + ' data-status="' + task.status + '"'
                            + ' data-category="' + (task.task_category||'') + '"'
                            + ' data-is-admin="true"><i class="fa fa-pencil"></i></button>'
                            + '<button class="btn btn-ghost-delete delete-task-btn" title="Delete task"'
                            + ' data-id="' + task.id + '"><i class="fa fa-trash"></i></button>';
                    } else if (isAssigned) {
                        footerActionBtns =
                            '<button class="btn btn-ghost-edit edit-task-btn" title="Edit task"'
                            + ' data-id="' + task.id + '"'
                            + ' data-title="' + esc(task.title) + '"'
                            + ' data-description="' + esc(task.description||'') + '"'
                            + " data-assigned='" + assignedJson + "'"
                            + ' data-priority="' + task.priority + '"'
                            + ' data-task-type="' + (task.task_type||'one_time') + '"'
                            + ' data-project="' + (task.project_id||'') + '"'
                            + ' data-due="' + (task.due_date ? task.due_date.substring(0,10) : '') + '"'
                            + ' data-status="' + task.status + '"'
                            + ' data-category="' + (task.task_category||'') + '"'
                            + ' data-is-admin="false"><i class="fa fa-pencil"></i></button>';
                    }

    $(document).ready(function () {

        // ── Select2 ──────────────────────────────────────────────────
        $('.assign-select-create').select2({ placeholder:
                    const titleCls = isCompleted
                        ? 'fw-semibold task-title text-decoration-line-through text-muted'
                        : 'fw-semibold task-title';
                    const descHtml = task.description
                        ? '<small class="text-muted task-description">'
                          + esc(task.description.substring(0,60))
                          + (task.description.length>60?'...':'')
                          + '</small>'
                        : '';

                    const card = $(
                        '<div class="card mb-2 task-card' + (isCompleted?' task-done':'') + '"'
                        + ' data-id="' + task.id + '"'
                        + ' data-position="' + task.position + '"'
                        + ' data-priority="' + task.priority + '"'
                        + ' data-task-type="' + (task.task_type||'one_time') + '"'
                        + ' data-project-id="' + (task.project_id||'') + '"'
                        + ' data-assigned-ids="' + assignedIds.join(',') + '"'
                        + ' data-category="' + (task.task_category||'') + '">'
                        + '<div class="card-body p-2">'
                        // Title row — remarks + timer only
                        + '<div class="d-flex justify-content-between align-items-start gap-1">'
                        + '<div class="flex-grow-1"><div class="' + titleCls + '">' + esc(task.title) + '</div>' + descHtml + '</div>'
                        + '<div class="d-flex gap-1 flex-shrink-0">' + remarkBtn + timerHtml + '</div>'
                        + '</div>'
                        // Badge row
                        + '<div class="mt-2 d-flex flex-wrap gap-1 align-items-center">'
                        + catHtml
                        + '<span class="badge task-priority ' + priCls + '">' + ucFirst(task.priority) + '</span>'
                        + typeHtml + projHtml + '</div>'
                        // Footer row — avatars | due date + edit + delete
                        + '<div class="mt-2 d-flex align-items-center justify-content-between">'
                        + avatarHtml
                        + '<div class="d-flex align-items-center gap-1">' + dueHtml + footerActionBtns + '</div>'
                        + '</div>'
                        + '</div></div>'
                    );

                    addBtn.length ? addBtn.before(card) : col.append(card);

                    if (timerRunning && runningLog) {
                        resumeCountup(col.find('.timer-btn[data-id="' + task.id + '"]'), task.id, totalSeconds, runningLog.start);
                    }
                });

                $('.col-count[data-col="' + status + '"]').text(tasks.length);
            });

            initSortable();

            const q = $('#taskSearch').val().toLowerCase();
            if (q) {
                $('.task-card').each(function () {
                    const title = $(this).find('.task-title').text().toLowerCase();
                    $(this).toggleClass('filter-hidden', !title.includes(q));
                });
                updateColumnCounts();
            }
        }

        // ════════════════════════════════════════════════════════════
        // STAT BADGES
        // ════════════════════════════════════════════════════════════
        function updateStatBadges(stats) {
            if (!stats) return;
            $('.stat-value[data-stat="total"]').text(stats.total || 0);
            $('.stat-value[data-stat="assigned"]').text(stats.new || 0);
            $('.stat-value[data-stat="in_progress"]').text(stats.in_progress || 0);
            $('.stat-value[data-stat="completed"]').text(stats.completed || 0);
            $('.sidebar-total').text(stats.total || 0);
        }

        // ════════════════════════════════════════════════════════════
        // FILTER BADGES
        // ════════════════════════════════════════════════════════════
        function renderFilterBadges() {
            const badges = [];
            if (serverFilters.assigned_to)
                badges.push(makeBadge('primary','fa-user', $('#filterEmployee option:selected').text(), 'assigned_to'));
            if (serverFilters.project_id)
                badges.push(makeBadge('dark','fa-folder', $('#filterProject option:selected').text(), 'project_id'));
            if (serverFilters.priority)
                badges.push(makeBadge('warning text-dark','fa-flag', ucFirst(serverFilters.priority), 'priority'));
            if (serverFilters.task_type)
                badges.push(makeBadge('info text-dark','fa-refresh', ucFirst(serverFilters.task_type.replace('_',' ')), 'task_type'));
            if (serverFilters.task_category)
                badges.push(makeBadge('secondary','fa-tag', ucFirst(serverFilters.task_category.replace('_',' ')), 'task_category'));
            if (serverFilters.quick_filter)
                badges.push(makeBadge('light text-dark border','fa-filter', ucFirst(serverFilters.quick_filter), 'quick_filter'));

            $('#activeFilterBadges').html(
                badges.length ? '<small class="text-muted me-1">Active:</small>' + badges.join('') : ''
            );
        }

        function makeBadge(color, icon, text, key) {
            return '<span class="badge bg-' + color + ' filter-badge">'
                + '<i class="fa ' + icon + '"></i> ' + text
                + ' <span class="remove-badge" data-key="' + key + '">×</span>'
                + '</span>';
        }

        // ════════════════════════════════════════════════════════════
        // FILTER BINDINGS
        // ════════════════════════════════════════════════════════════
        $('#filterEmployee').on('change', function () { serverFilters.assigned_to = $(this).val(); applyFilters(true); });
        $('#filterProject').on('change',  function () { serverFilters.project_id  = $(this).val(); applyFilters(true); });
        $('#filterPriority').on('change', function () { serverFilters.priority    = $(this).val(); applyFilters(true); });
        $('#filterTaskType').on('change', function () { serverFilters.task_type   = $(this).val(); applyFilters(true); });

        $(document).on('click', '.quick-filter', function (e) {
            e.preventDefault();
            $('.quick-filter').removeClass('active');
            $(this).addClass('active');
            serverFilters.quick_filter = $(this).data('filter') || '';
            applyFilters(true);
        });

        $(document).on('click', '.category-filter', function (e) {
            e.preventDefault();
            const cat = $(this).data('category');
            serverFilters.task_category = (serverFilters.task_category === cat) ? '' : cat;
            $('.category-filter').removeClass('active');
            if (serverFilters.task_category) $(this).addClass('active');
            applyFilters(true);
        });

        $(document).on('click', '.tasktype-filter', function (e) {
            e.preventDefault();
            const type = $(this).data('type');
            serverFilters.task_type = (serverFilters.task_type === type) ? '' : type;
            $('.tasktype-filter').removeClass('active');
            if (serverFilters.task_type) $(this).addClass('active');
            $('#filterTaskType').val(serverFilters.task_type);
            applyFilters(true);
        });

        $('#clearFiltersBtn').on('click', function () {
            Object.keys(serverFilters).forEach(function (k) { serverFilters[k] = ''; });
            $('#filterEmployee,#filterProject,#filterPriority,#filterTaskType').val('');
            $('#taskSearch').val('');
            $('.sidebar-item').removeClass('active');
            $('.quick-filter[data-filter=""]').addClass('active');
            applyFilters(true);
        });

        $(document).on('click', '.remove-badge', function (e) {
            e.preventDefault();
            const key = $(this).data('key');
            serverFilters[key] = '';
            if (key === 'assigned_to')  $('#filterEmployee').val('');
            if (key === 'project_id')   $('#filterProject').val('');
            if (key === 'priority')     $('#filterPriority').val('');
            if (key === 'task_type')    $('#filterTaskType').val('');
            if (key === 'quick_filter') {
                $('.quick-filter').removeClass('active');
                $('.quick-filter[data-filter=""]').addClass('active');
            }
            applyFilters(true);
        });

        $('#taskSearch').on('input', function () {
            const q = $(this).val().toLowerCase();
            $('.task-card').each(function () {
                const title = $(this).find('.task-title').text().toLowerCase();
                $(this).toggleClass('filter-hidden', q.length > 0 && !title.includes(q));
            });
            updateColumnCounts();
        });

        function updateColumnCounts() {
            $('.task-column').each(function () {
                const status = $(this).data('status');
                const count  = $(this).find('.task-card:not(.filter-hidden)').length;
                $('.col-count[data-col="' + status + '"]').text(count);
            });
        }

        // ════════════════════════════════════════════════════════════
        // SORTABLE
        // ════════════════════════════════════════════════════════════
        function initSortable() {
            $('.task-column').sortable({
                connectWith         : '.task-column',
                items               : '.task-card',
                placeholder         : 'task-placeholder',
                forcePlaceholderSize: true,
                tolerance           : 'pointer',
                cursor              : 'grabbing',
                start : function (e, ui) { ui.placeholder.height(ui.item.height()); ui.item.addClass('dragging'); },
                stop  : function (e, ui) { ui.item.removeClass('dragging'); },
                update: function (e, ui) {
                    if (this === ui.item.parent()[0]) {
                        const taskId    = ui.item.data('id');
                        const newStatus = ui.item.closest('.task-column').data('status');
                        const newPos    = ui.item.index() + 1;
                        ui.item.css('opacity','.6');
                        $.ajax({
                            url   : "{{ route('tasks.updateStatus') }}",
                            method: 'POST',
                            data  : { _token:"{{ csrf_token() }}", task_id:taskId, status:newStatus, position:newPos },
                            success: function (res) {
                                if (res.success) { ui.item.css('opacity','1'); updateColumnCounts(); showSuccess('Status updated'); }
                                else { $('.task-column').sortable('cancel'); showError('Failed to update'); }
                            },
                            error: function () { $('.task-column').sortable('cancel'); showError('Something went wrong'); }
                        });
                    }
                }
            }).disableSelection();
        }

        initSortable();

        // ════════════════════════════════════════════════════════════
        // EDIT TASK — prefill with .attr() to avoid jQuery cache issues
        // ════════════════════════════════════════════════════════════
        $(document).on('click', '.edit-task-btn', function () {
            const btn = $(this);

            $('#edit-task-id').val(btn.attr('data-id'));
            $('#edit-title').val(btn.attr('data-title'));
            $('#edit-description').val(btn.attr('data-description') || '');
            $('#edit-priority').val(btn.attr('data-priority'));
            $('#edit-task-type').val(btn.attr('data-task-type') || 'one_time');
            $('#edit-due').val((btn.attr('data-due') || '').substring(0, 10));
            $('#edit-status').val(btn.attr('data-status'));
            $('#edit-project').val(btn.attr('data-project') || '');
            $('#edit-category').val(btn.attr('data-category') || '');

            try {
                const ids = JSON.parse(btn.attr('data-assigned') || '[]');
                $('#edit-assigned').val(ids.map(String)).trigger('change');
            } catch (e) {
                $('#edit-assigned').val([]).trigger('change');
            }

            editModalObj.show();
        });

        $('#saveTaskBtn').on('click', function () {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            $.ajax({
                url    : "{{ route('tasks.update') }}",
                method : 'POST',
                data   : $('#editTaskForm').serialize(),
                success: function (res) {
                    if (res.success) { editModalObj.hide(); showSuccess('Task updated successfully'); applyFilters(true); }
                },
                error   : function (xhr) { showError(xhr.responseJSON?.error || 'Something went wrong'); },
                complete: function () { btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Changes'); }
            });
        });

        // ════════════════════════════════════════════════════════════
        // DELETE TASK
        // ════════════════════════════════════════════════════════════
        $(document).on('click', '.delete-task-btn', function () {
            const taskId = $(this).data('id');
            Swal.fire({
                title:'Are you sure?', text:'This task will be permanently deleted!',
                icon:'warning', showCancelButton:true,
                confirmButtonColor:'#d33', confirmButtonText:'Yes, delete it!'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.post("{{ route('tasks.delete') }}", { _token:"{{ csrf_token() }}", task_id:taskId }, function (res) {
                    if (res.success) { showSuccess('Task deleted'); applyFilters(true); }
                });
            });
        });

        // ════════════════════════════════════════════════════════════
        // MODAL RESETS
        // ════════════════════════════════════════════════════════════
        $('#createTaskModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $('.assign-select-create').val(null).trigger('change');
        });
        editModalEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('editTaskForm').reset();
            $('.assign-select-edit').val(null).trigger('change');
        });

        // ════════════════════════════════════════════════════════════
        // TOAST HELPERS
        // ════════════════════════════════════════════════════════════
        function showSuccess(msg) {
            Swal.fire({ icon:'success', title:'Success!', text:msg, timer:1500, showConfirmButton:false, toast:true, position:'top-end' });
        }
        function showError(msg) {
            Swal.fire({ icon:'error', title:'Error', text:msg });
        }

        // ════════════════════════════════════════════════════════════
        // HELPERS
        // ════════════════════════════════════════════════════════════
        function esc(str) {
            return String(str || '')
                .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
        }
        function ucFirst(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

    }); // end ready


    // ══════════════════════════════════════════════════════════════
    // REMARKS
    // ══════════════════════════════════════════════════════════════
    const remarkModal = new bootstrap.Modal(document.getElementById('remarkModal'));

    $(document).on('click', '.remark-btn', function () {
        const taskId    = $(this).data('id');
        const taskTitle = $(this).data('title');
        $('#currentTaskId').val(taskId);
        $('#remarkTaskTitle').text(taskTitle);
        $('#remarkText,#remarkLink').val('');
        $('#remarkAttachment').val('');
        $('#filePreview').addClass('d-none');
        $('.attach-type-btn').removeClass('active');
        $('.attach-type-btn[data-type="none"]').addClass('active');
        $('#fileUploadSection,#linkSection').addClass('d-none');
        remarkModal.show();
        loadRemarks(taskId);
    });

    function loadRemarks(taskId) {
        $('#remarksList').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>');
        $.get(`{{ url('/admin/admin/tasks') }}/${taskId}/remarks`, function (res) {
            if (!res.success || !res.remarks.length) {
                $('#remarksList').html('<div class="text-center py-4 text-muted"><i class="fa fa-comments fa-2x mb-2 d-block opacity-50"></i>No remarks yet.</div>');
                return;
            }
            $('#remarksList').html(res.remarks.map(buildRemarkHtml).join(''));
            $('#remarksList').scrollTop($('#remarksList')[0].scrollHeight);
        }).fail(function () { $('#remarksList').html('<p class="text-danger text-center">Failed to load.</p>'); });
    }

    function buildRemarkHtml(r) {
        let att = '';
        if (r.attachment_type === 'image')  att = `<div class="mt-2"><a href="${r.attachment_path}" target="_blank"><img src="${r.attachment_path}" class="img-thumbnail" style="max-height:150px"></a></div>`;
        else if (r.attachment_type === 'pdf')   att = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-danger mt-2"><i class="fa fa-file-pdf-o"></i> ${r.attachment_name}</a>`;
        else if (r.attachment_type === 'excel') att = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-success mt-2"><i class="fa fa-file-excel-o"></i> ${r.attachment_name}</a>`;
        else if (r.attachment_type === 'word')  att = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-primary mt-2"><i class="fa fa-file-word-o"></i> ${r.attachment_name}</a>`;
        else if (r.attachment_type === 'link')  att = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-info mt-2"><i class="fa fa-external-link"></i> Open Link</a>`;
        else if (r.attachment_type === 'text')  att = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-secondary mt-2"><i class="fa fa-file-text-o"></i> ${r.attachment_name}</a>`;

        const editBtn   = r.can_edit   ? `<button class="btn btn-xs btn-ghost text-primary edit-remark-btn me-1" data-id="${r.id}" data-remark="${escAttr(r.remark||'')}"><i class="fa fa-edit"></i></button>` : '';
        const deleteBtn = r.can_delete ? `<button class="btn btn-xs btn-ghost text-danger delete-remark-btn" data-id="${r.id}"><i class="fa fa-trash"></i></button>` : '';

        return `<div class="remark-item d-flex gap-2 mb-3" data-remark-id="${r.id}">
            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">${r.user_initials}</div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div><span class="fw-semibold small">${r.user_name}</span><span class="text-muted small ms-2">${r.created_at}</span></div>
                    <div class="d-flex align-items-center">${editBtn}${deleteBtn}</div>
                </div>
                ${r.remark ? `<p class="mb-1 small remark-text-display">${r.remark}</p>` : '<p class="mb-1 small remark-text-display text-muted fst-italic">(no text)</p>'}
                <div class="remark-edit-form d-none mt-1">
                    <textarea class="form-control form-control-sm remark-edit-textarea" rows="2">${escHtml(r.remark||'')}</textarea>
                    <div class="d-flex gap-1 mt-1">
                        <button class="btn btn-xs btn-primary save-remark-edit-btn">Save</button>
                        <button class="btn btn-xs btn-secondary cancel-remark-edit-btn">Cancel</button>
                    </div>
                </div>
                ${att}
            </div>
        </div>`;
    }

    function escAttr(str) {
        return String(str||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#039;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function escHtml(str) {
        return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    $(document).on('click', '.edit-remark-btn', function () {
        const item = $(this).closest('.remark-item');
        item.find('.remark-text-display').addClass('d-none');
        item.find('.remark-edit-form').removeClass('d-none');
        item.find('.remark-edit-textarea').focus();
    });
    $(document).on('click', '.cancel-remark-edit-btn', function () {
        const item = $(this).closest('.remark-item');
        item.find('.remark-edit-form').addClass('d-none');
        item.find('.remark-text-display').removeClass('d-none');
    });
    $(document).on('click', '.save-remark-edit-btn', function () {
        const btn  = $(this);
        const item = btn.closest('.remark-item');
        const id   = item.data('remark-id');
        const text = item.find('.remark-edit-textarea').val().trim();
        if (!text) { alert('Remark cannot be empty.'); return; }
        btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url    : `{{ url('/admin/admin/tasks/remarks') }}/${id}`,
            method : 'PUT',
            data   : { _token: '{{ csrf_token() }}', remark: text },
            success: function (res) {
                if (res.success) {
                    item.find('.remark-text-display').text(text).removeClass('d-none');
                    item.find('.remark-edit-form').addClass('d-none');
                    item.find('.edit-remark-btn').attr('data-remark', text);
                    Swal.fire({ icon:'success', title:'Updated!', timer:1000, showConfirmButton:false, toast:true, position:'top-end' });
                }
            },
            error   : function (xhr) { alert(xhr.responseJSON?.message || 'Failed to save remark.'); },
            complete: function () { btn.prop('disabled', false).text('Save'); }
        });
    });
    $(document).on('click', '.delete-remark-btn', function () {
        const id   = $(this).data('id');
        const item = $(this).closest('.remark-item');
        Swal.fire({ title:'Delete remark?', icon:'warning', showCancelButton:true, confirmButtonColor:'#d33', confirmButtonText:'Delete' })
            .then(function (r) {
                if (!r.isConfirmed) return;
                $.ajax({
                    url   : `{{ url('/admin/admin/tasks/remarks') }}/${id}`,
                    method: 'DELETE',
                    data  : { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) item.fadeOut(300, function () { $(this).remove(); });
                    }
                });
            });
    });

    $(document).on('click', '.attach-type-btn', function () {
        $('.attach-type-btn').removeClass('active'); $(this).addClass('active');
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
        const icons = { pdf:'fa-file-pdf-o text-danger', xlsx:'fa-file-excel-o text-success', xls:'fa-file-excel-o text-success', csv:'fa-file-excel-o text-success', doc:'fa-file-word-o text-primary', docx:'fa-file-word-o text-primary', jpg:'fa-file-image-o text-warning', jpeg:'fa-file-image-o text-warning', png:'fa-file-image-o text-warning', gif:'fa-file-image-o text-warning' };
        $('#filePreviewIcon').attr('class', `fa ${icons[ext] || 'fa-file text-secondary'} fa-lg`);
        $('#filePreviewName').text(file.name);
        $('#filePreview').removeClass('d-none');
    });
    $('#clearFileBtn').on('click', function () { $('#remarkAttachment').val(''); $('#filePreview').addClass('d-none'); });

    $('#submitRemarkBtn').on('click', function () {
        const taskId     = $('#currentTaskId').val();
        const remarkText = $('#remarkText').val().trim();
        const activeType = $('.attach-type-btn.active').data('type');
        const file       = $('#remarkAttachment')[0].files[0];
        const link       = $('#remarkLink').val().trim();
        if (!remarkText && activeType === 'none') { alert('Please add a remark or attachment.'); return; }
        if (activeType === 'file' && !file)       { alert('Please select a file.'); return; }
        if (activeType === 'link' && !link)       { alert('Please enter a link.'); return; }

        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        if (remarkText)                    fd.append('remark', remarkText);
        if (activeType === 'file' && file) fd.append('attachment', file);
        if (activeType === 'link' && link) fd.append('link', link);

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Adding...');
        $.ajax({
            url        : `{{ url('/admin/admin/tasks') }}/${taskId}/remarks`,
            method     : 'POST',
            data       : fd,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    const html = buildRemarkHtml(res.remark);
                    if ($('#remarksList .text-center').length) $('#remarksList').html(html);
                    else $('#remarksList').append(html);
                    $('#remarksList').scrollTop($('#remarksList')[0].scrollHeight);
                    $('#remarkText,#remarkLink').val('');
                    $('#remarkAttachment').val('');
                    $('#filePreview').addClass('d-none');
                    $('.attach-type-btn').removeClass('active');
                    $('.attach-type-btn[data-type="none"]').addClass('active');
                    $('#fileUploadSection,#linkSection').addClass('d-none');
                }
            },
            error   : function (xhr) { alert(xhr.responseJSON?.message || 'Failed to add remark.'); },
            complete: function () { btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Add Remark'); }
        });
    });


    // ══════════════════════════════════════════════════════════════
    // TIMER — auto-move to In Progress on start
    // ══════════════════════════════════════════════════════════════
    const timerIntervals = {};

    // Resume any already-running timers on page load
    $('.timer-btn').each(function () {
        const btn       = $(this);
        const isRunning = btn.data('running') === true || btn.data('running') === 'true';
        const startedAt = parseInt(btn.data('started-at')) || 0;
        const base      = parseInt(btn.data('total')) || 0;
        if (isRunning && startedAt) resumeCountup(btn, btn.data('id'), base, startedAt);
    });

    $(document).on('click', '.timer-btn', function () {
        const btn       = $(this);
        const taskId    = btn.data('id');
        const isRunning = btn.data('running') === true || btn.data('running') === 'true';
        btn.prop('disabled', true);

        if (!isRunning) {
            $.ajax({
                url   : `/admin/admin/tasks/${taskId}/timer/start`,
                method: 'POST',
                data  : { _token:'{{ csrf_token() }}' },
                success: function (res) {
                    if (!res.success) return;

                    const base = parseInt(btn.data('total')) || 0;
                    btn.data('running','true').data('started-at', res.started_at);
                    btn.removeClass('btn-success').addClass('btn-danger');
                    btn.find('i').removeClass('fa-play-circle').addClass('fa-stop-circle');
                    resumeCountup(btn, taskId, base, res.started_at);

                    // Auto-move card from Assigned → In Progress
                    if (res.status_changed) {
                        const card      = btn.closest('.task-card');
                        const targetCol = $('.task-column[data-status="in_progress"]');
                        const addInline = targetCol.find('.add-task-inline');

                        card.css({ transition:'opacity .3s', opacity:'0.3' });
                        setTimeout(function () {
                            card.detach().css({ opacity:'1', transition:'' });
                            addInline.length ? addInline.before(card) : targetCol.append(card);
                            updateColumnCounts();
                            Swal.fire({
                                icon:'info', title:'Task Started',
                                text:'Moved to In Progress',
                                timer:1500, showConfirmButton:false,
                                toast:true, position:'top-end'
                            });
                        }, 350);
                    }
                },
                complete: function () { btn.prop('disabled', false); }
            });
        } else {
            $.ajax({
                url   : `/admin/admin/tasks/${taskId}/timer/stop`,
                method: 'POST',
                data  : { _token:'{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        clearInterval(timerIntervals[taskId]);
                        delete timerIntervals[taskId];
                        btn.data('running','false').data('total', res.total_seconds);
                        btn.removeClass('btn-danger').addClass('btn-success');
                        btn.find('i').removeClass('fa-stop-circle').addClass('fa-play-circle');
                        btn.find('.timer-display').text(formatSeconds(res.total_seconds));
                    }
                },
                complete: function () { btn.prop('disabled', false); }
            });
        }
    });

    function resumeCountup(btn, taskId, base, startedAt) {
        clearInterval(timerIntervals[taskId]);
        timerIntervals[taskId] = setInterval(function () {
            const elapsed = Math.floor(Date.now() / 1000) - startedAt;
            btn.find('.timer-display').text(formatSeconds(base + elapsed));
        }, 1000);
    }

    function formatSeconds(total) {
        total = Math.max(0, Math.floor(total));
        return [Math.floor(total / 3600), Math.floor((total % 3600) / 60), total % 60]
            .map(function (n) { return String(n).padStart(2,'0'); }).join(':');
    }

    function updateColumnCounts() {
        $('.task-column').each(function () {
            const status = $(this).data('status');
            const count  = $(this).find('.task-card:not(.filter-hidden)').length;
            $('.col-count[data-col="' + status + '"]').text(count);
        });
    }
    </script>
@endpush