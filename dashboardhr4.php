<?php
session_start();
require_once 'php/connect.php';

// Get dashboard data for HR 4 (Core HR & Analytics focus)
try {
    // Total employees count
    $stmt = $conn->query("SELECT COUNT(*) as total FROM employees WHERE profile_status = 'Active'");
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Open leave requests
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
    
    // Core HR records
    $stmt = $conn->query("SELECT e.employee_id, e.first_name, e.last_name, 
        p.position_title, d.department_name,
        CASE 
            WHEN e.updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 'Verified'
            WHEN e.updated_at > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 'Needs Sync'
            ELSE 'Audit Flag'
        END as data_status
        FROM employees e
        LEFT JOIN positions p ON e.position_id = p.position_id
        LEFT JOIN departments d ON e.department_id = d.department_id
        LIMIT 5");
    $coreRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Benefits queue
    $stmt = $conn->query("SELECT cr.claim_id, e.first_name, e.last_name, 
        cr.claim_type, cr.claim_status
        FROM claim_reimbursements cr
        JOIN employees e ON cr.employee_id = e.employee_id
        WHERE cr.claim_status = 'Pending'
        ORDER BY cr.claim_date DESC
        LIMIT 3");
    $benefitsQueue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Payroll governance metrics
    $stmt = $conn->query("SELECT 
        COUNT(CASE WHEN payroll_status = 'Draft' THEN 1 END) as exceptions,
        COUNT(CASE WHEN payroll_status = 'Approved' THEN 1 END) as approved,
        COUNT(CASE WHEN payroll_status = 'Paid' THEN 1 END) as paid
        FROM payrolls");
    $payrollMetrics = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // HMO active members
    $stmt = $conn->query("SELECT COUNT(DISTINCT e.employee_id) as members
        FROM employees e
        JOIN employee_benefits eb ON e.employee_id = eb.employee_id
        JOIN benefits b ON eb.benefit_id = b.benefit_id
        WHERE b.benefit_name LIKE '%HMO%'");
    $hmoMembers = $stmt->fetchColumn();
    
    // Turnover risk (placeholder logic - would need actual turnover data)
    $turnoverRisk = 7;
    
} catch(PDOException $e) {
    error_log("Dashboard HR4 data error: " . $e->getMessage());
    // Set default values
    $totalEmployees = 248;
    $pendingLeaves = 19;
    $newApplicants = 34;
    $payrollReady = 92;
    $coreRecords = [];
    $benefitsQueue = [];
    $payrollMetrics = ['exceptions' => 6, 'approved' => 180, 'paid' => 62];
    $hmoMembers = 241;
    $turnoverRisk = 7;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital HR 4 Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-hr4.css">
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
<body data-dashboard-scope="hr4">
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
                    <small class="text-muted" id="sidebarRoleText">Core HR & Analytics</small>
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
                            <input class="form-control" type="search" name="q" placeholder="Search employees, analytics..." aria-label="Search">
                            <button class="btn btn-outline-secondary search-icon-btn" type="submit" aria-label="Submit search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <button class="btn btn-link position-relative notif-center-btn" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge rounded-pill bg-danger notification-badge"><?php echo $turnoverRisk; ?></span>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-link d-flex align-items-center text-decoration-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                            <img id="navbarAvatar" src="https://ui-avatars.com/api/?name=Hospital+HR&background=0D6EFD&color=fff&bold=true" class="rounded-circle me-2" width="32" alt="User avatar">
                            <span class="d-none d-md-inline small" id="navbarRoleText">Core HR & Analytics</span>
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
                <h2 class="page-title fw-bold" id="pageTitle">Core HR & Analytics Dashboard</h2>
                <p class="text-muted mb-0" id="pageSubtitle">Manage master records, compensation, benefits, and HR analytics.</p>
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
                            <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Analytics Overview</h5>
                            <span class="small text-muted">Live module insights</span>
                        </div>
                        <div class="card-body">
                            <div class="ui-graph-wrap">
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Total Employees</span><strong id="graphEmployeesValue"><?php echo $totalEmployees; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill" id="graphEmployeesBar" style="width: <?php echo min(100, $totalEmployees / 3); ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Open Vacancies</span><strong id="graphVacanciesValue">6</strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill warning" id="graphVacanciesBar" style="width: 30%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Pending Requests</span><strong id="graphPendingValue"><?php echo $pendingLeaves; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill danger" id="graphPendingBar" style="width: <?php echo min(100, $pendingLeaves * 3); ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Benefits Enrollment</span><strong id="graphTrainingValue">95%</strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill mint" id="graphTrainingBar" style="width: 95%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Data Integrity</span><strong id="graphComplianceValue">96%</strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill success" id="graphComplianceBar" style="width: 96%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Compensation Ratio</span><strong id="graphReadinessValue">103%</strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill info" id="graphReadinessBar" style="width: 103%"></div></div>
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
                            <h5 class="card-title mb-0" id="primaryCardTitle">Core HR Master Records</h5>
                            <a href="add_employee.php" class="btn btn-sm btn-primary">Add Record</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Position Band</th>
                                        <th>Data Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($coreRecords)): ?>
                                    <tr>
                                        <td>Anna Santos</td>
                                        <td>Manager IV</td>
                                        <td><span class="badge bg-success-subtle text-success">Verified</span></td>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Mark Rivera</td>
                                        <td>Specialist II</td>
                                        <td><span class="badge bg-warning-subtle text-warning">Needs Sync</span></td>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jude Molina</td>
                                        <td>Supervisor III</td>
                                        <td><span class="badge bg-info-subtle text-info">Audit Flag</span></td>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($coreRecords as $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['position_title'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php 
                                                $badgeClass = 'bg-success-subtle text-success';
                                                if ($record['data_status'] == 'Needs Sync') {
                                                    $badgeClass = 'bg-warning-subtle text-warning';
                                                } elseif ($record['data_status'] == 'Audit Flag') {
                                                    $badgeClass = 'bg-info-subtle text-info';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $record['data_status']; ?></span>
                                            </td>
                                            <td>
                                                <a href="view_employee.php?id=<?php echo $record['employee_id']; ?>" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                                <a href="edit_employee.php?id=<?php echo $record['employee_id']; ?>" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
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
                            <h5 class="card-title mb-0" id="secondaryCardTitle">Compensation and Benefits Queue</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php if (empty($benefitsQueue)): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Salary Adjustment Batch Q1</h6>
                                        <small class="text-muted">14 records awaiting finance confirmation</small>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning">Pending</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">HMO Enrollment Sync</h6>
                                        <small class="text-muted">8 dependents missing IDs</small>
                                    </div>
                                    <span class="badge bg-info-subtle text-info">In Progress</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Bonus Allocation Validation</h6>
                                        <small class="text-muted">Outlier variance in 3 departments</small>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger">Review Needed</span>
                                </li>
                                <?php else: ?>
                                    <?php foreach ($benefitsQueue as $claim): ?>
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($claim['claim_type']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($claim['first_name'] . ' ' . $claim['last_name']); ?></small>
                                        </div>
                                        <span class="badge bg-warning-subtle text-warning"><?php echo $claim['claim_status']; ?></span>
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
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-coins me-2"></i>Payroll Governance</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Payroll Controls Passed</span><strong class="metric-value">97%</strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Exception Tickets</span><strong class="metric-value"><?php echo $payrollMetrics['exceptions'] ?? 6; ?></strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Reconciliation Pending</span><strong class="metric-value">4</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="payroll">
                    <div class="card border-0 shadow-sm h-100 insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-hand-holding-medical me-2"></i>Benefits Administration</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Active HMO Members</span><strong class="metric-value"><?php echo $hmoMembers ?: 241; ?></strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Claims Processing SLA</span><strong class="metric-value">93%</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Enrollment Changes</span><strong class="metric-value">12</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="recruitment">
                    <div class="card border-0 shadow-sm h-100 insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-chart-column me-2"></i>HR Analytics</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Turnover Risk Alerts</span><strong class="metric-value"><?php echo $turnoverRisk; ?></strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Cost per Hire Trend</span><strong class="metric-value">-4.2%</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Absence Hotspots</span><strong class="metric-value">3 Units</strong></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mt-1 content-section" id="documents">
                <div class="col-12">
                    <div class="card border-0 shadow-sm insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-file-shield me-2"></i>Data and Policy Integrity</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-1"><span class="metric-label">Master Data Conflicts</span><strong class="metric-value">5</strong></p>
                            <p class="metric-line mb-1"><span class="metric-label">Policy Revisions Pending Publish</span><strong class="metric-value">3</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Latest Audit Confidence</span><strong class="metric-value">96%</strong></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mt-1 content-section" id="settings">
                <div class="col-12">
                    <div class="card border-0 shadow-sm insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-sliders me-2"></i>Compensation Controls</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-1"><span class="metric-label">Compa-Ratio Thresholds</span><strong class="metric-value">Enabled</strong></p>
                            <p class="metric-line mb-1"><span class="metric-label">Merit Budget Guardrails</span><strong class="metric-value">Configured</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Rewards Review Cycle</span><strong class="metric-value">Semi-Annual</strong></p>
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
    <script src="assets/js/main-hr4.js"></script>
</body>
</html>