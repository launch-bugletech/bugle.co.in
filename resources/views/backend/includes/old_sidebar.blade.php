<!-- sidebar -->
<div class="sidebar px-4  me-0">
   <div class="d-flex flex-column h-100">
      <!-- <a href="{{url('admin/home')}}" class="mb-0 brand-icon">
         <span class="logo-icon">
         <img src="{{asset('public/logo.png')}}" height="60" style="width:auto">
         </span>
          
         </a> -->
      <ul class="menu-list  mt-3">
         <!-- Dashboard -->
         <li><a class="m-link <?php if ($pagename == 'Dashboard') {
   echo 'active';
} ?>" href="{{url('admin/home')}}"><i class="icofont-home fs-5" data-bs-toggle="tooltip" data-bs-placement="right"
                  title="Dashboard"></i>
               <span>Dashboard </span></a></li>

         <!-- Users -->
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

               $expanduser = $showuserPage || $showapprovalPage || $showleavePage;
            ?>

                     <li class="collapsed">
                        <a class="m-link
                    <?php
               if (
                  $showuserPage ||
                  $showapprovalPage ||
                  $showleavePage
               ) {
                  echo 'active';
               }
                    ?>" data-bs-toggle="collapse" data-bs-target="#menu-user" href="#">
                           <i class="icofont-ui-user fs-5" data-bs-toggle="tooltip" data-bs-placement="right" title="Users"></i>
                           <span>Users </span>
                           <span class="arrow icofont-rounded-down ms-auto text-end fs-5"></span>
                        </a>

                        <ul class="sub-menu collapse <?php   if ($expanduser) {
                  echo 'show';
               } ?>" id="menu-user">

                           <!-- Employee Management -->
                           <li>
                              <a class="ms-link
                            <?php
               if ($showuserPage) {
                  echo 'active';
               }
                            ?>" href="{{ url('admin/employees') }}">
                                 <span>Employee Management</span>
                              </a>
                           </li>

                           <!-- Leave Management -->
                           <li>
                              <a class="ms-link
                            <?php
               if ($showleavePage) {
                  echo 'active';
               }
                            ?>" href="{{ url('admin/leaves') }}">
                                 <span>Leave Management</span>
                              </a>
                           </li>

                           <!-- Existing commented code untouched -->
                           <!--
                    <li>
                        <a class="ms-link <?php   if ($pagename == 'Customer Approvals' || $pagename == 'Customer Approval View') {
                  echo 'active';
               } ?>"
                           href="{{url('admin/approvals')}}">
                            <span>Approvals</span>
                        </a>
                    </li>
                    -->
                        </ul>
                     </li>


                     <!-- Products -->
                     @if(Auth::user()->role_id == 1)

                           <?php 
                                       $showproductPage = false;
                        if ($pagename == 'Product Index' || $pagename == 'Create Product' || $pagename == 'Product Edit' || $pagename == 'Product View') {
                           $showproductPage = true;
                        }

                        $showcategoryPage = false;
                        if ($pagename == 'Category Index' || $pagename == 'Create Category' || $pagename == 'Category Edit') {
                           $showcategoryPage = true;
                        }

                        $showdoorstylePage = false;
                        if ($pagename == 'Door Style Index' || $pagename == 'Create Door Style' || $pagename == 'Door Style Edit') {
                           $showdoorstylePage = true;
                        }

                        $showmodificationPage = false;
                        if ($pagename == 'Addmodification Index' || $pagename == 'Create Addmodification' || $pagename == 'Addmodification Edit') {
                           $showmodificationPage = true;
                        }

                        $showaccessoriesPage = false;
                        if ($pagename == 'Accessories Index' || $pagename == 'Create Accessories' || $pagename == 'Accessories Edit') {
                           $showaccessoriesPage = true;
                        }

                        $expandproduct = $showproductPage || $showcategoryPage || $showdoorstylePage || $showmodificationPage || $showaccessoriesPage;
                                       ?>

                           <li class="collapsed">
                              <a class="m-link <?php      if ($pagename == 'Product Index' || $pagename == 'Create Item' || $pagename == 'Item Edit' || $pagename == 'Item View' || $pagename == 'Category Index' || $pagename == 'Create Category' || $pagename == 'Category Edit' || $pagename == 'Door Style Index' || $pagename == 'Create Door Style' || $pagename == 'Door Style Edit' || $pagename == 'Addmodification Index' || $pagename == 'Create Addmodification' || $pagename == 'Addmodification Edit' || $pagename == 'Accessories Index' || $pagename == 'Create Accessories' || $pagename == 'Accessories Edit') {
                           echo 'active';
                        } ?>" data-bs-toggle="collapse" data-bs-target="#menu-product" href="#">
                                 <i class="icofont-cube fs-5" data-bs-toggle="tooltip" data-bs-placement="right" title="Products"></i>
                                 <span>Products</span> <span class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                              <!-- Menu: Sub menu ul -->
                              <ul class="sub-menu collapse <?php      if ($expandproduct) {
                           echo "show";
                        }?>" id="menu-product">
                                 <li><a class="ms-link <?php      if ($pagename == 'Product Index' || $pagename == 'Create Product' || $pagename == 'Product Edit' || $pagename == 'Product View') {
                           echo 'active';
                        } ?>" href="{{url('admin/product-list')}}"
                                       style="background: none!important; border: none;"><span>Product</span></a></li>
                                 <li><a class="ms-link <?php      if ($pagename == 'Category Index' || $pagename == 'Create Category' || $pagename == 'Category Edit') {
                           echo 'active';
                        } ?>" href="{{url('admin/categories-list')}}"><span>Category</span> </a></li>
                                 <!-- <li><a class="ms-link <?php      if ($pagename == 'Door Style Index' || $pagename == 'Create Door Style' || $pagename == 'Door Style Edit') {
                           echo 'active';
                        } ?>" href="{{url('admin/door-style-list')}}"><span>Door Style</span></a></li>
                                          <li><a class="ms-link <?php      if ($pagename == 'Addmodification Index' || $pagename == 'Create Addmodification' || $pagename == 'Addmodification Edit') {
                           echo 'active';
                        } ?>" href="{{url('admin/addmodification/index')}}" ><span>Modification</span></a></li>
                                          <li><a class="ms-link <?php      if ($pagename == 'Accessories Index' || $pagename == 'Create Accessories' || $pagename == 'Accessories Edit') {
                           echo 'active';
                        } ?>" href="{{url('admin/accessories/index')}}" ><span>Accessories</span></a></li> -->
                              </ul>
                           </li>
                     @endif
     @if(Auth::user()->role_id == 1)
                     <?php 
                           $showmaterialPage = false;
               if ($pagename == 'Material Request' || $pagename == 'Create Material Request' || $pagename == 'Material Request Edit' || $pagename == 'Material Request View') {
                  $showmaterialPage = true;
               }
               $showtestingPage = false;
               if ($pagename == 'Testing Request Index' || $pagename == 'Create Testing Request' || $pagename == 'Testing Request Edit' || $pagename == 'Testing Request View') {
                  $showtestingPage = true;
               }
               $showdefectPage = false;
               if ($pagename == 'Defect' || $pagename == 'Defect View') {
                  $showdefectPage = true;
               }
               $expandrequest = $showmaterialPage || $showtestingPage || $showdefectPage;
                         ?>

                     <li class="collapsed">
                        <a class="m-link <?php   if ($pagename == 'Material Request' || $pagename == 'Create Material Request' || $pagename == 'Material Request Edit' || $pagename == 'Material Request View' || $pagename == 'Testing Request Index' || $pagename == 'Create Testing Request' || $pagename == 'Testing Request Edit' || $pagename == 'Testing Request View' || $pagename == 'Defect' || $pagename == 'Defect View') {
                  echo 'active';
               } ?>" data-bs-toggle="collapse" data-bs-target="#menu-request" href="#">
                           <i class="icofont-ui-user fs-5" data-bs-toggle="tooltip" data-bs-placement="right" title="Users"></i>
                           <span>Requests </span> <span class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse <?php   if ($expandrequest) {
                  echo "show";
               }?>" id="menu-request">
                           <li><a class="ms-link  <?php   if ($pagename == 'Material Request' || $pagename == 'Create Material Request' || $pagename == 'Material Request Edit' || $pagename == 'Material Request View') {
                  echo 'active';
               } ?>" href="{{url('admin/material-request/index')}}"><span>Material Request</span></a></li>
                           <li><a class="ms-link  <?php   if ($pagename == 'Testing Request Index' || $pagename == 'Create Testing Request' || $pagename == 'Testing Request Edit' || $pagename == 'Testing Request View') {
                  echo 'active';
               } ?>" href="{{url('admin/testing-request/index')}}"><span>Testing Request</span></a></li>
                           <li><a class="ms-link  <?php   if ($pagename == 'Defect' || $pagename == 'Defect View') {
                  echo 'active';
               } ?>" href="{{url('admin/defect/index')}}"><span>Defect Request</span></a></li>
                        </ul>
                     </li>
         @endif
         @endif

         @if(Auth::user()->role_id == 1)


                  <li>
                     <a class="m-link <?php   if ($pagename == 'Stakeholders' || $pagename == "Stakeholders Edit") {
               echo 'active ';
            } ?>" href="{{url('admin/grouping-stakeholders')}}"><i class="icofont-ui-user fs-5"
                           data-bs-toggle="tooltip" data-bs-placement="right" title="Specbook"></i><span>Stakeholders</span></a>
                  </li>
                  <li>
                     <a class="m-link <?php   if ($pagename == 'Roles' || $pagename == "Roles Create" || $pagename == "Roles View" || $pagename == "Roles Edit") {
               echo 'active';
            } ?>" href="{{url('admin/roles')}}"><i class="icofont-ui-user fs-5" data-bs-toggle="tooltip"
                           data-bs-placement="right" title="Specbook"></i><span>Group</span></a>
                  </li>
                  <?php 
                              $showcustomergroupPage = false;
            if ($pagename == 'Customer Group' || $pagename == 'Customer Group Create' || $pagename == 'Customer Group Edit') {
               $showcustomergroupPage = true;
            }
            $showgroupingPage = false;
            if ($pagename == 'Customer Grouping' || $pagename == 'Customer Grouping Edit') {
               $showgroupingPage = true;
            }
            $expandCust_grp = $showcustomergroupPage || $showgroupingPage;
                              ?>
                  <li class="collapsed">
                     <a class="m-link <?php   if ($pagename == 'Customer Group' || $pagename == 'Customer Group Create' || $pagename == 'Customer Group Edit' || $pagename == 'Customer Grouping' || $pagename == 'Customer Grouping Edit') {
               echo 'active';
            } ?>" data-bs-toggle="collapse" data-bs-target="#menu-customer-group" href="#"
                        style="padding: 8px 0px 10px 1px!important;">
                        <i class="icofont-users-alt-5 fs-5" data-bs-toggle="tooltip" data-bs-placement="right"
                           title="Customer Group"></i>
                        <span>Customer Group</span>
                        <span class="arrow icofont-rounded-down ms-auto text-end fs-5" <?php   if ($pagename == 'Customer Group' || $pagename == 'Customer Group Create' || $pagename == 'Customer Group Edit' || $pagename == 'Customer Grouping' || $pagename == 'Customer Grouping Edit') {
               echo 'style="margin-right: 20px;"';
            } ?>></span>
                     </a>
                     <!-- Menu: Sub menu ul -->
                     <ul class="sub-menu collapse <?php   if ($expandCust_grp) {
               echo "show";
            }?>" id="menu-customer-group">
                        <li><a class="ms-link <?php   if ($pagename == 'Customer Group' || $pagename == 'Customer Group Create' || $pagename == 'Customer Group Edit') {
               echo 'active';
            } ?>" href="{{url('admin/customer-group/index')}}"><span>Customer Group</span></a></li>
                        <li><a class="ms-link <?php   if ($pagename == 'Customer Grouping' || $pagename == 'Customer Grouping Edit') {
               echo 'active';
            } ?>" href="{{url('admin/customer-grouping/index')}}"><span>Customer Grouping</span></a></li>
                     </ul>
                  </li>
                  <!-- Management -->

                  <?php 
                                 $showmanagementPage = false;
            if ($pagename == 'Substations Index' || $pagename == 'Create Substations' || $pagename == 'Substations Edit' || $pagename == 'Substations View' || $pagename == 'Create Engineer') {
               $showmanagementPage = true;
            }
            $expensesPage = false;
            if ($pagename == 'Expenses Add' || $pagename == 'Expenses Edit' || $pagename == 'Expenses Index') {
               $expensesPage = true;
            }
            $equipmentPage = false;
            if ($pagename == 'Equipment Index' || $pagename == 'Create Equipment' || $pagename == 'Equipment Edit') {
               $equipmentPage = true;
            }
            $testingPage = false;
            if ($pagename == 'Testing Index' || $pagename == 'Create Testing' || $pagename == 'Testing Edit') {
               $testingPage = true;
            }
            $exlistPage = false;
            if ($pagename == 'Approve Expenses Report' || $pagename == 'Expenses Record Status') {
               $exlistPage = true;
            }

            $expandmangement = $showmanagementPage || $expensesPage || $equipmentPage || $testingPage || $exlistPage;
                               ?>

                  <li class="collapsed">
                     <a class="m-link <?php   if ($pagename == 'Substations Index' || $pagename == 'Create Substations' || $pagename == 'Substations Edit' || $pagename == 'Substations View' || $pagename == 'Create Engineer' || $pagename == 'Expenses Index' || $pagename == 'Expenses Add' || $pagename == 'Expenses Edit' || $pagename == 'Approve Expenses Report' || $pagename == 'Expenses Record Status' || $pagename == 'Equipment Index' || $pagename == 'Create Equipment' || $pagename == 'Equipment Edit' || $pagename == 'Testing Index' || $pagename == 'Create Testing' || $pagename == 'Testing Edit') {
               echo 'active';
            } ?>" data-bs-toggle="collapse" data-bs-target="#menu-sites" href="#">
                        <i class="icofont-ui-user fs-5" data-bs-toggle="tooltip" data-bs-placement="right" title="Users"></i>
                        <span>Management </span> <span class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                     <!-- Menu: Sub menu ul -->

                     <ul class="sub-menu collapse <?php   if ($expandmangement) {
               echo "show";
            }?>" id="menu-sites">
                        <li><a class="ms-link  <?php   if ($pagename == 'Substations Index' || $pagename == 'Create Substations' || $pagename == 'Substations Edit' || $pagename == 'Substations View' || $pagename == 'Create Engineer') {
               echo 'active';
            } ?>" href="{{url('admin/substations/index')}}"><span>Site Management</span></a></li>

                        <li><a class="ms-link  <?php   if ($pagename == 'Expenses Index' || $pagename == 'Expenses Add' || $pagename == 'Expenses Edit') {
               echo 'active';
            } ?>" href="{{url('admin/expenses-list')}}"><span>Add Expenses</span></a></li>

                        <li><a class="ms-link  <?php   if ($pagename == 'Equipment Index' || $pagename == 'Equipment Edit' || $pagename == 'Create Equipment') {
               echo 'active';
            } ?>" href="{{url('admin/equipment-list')}}"><span>Add Equipment</span></a>
                        </li>

                        <li><a class="ms-link  <?php   if ($pagename == 'Testing Index' || $pagename == 'Create Testing' || $pagename == 'Testing Edit') {
               echo 'active';
            } ?>" href="{{url('admin/testing-list')}}"><span>Add Testing</span></a>
                        </li>

                        <li><a class="ms-link  <?php   if ($pagename == 'Approve Expenses Report' || $pagename == 'Expenses Record Status') {
               echo 'active';
            } ?>" href="{{url('admin/expenses-record')}}"><span>Expenses List</span></a>
                        </li>

                     </ul>
                  </li>


         @endif
         <!-- ATTENDANCE MODULE ADMIN AND ENGINEER , SUPERVISOR -->
         <?php 
$showattendancePage = false;

// Normalize $pagename for comparison
$currentPage = strtolower($pagename ?? '');

// If page is attendance
if ($currentPage == 'attendance' || $currentPage == 'attendance list') {
   $showattendancePage = true;
}

// Check if user is admin (roles 1 or 3)
$isAdmin = auth()->check() && in_array(auth()->user()->role_id, [1, 2, 3]);
?>

         <li>
            <a class="m-link <?php if ($showattendancePage) {
   echo 'active';
} ?>"
               href="{{ url('admin/attendance/list') }}">

               <i class="icofont-clock-time fs-5" data-bs-toggle="tooltip" data-bs-placement="right"
                  title="Attendance"></i>

               <span>Attendance</span>
            </a>
         </li>



         <!-- New Module -->

      </ul>
      <button type="button" class="btn btn-link sidebar-mini-btn text-light sd_cls">
         <span class="ms-2"><i class="icofont-bubble-right"></i></span>
      </button>
   </div>
</div>