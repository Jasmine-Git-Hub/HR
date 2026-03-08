<?php
session_start();
require_once 'php/connect.php';

// Get dashboard data for HR 2 (Learning & Development focus)
try {
    // Total employees count
    $stmt = $conn->query("SELECT COUNT(*) as total FROM employees WHERE profile_status = 'Active'");
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Active programs (trainings)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM trainings WHERE end_date >= CURDATE() OR end_date IS NULL");
    $activePrograms = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Upcoming sessions
    $stmt = $conn->query("SELECT COUNT(*) as total FROM trainings WHERE start_date >= CURDATE()");
    $upcomingSessions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Active learners
    $stmt = $conn->query("SELECT COUNT(DISTINCT employee_id) as total FROM employee_trainings WHERE completion_status = 'Enrolled'");
    $activeLearners = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Learning programs
    $stmt = $conn->query("SELECT t.training_id, t.training_name, t.description, 
        COUNT(et.emp_training_id) as enrolled_count,
        t.start_date, t.end_date
        FROM trainings t
        LEFT JOIN employee_trainings et ON t.training_id = et.training_id
        GROUP BY t.training_id
        ORDER BY t.start_date DESC
        LIMIT 5");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Competency data
    $stmt = $conn->query("SELECT COUNT(*) as total FROM competencies");
    $totalCompetencies = $stmt->fetchColumn();
    
    // Succession candidates
    $stmt = $conn->query("SELECT COUNT(*) as total FROM succession_plans");
    $successionCandidates = $stmt->fetchColumn();
    
    // Learning delivery metrics
    $stmt = $conn->query("SELECT 
        COUNT(CASE WHEN start_date <= CURDATE() AND end_date >= CURDATE() THEN 1 END) as ongoing_sessions,
        COUNT(CASE WHEN start_date > CURDATE() THEN 1 END) as upcoming_sessions
        FROM trainings");
    $sessions = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Performance reviews pending
    $stmt = $conn->query("SELECT COUNT(*) as total FROM performance_reviews WHERE review_date IS NULL");
    $pendingReviews = $stmt->fetchColumn();
    
} catch(PDOException $e) {
    error_log("Dashboard HR2 data error: " . $e->getMessage());
    // Set default values
    $totalEmployees = 248;
    $activePrograms = 12;
    $upcomingSessions = 21;
    $activeLearners = 147;
    $programs = [];
    $totalCompetencies = 24;
    $successionCandidates = 18;
    $sessions = ['ongoing_sessions' => 8, 'upcoming_sessions' => 16];
    $pendingReviews = 15;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital HR 2 Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-hr2.css">
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
<body data-dashboard-scope="hr2">
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
                    <small class="text-muted" id="sidebarRoleText">Learning & Development</small>
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
                            <input class="form-control" type="search" name="q" placeholder="Search programs, employees..." aria-label="Search">
                            <button class="btn btn-outline-secondary search-icon-btn" type="submit" aria-label="Submit search">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <button class="btn btn-link position-relative notif-center-btn" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge rounded-pill bg-danger notification-badge"><?php echo $pendingReviews; ?></span>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-link d-flex align-items-center text-decoration-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                            <img id="navbarAvatar" src="https://ui-avatars.com/api/?name=Hospital+HR&background=0D6EFD&color=fff&bold=true" class="rounded-circle me-2" width="32" alt="User avatar">
                            <span class="d-none d-md-inline small" id="navbarRoleText">Learning & Development</span>
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
                <h2 class="page-title fw-bold" id="pageTitle">Learning & Development Dashboard</h2>
                <p class="text-muted mb-0" id="pageSubtitle">Manage training programs, competencies, and talent development.</p>
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
                                <p class="text-muted mb-1" id="statLabel2">Active Programs</p>
                                <h3 class="mb-0 fw-bold" id="statValue2"><?php echo $activePrograms; ?></h3>
                            </div>
                            <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-calendar-check"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" id="statLabel3">Upcoming Sessions</p>
                                <h3 class="mb-0 fw-bold" id="statValue3"><?php echo $upcomingSessions; ?></h3>
                            </div>
                            <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-calendar-plus"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" id="statLabel4">Compliance Rate</p>
                                <h3 class="mb-0 fw-bold" id="statValue4">95%</h3>
                            </div>
                            <div class="stat-icon bg-info-subtle text-info"><i class="fas fa-shield-alt"></i></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mb-4 content-section" id="dashboardGraph">
                <div class="col-12">
                    <div class="card border-0 shadow-sm graph-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Learning Overview</h5>
                            <span class="small text-muted">Live module insights</span>
                        </div>
                        <div class="card-body">
                            <div class="ui-graph-wrap">
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Total Employees</span><strong id="graphEmployeesValue"><?php echo $totalEmployees; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill" id="graphEmployeesBar" style="width: <?php echo min(100, $totalEmployees / 3); ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Active Programs</span><strong id="graphVacanciesValue"><?php echo $activePrograms; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill warning" id="graphVacanciesBar" style="width: <?php echo min(100, $activePrograms * 5); ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Upcoming Sessions</span><strong id="graphPendingValue"><?php echo $upcomingSessions; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill danger" id="graphPendingBar" style="width: <?php echo min(100, $upcomingSessions * 3); ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Active Learners</span><strong id="graphTrainingValue"><?php echo $activeLearners; ?></strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill mint" id="graphTrainingBar" style="width: <?php echo $activeLearners > 0 ? round(($activeLearners / $totalEmployees) * 100) : 0; ?>%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Compliance Rate</span><strong id="graphComplianceValue">95%</strong></div>
                                    <div class="ui-graph-track"><div class="ui-graph-fill success" id="graphComplianceBar" style="width: 95%"></div></div>
                                </div>
                                <div class="ui-graph-row">
                                    <div class="ui-graph-head"><span>Leadership Readiness</span><strong id="graphReadinessValue">78%</strong></div>
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
                            <h5 class="card-title mb-0" id="primaryCardTitle">Learning Program Pipeline</h5>
                            <a href="add_training.php" class="btn btn-sm btn-primary">Create Program</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Program</th>
                                        <th>Track</th>
                                        <th>Phase</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($programs)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Clinical Leadership Bootcamp</td>
                                        <td>Leadership Development</td>
                                        <td><span class="badge bg-warning-subtle text-warning">Enrollment</span></td>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Patient Safety Refresh 2026</td>
                                        <td>Compliance</td>
                                        <td><span class="badge bg-info-subtle text-info">In Progress</span></td>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Nursing Preceptor Certification</td>
                                        <td>Clinical Skills</td>
                                        <td><span class="badge bg-success-subtle text-success">Ready to Launch</span></td>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="#" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($programs as $program): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($program['training_name']); ?></td>
                                            <td><?php echo $program['enrolled_count'] > 0 ? 'Enrolled: ' . $program['enrolled_count'] : 'Compliance'; ?></td>
                                            <td>
                                                <?php 
                                                $phase = 'Planned';
                                                $badgeClass = 'bg-secondary-subtle text-secondary';
                                                if ($program['start_date'] && $program['start_date'] <= date('Y-m-d')) {
                                                    if ($program['end_date'] && $program['end_date'] >= date('Y-m-d')) {
                                                        $phase = 'In Progress';
                                                        $badgeClass = 'bg-info-subtle text-info';
                                                    } elseif ($program['end_date'] && $program['end_date'] < date('Y-m-d')) {
                                                        $phase = 'Completed';
                                                        $badgeClass = 'bg-success-subtle text-success';
                                                    }
                                                } elseif ($program['start_date'] && $program['start_date'] > date('Y-m-d')) {
                                                    $phase = 'Upcoming';
                                                    $badgeClass = 'bg-warning-subtle text-warning';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $phase; ?></span>
                                            </td>
                                            <td>
                                                <a href="view_training.php?id=<?php echo $program['training_id']; ?>" class="btn btn-link btn-sm"><i class="fas fa-eye"></i></a>
                                                <a href="edit_training.php?id=<?php echo $program['training_id']; ?>" class="btn btn-link btn-sm"><i class="fas fa-edit"></i></a>
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
                            <h5 class="card-title mb-0" id="secondaryCardTitle">Capability Action Queue</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Competency Reassessment Batch A</h6>
                                        <small class="text-muted">Due this week • 18 employees</small>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning">For Review</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Leadership Pool Validation</h6>
                                        <small class="text-muted">Nominees pending approval • <?php echo $successionCandidates; ?></small>
                                    </div>
                                    <span class="badge bg-info-subtle text-info">In Progress</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Mandatory Course Expiry Escalations</h6>
                                        <small class="text-muted">Certificates expiring in 14 days • 23</small>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger">Urgent</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mt-1 content-section">
                <div class="col-md-4" id="attendance">
                    <div class="card border-0 shadow-sm h-100 insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-graduation-cap me-2"></i>Learning Delivery</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Sessions This Week</span><strong class="metric-value"><?php echo $sessions['ongoing_sessions'] + $sessions['upcoming_sessions']; ?></strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Active Learners</span><strong class="metric-value"><?php echo $activeLearners; ?></strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Completion Alerts</span><strong class="metric-value">8</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="payroll">
                    <div class="card border-0 shadow-sm h-100 insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-route me-2"></i>Career Pathing</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Role Paths Updated</span><strong class="metric-value">12</strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">Promotion-Ready Pool</span><strong class="metric-value">34</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Mentorship Matches</span><strong class="metric-value">21</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="recruitment">
                    <div class="card border-0 shadow-sm h-100 insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-award me-2"></i>Performance Enablement</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-2"><span class="metric-label">Goals Needing Calibration</span><strong class="metric-value">15</strong></p>
                            <p class="metric-line mb-2"><span class="metric-label">High Performer Nominations</span><strong class="metric-value">11</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Recognition Drafts</span><strong class="metric-value">5</strong></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mt-1 content-section" id="documents">
                <div class="col-12">
                    <div class="card border-0 shadow-sm insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-clipboard-check me-2"></i>Governance and Compliance</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-1"><span class="metric-label">Critical Positions Tracked</span><strong class="metric-value">19</strong></p>
                            <p class="metric-line mb-1"><span class="metric-label">Compliance Exceptions</span><strong class="metric-value">6</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Audit-Ready Score</span><strong class="metric-value">94%</strong></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="row g-4 mt-1 content-section" id="settings">
                <div class="col-12">
                    <div class="card border-0 shadow-sm insight-card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-sliders me-2"></i>Workforce Enablement Controls</h5></div>
                        <div class="card-body">
                            <p class="metric-line mb-1"><span class="metric-label">Quarterly Skill Calibration</span><strong class="metric-value">Enabled</strong></p>
                            <p class="metric-line mb-1"><span class="metric-label">Succession Alerts</span><strong class="metric-value">Enabled</strong></p>
                            <p class="metric-line mb-0"><span class="metric-label">Talent Review Cycle</span><strong class="metric-value">Q2 2026</strong></p>
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
    <script src="assets/js/main-hr2.js"></script>
</body>
</html>