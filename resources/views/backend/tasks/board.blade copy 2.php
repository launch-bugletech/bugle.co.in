@extends(backendView('layouts.app'))

@section('title', 'Task Board')

@section('content')
<div class="container-xxl">

    {{-- ═══════════════════════════════════════════════════════════
         TOP STATS BAR
    ════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-3">
        @php
            $statItems = [
                ['label' => 'Total',       'value' => $stats['total'],       'sub' => 'this week',    'color' => 'primary'],
                ['label' => 'New',         'value' => $stats['new'],         'sub' => 'not started',  'color' => 'secondary'],
                ['label' => 'In Progress', 'value' => $stats['in_progress'], 'sub' => 'active',       'color' => 'warning'],
                ['label' => 'Done',        'value' => $stats['completed'],   'sub' => 'completed',    'color' => 'success'],
                ['label' => 'Recurring',   'value' => $stats['recurring'],   'sub' => 'auto-creates', 'color' => 'info'],
            ];
        @endphp
        @foreach($statItems as $stat)
            <div class="col">
                <div class="card shadow-sm border-0 stat-card">
                    <div class="card-body py-3 px-3">
                        <div class="text-muted small">{{ $stat['label'] }}</div>
                        <div class="fs-4 fw-bold text-{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ $stat['sub'] }}</div>
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
                        <span class="ms-auto badge bg-secondary">{{ $stats['total'] }}</span>
                    </a>
                    <a href="#" class="sidebar-item quick-filter" data-filter="recurring">
                        <i class="fa fa-refresh me-2"></i> Recurring
                        <span class="ms-auto badge bg-info text-dark">{{ $stats['recurring'] }}</span>
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
                                <span class="ms-auto text-muted small">{{ $count }}</span>
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
        <div class="col-md-10">

            {{-- Top action bar --}}
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <div class="input-group input-group-sm" style="width:220px">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                        <input type="text" id="taskSearch" class="form-control" placeholder="Search tasks...">
                    </div>

                    {{-- Quick filter pills (mirror sidebar) --}}
                    <div class="btn-group btn-group-sm" id="quickFilterPills">
                        <button class="btn btn-outline-secondary active" data-filter="">All</button>
                        <button class="btn btn-outline-secondary" data-filter="recurring"><i class="fa fa-refresh me-1"></i>Recurring</button>
                        <button class="btn btn-outline-secondary" data-filter="mine"><i class="fa fa-user me-1"></i>Mine</button>
                        <button class="btn btn-outline-secondary" data-filter="overdue"><i class="fa fa-clock-o me-1"></i>Overdue</button>
                    </div>
                </div>

                {{-- Inline filters (collapsible) --}}
                <div class="d-flex gap-2 flex-wrap">
                    @if(auth()->user()->canManageTasks())
                        <select id="filterEmployee" class="form-select form-select-sm" style="width:auto">
                            <option value="">All Employees</option>
                            @foreach(\App\Models\User::role(['Employee', 'Admin'])->get() as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->username }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select id="filterProject" class="form-select form-select-sm" style="width:auto">
                        <option value="">All Projects</option>
                        @foreach(\App\Models\Project::where('status', 'active')->get() as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                    <select id="filterPriority" class="form-select form-select-sm" style="width:auto">
                        <option value="">All Priorities</option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                    <button class="btn btn-sm btn-outline-secondary" id="clearFiltersBtn">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

            <div id="activeFilterBadges" class="mb-2"></div>

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
                        $recent = $bucket->filter(fn($t) => $t->created_at->gte($cutoff));
                        $old    = $bucket->filter(fn($t) => $t->created_at->lt($cutoff));
                        $recentByStatus[$statusKey] = $recent;
                        $allOldTasks = $allOldTasks->merge($old);
                    } else {
                        $recentByStatus[$statusKey] = $bucket;
                    }
                }

                $allOldTasks = $allOldTasks->sortByDesc('created_at')->values();

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

                $categoryDotMap = [
                    'social_media' => 'cat-social_media',
                    'blog_posts'   => 'cat-blog_posts',
                    'design'       => 'cat-design',
                    'development'  => 'cat-development',
                    'client_work'  => 'cat-client_work',
                    'meetings'     => 'cat-meetings',
                ];
                $categoryBadgeMap = [
                    'social_media' => ['label' => 'Social',  'cls' => 'cat-badge-social'],
                    'blog_posts'   => ['label' => 'Blog',    'cls' => 'cat-badge-blog'],
                    'design'       => ['label' => 'Design',  'cls' => 'cat-badge-design'],
                    'development'  => ['label' => 'Dev',     'cls' => 'cat-badge-dev'],
                    'client_work'  => ['label' => 'Client',  'cls' => 'cat-badge-client'],
                    'meetings'     => ['label' => 'Meeting', 'cls' => 'cat-badge-meeting'],
                ];
            @endphp

            <div class="row kanban-board g-3">
                @foreach(['new' => 'New', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $key => $label)
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
                                        $category      = $task->category ?? null;
                                        $catBadge      = $category ? ($categoryBadgeMap[$category] ?? null) : null;
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

                                            {{-- Title row --}}
                                            <div class="d-flex justify-content-between align-items-start gap-1">
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold task-title {{ $isCompleted ? 'text-decoration-line-through text-muted' : '' }}">
                                                        {{ $task->title }}
                                                    </div>
                                                    @if($task->description)
                                                        <small class="text-muted task-description">{{ Str::limit($task->description, 60) }}</small>
                                                    @endif
                                                </div>
                                                {{-- Action buttons --}}
                                                <div class="d-flex gap-1 flex-shrink-0">
                                                    @if(auth()->user()->canManageTasks())
                                                        <button class="btn btn-xs btn-ghost edit-task-btn"
                                                                data-id="{{ $task->id }}"
                                                                data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}"
                                                                data-description="{{ htmlspecialchars($task->description ?? '', ENT_QUOTES) }}"
                                                                data-assigned='@json($task->assigned_to)'
                                                                data-priority="{{ $task->priority }}"
                                                                data-task-type="{{ $taskType }}"
                                                                data-project="{{ $task->project_id ?? '' }}"
                                                                data-due="{{ $task->due_date ?? '' }}"
                                                                data-status="{{ $task->status }}"
                                                                data-category="{{ $category ?? '' }}"
                                                                data-is-admin="true">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-xs btn-ghost text-danger delete-task-btn" data-id="{{ $task->id }}">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    @elseif(in_array(auth()->id(), $assignedIds))
                                                        <button class="btn btn-xs btn-ghost edit-task-btn"
                                                                data-id="{{ $task->id }}"
                                                                data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}"
                                                                data-description="{{ htmlspecialchars($task->description ?? '', ENT_QUOTES) }}"
                                                                data-assigned='@json($task->assigned_to)'
                                                                data-priority="{{ $task->priority }}"
                                                                data-task-type="{{ $taskType }}"
                                                                data-project="{{ $task->project_id ?? '' }}"
                                                                data-due="{{ $task->due_date ?? '' }}"
                                                                data-status="{{ $task->status }}"
                                                                data-category="{{ $category ?? '' }}"
                                                                data-assignee-name="{{ $employeeNames }}"
                                                                data-is-admin="false">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                    @endif

                                                    <button class="btn btn-xs btn-ghost remark-btn"
                                                            data-id="{{ $task->id }}"
                                                            data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}">
                                                        <i class="fa fa-comments"></i>
                                                    </button>

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

                                            {{-- Badge row --}}
                                            <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">

                                                {{-- Category badge --}}
                                                @if($catBadge)
                                                    <span class="badge task-cat-badge {{ $catBadge['cls'] }}">
                                                        {{ $catBadge['label'] }}
                                                    </span>
                                                @endif

                                                {{-- Priority --}}
                                                <span class="badge task-priority
                                                    @if($task->priority == 'urgent') bg-danger
                                                    @elseif($task->priority == 'high') bg-danger opacity-75
                                                    @elseif($task->priority == 'medium') bg-warning text-dark
                                                    @else bg-secondary @endif">
                                                    {{ ucfirst($task->priority) }}
                                                </span>

                                                {{-- Recurring type badge --}}
                                                @if($isRecurring)
                                                    <span class="badge task-type-badge {{ $typeColors[$taskType] ?? 'type-onetime' }}">
                                                        <i class="fa fa-refresh me-1" style="font-size:.65rem"></i>{{ $typeLabels[$taskType] }}
                                                    </span>
                                                @endif

                                                {{-- Project --}}
                                                @if($task->project)
                                                    <span class="badge bg-dark task-project">{{ $task->project->name }}</span>
                                                @endif
                                            </div>

                                            {{-- Footer row: assignees + due date --}}
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

                                                <div class="d-flex align-items-center gap-2">
                                                    {{-- Completed + recreated label for recurring --}}
                                                    @if($isCompleted && $isRecurring)
                                                        <span class="text-muted" style="font-size:.7rem">
                                                            Done <i class="fa fa-refresh" style="font-size:.65rem"></i> recreated
                                                        </span>
                                                    @elseif($isCompleted)
                                                        <span class="text-muted" style="font-size:.7rem">Done</span>
                                                    @elseif($task->due_date)
                                                        @php
                                                            $due = \Carbon\Carbon::parse($task->due_date);
                                                            $dueLabel = $due->isToday() ? 'Today'
                                                                : ($due->isTomorrow() ? 'Tomorrow'
                                                                : ($due->isCurrentWeek() ? $due->format('D')
                                                                : $due->format('d M')));
                                                            $dueClass = $task->getIsOverdueAttribute() ? 'text-danger' : 'text-muted';
                                                        @endphp
                                                        <span class="small {{ $dueClass }}">{{ $dueLabel }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach

                                {{-- Inline add task (New column only) --}}
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


            {{-- ═══════════════════════════════════════════════
                 OLDER TASKS
            ════════════════════════════════════════════════════ --}}
            <!-- <div class="mt-5" id="olderTasksSection" style="{{ $allOldTasks->count() === 0 ? 'display:none' : '' }}">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0">
                            <i class="fa fa-archive text-muted me-1"></i> Completed Older Tasks
                        </h5>
                        <span class="badge bg-secondary" id="oldTaskCount">{{ $allOldTasks->count() }}</span>
                        <small class="text-muted">(older than 7 days)</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" id="toggleOldTasks">
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
                                            <th>Category</th>
                                            <th>Priority</th>
                                            <th>Type</th>
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
                                                $taskType      = $task->task_type ?? 'one_time';
                                                $category      = $task->category ?? null;
                                                $catBadge      = $category ? ($categoryBadgeMap[$category] ?? null) : null;
                                            @endphp
                                            <tr class="old-task-row"
                                                data-priority="{{ $task->priority }}"
                                                data-task-type="{{ $taskType }}"
                                                data-project-id="{{ $task->project_id ?? '' }}"
                                                data-assigned-ids="{{ implode(',', $assignedIds) }}"
                                                data-category="{{ $category ?? '' }}">

                                                <td>
                                                    <div class="fw-semibold text-decoration-line-through text-muted">{{ $task->title }}</div>
                                                    @if($task->description)
                                                        <small class="text-muted">{{ Str::limit($task->description, 60) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($catBadge)
                                                        <span class="badge task-cat-badge {{ $catBadge['cls'] }}">{{ $catBadge['label'] }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge
                                                        @if($task->priority == 'high' || $task->priority == 'urgent') bg-danger
                                                        @elseif($task->priority == 'medium') bg-warning text-dark
                                                        @else bg-secondary @endif">
                                                        {{ ucfirst($task->priority) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $typeColors[$taskType] ?? 'type-onetime' }}">
                                                        {{ $typeLabels[$taskType] ?? 'One-Time' }}
                                                    </span>
                                                </td>
                                                <td>{{ optional($task->project)->name ?? '—' }}</td>
                                                <td><span class="badge bg-info text-dark">{{ $employeeNames ?: '—' }}</span></td>
                                                <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : '—' }}</td>
                                                <td><span class="text-muted small">{{ \Carbon\Carbon::parse($task->created_at)->format('d M Y') }}</span></td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        @if(auth()->user()->canManageTasks())
                                                            <button class="btn btn-xs btn-ghost edit-task-btn"
                                                                    data-id="{{ $task->id }}"
                                                                    data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}"
                                                                    data-description="{{ htmlspecialchars($task->description ?? '', ENT_QUOTES) }}"
                                                                    data-assigned='@json($task->assigned_to)'
                                                                    data-priority="{{ $task->priority }}"
                                                                    data-task-type="{{ $taskType }}"
                                                                    data-project="{{ $task->project_id ?? '' }}"
                                                                    data-due="{{ $task->due_date ?? '' }}"
                                                                    data-status="{{ $task->status }}"
                                                                    data-category="{{ $category ?? '' }}"
                                                                    data-is-admin="true">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-xs btn-ghost text-danger delete-task-btn" data-id="{{ $task->id }}">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        @endif
                                                        <button class="btn btn-xs btn-ghost remark-btn"
                                                                data-id="{{ $task->id }}"
                                                                data-title="{{ htmlspecialchars($task->title, ENT_QUOTES) }}">
                                                            <i class="fa fa-comments"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
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
            </div> -->

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
                                @foreach(\App\Models\User::role(['Employee', 'Admin'])->get() as $emp)
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
                            <select name="category" class="form-select">
                                <option value="">No Category</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">Select project</option>
                                @foreach(\App\Models\Project::where('status', 'active')->get() as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
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
                        <select name="assigned_to[]" id="edit-assigned" class="form-select assign-select-edit" multiple required
                                @if(!auth()->user()->canManageTasks()) disabled @endif>
                            @foreach(\App\Models\User::role(['Employee', 'Admin'])->get() as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" id="edit-category" class="form-select">
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
                                @foreach(\App\Models\Project::where('status', 'active')->get() as $project)
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

{{-- REMARK MODAL (unchanged) --}}
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* ── Sidebar ────────────────────────────────────────────────── */
        .sidebar-panel { padding: 4px 0; }
        .sidebar-label { font-size:.7rem; font-weight:700; letter-spacing:.08em; color:#9ca3af; padding:0 8px 4px; text-transform:uppercase; }
        .sidebar-item  { display:flex; align-items:center; padding:6px 10px; border-radius:8px; font-size:.85rem; color:#374151; text-decoration:none; transition:.15s; cursor:pointer; }
        .sidebar-item:hover, .sidebar-item.active { background:#f3f4f6; color:#111; }
        .sidebar-section { border-bottom:1px solid #f3f4f6; padding-bottom:8px; }

        /* Category dots */
        .category-dot { width:8px; height:8px; border-radius:50%; margin-right:8px; flex-shrink:0; }
        .cat-social_media  { background:#8b5cf6; }
        .cat-blog_posts    { background:#3b82f6; }
        .cat-design        { background:#ec4899; }
        .cat-development   { background:#10b981; }
        .cat-client_work   { background:#f59e0b; }
        .cat-meetings      { background:#6b7280; }

        /* ── Stats bar ────────────────────────────────────────────── */
        .stat-card { border-radius:10px; }

        /* ── Kanban ──────────────────────────────────────────────── */
        .kanban-column-card { border-radius:12px; border:1px solid #e5e7eb; overflow:hidden; background:#fff; }
        .kanban-col-header  { display:flex; justify-content:space-between; align-items:center; padding:10px 14px; font-weight:600; font-size:.9rem; background:#f9fafb; border-bottom:1px solid #e5e7eb; }
        .kanban-col-header.new         .col-label { color:#374151; }
        .kanban-col-header.in_progress .col-label { color:#d97706; }
        .kanban-col-header.completed   .col-label { color:#059669; }
        .kanban-col-header .col-count  { background:#e5e7eb; color:#6b7280; font-size:.75rem; }

        .task-column  { min-height:400px; transition:background .2s; }
        .task-card    { cursor:grab; border-radius:8px !important; border:1px solid #e5e7eb !important; transition:box-shadow .2s, transform .15s; }
        .task-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.08) !important; transform:translateY(-1px); }
        .task-card.dragging { cursor:grabbing; opacity:.5; }
        .task-placeholder   { border:2px dashed #93c5fd; background:#eff6ff; min-height:80px; margin-bottom:8px; border-radius:8px; }
        .task-done { opacity:.7; }
        .task-title   { font-size:.875rem; word-break:break-word; }
        .task-description { font-size:.8rem; }

        /* ── Ghost buttons ────────────────────────────────────────── */
        .btn-xs       { padding:2px 6px; font-size:.75rem; border-radius:5px; }
        .btn-ghost     { background:transparent; border:none; color:#6b7280; }
        .btn-ghost:hover { background:#f3f4f6; color:#111; }

        /* ── Category badges ──────────────────────────────────────── */
        .task-cat-badge      { font-size:.7rem; border-radius:12px; padding:2px 8px; font-weight:500; }
        .cat-badge-social    { background:#ede9fe; color:#5b21b6; }
        .cat-badge-blog      { background:#dbeafe; color:#1d4ed8; }
        .cat-badge-design    { background:#fce7f3; color:#9d174d; }
        .cat-badge-dev       { background:#d1fae5; color:#065f46; }
        .cat-badge-client    { background:#fef3c7; color:#92400e; }
        .cat-badge-meeting   { background:#f3f4f6; color:#374151; }

        /* ── Task type badges ─────────────────────────────────────── */
        .type-daily   { background:#dbeafe !important; color:#1d4ed8 !important; }
        .type-weekly  { background:#ede9fe !important; color:#5b21b6 !important; }
        .type-monthly { background:#d1fae5 !important; color:#065f46 !important; }
        .type-onetime { background:#f3f4f6 !important; color:#6b7280 !important; }

        /* ── Avatar stack ─────────────────────────────────────────── */
        .avatar-stack   { display:flex; }
        .avatar-circle  { width:24px; height:24px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2);
                          color:#fff; font-size:.65rem; font-weight:700; display:flex; align-items:center; justify-content:center;
                          border:2px solid #fff; margin-left:-6px; }
        .avatar-stack .avatar-circle:first-child { margin-left:0; }
        .avatar-more { background:#e5e7eb; color:#6b7280; }

        /* ── Inline add task ──────────────────────────────────────── */
        .add-task-inline { padding:8px 10px; border-radius:8px; cursor:pointer; border:1px dashed #d1d5db; font-size:.85rem; display:flex; align-items:center; margin-top:4px; }
        .add-task-inline:hover { background:#f9fafb; }

        /* ── Select2 ──────────────────────────────────────────────── */
        .select2-container--default .select2-selection--multiple { border-radius:8px; min-height:42px; }
        .select2-selection__choice { background-color:#0d6efd !important; border:none !important; color:white !important; border-radius:12px !important; }

        /* ── Timer ────────────────────────────────────────────────── */
        .timer-btn .timer-display { font-family:'Courier New',monospace; font-weight:600; letter-spacing:.5px; font-size:.7rem; }

        /* ── Remarks ─────────────────────────────────────────────── */
        .remark-item { padding:.75rem; border-radius:8px; background:#f9fafb; border:1px solid #e5e7eb; }
        .attach-type-btn.active { font-weight:700; }

        /* ── Old tasks table ──────────────────────────────────────── */
        #oldTasksTable .table th { font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function () {

        // ── Select2 ─────────────────────────────────────────────────
        $('.assign-select-create').select2({ placeholder:"Select employees...", width:'100%', dropdownParent:$('#createTaskModal') });
        $('.assign-select-edit').select2({ placeholder:"Select employees...", width:'100%', dropdownParent:$('#editTaskModal') });

        const editModalEl  = document.getElementById('editTaskModal');
        const editModalObj = new bootstrap.Modal(editModalEl);

        // ── Edit Task ────────────────────────────────────────────────
        $(document).on('click', '.edit-task-btn', function () {
            const btn = $(this);
            $('#edit-task-id').val(btn.data('id'));
            $('#edit-title').val(btn.attr('data-title'));
            $('#edit-description').val(btn.attr('data-description') || '');
            $('#edit-priority').val(btn.attr('data-priority'));
            $('#edit-task-type').val(btn.attr('data-task-type') || 'one_time');
            $('#edit-due').val(btn.attr('data-due') || '');
            $('#edit-status').val(btn.attr('data-status'));
            $('#edit-project').val(btn.attr('data-project') || '');
            $('#edit-category').val(btn.attr('data-category') || '');
            try {
                const ids = JSON.parse(btn.attr('data-assigned') || '[]');
                $('#edit-assigned').val(ids.map(String)).trigger('change');
            } catch(e) { $('#edit-assigned').val([]).trigger('change'); }
            editModalObj.show();
        });

        // ── Save Task ────────────────────────────────────────────────
        $('#saveTaskBtn').on('click', function () {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            $.ajax({
                url: "{{ route('tasks.update') }}",
                method: 'POST',
                data: $('#editTaskForm').serialize(),
                success: function (res) {
                    if (res.success) {
                        updateTaskCard(res.task, res.employee_name);
                        editModalObj.hide();
                        showSuccess('Task updated successfully');
                    }
                },
                error: function (xhr) { showError(xhr.responseJSON?.error || 'Something went wrong'); },
                complete: function () { btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save Changes'); }
            });
        });

        // ── Update card in DOM ───────────────────────────────────────
        function updateTaskCard(task, employeeNames) {
            const card      = $('.task-card[data-id="' + task.id + '"]');
            const oldStatus = card.closest('.task-column').data('status');

            card.find('.task-title').text(task.title);
            card.find('.task-description').text(task.description || '');
            card.attr('data-task-type', task.task_type || 'one_time');

            // Priority badge
            const pb = card.find('.task-priority');
            pb.removeClass('bg-danger bg-warning bg-secondary text-dark opacity-75');
            if (task.priority === 'urgent' || task.priority === 'high') pb.addClass('bg-danger');
            else if (task.priority === 'medium') pb.addClass('bg-warning text-dark');
            else pb.addClass('bg-secondary');
            pb.text(task.priority.charAt(0).toUpperCase() + task.priority.slice(1));

            // Type badge
            const typeColors = { daily:'type-daily', weekly:'type-weekly', monthly:'type-monthly', one_time:'type-onetime' };
            const typeLabels = { daily:'Daily', weekly:'Weekly', monthly:'Monthly', one_time:'One-Time' };
            const tb = card.find('.task-type-badge');
            if (tb.length) {
                tb.attr('class','badge task-type-badge ' + (typeColors[task.task_type] || 'type-onetime'));
                tb.html('<i class="fa fa-refresh me-1" style="font-size:.65rem"></i>' + (typeLabels[task.task_type] || 'One-Time'));
            }

            // Move if status changed
            if (oldStatus !== task.status) {
                const newCol = $('.task-column[data-status="' + task.status + '"]');
                newCol.append(card);
                updateColumnCounts();
            }
        }

        // ── Sortable ─────────────────────────────────────────────────
        $('.task-column').sortable({
            connectWith: '.task-column',
            items: '.task-card',
            placeholder: 'task-placeholder',
            forcePlaceholderSize: true,
            tolerance: 'pointer',
            cursor: 'grabbing',
            start: function(e, ui) { ui.placeholder.height(ui.item.height()); ui.item.addClass('dragging'); },
            stop:  function(e, ui) { ui.item.removeClass('dragging'); },
            update: function(e, ui) {
                if (this === ui.item.parent()[0]) {
                    const taskId    = ui.item.data('id');
                    const newStatus = ui.item.closest('.task-column').data('status');
                    const newPos    = ui.item.index() + 1;
                    ui.item.css('opacity','.6');
                    $.ajax({
                        url: "{{ route('tasks.updateStatus') }}",
                        method: 'POST',
                        data: { _token:"{{ csrf_token() }}", task_id:taskId, status:newStatus, position:newPos },
                        success: function(res) {
                            if (res.success) { ui.item.css('opacity','1'); updateColumnCounts(); showSuccess('Status updated'); }
                            else { $('.task-column').sortable('cancel'); showError('Failed to update'); }
                        },
                        error: function() { $('.task-column').sortable('cancel'); showError('Something went wrong'); }
                    });
                }
            }
        }).disableSelection();

        function updateColumnCounts() {
            $('.task-column').each(function() {
                const status = $(this).data('status');
                const count  = $(this).find('.task-card:not(.filter-hidden)').length;
                $('.col-count[data-col="' + status + '"]').text(count);
            });
        }

        // ── Delete Task ──────────────────────────────────────────────
        $(document).on('click', '.delete-task-btn', function() {
            const taskId = $(this).data('id');
            Swal.fire({ title:'Are you sure?', text:'This task will be permanently deleted!', icon:'warning',
                showCancelButton:true, confirmButtonColor:'#d33', confirmButtonText:'Yes, delete it!' })
            .then(result => {
                if (!result.isConfirmed) return;
                $.post("{{ route('tasks.delete') }}", { _token:"{{ csrf_token() }}", task_id:taskId }, function(res) {
                    if (res.success) {
                        $('.task-card[data-id="' + taskId + '"]').fadeOut(300, function() { $(this).remove(); updateColumnCounts(); });
                        $('#oldTasksDataTable').find('.edit-task-btn[data-id="' + taskId + '"]')
                            .closest('tr').fadeOut(300, function() { $(this).remove(); });
                        showSuccess('Task deleted');
                    }
                });
            });
        });

        // ── Notifications ────────────────────────────────────────────
        function showSuccess(msg) { Swal.fire({icon:'success',title:'Success!',text:msg,timer:1500,showConfirmButton:false,toast:true,position:'top-end'}); }
        function showError(msg)   { Swal.fire({icon:'error',title:'Error',text:msg}); }

        // ── Reset Modals ─────────────────────────────────────────────
        $('#createTaskModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('.assign-select-create').val(null).trigger('change');
        });
        editModalEl.addEventListener('hidden.bs.modal', function() {
            document.getElementById('editTaskForm').reset();
            $('.assign-select-edit').val(null).trigger('change');
        });

        // ── Toggle Older Tasks ───────────────────────────────────────
        $('#toggleOldTasks').on('click', function() {
            const open = $('#oldTasksTable').hasClass('d-none');
            $('#oldTasksTable').toggleClass('d-none', !open);
            $(this).html(open ? '<i class="fa fa-chevron-up me-1"></i> Hide' : '<i class="fa fa-chevron-down me-1"></i> Show');
        });

        // ══════════════════════════════════════════════════════════════
        // FILTERS
        // ══════════════════════════════════════════════════════════════
        const activeFilters = { employee:'', project:'', priority:'', taskType:'', category:'', quick:'' };

        function applyFilters() {
            const { employee, project, priority, taskType, category, quick } = activeFilters;
            const now = Math.floor(Date.now() / 1000);

            $('.task-card').each(function() {
                const card   = $(this);
                const col    = card.closest('.task-column').data('status');
                let show     = true;

                // Employee
                if (employee) {
                    try {
                        const ids = JSON.parse(card.find('.edit-task-btn').attr('data-assigned') || '[]');
                        show = show && ids.map(String).includes(String(employee));
                    } catch(e) {
                        const ids = String(card.data('assigned-ids') || '').split(',');
                        show = show && ids.includes(String(employee));
                    }
                }
                // Project
                if (project)  show = show && String(card.data('project-id')) === String(project);
                // Priority
                if (priority) show = show && card.data('priority') === priority;
                // Task type
                if (taskType) show = show && card.data('task-type') === taskType;
                // Category
                if (category) show = show && card.data('category') === category;

                // Quick filter
                if (quick === 'recurring') show = show && ['daily','weekly','monthly'].includes(card.data('task-type'));
                if (quick === 'mine') {
                    const ids = String(card.data('assigned-ids') || '').split(',');
                    show = show && ids.includes(String({{ auth()->id() }}));
                }
                if (quick === 'overdue') show = show && col !== 'completed';

                card.toggleClass('filter-hidden', !show);
            });

            // Old task rows
            $('.old-task-row').each(function() {
                const row = $(this);
                let show  = true;
                if (employee) {
                    const ids = String(row.data('assigned-ids') || '').split(',');
                    show = show && ids.includes(String(employee));
                }
                if (project)  show = show && String(row.data('project-id')) === String(project);
                if (priority) show = show && row.data('priority') === priority;
                if (taskType) show = show && row.data('task-type') === taskType;
                if (category) show = show && row.data('category') === category;
                if (quick === 'recurring') show = show && ['daily','weekly','monthly'].includes(row.data('task-type'));
                row.toggleClass('filter-hidden', !show);
            });

            // Empty messages
            $('.task-column').each(function() {
                const col = $(this);
                col.find('.filter-empty-msg').remove();
                const hasFilters = employee || project || priority || taskType || category || quick;
                if (hasFilters && col.find('.task-card:not(.filter-hidden)').length === 0) {
                    col.find('.add-task-inline').before('<div class="filter-empty-msg text-center text-muted py-3 small"><i class="fa fa-inbox fa-lg d-block mb-1 opacity-50"></i>No tasks match filters</div>');
                }
            });

            renderBadges();
            updateColumnCounts();
        }

        function renderBadges() {
            const badges = [];
            if (activeFilters.employee) badges.push(badgeHtml('primary','fa-user', $('#filterEmployee option:selected').text(), 'employee'));
            if (activeFilters.project)  badges.push(badgeHtml('dark','fa-folder', $('#filterProject option:selected').text(), 'project'));
            if (activeFilters.priority) badges.push(badgeHtml('warning text-dark','fa-flag', activeFilters.priority, 'priority'));
            if (activeFilters.category) badges.push(badgeHtml('secondary','fa-tag', activeFilters.category.replace('_',' '), 'category'));
            if (activeFilters.quick)    badges.push(badgeHtml('info text-dark','fa-filter', activeFilters.quick, 'quick'));
            $('#activeFilterBadges').html(badges.length ? '<small class="text-muted me-1">Active:</small>' + badges.join('') : '');
        }

        function badgeHtml(color, icon, text, key) {
            return `<span class="badge bg-${color} me-1" style="font-size:.8rem;padding:5px 10px;border-radius:20px"><i class="fa ${icon} me-1"></i>${text}<a href="#" class="text-inherit ms-2 clear-badge" data-key="${key}">×</a></span>`;
        }

        $('#filterEmployee').on('change', function() { activeFilters.employee = $(this).val(); applyFilters(); });
        $('#filterProject').on('change',  function() { activeFilters.project  = $(this).val(); applyFilters(); });
        $('#filterPriority').on('change', function() { activeFilters.priority = $(this).val(); applyFilters(); });

        // Quick filter pills (top bar)
        $('#quickFilterPills .btn').on('click', function() {
            $('#quickFilterPills .btn').removeClass('active');
            $(this).addClass('active');
            activeFilters.quick = $(this).data('filter');
            // sync sidebar
            $('.quick-filter').removeClass('active');
            $('.quick-filter[data-filter="' + activeFilters.quick + '"]').addClass('active');
            applyFilters();
        });

        // Sidebar quick filters
        $('.quick-filter').on('click', function(e) {
            e.preventDefault();
            $('.quick-filter').removeClass('active');
            $(this).addClass('active');
            activeFilters.quick = $(this).data('filter');
            $('#quickFilterPills .btn').removeClass('active');
            $('#quickFilterPills .btn[data-filter="' + activeFilters.quick + '"]').addClass('active');
            applyFilters();
        });

        // Category filter from sidebar
        $('.category-filter').on('click', function(e) {
            e.preventDefault();
            const cat = $(this).data('category');
            activeFilters.category = (activeFilters.category === cat) ? '' : cat;
            $('.category-filter').removeClass('active');
            if (activeFilters.category) $(this).addClass('active');
            applyFilters();
        });

        // Task type filter from sidebar
        $('.tasktype-filter').on('click', function(e) {
            e.preventDefault();
            const type = $(this).data('type');
            activeFilters.taskType = (activeFilters.taskType === type) ? '' : type;
            $('.tasktype-filter').removeClass('active');
            if (activeFilters.taskType) $(this).addClass('active');
            applyFilters();
        });

        $('#clearFiltersBtn').on('click', function() {
            Object.keys(activeFilters).forEach(k => activeFilters[k] = '');
            $('#filterEmployee,#filterProject,#filterPriority').val('');
            $('#quickFilterPills .btn').removeClass('active');
            $('#quickFilterPills .btn[data-filter=""]').addClass('active');
            $('.sidebar-item').removeClass('active');
            $('.quick-filter[data-filter=""]').addClass('active');
            applyFilters();
        });

        $(document).on('click', '.clear-badge', function(e) {
            e.preventDefault();
            const key = $(this).data('key');
            activeFilters[key] = '';
            if (key === 'employee') $('#filterEmployee').val('');
            if (key === 'project')  $('#filterProject').val('');
            if (key === 'priority') $('#filterPriority').val('');
            applyFilters();
        });

        // ── Task Search ──────────────────────────────────────────────
        $('#taskSearch').on('input', function() {
            const q = $(this).val().toLowerCase();
            $('.task-card').each(function() {
                const title = $(this).find('.task-title').text().toLowerCase();
                $(this).toggleClass('filter-hidden', q.length > 0 && !title.includes(q));
            });
            updateColumnCounts();
        });

    }); // end ready


    // ══════════════════════════════════════════════════════════════
    // REMARKS (unchanged logic)
    // ══════════════════════════════════════════════════════════════
    const remarkModal = new bootstrap.Modal(document.getElementById('remarkModal'));

    $(document).on('click', '.remark-btn', function() {
        const taskId = $(this).data('id'), taskTitle = $(this).data('title');
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
        $.get(`{{ url('/admin/admin/tasks') }}/${taskId}/remarks`, function(res) {
            if (!res.success || !res.remarks.length) {
                $('#remarksList').html('<div class="text-center py-4 text-muted"><i class="fa fa-comments fa-2x mb-2 d-block opacity-50"></i>No remarks yet.</div>');
                return;
            }
            $('#remarksList').html(res.remarks.map(buildRemarkHtml).join(''));
            $('#remarksList').scrollTop($('#remarksList')[0].scrollHeight);
        }).fail(() => $('#remarksList').html('<p class="text-danger text-center">Failed to load.</p>'));
    }

    function buildRemarkHtml(r) {
        let att = '';
        if (r.attachment_type === 'image')  att = `<div class="mt-2"><a href="${r.attachment_path}" target="_blank"><img src="${r.attachment_path}" class="img-thumbnail" style="max-height:150px"></a></div>`;
        else if (r.attachment_type === 'pdf')   att = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-danger mt-2"><i class="fa fa-file-pdf-o"></i> ${r.attachment_name}</a>`;
        else if (r.attachment_type === 'excel') att = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-success mt-2"><i class="fa fa-file-excel-o"></i> ${r.attachment_name}</a>`;
        else if (r.attachment_type === 'word')  att = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-primary mt-2"><i class="fa fa-file-word-o"></i> ${r.attachment_name}</a>`;
        else if (r.attachment_type === 'link')  att = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-info mt-2"><i class="fa fa-external-link"></i> Open Link</a>`;
        else if (r.attachment_type === 'text')  att = `<a href="${r.attachment_path}" target="_blank" class="btn btn-sm btn-secondary mt-2"><i class="fa fa-file-text-o"></i> ${r.attachment_name}</a>`;

        return `<div class="remark-item d-flex gap-2 mb-3" data-remark-id="${r.id}">
            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">${r.user_initials}</div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div><span class="fw-semibold small">${r.user_name}</span><span class="text-muted small ms-2">${r.created_at}</span></div>
                    <button class="btn btn-sm btn-link text-danger p-0 delete-remark-btn" data-id="${r.id}"><i class="fa fa-trash"></i></button>
                </div>
                ${r.remark ? `<p class="mb-1 small">${r.remark}</p>` : ''}${att}
            </div>
        </div>`;
    }

    $(document).on('click', '.attach-type-btn', function() {
        $('.attach-type-btn').removeClass('active'); $(this).addClass('active');
        const type = $(this).data('type');
        $('#fileUploadSection').toggleClass('d-none', type !== 'file');
        $('#linkSection').toggleClass('d-none', type !== 'link');
        if (type !== 'file') { $('#remarkAttachment').val(''); $('#filePreview').addClass('d-none'); }
        if (type !== 'link') { $('#remarkLink').val(''); }
    });

    $('#remarkAttachment').on('change', function() {
        const file = this.files[0];
        if (!file) { $('#filePreview').addClass('d-none'); return; }
        const ext = file.name.split('.').pop().toLowerCase();
        const icons = { pdf:'fa-file-pdf-o text-danger', xlsx:'fa-file-excel-o text-success', xls:'fa-file-excel-o text-success', csv:'fa-file-excel-o text-success', doc:'fa-file-word-o text-primary', docx:'fa-file-word-o text-primary', jpg:'fa-file-image-o text-warning', jpeg:'fa-file-image-o text-warning', png:'fa-file-image-o text-warning', gif:'fa-file-image-o text-warning' };
        $('#filePreviewIcon').attr('class', `fa ${icons[ext] || 'fa-file text-secondary'} fa-lg`);
        $('#filePreviewName').text(file.name);
        $('#filePreview').removeClass('d-none');
    });
    $('#clearFileBtn').on('click', function() { $('#remarkAttachment').val(''); $('#filePreview').addClass('d-none'); });

    $('#submitRemarkBtn').on('click', function() {
        const taskId = $('#currentTaskId').val(), remarkText = $('#remarkText').val().trim();
        const activeType = $('.attach-type-btn.active').data('type');
        const file = $('#remarkAttachment')[0].files[0], link = $('#remarkLink').val().trim();

        if (!remarkText && activeType === 'none') { alert('Please add a remark or attachment.'); return; }
        if (activeType === 'file' && !file)        { alert('Please select a file.'); return; }
        if (activeType === 'link' && !link)         { alert('Please enter a link.'); return; }

        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        if (remarkText)                    fd.append('remark', remarkText);
        if (activeType === 'file' && file) fd.append('attachment', file);
        if (activeType === 'link' && link) fd.append('link', link);

        const btn = $(this);
        btn.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Adding...');
        $.ajax({
            url: `{{ url('/admin/admin/tasks') }}/${taskId}/remarks`, method:'POST', data:fd, processData:false, contentType:false,
            success: function(res) {
                if (res.success) {
                    const html = buildRemarkHtml(res.remark);
                    if ($('#remarksList .text-center').length) $('#remarksList').html(html);
                    else $('#remarksList').append(html);
                    $('#remarksList').scrollTop($('#remarksList')[0].scrollHeight);
                    $('#remarkText,#remarkLink').val(''); $('#remarkAttachment').val(''); $('#filePreview').addClass('d-none');
                    $('.attach-type-btn').removeClass('active'); $('.attach-type-btn[data-type="none"]').addClass('active');
                    $('#fileUploadSection,#linkSection').addClass('d-none');
                }
            },
            error: function(xhr) { alert(xhr.responseJSON?.message || 'Failed to add remark.'); },
            complete: function() { btn.prop('disabled',false).html('<i class="fa fa-paper-plane"></i> Add Remark'); }
        });
    });

    $(document).on('click', '.delete-remark-btn', function() {
        const id = $(this).data('id'), item = $(this).closest('.remark-item');
        Swal.fire({ title:'Delete remark?', icon:'warning', showCancelButton:true, confirmButtonColor:'#d33', confirmButtonText:'Delete' })
        .then(r => {
            if (!r.isConfirmed) return;
            $.ajax({ url:`{{ url('/admin/admin/tasks/remarks') }}/${id}`, method:'DELETE', data:{_token:'{{ csrf_token() }}'},
                success: function(res) { if(res.success) item.fadeOut(300, function(){$(this).remove();}); }
            });
        });
    });


    // ══════════════════════════════════════════════════════════════
    // TIMER
    // ══════════════════════════════════════════════════════════════
    const timerIntervals = {};

    $('.timer-btn').each(function() {
        const btn = $(this), isRunning = btn.data('running') === true || btn.data('running') === 'true';
        const startedAt = parseInt(btn.data('started-at')) || 0, base = parseInt(btn.data('total')) || 0;
        if (isRunning && startedAt) resumeCountup(btn, btn.data('id'), base, startedAt);
    });

    $(document).on('click', '.timer-btn', function() {
        const btn = $(this), taskId = btn.data('id');
        const isRunning = btn.data('running') === true || btn.data('running') === 'true';
        btn.prop('disabled', true);

        if (!isRunning) {
            $.ajax({
                url: `/admin/admin/tasks/${taskId}/timer/start`, method:'POST', data:{_token:'{{ csrf_token() }}'},
                success: function(res) {
                    if (res.success) {
                        const base = parseInt(btn.data('total')) || 0;
                        btn.data('running','true').data('started-at', res.started_at);
                        btn.removeClass('btn-success').addClass('btn-danger');
                        btn.find('i').removeClass('fa-play-circle').addClass('fa-stop-circle');
                        resumeCountup(btn, taskId, base, res.started_at);
                    }
                },
                complete: function() { btn.prop('disabled',false); }
            });
        } else {
            $.ajax({
                url: `/admin/admin/tasks/${taskId}/timer/stop`, method:'POST', data:{_token:'{{ csrf_token() }}'},
                success: function(res) {
                    if (res.success) {
                        clearInterval(timerIntervals[taskId]); delete timerIntervals[taskId];
                        btn.data('running','false').data('total', res.total_seconds);
                        btn.removeClass('btn-danger').addClass('btn-success');
                        btn.find('i').removeClass('fa-stop-circle').addClass('fa-play-circle');
                        btn.find('.timer-display').text(formatSeconds(res.total_seconds));
                    }
                },
                complete: function() { btn.prop('disabled',false); }
            });
        }
    });

    function resumeCountup(btn, taskId, base, startedAt) {
        clearInterval(timerIntervals[taskId]);
        timerIntervals[taskId] = setInterval(() => {
            const elapsed = Math.floor(Date.now() / 1000) - startedAt;
            btn.find('.timer-display').text(formatSeconds(base + elapsed));
        }, 1000);
    }

    function formatSeconds(total) {
        total = Math.max(0, Math.floor(total));
        return [Math.floor(total/3600), Math.floor((total%3600)/60), total%60].map(n => String(n).padStart(2,'0')).join(':');
    }
    </script>
@endpush