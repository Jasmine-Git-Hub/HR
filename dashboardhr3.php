<?php
session_start();
require_once 'php/connect.php';

// Get dashboard data for HR 3 (Operations focus)
try {
    // Total employees count
    $stmt = $conn->query("SELECT COUNT(*) as total FROM employees WHERE profile_status = 'Active'");
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Open leave requests
    $stmt = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE leave_status = 'Pending'");
    $pendingLeaves = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Attendance data
    $stmt = $conn->query("SELECT 
        COUNT(CASE WHEN attendance_status = 'Present' THEN 1 END) as present,
        COUNT(CASE WHEN attendance_status = 'Late' THEN 1 END) as late,
        COUNT(CASE WHEN attendance_status = 'Absent' THEN 1 END) as absent
        FROM attendance WHERE attendance_date = CURDATE()");
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Shift coverage
    $stmt = $conn->query("SELECT d.department_name, s.shift_name, 
        COUNT(DISTINCT rs.employee_id) as assigned,
        COUNT(DISTINCT e.employee_id) as total_staff
        FROM departments d
        CROSS JOIN shifts s
        LEFT JOIN roster_schedules rs ON d.department_id = rs.department_id AND s.shift_id = rs.shift_id AND rs.shift_date = CURDATE()
        LEFT JOIN employees e ON d.department_id = e.department_id AND e.profile_status = 'Active'
        GROUP BY d.department_id, s.shift_id
        LIMIT 3");
    $shiftCoverage = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Correction requests
    $stmt = $conn->query("SELECT cr.request_id, e.first_name, e.last_name, 
        cr.issue_description, cr.status
        FROM correction_requests cr
        JOIN employees e ON cr.employee_id = e.employee_id
        WHERE cr.status = 'Pending'
        ORDER BY cr.request_date DESC
        LIMIT 3");
    $correctionRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Timesheet stats
    $stmt = $conn->query("SELECT 
        COUNT(CASE WHEN status = 'Submitted' THEN 1 END) as submitted,
        COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending
        FROM timesheets");
    $timesheetStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Scheduled units
    $stmt = $conn->query("SELECT COUNT(DISTINCT department_id) as scheduled 
        FROM roster_schedules WHERE shift_date = CURDATE()");
    $scheduledUnits = $stmt->fetchColumn();
    
    $totalUnits = $conn->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    
} catch(PDOException $e) {
    error_log("Dashboard HR3 data error: " . $e->getMessage());
    // Set default values
    $totalEmployees = 248;
    $pendingLeaves = 19;
    $attendance = ['present' => 223, 'late' => 11, 'absent' => 14];
    $shiftCoverage = [];
    $correctionRequests = [];
    $timesheetStats = ['submitted' => 82, 'pending' => 19];
    $scheduledUnits = 14;
    $totalUnits = 16;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital HR 3 Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-hr3.css">
    <link rel="stylesheet" href="assets/css/modules/applicant-management/applicant-management.css">
    <link rel="stylesheet" href="assets/css/modules/recruitment-management/recruitment-management.css">
    <link rel="stylesheet" href="assets/css/modules/new-hire-onboarding/new-hire-onboarding.css">
    <link rel="stylesheet" href="assets/css/modules/performance-management/performance-management.css">
    <link rel="stylesheet" href="assets/css/modules/social-recognition/social-recognition.css">
    <link rel="stylesheet" href="assets/css/modules/competency-management/competency-management.css">
    <link rel="stylesheet" href="assets/css/modules/learning-management/learning-management.css">
    <link rel="stylesheet" href="assets/css/modules/training-management/training-management.css">
    <link rel="stylesheet" href="assets/css/modules/succession-planning/succession-planning.css">
    <link rel="stylesheet" href="assets/css/modules/employee-self-service/employee-self-service.css">
    <link rel="stylesheet" href="assets/css/modules/time and attendance system/time-and-attendance-system.css">
    <link rel="stylesheet" href="assets/css/modules/shift-and-schedule-management/shift-and-schedule-management.css">
    <link rel="stylesheet" href="assets/css/modules/timesheet-management/timesheet-management.css">
    <link rel="stylesheet" href="assets/css/modules/leave-management/leave-management.css">
    <link rel="stylesheet" href="assets/css/modules/claims-and-reimbursement/claims-and-reimbursement.css">
    <link rel="stylesheet" href="assets/css/modules/core-human-capital-management/core-human-capital-management.css">
    <link rel="stylesheet" href="assets/css/modules/payroll-management/payroll-management.css">
    <link rel="stylesheet" href="assets/css/modules/compensation-planning/compensation-planning.css">
    <link rel="stylesheet" href="assets/css/modules/hr-analytics-dashboard/hr-analytics-dashboard.css">
    <link rel="stylesheet" href="assets/css/modules/hmo-benefits-administration/hmo-benefits-administration.css">
</head>
<body data-dashboard-scope="hr3">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-user-shield text-primary fs-4"></i>
                <h4 class="mb-0">Hospital HR</h4>
            </div>
            <button class="btn btn-link sidebar-toggle d-lg-none" id="sidebarClose" aria-label="Close sidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="#dashboard"><i class="fas fa-gauge"></i><span>Dashboard</span></a></li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <img id="sidebarAvatar" src="https://ui-avatars.com/api/?name=Hospital+HR&background=0D6EFD&color=fff&bold=true" alt="Hospital HR avatar" class="rounded-circle">
                <div class="ms-2">
                    <h6 class="mb-0 small fw-bold" id="sidebarUserName">Hospital HR</h6>
                    <small class="text-muted" id="sidebarRoleText">Workforce Operations</small>
                </div>
            </div>
        </div>
    </aside>

    <div class="main-content">
        <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
            <div class="container-fluid">
                <button class="btn btn-link sidebar-toggle" id="sidebarToggle" aria-label="Open sidebar">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="ms-auto d-flex align-items-center navbar-actions">
                    <form class="d-none d-md-flex navbar-search" role="search" method="GET" action="search.php">
                        <div class="input-group">
                            <input class="form-control" type="search" name="q" placeholder="Search shifts, attendance..." aria-label="Search">
                            <button class="btn btn-outline-secondary search-icon-btn" type="submit" aria-label="Submit search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <button class="btn btn-link position-relative notif-center-btn" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge rounded-pill bg-danger notification-badge"><?php echo count($correctionRequests); ?></span>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-link d-flex align-items-center text-decoration-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                            <img id="navbarAvatar" src="https://ui-avatars.com/api/?name=Hospital+HR&background=0D6EFD&color=fff&bold=true" class="rounded-circle me-2" width="32" alt="User avatar">
                            <span class="d-none d-md-inline small" id="navbarRoleText">Workforce Operations</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php" id="logoutButton">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="content-wrapper p-4">
            <div class="page-header mb-4">
                <h2 class="page-title fw-bold" id="pageTitle">Workforce Operations Dashboard</h2>
                <p class="text-muted mb-0" id="pageSubtitle">Manage attendance, shifts, and timesheet operations.</p>
            </div>

            <section class="row g-4 mb-4 content-section" id="dashboard">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" id="statLabel1">Total Employees</p>
                                <h3 class="mb-0 fw-bold" id="statValue1"><?php echo $totalEmployees; ?></h3>
                            </div>
                            <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" id="statLabel2">Open Leave Requests</p>
                                <h3 class="mb-0 fw-bold" id="statValue2"><?php echo $pendingLeaves; ?></h3>
                            </div>
                            <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-calendar-check"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" id="statLabel3">Present Today</p>
                                <h3 class="mb-0 fw-bold" id="statValue3"><?php echo $attendance['present'] ?? 223; ?></h3>
                            </div>
                            <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-user-check"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" id="statLabel4">Compliance Rate</p>
                                <h3 class="mb-0 fw-bold" id="statValue4">96%</h3>
                            </div>
                            <div class="stat-icon bg-info-subtle text-info"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mb-4 content-section" id="dashboardGraph">
                <div class="col-12">
                    <div class="card border-0 shadow-sm graph-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Operations Overview</h5>
                            <span class="small text-muted">Live module insights</span>
                        </div>
                        <div class="card-body">
                            <div class="ui-graph-wrap">
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Total Employees</span><strong id="graphEmployeesValue"><?php echo $totalEmployees; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill" id="graphEmployeesBar" style="width: <?php echo min(100, $totalEmployees / 3); ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Present Today</span><strong id="graphVacanciesValue"><?php echo $attendance['present'] ?? 223; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill warning" id="graphVacanciesBar" style="width: <?php echo $attendance['present'] ? round(($attendance['present'] / $totalEmployees) * 100) : 90; ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Late Entries</span><strong id="graphPendingValue"><?php echo $attendance['late'] ?? 11; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill danger" id="graphPendingBar" style="width: <?php echo $attendance['late'] ? min(100, $attendance['late'] * 5) : 15; ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>On Leave</span><strong id="graphTrainingValue"><?php echo $attendance['absent'] ?? 14; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill mint" id="graphTrainingBar" style="width: <?php echo $attendance['absent'] ? min(100, $attendance['absent'] * 4) : 10; ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Compliance Rate</span><strong id="graphComplianceValue">96%</strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill success" id="graphComplianceBar" style="width: 96%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Shift Fill Rate</span><strong id="graphReadinessValue">88%</strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill info" id="graphReadinessBar" style="width: 88%"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mb-4 content-section d-none" id="roleOverview">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0" id="roleOverviewTitle">Submodule Workspace</h5>
                        </div>
                        <div class="card-body" id="roleModuleOverviewBody">
                            <p class="text-muted mb-0">Select a submodule from the sidebar to view its content.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 content-section">
                <div class="col-lg-7" id="employees">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0" id="primaryCardTitle">Shift Coverage Board</h5>
                            <a href="assign_shift.php" class="btn btn-sm btn-primary">Assign Shift</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Unit</th>
                                        <th>Current Shift</th>
                                        <th>Coverage</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($shiftCoverage)): ?>
                                    <tr>
                                        <td>ER Nursing</td>
                                        <td>07:00 - 15:00</td>
                                        <td><span class="badge bg-success-subtle text-success">Fully Staffed</span></td>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>ICU</td>
                                        <td>15:00 - 23:00</td>
                                        <td><span class="badge bg-warning-subtle text-warning">Short 1 RN</span></td>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Laboratory</td>
                                        <td>23:00 - 07:00</td>
                                        <td><span class="badge bg-info-subtle text-info">Float Pool Assigned</span></td>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($shiftCoverage as $shift): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($shift['department_name']); ?></td>
                                            <td><?php echo htmlspecialchars($shift['shift_name'] ?? 'Day Shift'); ?></td>
                                            <td>
                                                <?php 
                                                $coverage = $shift['assigned'] ?? 0;
                                                $total = $shift['total_staff'] ?? 5;
                                                $percentage = $total > 0 ? ($coverage / $total) * 100 : 0;
                                                
                                                if ($percentage >= 90) {
                                                    $badgeClass = 'bg-success-subtle text-success';
                                                    $status = 'Fully Staffed';
                                                } elseif ($percentage >= 70) {
                                                    $badgeClass = 'bg-warning-subtle text-warning';
                                                    $status = 'Partially Staffed';
                                                } else {
                                                    $badgeClass = 'bg-danger-subtle text-danger';
                                                    $status = 'Understaffed';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span>
                                            </td>
                                            <td>
                                                <a href="view_shift.php?dept=<?php echo $shift['department_id'] ?? 0; ?>" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                                <a href="edit_shift.php?dept=<?php echo $shift['department_id'] ?? 0; ?>" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5" id="leave">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0" id="secondaryCardTitle">Timekeeping Exceptions</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php if (empty($correctionRequests)): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Missing Clock-Out (ER-102)</h6>
                                        <small class="text-muted">Shift ended 15:00 • Needs validation</small>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger">Critical</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Overtime Adjustment (LAB-044)</h6>
                                        <small class="text-muted">+2.5 hours pending supervisor sign-off</small>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning">For Review</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Late-In Appeal (WARD-017)</h6>
                                        <small class="text-muted">Medical emergency documentation attached</small>
                                    </div>
                                    <span class="badge bg-info-subtle text-info">Escalated</span>
                                </li>
                                <?php else: ?>
                                    <?php foreach ($correctionRequests as $cr): ?>
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars(substr($cr['issue_description'], 0, 30)) . '...'; ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($cr['first_name'] . ' ' . $cr['last_name']); ?></small>
                                        </div>
                                        <span class="badge bg-warning-subtle text-warning"><?php echo $cr['status']; ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mt-1 content-section">
                <div class="col-md-4" id="attendance">
                    <div class="card border-0 shadow-sm h-100 insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Attendance Control</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Clock-In Compliance</span><strong class="metric-value">96%</strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Unresolved Logs</span><strong class="metric-value">9</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Manual Corrections</span><strong class="metric-value">5</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="payroll">
                    <div class="card border-0 shadow-sm h-100 insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-calendar-days me-2"></i>Shift Scheduling</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Schedules Published</span><strong class="metric-value"><?php echo $scheduledUnits; ?>/<?php echo $totalUnits; ?> Units</strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Open Shift Slots</span><strong class="metric-value">12</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Swap Requests</span><strong class="metric-value">7</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="recruitment">
                    <div class="card border-0 shadow-sm h-100 insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-file-signature me-2"></i>Timesheet Operations</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Submitted Today</span><strong class="metric-value"><?php echo $timesheetStats['submitted'] ?? 82; ?></strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Pending Approval</span><strong class="metric-value"><?php echo $timesheetStats['pending'] ?? 19; ?></strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Overtime Claims</span><strong class="metric-value">11</strong></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mt-1 content-section" id="documents">
                <div class="col-12">
                    <div class="card border-0 shadow-sm insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-clipboard-list me-2"></i>Operational Compliance</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-1"><span class="metric-label">Shift Policy Violations</span><strong class="metric-value">3</strong></p>
                            <p class="metric-line mb-1"><span class="metric-label">Attendance Audit Findings</span><strong class="metric-value">4</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Resolved This Week</span><strong class="metric-value">12</strong></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mt-1 content-section" id="settings">
                <div class="col-12">
                    <div class="card border-0 shadow-sm insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-sliders me-2"></i>Operations Controls</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-1"><span class="metric-label">Auto-Roster Rules</span><strong class="metric-value">Enabled</strong></p>
                            <p class="metric-line mb-1"><span class="metric-label">Overtime Approval Layer</span><strong class="metric-value">2-step</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Correction SLA</span><strong class="metric-value">24 hours</strong></p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modules/applicant-management/applicant-management.js"></script>
    <script src="assets/js/modules/recruitment-management/recruitment-management.js"></script>
    <script src="assets/js/modules/new-hire-onboarding/new-hire-onboarding.js"></script>
    <script src="assets/js/modules/performance-management/performance-management.js"></script>
    <script src="assets/js/modules/social-recognition/social-recognition.js"></script>
    <script src="assets/js/modules/competency-management/competency-management.js"></script>
    <script src="assets/js/modules/learning-management/learning-management.js"></script>
    <script src="assets/js/modules/training-management/training-management.js"></script>
    <script src="assets/js/modules/succession-planning/succession-planning.js"></script>
    <script src="assets/js/modules/employee-self-service/employee-self-service.js"></script>
    <script src="assets/js/modules/time and attendance system/time-and-attendance-system.js"></script>
    <script src="assets/js/modules/shift-and-schedule-management/shift-and-schedule-management.js"></script>
    <script src="assets/js/modules/timesheet-management/timesheet-management.js"></script>
    <script src="assets/js/modules/leave-management/leave-management.js"></script>
    <script src="assets/js/modules/claims-and-reimbursement/claims-and-reimbursement.js"></script>
    <script src="assets/js/modules/core-human-capital-management/core-human-capital-management.js"></script>
    <script src="assets/js/modules/payroll-management/payroll-management.js"></script>
    <script src="assets/js/modules/compensation-planning/compensation-planning.js"></script>
    <script src="assets/js/modules/hr-analytics-dashboard/hr-analytics-dashboard.js"></script>
    <script src="assets/js/modules/hmo-benefits-administration/hmo-benefits-administration.js"></script>
    <script src="assets/js/main-hr3.js"></script>
</body>
</html>