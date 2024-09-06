<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

<div class="d-flex align-items-center justify-content-between">
  <a href="admindashboard.php" class="logo d-flex align-items-center">
    <img src="assets/img/logo.png" alt="">
    <span class="d-none d-lg-block">ExpenseAnalyser</span>
  </a>
  <i class="bi bi-list toggle-sidebar-btn"></i>
</div><!-- End Logo -->

<div class="search-bar">
  <form class="search-form d-flex align-items-center" method="POST" action="#">
    <input type="text" name="query" placeholder="Search" title="Enter search keyword">
    <button type="submit" title="Search"><i class="bi bi-search"></i></button>
  </form>
</div><!-- End Search Bar -->

<nav class="header-nav ms-auto">
  <ul class="d-flex align-items-center">

    <li class="nav-item d-block d-lg-none">
      <a class="nav-link nav-icon search-bar-toggle " href="#">
        <i class="bi bi-search"></i>
      </a>
    </li><!-- End Search Icon-->

    
    <li class="nav-item dropdown pe-3">

    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
      <span class="d-none d-md-block dropdown-toggle ps-2">Welcome Back <?php echo $username; ?></span>
    </a>

      <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
        <li class="dropdown-header">
          <h6>Welcome <?php echo $username; ?></h6>
          
        </li>
        <li>
          <hr class="dropdown-divider">
        </li>

        <li>
          <a class="dropdown-item d-flex align-items-center" href="admin-profile.php">
            <i class="bi bi-person"></i>
            <span>My Profile</span>
          </a>
        </li>
        <li>
          <hr class="dropdown-divider">
        </li>

        <li>
          <a class="dropdown-item d-flex align-items-center" href="admin-profile.php">
            <i class="bi bi-gear"></i>
            <span>Account Settings</span>
          </a>
        </li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li>
          <hr class="dropdown-divider">
        </li>

      </ul><!-- End Profile Dropdown Items -->
    </li><!-- End Profile Nav -->

  </ul>
</nav><!-- End Icons Navigation -->

</header><!-- End Header -->

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

<ul class="sidebar-nav" id="sidebar-nav">

  <li class="nav-item">
    <a class="nav-link " href="admindashboard.php">
      <i class="bi bi-grid"></i>
      <span>Dashboard</span>
    </a>
  </li><!-- End Dashboard Nav -->

  <li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-people-fill"></i><span>User Managment</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
      <li>
        <a href="manage_users.php">
          <i class="bi bi-circle"></i><span>View users</span>
        </a>
      </li>
      
    </ul>
  </li><!-- End Components Nav -->
<!-- 
  <li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-alarm"></i><span>User sessions</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
      <li>
        <a href="budget.php">
          <i class="bi bi-circle"></i><span>View/Add new</span>
        </a>
      </li>
      <li>
        <a href="budgetreport.php">
          <i class="bi bi-circle"></i><span>View report</span>
        </a>
      </li>
    </ul>
  </li> End Forms Nav -->

  <li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-card-list"></i><span>Notifications templates</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="tables-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
      <li>
        <a href="viewtemplate.php">
          <i class="bi bi-circle"></i><span>View templates</span>
        </a>
      </li>
      <li>
        <a href="notificationtemplate.php">
          <i class="bi bi-circle"></i><span>Add new templates</span>
        </a>
      </li>
    </ul>
  </li><!-- End Tables Nav -->
  <li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-bell-fill"></i><span>Push Notification</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
      <li>
        <a href="push_notification_bill_reminder.php">
          <i class="bi bi-circle"></i><span>Bill Reminders</span>
        </a>
      </li>
      <li>
        <a href="push_notification_goalday.php">
          <i class="bi bi-circle"></i><span>Goal reminder</span>
        </a>
      </li>
      <li>
        <a href="push_notification_before_goal.php">
          <i class="bi bi-circle"></i><span>Goal success</span>
        </a>
      </li>
      <li>
        <a href="push_notification_limit_exceeded.php">
          <i class="bi bi-circle"></i><span>Budget limit</span>
        </a>
      </li>
    </ul>
  </li> 
  <li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-person-lines-fill"></i><span>Admin management</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
      <li>
        <a href="view_admin.php">
          <i class="bi bi-circle"></i><span>View admins</span>
        </a>
      </li>
      <li>
        <a href="view_adminbuffer.php">
          <i class="bi bi-circle"></i><span>View(buffer)</span>
        </a>
      </li>
      <li>
        <a href="addadmin.php">
          <i class="bi bi-circle"></i><span>Add new Admin</span>
        </a>
      </li>
    </ul>
  </li><!-- End Charts Nav -->
  <li class="nav-item">
    <a class="nav-link collapsed" href="admin-profile.php">
      <i class="bi bi-person"></i>
      <span>Profile</span>
    </a>
  </li><!-- End Profile Page Nav -->

  <li class="nav-item">
    <a class="nav-link collapsed" href="logout.php">
      <i class="bi bi-box-arrow-in-right"></i>
      <span>Logout</span>
    </a>
  </li><!-- End Login Page Nav -->

</ul>

</aside><!-- End Sidebar-->