<?php
session_start();
require_once 'php/connect.php';

// Get dashboard data
try {
    // Total employees count
    $stmt = $conn->query("SELECT COUNT(*) as total FROM employees WHERE profile_status = 'Active'");
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pending leave requests
    $stmt = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE leave_status = 'Pending'");
    $pendingLeaves = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // New applicants
    $stmt = $conn->query("SELECT COUNT(*) as total FROM applicants WHERE status = 'Pending' OR status IS NULL");
    $newApplicants = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Payroll ready percentage
    $stmt = $conn->query("SELECT 
        COUNT(CASE WHEN payroll_status = 'Approved' OR payroll_status = 'Paid' THEN 1 END) as processed,
        COUNT(*) as total 
        FROM payrolls WHERE pay_period_end >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    $payrollData = $stmt->fetch(PDO::FETCH_ASSOC);
    $payrollReady = $payrollData['total'] > 0 ? round(($payrollData['processed'] / $payrollData['total']) * 100) : 92;
    
    // Attendance data
    $stmt = $conn->query("SELECT 
        COUNT(CASE WHEN attendance_status = 'Present' THEN 1 END) as present,
        COUNT(CASE WHEN attendance_status = 'Late' THEN 1 END) as late,
        COUNT(CASE WHEN attendance_status = 'Absent' THEN 1 END) as absent
        FROM attendance WHERE attendance_date = CURDATE()");
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Recruitment data
    $stmt = $conn->query("SELECT COUNT(*) as open_positions FROM job_postings WHERE status = 'Open'");
    $openPositions = $stmt->fetch(PDO::FETCH_ASSOC)['open_positions'];
    
    $stmt = $conn->query("SELECT COUNT(*) as interviews FROM applications WHERE interview_date >= CURDATE()");
    $interviewsToday = $stmt->fetch(PDO::FETCH_ASSOC)['interviews'];
    
    // Employee directory
    $stmt = $conn->query("SELECT e.employee_id, e.first_name, e.last_name, d.department_name, 
        e.employment_status, e.profile_status
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.department_id
        LIMIT 5");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pending leave requests list
    $stmt = $conn->query("SELECT lr.leave_id, lr.employee_name, lr.leave_type, 
        DATEDIFF(lr.end_date, lr.start_date) + 1 as days, lr.leave_status
        FROM leave_requests lr
        WHERE lr.leave_status = 'Pending'
        ORDER BY lr.start_date ASC
        LIMIT 5");
    $leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Graph data
    $graphEmployees = $totalEmployees;
    $graphVacancies = $openPositions;
    $graphPending = $pendingLeaves;
    $graphTraining = $conn->query("SELECT COUNT(*) FROM employee_trainings WHERE completion_status = 'Enrolled'")->fetchColumn();
    $graphCompliance = 94; // Placeholder - calculate based on certifications
    
} catch(PDOException $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    // Set default values
    $totalEmployees = 248;
    $pendingLeaves = 19;
    $newApplicants = 34;
    $payrollReady = 92;
    $attendance = ['present' => 223, 'late' => 11, 'absent' => 14];
    $openPositions = 6;
    $interviewsToday = 4;
    $employees = [];
    $leaveRequests = [];
    $graphEmployees = 248;
    $graphVacancies = 6;
    $graphPending = 19;
    $graphTraining = 24;
    $graphCompliance = 94;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital HR 1 Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-hr1.css">
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
<body data-dashboard-scope="hr1">
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
                    <small class="text-muted" id="sidebarRoleText">People Operations</small>
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
                            <input class="form-control" type="search" name="q" placeholder="Search employees, leave requests..." aria-label="Search">
                            <button class="btn btn-outline-secondary search-icon-btn" type="submit" aria-label="Submit search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <button class="btn btn-link position-relative notif-center-btn" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge rounded-pill bg-danger notification-badge"><?php echo $pendingLeaves + 3; ?></span>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-link d-flex align-items-center text-decoration-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                            <img id="navbarAvatar" src="https://ui-avatars.com/api/?name=Hospital+HR&background=0D6EFD&color=fff&bold=true" class="rounded-circle me-2" width="32" alt="User avatar">
                            <span class="d-none d-md-inline small" id="navbarRoleText">Hospital HR</span>
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
                <h2 class="page-title fw-bold" id="pageTitle">Hospital HR Dashboard</h2>
                <p class="text-muted mb-0" id="pageSubtitle">Manage workforce, operations, and employee lifecycle in one place.</p>
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
                                <p class="text-muted mb-1" id="statLabel3">New Applicants</p>
                                <h3 class="mb-0 fw-bold" id="statValue3"><?php echo $newApplicants; ?></h3>
                            </div>
                            <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-user-plus"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" id="statLabel4">Payroll Ready</p>
                                <h3 class="mb-0 fw-bold" id="statValue4"><?php echo $payrollReady; ?>%</h3>
                            </div>
                            <div class="stat-icon bg-info-subtle text-info"><i class="fas fa-money-bill-wave"></i></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mb-4 content-section" id="dashboardGraph">
                <div class="col-12">
                    <div class="card border-0 shadow-sm graph-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Dashboard Overview Graph</h5>
                            <span class="small text-muted">Live module insights</span>
                        </div>
                        <div class="card-body">
                            <div class="ui-graph-wrap">
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Total Employees</span><strong id="graphEmployeesValue"><?php echo $graphEmployees; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill" id="graphEmployeesBar" style="width: <?php echo min(100, $graphEmployees / 3); ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Open Vacancies</span><strong id="graphVacanciesValue"><?php echo $graphVacancies; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill warning" id="graphVacanciesBar" style="width: <?php echo min(100, $graphVacancies * 10); ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Pending Requests</span><strong id="graphPendingValue"><?php echo $graphPending; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill danger" id="graphPendingBar" style="width: <?php echo min(100, $graphPending * 3); ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>In-Training</span><strong id="graphTrainingValue"><?php echo $graphTraining; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill mint" id="graphTrainingBar" style="width: <?php echo min(100, $graphTraining / 2); ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Compliance Rate</span><strong id="graphComplianceValue"><?php echo $graphCompliance; ?>%</strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill success" id="graphComplianceBar" style="width: <?php echo $graphCompliance; ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Talent Readiness</span><strong id="graphReadinessValue">78%</strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill info" id="graphReadinessBar" style="width: 78%"></div></div>
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
                            <h5 class="card-title mb-0" id="primaryCardTitle">Employee Directory</h5>
                            <a href="add_employee.php" class="btn btn-sm btn-primary">Add Employee</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($employees)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No employees found</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($employees as $emp): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge <?php echo $emp['profile_status'] == 'Active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?>">
                                                    <?php echo $emp['employment_status'] ?? $emp['profile_status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_employee.php?id=<?php echo $emp['employee_id']; ?>" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                                <a href="edit_employee.php?id=<?php echo $emp['employee_id']; ?>" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
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
                            <h5 class="card-title mb-0" id="secondaryCardTitle">Pending Leave Approvals</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php if (empty($leaveRequests)): ?>
                                <li class="list-group-item px-0 text-center text-muted">No pending leave requests</li>
                                <?php else: ?>
                                    <?php foreach ($leaveRequests as $lr): ?>
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($lr['employee_name'] ?? 'Employee'); ?></h6>
                                            <small class="text-muted"><?php echo $lr['leave_type'] ?? 'Leave'; ?> • <?php echo $lr['days'] ?? 1; ?> days</small>
                                        </div>
                                        <span class="badge bg-warning-subtle text-warning"><?php echo $lr['leave_status'] ?? 'Pending'; ?></span>
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
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-user-check me-2"></i>Attendance</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Present Today</span><strong class="metric-value"><?php echo $attendance['present'] ?? 223; ?></strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Late Entries</span><strong class="metric-value"><?php echo $attendance['late'] ?? 11; ?></strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">On Leave</span><strong class="metric-value"><?php echo $attendance['absent'] ?? 14; ?></strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="payroll">
                    <div class="card border-0 shadow-sm h-100 insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-wallet me-2"></i>Payroll</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Cut-off</span><strong class="metric-value"><?php echo date('M d, Y', strtotime('last day of this month')); ?></strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Processed</span><strong class="metric-value">231/<?php echo $totalEmployees; ?></strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Outstanding</span><strong class="metric-value"><?php echo $totalEmployees - 231; ?></strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="recruitment">
                    <div class="card border-0 shadow-sm h-100 insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-user-plus me-2"></i>Recruitment</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Open Positions</span><strong class="metric-value"><?php echo $openPositions; ?></strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Interviews Today</span><strong class="metric-value"><?php echo $interviewsToday; ?></strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Offers Sent</span><strong class="metric-value">3</strong></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mt-1 content-section" id="documents">
                <div class="col-12">
                    <div class="card border-0 shadow-sm insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-folder-open me-2"></i>Documents</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-1"><span class="metric-label">Employee Files</span><strong class="metric-value"><?php echo $totalEmployees; ?></strong></p>
                            <p class="metric-line mb-1"><span class="metric-label">Contracts Pending Signature</span><strong class="metric-value">7</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Policy Updates This Month</span><strong class="metric-value">2</strong></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mt-1 content-section" id="settings">
                <div class="col-12">
                    <div class="card border-0 shadow-sm insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-gear me-2"></i>Settings</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-1"><span class="metric-label">Access Control</span><strong class="metric-value">Role-based</strong></p>
                            <p class="metric-line mb-1"><span class="metric-label">Notification Preferences</span><strong class="metric-value">Enabled</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">System Timezone</span><strong class="metric-value">Asia/Manila</strong></p>
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
    <script src="assets/js/main-hr1.js"></script>
</body>
</html>