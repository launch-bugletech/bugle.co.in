<!-- sidebar -->
<div class="sidebar px-4 me-0">
   <div class="d-flex flex-column h-100">
      <!-- Logo (commented out) -->
      <!-- <a href="{{url('admin/home')}}" class="mb-0 brand-icon">
         <span class="logo-icon">
         <img src="{{asset('public/logo.png')}}" height="60" style="width:auto">
         </span>
      </a> -->
      
      <ul class="menu-list mt-3">
         <!-- 1. Dashboard -->
         <li>
            <a class="m-link <?php if ($pagename == 'Dashboard') { echo 'active'; } ?>" 
               href="{{url('admin/home')}}">
               <i class="icofont-home fs-5" data-bs-toggle="tooltip" 
                  data-bs-placement="right" title="Dashboard"></i>
               <span>Dashboard</span>
            </a>
         </li>

         <!-- 2. Users -->
         @if(in_array(Auth::user()->role_id, [1, 3]))
            <?php
            /* Employee Pages */
            $showuserPage = false;
            if (
               $pagename == 'Team Member Index' ||
               $pagename == 'Create Team Member' ||
               $pagename == 'Team Member Edit' ||
               $pagename == 'Team Member View'
            ) {
               $showuserPage = true;
            }

            /* Customer Approvals Pages */
            $showapprovalPage = false;
            if ($pagename == 'Customer Approvals' || $pagename == 'Customer Approval View') {
               $showapprovalPage = true;
            }

            $expanduser = $showuserPage || $showapprovalPage;
            ?>

            <li class="collapsed">
               <a class="m-link <?php if ($showuserPage || $showapprovalPage) { echo 'active'; } ?>" 
                  data-bs-toggle="collapse" data-bs-target="#menu-user" href="#">
                  <i class="icofont-ui-user fs-5" data-bs-toggle="tooltip" 
                     data-bs-placement="right" title="Users"></i>
                  <span>Users</span>
                  <span class="arrow icofont-rounded-down ms-auto text-end fs-5"></span>
               </a>
               
               <ul class="sub-menu collapse <?php if ($expanduser) { echo 'show'; } ?>" id="menu-user">
                  <!-- Employee Management -->
                  <li>
                     <a class="ms-link <?php if ($showuserPage) { echo 'active'; } ?>" 
                        href="{{ url('admin/employees') }}">
                        <span>Employee Management</span>
                     </a>
                  </li>
               </ul>
            </li>
         @endif

         <!-- 3. Attendance -->
         <?php
         $showattendancePage = false;
         $currentPage = strtolower($pagename ?? '');
         if ($currentPage == 'attendance' || $currentPage == 'attendance list') {
            $showattendancePage = true;
         }
         ?>

         <li>
            <a class="m-link <?php if ($showattendancePage) { echo 'active'; } ?>" 
               href="{{ url('admin/attendance/list') }}">
               <i class="icofont-clock-time fs-5" data-bs-toggle="tooltip" 
                  data-bs-placement="right" title="Attendance"></i>
               <span>Attendance</span>
            </a>
         </li>

         <!-- 4. Leaves -->
         @if(in_array(Auth::user()->role_id, [1, 3]))
            <?php
            /* Leave Pages */
            $showleavePage = false;
            if (
               $pagename == 'Leave Index' ||
               $pagename == 'Create Leave' ||
               $pagename == 'Edit Leave' ||
               $pagename == 'View Leave'
            ) {
               $showleavePage = true;
            }
            ?>

            <li>
               <a class="m-link <?php if ($showleavePage) { echo 'active'; } ?>" 
                  href="{{ url('admin/leaves') }}">
                  <i class="icofont-calendar fs-5" data-bs-toggle="tooltip" 
                     data-bs-placement="right" title="Leaves"></i>
                  <span>Leaves</span>
               </a>
            </li>
         @endif

         <!-- 5. Projects -->
           <!-- 5. Projects -->
      @if(Auth::user()->role_id == 1)

@php
    $showprojectPage = false;

    if (
        $pagename == 'Projects' ||
        $pagename == 'Create Project' ||
        $pagename == 'Project Edit' ||
        $pagename == 'Project View'
    ) {
        $showprojectPage = true;
    }
@endphp

<li>
    <a class="m-link {{ $showprojectPage ? 'active' : '' }}"
       href="{{ route('admin.projects.index') }}">
       
        <i class="icofont-cube fs-5"
           data-bs-toggle="tooltip"
           data-bs-placement="right"
           title="Projects"></i>

        <span>Projects</span>
    </a>
</li>

@endif


         <!-- 6. Tasks -->
           <li>
            <a class="m-link {{ in_array($pagename, ['Tasks', 'Task View', 'Create Task', 'Edit Task']) ? 'active' : '' }}"
               href="{{ route('tasks.board') }}">

               <i class="icofont-tasks fs-5" data-bs-toggle="tooltip" data-bs-placement="right" title="Tasks"></i>

               <span>Tasks</span>
            </a>
         </li>

         <!-- 7. Reports -->
         <li>
            <a class="m-link <?php if ($pagename == 'Reports' || $pagename == 'Report View') { 
                  echo 'active'; } ?>" 
               href="{{ url('admin/reports') }}">
               <i class="icofont-file-document fs-5" data-bs-toggle="tooltip" 
                  data-bs-placement="right" title="Reports"></i>
               <span>Reports</span>
            </a>
         </li>

      </ul>
      
      <button type="button" class="btn btn-link sidebar-mini-btn text-light sd_cls">
         <span class="ms-2"><i class="icofont-bubble-right"></i></span>
      </button>
   </div>
</div>