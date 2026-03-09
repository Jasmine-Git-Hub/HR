<?php
// Include database connection
require_once 'php/connect.php';

// Helper function to safely escape HTML
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Initialize variables with default values
$total_employees = 1248;
$active_reviews = 892;
$completed_reviews = 745;
$avg_rating = 4.2;

// Initialize rating distribution array
$rating_dist = [
    'outstanding' => 0,
    'exceeds' => 0,
    'meets' => 0,
    'needs_improvement' => 0,
    'unsatisfactory' => 0
];

// Initialize variables for upcoming deadlines
$reviews_pending = 0;
$cycle_stats = [
    'self_assessment' => 0,
    'manager_review' => 0
];

// Fetch data from database
try {
    // Get total employees
    $stmt = $conn->query("SELECT COUNT(*) as total FROM employees WHERE profile_status = 'Active'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_employees = $result['total'];

    // Get performance review statistics
    $stmt = $conn->query("SELECT 
        COUNT(*) as total_reviews,
        COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed,
        AVG(CASE WHEN rating IS NOT NULL THEN rating END) as avg_rating
        FROM performance_reviews 
        WHERE review_period = 'Q1 2026'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $active_reviews = $result['total_reviews'];
    $completed_reviews = $result['completed'];
    $avg_rating = $result['avg_rating'] ? round($result['avg_rating'], 1) : 4.2;

    // Get rating distribution
    $stmt = $conn->query("SELECT 
        COUNT(CASE WHEN rating >= 4.5 THEN 1 END) as outstanding,
        COUNT(CASE WHEN rating >= 4.0 AND rating < 4.5 THEN 1 END) as exceeds,
        COUNT(CASE WHEN rating >= 3.0 AND rating < 4.0 THEN 1 END) as meets,
        COUNT(CASE WHEN rating >= 2.0 AND rating < 3.0 THEN 1 END) as needs_improvement,
        COUNT(CASE WHEN rating < 2.0 THEN 1 END) as unsatisfactory
        FROM performance_reviews 
        WHERE review_period = 'Q1 2026' AND rating IS NOT NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $rating_dist['outstanding'] = $result['outstanding'];
        $rating_dist['exceeds'] = $result['exceeds'];
        $rating_dist['meets'] = $result['meets'];
        $rating_dist['needs_improvement'] = $result['needs_improvement'];
        $rating_dist['unsatisfactory'] = $result['unsatisfactory'];
    }

    // Get goals statistics
    $stmt = $conn->query("SELECT 
        COUNT(*) as total_goals,
        COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_goals,
        AVG(CASE WHEN achieved_score IS NOT NULL THEN achieved_score END) as avg_achievement
        FROM goals 
        WHERE review_period = 'Q1 2026'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_goals = $result['total_goals'] ?? 0;
    $completed_goals = $result['completed_goals'] ?? 0;
    $avg_goal_achievement = $result['avg_achievement'] ? round($result['avg_achievement'], 1) : 75;

    // Get feedback statistics
    $stmt = $conn->query("SELECT 
        COUNT(*) as total_feedback,
        COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_feedback,
        AVG(CASE WHEN rating IS NOT NULL THEN rating END) as avg_feedback_rating
        FROM feedback");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_feedback = $result['total_feedback'] ?? 0;
    $completed_feedback = $result['completed_feedback'] ?? 0;
    $avg_feedback_rating = $result['avg_feedback_rating'] ? round($result['avg_feedback_rating'], 1) : 4.1;

    // Get PIP statistics
    $stmt = $conn->query("SELECT 
        COUNT(*) as total_pips,
        COUNT(CASE WHEN status = 'Active' THEN 1 END) as active_pips,
        COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_pips,
        AVG(CASE WHEN progress_score IS NOT NULL THEN progress_score END) as avg_progress
        FROM performance_improvement_plans");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_pips = $result['total_pips'] ?? 0;
    $active_pips = $result['active_pips'] ?? 0;
    $completed_pips = $result['completed_pips'] ?? 0;
    $avg_pip_progress = $result['avg_progress'] ? round($result['avg_progress'], 1) : 65;

    // Get cycle statistics for deadlines
    $stmt = $conn->query("SELECT 
        COUNT(CASE WHEN status = 'Self-Assessment' THEN 1 END) as self_assessment,
        COUNT(CASE WHEN status = 'Manager Review' THEN 1 END) as manager_review
        FROM performance_reviews 
        WHERE review_period = 'Q1 2026'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cycle_stats['self_assessment'] = $result['self_assessment'] ?? 0;
    $cycle_stats['manager_review'] = $result['manager_review'] ?? 0;
    $reviews_pending = ($result['self_assessment'] ?? 0) + ($result['manager_review'] ?? 0);

    // Get employee reviews with details
    $stmt = $conn->query("SELECT 
        e.employee_id as id,
        e.first_name,
        e.last_name,
        COALESCE(p.position_title, 'Not Assigned') as position,
        COALESCE(d.department_name, 'Not Assigned') as department,
        COALESCE(pr.rating, 0) as rating,
        COALESCE(pr.status, 'Pending') as status,
        COALESCE(pr.review_period, 'Q1 2026') as review_period,
        e.profile_status
        FROM employees e
        LEFT JOIN positions p ON e.position_id = p.position_id
        LEFT JOIN departments d ON e.department_id = d.department_id
        LEFT JOIN performance_reviews pr ON e.employee_id = pr.employee_id AND pr.review_period = 'Q1 2026'
        WHERE e.profile_status = 'Active'
        ORDER BY e.last_name, e.first_name
        LIMIT 8");
    $employee_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no reviews found, use sample data
    if (empty($employee_reviews)) {
        $employee_reviews = [
            ['id' => 1, 'first_name' => 'Juan', 'last_name' => 'Reyes', 'position' => 'HR Manager', 'department' => 'Human Resources', 'rating' => 4.5, 'status' => 'Completed', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
            ['id' => 2, 'first_name' => 'Elena', 'last_name' => 'Marcos', 'position' => 'Staff Nurse', 'department' => 'Nursing', 'rating' => 4.2, 'status' => 'Manager Review', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
            ['id' => 3, 'first_name' => 'Carlos', 'last_name' => 'Garcia', 'position' => 'Payroll Officer', 'department' => 'Finance', 'rating' => 3.8, 'status' => 'Self-Assessment', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
            ['id' => 4, 'first_name' => 'Ana', 'last_name' => 'Santos', 'position' => 'HR Staff', 'department' => 'Human Resources', 'rating' => 4.7, 'status' => 'Completed', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
            ['id' => 5, 'first_name' => 'Daniel', 'last_name' => 'Mercado', 'position' => 'Physical Therapist', 'department' => 'Rehabilitation', 'rating' => 4.8, 'status' => 'Completed', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
            ['id' => 6, 'first_name' => 'Karen', 'last_name' => 'Lim', 'position' => 'Lab Technician', 'department' => 'Laboratory', 'rating' => 4.6, 'status' => 'Manager Review', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
            ['id' => 7, 'first_name' => 'Maria', 'last_name' => 'Santos', 'position' => 'Department Head', 'department' => 'Human Resources', 'rating' => 4.9, 'status' => 'Completed', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
            ['id' => 8, 'first_name' => 'John', 'last_name' => 'Doe', 'position' => 'Administrative Assistant', 'department' => 'Administration', 'rating' => 3.5, 'status' => 'Pending', 'review_period' => 'Q1 2026', 'profile_status' => 'Active']
        ];
    }

    // Get goals data
    $stmt = $conn->query("SELECT 
        g.id,
        g.employee_id,
        g.goal_description,
        g.goal_type,
        g.target_date,
        g.status,
        g.weight,
        g.target_value,
        g.achieved_score,
        e.first_name,
        e.last_name,
        d.department_name
        FROM goals g
        LEFT JOIN employees e ON g.employee_id = e.employee_id
        LEFT JOIN departments d ON e.department_id = d.department_id
        WHERE g.review_period = 'Q1 2026'
        ORDER BY g.target_date DESC
        LIMIT 10");
    $goals_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($goals_data)) {
        $goals_data = [
            ['id' => 1, 'employee_id' => 1, 'goal_description' => 'Complete patient care certification', 'goal_type' => 'Development', 'target_date' => '2026-03-15', 'status' => 'In Progress', 'weight' => 2.0, 'target_value' => 100.00, 'achieved_score' => 85.00, 'first_name' => 'Juan', 'last_name' => 'Reyes', 'department_name' => 'Human Resources'],
            ['id' => 2, 'employee_id' => 1, 'goal_description' => 'Maintain 95% patient satisfaction', 'goal_type' => 'Performance', 'target_date' => '2026-03-31', 'status' => 'In Progress', 'weight' => 3.0, 'target_value' => 95.00, 'achieved_score' => 92.00, 'first_name' => 'Juan', 'last_name' => 'Reyes', 'department_name' => 'Human Resources'],
            ['id' => 3, 'employee_id' => 2, 'goal_description' => 'Complete advanced therapy training', 'goal_type' => 'Development', 'target_date' => '2026-02-28', 'status' => 'Completed', 'weight' => 1.5, 'target_value' => 100.00, 'achieved_score' => 100.00, 'first_name' => 'Elena', 'last_name' => 'Marcos', 'department_name' => 'Nursing'],
            ['id' => 4, 'employee_id' => 3, 'goal_description' => 'Reduce payroll processing time by 20%', 'goal_type' => 'Performance', 'target_date' => '2026-04-30', 'status' => 'Not Started', 'weight' => 2.5, 'target_value' => 80.00, 'achieved_score' => null, 'first_name' => 'Carlos', 'last_name' => 'Garcia', 'department_name' => 'Finance']
        ];
    }

    // Get feedback data
    $stmt = $conn->query("SELECT 
        f.id,
        f.employee_id,
        f.provider_id,
        f.feedback_type,
        f.feedback_date,
        f.rating,
        f.comments,
        f.anonymous,
        f.status,
        e.first_name,
        e.last_name,
        p.first_name as provider_first_name,
        p.last_name as provider_last_name,
        d.department_name
        FROM feedback f
        LEFT JOIN employees e ON f.employee_id = e.employee_id
        LEFT JOIN employees p ON f.provider_id = p.employee_id
        LEFT JOIN departments d ON e.department_id = d.department_id
        ORDER BY f.feedback_date DESC
        LIMIT 8");
    $feedback_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($feedback_data)) {
        $feedback_data = [
            ['id' => 1, 'employee_id' => 1, 'provider_id' => 2, 'feedback_type' => 'Peer', 'feedback_date' => '2026-03-10', 'rating' => 4.5, 'comments' => 'Excellent teamwork and communication skills', 'anonymous' => 0, 'status' => 'Completed', 'first_name' => 'Juan', 'last_name' => 'Reyes', 'provider_first_name' => 'Elena', 'provider_last_name' => 'Marcos', 'department_name' => 'Human Resources'],
            ['id' => 2, 'employee_id' => 3, 'provider_id' => 4, 'feedback_type' => 'Patient', 'feedback_date' => '2026-03-09', 'rating' => 4.8, 'comments' => 'Outstanding patient care and professionalism', 'anonymous' => 0, 'status' => 'Completed', 'first_name' => 'Elena', 'last_name' => 'Marcos', 'provider_first_name' => 'Patient', 'provider_last_name' => 'Feedback', 'department_name' => 'Nursing'],
            ['id' => 3, 'employee_id' => 2, 'provider_id' => 5, 'feedback_type' => 'Supervisor', 'feedback_date' => '2026-03-08', 'rating' => 3.9, 'comments' => 'Good performance, needs improvement in time management', 'anonymous' => 1, 'status' => 'Pending', 'first_name' => 'Carlos', 'last_name' => 'Garcia', 'provider_first_name' => 'Anonymous', 'provider_last_name' => 'Supervisor', 'department_name' => 'Finance']
        ];
    }

    // Get PIP data
    $stmt = $conn->query("SELECT 
        pip.id,
        pip.employee_id,
        pip.supervisor_id,
        pip.start_date,
        pip.end_date,
        pip.issue_description,
        pip.action_plan,
        pip.target_goals,
        pip.progress_score,
        pip.status,
        pip.extension_count,
        pip.supervisor_notes,
        e.first_name,
        e.last_name,
        p.position_title as position,
        d.department_name,
        s.first_name as supervisor_first_name,
        s.last_name as supervisor_last_name
        FROM performance_improvement_plans pip
        LEFT JOIN employees e ON pip.employee_id = e.employee_id
        LEFT JOIN positions p ON e.position_id = p.position_id
        LEFT JOIN departments d ON e.department_id = d.department_id
        LEFT JOIN employees s ON pip.supervisor_id = s.employee_id
        ORDER BY pip.created_date DESC
        LIMIT 8");
    $pip_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($pip_data)) {
        $pip_data = [
            ['id' => 1, 'employee_id' => 8, 'supervisor_id' => 1, 'start_date' => '2026-02-01', 'end_date' => '2026-03-15', 'issue_description' => 'Consistent tardiness and missed deadlines', 'action_plan' => 'Implement time management training and daily check-ins', 'target_goals' => 'Improve punctuality to 95% attendance', 'progress_score' => 65.0, 'status' => 'Active', 'extension_count' => 0, 'supervisor_notes' => 'Showing improvement, needs continued monitoring', 'first_name' => 'John', 'last_name' => 'Doe', 'position' => 'Administrative Assistant', 'department_name' => 'Administration', 'supervisor_first_name' => 'Juan', 'supervisor_last_name' => 'Reyes'],
            ['id' => 2, 'employee_id' => 9, 'supervisor_id' => 1, 'start_date' => '2026-01-15', 'end_date' => '2026-02-28', 'issue_description' => 'Poor communication with team members', 'action_plan' => 'Communication skills workshop and weekly team meetings', 'target_goals' => 'Achieve 90% positive feedback from team', 'progress_score' => 92.0, 'status' => 'Completed', 'extension_count' => 0, 'supervisor_notes' => 'Successfully completed all objectives', 'first_name' => 'Sarah', 'last_name' => 'Johnson', 'position' => 'Staff Nurse', 'department_name' => 'Nursing', 'supervisor_first_name' => 'Juan', 'supervisor_last_name' => 'Reyes']
        ];
    }

    // Get KPI data
    $stmt = $conn->query("SELECT 
        k.id,
        k.name,
        k.description,
        k.category,
        k.target_value,
        k.current_value,
        k.unit,
        k.period,
        k.status,
        ((k.current_value / k.target_value) * 100) as achievement_percentage
        FROM kpis k 
        WHERE k.period = 'Q1 2026'
        ORDER BY achievement_percentage DESC
        LIMIT 10");
    $kpi_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($kpi_data)) {
        $kpi_data = [
            ['id' => 1, 'name' => 'Patient Satisfaction', 'description' => 'Overall patient satisfaction score', 'category' => 'Quality', 'target_value' => 95.0, 'current_value' => 92.0, 'unit' => '%', 'period' => 'Q1 2026', 'status' => 'On Track', 'achievement_percentage' => 96.8],
            ['id' => 2, 'name' => 'Employee Attendance', 'description' => 'Monthly average employee attendance', 'category' => 'HR', 'target_value' => 98.0, 'current_value' => 96.0, 'unit' => '%', 'period' => 'Q1 2026', 'status' => 'On Track', 'achievement_percentage' => 98.0],
            ['id' => 3, 'name' => 'Training Completion', 'description' => 'Completion rate for mandatory training', 'category' => 'Development', 'target_value' => 85.0, 'current_value' => 78.0, 'unit' => '%', 'period' => 'Q1 2026', 'status' => 'At Risk', 'achievement_percentage' => 91.8],
            ['id' => 4, 'name' => 'Medication Error Rate', 'description' => 'Medication dispensing error rate', 'category' => 'Quality', 'target_value' => 0.5, 'current_value' => 0.3, 'unit' => '%', 'period' => 'Q1 2026', 'status' => 'Achieved', 'achievement_percentage' => 60.0]
        ];
    }

} catch(PDOException $e) {
    // Initialize variables that might not be set if database queries fail
    $total_goals = 0;
    $completed_goals = 0;
    $avg_goal_achievement = 75;
    $total_feedback = 0;
    $completed_feedback = 0;
    $avg_feedback_rating = 4.1;
    $total_pips = 0;
    $active_pips = 0;
    $completed_pips = 0;
    $avg_pip_progress = 65;
    $reviews_pending = 15;
    $cycle_stats = [
        'self_assessment' => 8,
        'manager_review' => 7
    ];
    
    // Use fallback data if database queries fail
    $employee_reviews = [
        ['id' => 1, 'first_name' => 'Juan', 'last_name' => 'Reyes', 'position' => 'HR Manager', 'department' => 'Human Resources', 'rating' => 4.5, 'status' => 'Completed', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
        ['id' => 2, 'first_name' => 'Elena', 'last_name' => 'Marcos', 'position' => 'Staff Nurse', 'department' => 'Nursing', 'rating' => 4.2, 'status' => 'Manager Review', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
        ['id' => 3, 'first_name' => 'Carlos', 'last_name' => 'Garcia', 'position' => 'Payroll Officer', 'department' => 'Finance', 'rating' => 3.8, 'status' => 'Self-Assessment', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
        ['id' => 4, 'first_name' => 'Ana', 'last_name' => 'Santos', 'position' => 'HR Staff', 'department' => 'Human Resources', 'rating' => 4.7, 'status' => 'Completed', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
        ['id' => 5, 'first_name' => 'Daniel', 'last_name' => 'Mercado', 'position' => 'Physical Therapist', 'department' => 'Rehabilitation', 'rating' => 4.8, 'status' => 'Completed', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
        ['id' => 6, 'first_name' => 'Karen', 'last_name' => 'Lim', 'position' => 'Lab Technician', 'department' => 'Laboratory', 'rating' => 4.6, 'status' => 'Manager Review', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
        ['id' => 7, 'first_name' => 'Maria', 'last_name' => 'Santos', 'position' => 'Department Head', 'department' => 'Human Resources', 'rating' => 4.9, 'status' => 'Completed', 'review_period' => 'Q1 2026', 'profile_status' => 'Active'],
        ['id' => 8, 'first_name' => 'John', 'last_name' => 'Doe', 'position' => 'Administrative Assistant', 'department' => 'Administration', 'rating' => 3.5, 'status' => 'Pending', 'review_period' => 'Q1 2026', 'profile_status' => 'Active']
    ];
    
    $goals_data = [
        ['id' => 1, 'employee_id' => 1, 'goal_description' => 'Complete patient care certification', 'goal_type' => 'Development', 'target_date' => '2026-03-15', 'status' => 'In Progress', 'weight' => 2.0, 'target_value' => 100.00, 'achieved_score' => 85.00, 'first_name' => 'Juan', 'last_name' => 'Reyes', 'department_name' => 'Human Resources'],
        ['id' => 2, 'employee_id' => 1, 'goal_description' => 'Maintain 95% patient satisfaction', 'goal_type' => 'Performance', 'target_date' => '2026-03-31', 'status' => 'In Progress', 'weight' => 3.0, 'target_value' => 95.00, 'achieved_score' => 92.00, 'first_name' => 'Juan', 'last_name' => 'Reyes', 'department_name' => 'Human Resources'],
        ['id' => 3, 'employee_id' => 2, 'goal_description' => 'Complete advanced therapy training', 'goal_type' => 'Development', 'target_date' => '2026-02-28', 'status' => 'Completed', 'weight' => 1.5, 'target_value' => 100.00, 'achieved_score' => 100.00, 'first_name' => 'Elena', 'last_name' => 'Marcos', 'department_name' => 'Nursing'],
        ['id' => 4, 'employee_id' => 3, 'goal_description' => 'Reduce payroll processing time by 20%', 'goal_type' => 'Performance', 'target_date' => '2026-04-30', 'status' => 'Not Started', 'weight' => 2.5, 'target_value' => 80.00, 'achieved_score' => null, 'first_name' => 'Carlos', 'last_name' => 'Garcia', 'department_name' => 'Finance']
    ];
    
    $feedback_data = [
        ['id' => 1, 'employee_id' => 1, 'provider_id' => 2, 'feedback_type' => 'Peer', 'feedback_date' => '2026-03-10', 'rating' => 4.5, 'comments' => 'Excellent teamwork and communication skills', 'anonymous' => 0, 'status' => 'Completed', 'first_name' => 'Juan', 'last_name' => 'Reyes', 'provider_first_name' => 'Elena', 'provider_last_name' => 'Marcos', 'department_name' => 'Human Resources'],
        ['id' => 2, 'employee_id' => 3, 'provider_id' => 4, 'feedback_type' => 'Patient', 'feedback_date' => '2026-03-09', 'rating' => 4.8, 'comments' => 'Outstanding patient care and professionalism', 'anonymous' => 0, 'status' => 'Completed', 'first_name' => 'Elena', 'last_name' => 'Marcos', 'provider_first_name' => 'Patient', 'provider_last_name' => 'Feedback', 'department_name' => 'Nursing'],
        ['id' => 3, 'employee_id' => 2, 'provider_id' => 5, 'feedback_type' => 'Supervisor', 'feedback_date' => '2026-03-08', 'rating' => 3.9, 'comments' => 'Good performance, needs improvement in time management', 'anonymous' => 1, 'status' => 'Pending', 'first_name' => 'Carlos', 'last_name' => 'Garcia', 'provider_first_name' => 'Anonymous', 'provider_last_name' => 'Supervisor', 'department_name' => 'Finance']
    ];
    
    $pip_data = [
        ['id' => 1, 'employee_id' => 8, 'supervisor_id' => 1, 'start_date' => '2026-02-01', 'end_date' => '2026-03-15', 'issue_description' => 'Consistent tardiness and missed deadlines', 'action_plan' => 'Implement time management training and daily check-ins', 'target_goals' => 'Improve punctuality to 95% attendance', 'progress_score' => 65.0, 'status' => 'Active', 'extension_count' => 0, 'supervisor_notes' => 'Showing improvement, needs continued monitoring', 'first_name' => 'John', 'last_name' => 'Doe', 'position' => 'Administrative Assistant', 'department_name' => 'Administration', 'supervisor_first_name' => 'Juan', 'supervisor_last_name' => 'Reyes'],
        ['id' => 2, 'employee_id' => 9, 'supervisor_id' => 1, 'start_date' => '2026-01-15', 'end_date' => '2026-02-28', 'issue_description' => 'Poor communication with team members', 'action_plan' => 'Communication skills workshop and weekly team meetings', 'target_goals' => 'Achieve 90% positive feedback from team', 'progress_score' => 92.0, 'status' => 'Completed', 'extension_count' => 0, 'supervisor_notes' => 'Successfully completed all objectives', 'first_name' => 'Sarah', 'last_name' => 'Johnson', 'position' => 'Staff Nurse', 'department_name' => 'Nursing', 'supervisor_first_name' => 'Juan', 'supervisor_last_name' => 'Reyes']
    ];
    
    $kpi_data = [
        ['id' => 1, 'name' => 'Patient Satisfaction', 'description' => 'Overall patient satisfaction score', 'category' => 'Quality', 'target_value' => 95.0, 'current_value' => 92.0, 'unit' => '%', 'period' => 'Q1 2026', 'status' => 'On Track', 'achievement_percentage' => 96.8],
        ['id' => 2, 'name' => 'Employee Attendance', 'description' => 'Monthly average employee attendance', 'category' => 'HR', 'target_value' => 98.0, 'current_value' => 96.0, 'unit' => '%', 'period' => 'Q1 2026', 'status' => 'On Track', 'achievement_percentage' => 98.0],
        ['id' => 3, 'name' => 'Training Completion', 'description' => 'Completion rate for mandatory training', 'category' => 'Development', 'target_value' => 85.0, 'current_value' => 78.0, 'unit' => '%', 'period' => 'Q1 2026', 'status' => 'At Risk', 'achievement_percentage' => 91.8],
        ['id' => 4, 'name' => 'Medication Error Rate', 'description' => 'Medication dispensing error rate', 'category' => 'Quality', 'target_value' => 0.5, 'current_value' => 0.3, 'unit' => '%', 'period' => 'Q1 2026', 'status' => 'Achieved', 'achievement_percentage' => 60.0]
    ];
}

// Helper functions
function getStatusBadgeClass($status) {
    switch($status) {
        case 'Completed': return 'bg-success';
        case 'Manager Review': return 'bg-info';
        case 'Self-Assessment': return 'bg-warning';
        case 'Pending': return 'bg-light text-dark';
        case 'Active': return 'bg-warning';
        case 'In Progress': return 'bg-primary';
        case 'Not Started': return 'bg-secondary';
        case 'Extended': return 'bg-info';
        case 'Terminated': return 'bg-danger';
        case 'Cancelled': return 'bg-secondary';
        case 'On Track': return 'bg-success';
        case 'At Risk': return 'bg-warning';
        case 'Behind': return 'bg-danger';
        case 'Achieved': return 'bg-info';
        case 'Exceeded': return 'bg-primary';
        default: return 'bg-secondary';
    }
}

function getRatingColor($rating) {
    if ($rating >= 4.5) return 'text-success';
    if ($rating >= 4.0) return 'text-primary';
    if ($rating >= 3.5) return 'text-warning';
    if ($rating >= 3.0) return 'text-info';
    return 'text-danger';
}

function getProgressColor($percentage) {
    if ($percentage >= 100) return 'bg-success';
    if ($percentage >= 90) return 'bg-primary';
    if ($percentage >= 75) return 'bg-info';
    if ($percentage >= 60) return 'bg-warning';
    return 'bg-danger';
}

function getInitials($first_name, $last_name) {
    return strtoupper(substr($first_name ?? '', 0, 1) . substr($last_name ?? '', 0, 1));
}

function getAvatarBgColor($index) {
    $colors = ['bg-light-primary', 'bg-light-success', 'bg-light-warning', 'bg-light-info', 'bg-light-danger'];
    return $colors[$index % count($colors)];
}

function getAvatarTextColor($index) {
    $colors = ['text-primary', 'text-success', 'text-warning', 'text-info', 'text-danger'];
    return $colors[$index % count($colors)];
}

// Calculate rating distribution percentages
$total_ratings = array_sum($rating_dist);
$outstanding_pct = $total_ratings > 0 ? round(($rating_dist['outstanding'] / $total_ratings) * 100) : 25;
$exceeds_pct = $total_ratings > 0 ? round(($rating_dist['exceeds'] / $total_ratings) * 100) : 39;
$meets_pct = $total_ratings > 0 ? round(($rating_dist['meets'] / $total_ratings) * 100) : 27;
$needs_improvement_pct = $total_ratings > 0 ? round(($rating_dist['needs_improvement'] / $total_ratings) * 100) : 9;
$unsatisfactory_pct = $total_ratings > 0 ? round(($rating_dist['unsatisfactory'] / $total_ratings) * 100) : 0;
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HMS | Performance Management</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/seodashlogo.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <aside class="left-sidebar">
      <!-- Sidebar scroll-->
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="./index.html" class="text-nowrap logo-img">
            <img src="../assets/images/logos/logo-light.svg" alt="" />
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
              <span class="hide-menu">Home</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./index.html" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:home-smile-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
              <span class="hide-menu">PILLARS</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#buttonsDropdown"
                aria-expanded="false" aria-controls="buttonsDropdown">
                <span class="hide-menu fs-3">HR1: Talent ACQ</span>
                <span class="ms-auto d-flex align-items-center justify-content-center">
                  <i class="ti ti-chevron-down fs-5"></i>
                </span>
              </a>
              <!-- Dropdown menu items -->
              <div class="collapse" id="buttonsDropdown">
                <ul class="flex-column sub-menu">
                  <li class="sidebar-item">
                    <a class="sidebar-link" href="./hr1-applicants.html">Applicants</a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link" href="./hr1-interviews.html">Interviews</a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link" href="./hr1-onboarding.html">Onboarding</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link active" href="./hr1-performance.php" aria-expanded="false">
                <span class="hide-menu fs-3">HR1: Performance</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#tablesDropdown"
                aria-expanded="false" aria-controls="tablesDropdown">
                <span class="hide-menu fs-3">HR3: Development</span>
                <span class="ms-auto d-flex align-items-center justify-content-center">
                  <i class="ti ti-chevron-down fs-5"></i>
                </span>
              </a>
              <div class="collapse" id="tablesDropdown">
                <ul class="flex-column sub-menu">
                  <li class="sidebar-item">
                    <a class="sidebar-link" href="./hr3-training.html">Training Programs</a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link" href="./hr3-career.html">Career Paths</a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link" href="./hr3-skills.html">Skills Management</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#chartsDropdown"
                aria-expanded="false" aria-controls="chartsDropdown">
                <span class="hide-menu fs-3">HR4: Compensation</span>
                <span class="ms-auto d-flex align-items-center justify-content-center">
                  <i class="ti ti-chevron-down fs-5"></i>
                </span>
              </a>
              <div class="collapse" id="chartsDropdown">
                <ul class="flex-column sub-menu">
                  <li class="sidebar-item">
                    <a class="sidebar-link" href="./hr4-payroll.html">Payroll</a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link" href="./hr4-benefits.html">Benefits</a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link" href="./hr4-rewards.html">Rewards</a>
                  </li>
                </ul>
              </div>
            </li>
          </ul>
        </nav>
      </div>
    </aside>
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
              <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link nav-icon-hover" href="javascript:void(0)">
                <i class="ti ti-bell-ringing"></i>
                <div class="notification bg-primary rounded-circle"></div>
              </a>
            </li>
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
              <li class="nav-item dropdown">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <img src="../assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                      <i class="ti ti-user fs-6"></i>
                      <p class="mb-0 fs-3">My Profile</p>
                    </a>
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                      <i class="ti ti-mail fs-6"></i>
                      <p class="mb-0 fs-3">My Account</p>
                    </a>
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                      <i class="ti ti-list-check fs-6"></i>
                      <p class="mb-0 fs-3">My Task</p>
                    </a>
                    <a href="./authentication-login.html" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->
      <div class="container-fluid">
        <!-- Page Header -->
        <div class="card">
          <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
              <h5 class="card-title fw-semibold mb-1">HR1 Performance Management</h5>
              <p class="mb-0 text-muted">Complete performance management system with goals, appraisals, feedback, and improvement plans.</p>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <iconify-icon icon="solar:download-minimalistic-bold-duotone"></iconify-icon>
                Export Reports
              </button>
              <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newReviewModal">
                <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                New Review
              </button>
            </div>
          </div>
        </div>

        <!-- Performance Overview Section -->
        <div class="row">
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle d-flex align-items-center justify-content-center bg-light-primary p-2 me-3" style="width:45px;height:45px;">
                    <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="fs-6 text-primary"></iconify-icon>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fw-normal">Total Employees</h6>
                    <h4 class="mb-0 fw-semibold"><?php echo number_format($total_employees); ?></h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle d-flex align-items-center justify-content-center bg-light-success p-2 me-3" style="width:45px;height:45px;">
                    <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-6 text-success"></iconify-icon>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fw-normal">Completed Reviews</h6>
                    <h4 class="mb-0 fw-semibold"><?php echo number_format($completed_reviews); ?></h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle d-flex align-items-center justify-content-center bg-light-warning p-2 me-3" style="width:45px;height:45px;">
                    <iconify-icon icon="solar:clock-circle-bold-duotone" class="fs-6 text-warning"></iconify-icon>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fw-normal">Active Reviews</h6>
                    <h4 class="mb-0 fw-semibold"><?php echo number_format($active_reviews); ?></h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle d-flex align-items-center justify-content-center bg-light-info p-2 me-3" style="width:45px;height:45px;">
                    <iconify-icon icon="solar:star-bold-duotone" class="fs-6 text-info"></iconify-icon>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted fw-normal">Avg Rating</h6>
                    <h4 class="mb-0 fw-semibold"><?php echo $avg_rating; ?>/5.0</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Employee Reviews Table -->
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <h5 class="card-title fw-semibold mb-0">Employee Performance Reviews</h5>
              <div class="d-flex gap-2">
                <div class="input-group" style="width:220px;">
                  <span class="input-group-text bg-transparent border-end-0"><i class="ti ti-search"></i></span>
                  <input type="text" class="form-control border-start-0 ps-0" placeholder="Search employees...">
                </div>
                <select class="form-select" style="width:160px;">
                  <option value="">All Departments</option>
                  <option>Human Resources</option>
                  <option>Nursing</option>
                  <option>Finance</option>
                  <option>Laboratory</option>
                  <option>Rehabilitation</option>
                  <option>Administration</option>
                </select>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table text-nowrap align-middle mb-0">
                <thead class="text-dark fs-4">
                  <tr>
                    <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Employee</h6></th>
                    <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Position</h6></th>
                    <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Department</h6></th>
                    <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Rating</h6></th>
                    <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Status</h6></th>
                    <th class="border-bottom-0 text-end"><h6 class="fw-semibold mb-0">Actions</h6></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($employee_reviews as $index => $employee): ?>
                  <tr>
                    <td class="border-bottom-0">
                      <div class="d-flex align-items-center">
                        <div class="rounded-circle <?php echo getAvatarBgColor($index); ?> d-flex align-items-center justify-content-center me-2" style="width:35px;height:35px;">
                          <span class="fw-semibold <?php echo getAvatarTextColor($index); ?>"><?php echo getInitials($employee['first_name'], $employee['last_name']); ?></span>
                        </div>
                        <div>
                          <h6 class="fw-semibold mb-0"><?php echo h($employee['first_name'] . ' ' . $employee['last_name']); ?></h6>
                          <small class="text-muted">ID: <?php echo $employee['id']; ?></small>
                        </div>
                      </div>
                    </td>
                    <td class="border-bottom-0">
                      <small><?php echo h($employee['position']); ?></small>
                    </td>
                    <td class="border-bottom-0">
                      <small><?php echo h($employee['department']); ?></small>
                    </td>
                    <td class="border-bottom-0">
                      <?php if ($employee['rating'] > 0): ?>
                        <div class="d-flex align-items-center gap-2">
                          <div class="progress flex-grow-1" style="height:8px; width:80px;">
                            <div class="progress-bar <?php echo getProgressColor($employee['rating'] * 20); ?>" style="width:<?php echo ($employee['rating'] / 5) * 100; ?>%"></div>
                          </div>
                          <small class="fw-semibold <?php echo getRatingColor($employee['rating']); ?>"><?php echo $employee['rating']; ?>/5.0</small>
                        </div>
                      <?php else: ?>
                        <span class="text-muted">Not Rated</span>
                      <?php endif; ?>
                    </td>
                    <td class="border-bottom-0"><span class="badge <?php echo getStatusBadgeClass($employee['status']); ?>"><?php echo h($employee['status']); ?></span></td>
                    <td class="border-bottom-0 text-end">
                      <div class="dropdown">
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li><a class="dropdown-item" href="javascript:void(0)"><i class="ti ti-eye me-2"></i>View Details</a></li>
                          <li><a class="dropdown-item" href="javascript:void(0)"><i class="ti ti-edit me-2"></i>Edit Review</a></li>
                          <li><a class="dropdown-item" href="javascript:void(0)"><i class="ti ti-file-text me-2"></i>View Goals</a></li>
                          <li><a class="dropdown-item" href="javascript:void(0)"><i class="ti ti-message me-2"></i>Request Feedback</a></li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Goals & KPIs Section -->
        <div class="row mt-4">
          <div class="col-lg-8">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <h5 class="card-title fw-semibold mb-0">Goals & KPIs</h5>
                  <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newGoalModal">
                    <iconify-icon icon="solar:add-circle-bold-duotone" class="fs-5"></iconify-icon>
                    Add Goal
                  </button>
                </div>
                <div class="table-responsive">
                  <table class="table text-nowrap align-middle mb-0">
                    <thead class="text-dark fs-4">
                      <tr>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Goal/KPI</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Employee</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Type</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Target</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Achievement</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Status</h6></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($goals_data as $goal): ?>
                      <tr>
                        <td class="border-bottom-0">
                          <div>
                            <h6 class="fw-semibold mb-0"><?php echo h(substr($goal['goal_description'], 0, 50)) . (strlen($goal['goal_description']) > 50 ? '...' : ''); ?></h6>
                            <small class="text-muted">Due: <?php echo date('M d', strtotime($goal['target_date'])); ?></small>
                          </div>
                        </td>
                        <td class="border-bottom-0">
                          <small><?php echo h($goal['first_name'] . ' ' . $goal['last_name']); ?></small>
                        </td>
                        <td class="border-bottom-0">
                          <span class="badge bg-light-primary text-primary"><?php echo h($goal['goal_type']); ?></span>
                        </td>
                        <td class="border-bottom-0">
                          <small><?php echo $goal['target_value']; ?>%</small>
                        </td>
                        <td class="border-bottom-0">
                          <?php if ($goal['achieved_score'] !== null): ?>
                            <div class="d-flex align-items-center gap-2">
                              <div class="progress flex-grow-1" style="height:8px; width:60px;">
                                <div class="progress-bar <?php echo getProgressColor($goal['achieved_score']); ?>" style="width:<?php echo $goal['achieved_score']; ?>%"></div>
                              </div>
                              <small class="fw-semibold"><?php echo $goal['achieved_score']; ?>%</small>
                            </div>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td class="border-bottom-0"><span class="badge <?php echo getStatusBadgeClass($goal['status']); ?>"><?php echo h($goal['status']); ?></span></td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <h5 class="card-title fw-semibold mb-0">KPI Overview</h5>
                  <span class="badge bg-primary">Q1 2026</span>
                </div>
                <?php foreach ($kpi_data as $kpi): ?>
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h6 class="fw-semibold mb-0"><?php echo h($kpi['name']); ?></h6>
                    <small class="text-muted"><?php echo h($kpi['category']); ?></small>
                  </div>
                  <span class="badge <?php echo getStatusBadgeClass($kpi['status']); ?>"><?php echo h($kpi['status']); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <small class="text-muted">Target: <?php echo $kpi['target_value']; ?> <?php echo h($kpi['unit']); ?></small>
                  <small class="fw-semibold <?php echo getRatingColor(($kpi['achievement_percentage'] ?? 0) / 20); ?>"><?php echo $kpi['current_value']; ?> <?php echo h($kpi['unit']); ?></small>
                </div>
                <div class="progress mb-3" style="height:8px;">
                  <div class="progress-bar <?php echo getProgressColor($kpi['achievement_percentage'] ?? 0); ?>" style="width:<?php echo min(100, $kpi['achievement_percentage'] ?? 0); ?>%"></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- 360-Degree Feedback & PIP Section -->
        <div class="row mt-4">
          <div class="col-lg-6">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <h5 class="card-title fw-semibold mb-0">360-Degree Feedback</h5>
                  <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newFeedbackModal">
                    <iconify-icon icon="solar:add-circle-bold-duotone" class="fs-5"></iconify-icon>
                    Request Feedback
                  </button>
                </div>
                <div class="table-responsive">
                  <table class="table text-nowrap align-middle mb-0">
                    <thead class="text-dark fs-4">
                      <tr>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Employee</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Type</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Rating</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Status</h6></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($feedback_data as $feedback): ?>
                      <tr>
                        <td class="border-bottom-0">
                          <div>
                            <h6 class="fw-semibold mb-0"><?php echo h($feedback['first_name'] . ' ' . $feedback['last_name']); ?></h6>
                            <small class="text-muted"><?php echo h($feedback['department_name']); ?></small>
                          </div>
                        </td>
                        <td class="border-bottom-0">
                          <span class="badge bg-light-info text-info"><?php echo h($feedback['feedback_type']); ?></span>
                          <?php if (!empty($feedback['anonymous']) && $feedback['anonymous']): ?>
                            <span class="badge bg-light-warning text-warning ms-1">Anonymous</span>
                          <?php endif; ?>
                        </td>
                        <td class="border-bottom-0">
                          <?php if (!empty($feedback['rating'])): ?>
                            <div class="d-flex align-items-center gap-2">
                              <div class="progress flex-grow-1" style="height:8px; width:60px;">
                                <div class="progress-bar <?php echo getProgressColor($feedback['rating'] * 20); ?>" style="width:<?php echo ($feedback['rating'] / 5) * 100; ?>%"></div>
                              </div>
                              <small class="fw-semibold <?php echo getRatingColor($feedback['rating']); ?>"><?php echo $feedback['rating']; ?>/5.0</small>
                            </div>
                          <?php else: ?>
                            <span class="text-muted">Pending</span>
                          <?php endif; ?>
                        </td>
                        <td class="border-bottom-0"><span class="badge <?php echo getStatusBadgeClass($feedback['status']); ?>"><?php echo h($feedback['status']); ?></span></td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <h5 class="card-title fw-semibold mb-0">Performance Improvement Plans</h5>
                  <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newPIPModal">
                    <iconify-icon icon="solar:add-circle-bold-duotone" class="fs-5"></iconify-icon>
                    Create PIP
                  </button>
                </div>
                <div class="table-responsive">
                  <table class="table text-nowrap align-middle mb-0">
                    <thead class="text-dark fs-4">
                      <tr>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Employee</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Issue</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Progress</h6></th>
                        <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Status</h6></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($pip_data as $pip): ?>
                      <tr>
                        <td class="border-bottom-0">
                          <div>
                            <h6 class="fw-semibold mb-0"><?php echo h($pip['first_name'] . ' ' . $pip['last_name']); ?></h6>
                            <small class="text-muted"><?php echo h($pip['position'] ?? ''); ?></small>
                          </div>
                        </td>
                        <td class="border-bottom-0">
                          <small><?php echo h(substr($pip['issue_description'], 0, 40)) . (strlen($pip['issue_description']) > 40 ? '...' : ''); ?></small>
                        </td>
                        <td class="border-bottom-0">
                          <?php if (!empty($pip['progress_score'])): ?>
                            <div class="d-flex align-items-center gap-2">
                              <div class="progress flex-grow-1" style="height:8px; width:60px;">
                                <div class="progress-bar <?php echo getProgressColor($pip['progress_score']); ?>" style="width:<?php echo $pip['progress_score']; ?>%"></div>
                              </div>
                              <small class="fw-semibold <?php echo getRatingColor($pip['progress_score'] / 20); ?>"><?php echo $pip['progress_score']; ?>%</small>
                            </div>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td class="border-bottom-0"><span class="badge <?php echo getStatusBadgeClass($pip['status']); ?>"><?php echo h($pip['status']); ?></span></td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Reports & Analytics Section -->
        <div class="card mt-4">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <h5 class="card-title fw-semibold mb-0">Reports & Analytics</h5>
              <div class="d-flex gap-2">
                <select class="form-select" style="width:150px;">
                  <option value="">Q1 2026</option>
                  <option>Q4 2025</option>
                  <option>Q3 2025</option>
                </select>
                <button type="button" class="btn btn-outline-primary btn-sm">
                  <iconify-icon icon="solar:download-minimalistic-bold-duotone" class="fs-5"></iconify-icon>
                  Export
                </button>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-8">
                <div id="performanceChart"></div>
              </div>
              <div class="col-lg-4">
                <div id="distributionChart"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="card mt-4">
          <div class="card-body">
            <div class="row">
              <div class="col-lg-8">
                <div class="d-flex align-items-center mb-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center bg-light-success p-2 me-3" style="width:45px;height:45px;">
                    <iconify-icon icon="solar:target-bold-duotone" class="fs-6 text-success"></iconify-icon>
                  </div>
                  <h5 class="card-title fw-semibold mb-0">Key KPIs</h5>
                </div>
                <?php 
                $display_kpis = array_slice($kpi_data, 0, 4);
                foreach ($display_kpis as $kpi): 
                ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                  <div>
                    <h6 class="fw-semibold mb-0"><?php echo h($kpi['name']); ?></h6>
                    <small class="text-muted"><?php echo h($kpi['category']); ?></small>
                  </div>
                  <span class="badge <?php echo getStatusBadgeClass($kpi['status']); ?> px-3 py-2"><?php echo $kpi['current_value']; ?><?php echo h($kpi['unit']); ?></span>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="col-lg-4">
                <div class="d-flex align-items-center mb-3">
                  <h5 class="card-title fw-semibold mb-0">Upcoming Deadlines</h5>
                </div>
                <div class="d-flex align-items-start pb-3 mb-3 border-bottom">
                  <div class="rounded bg-light-danger text-danger text-center p-2 me-3" style="min-width:48px;">
                    <small class="d-block fw-semibold" style="font-size:11px;">MAR</small>
                    <h5 class="mb-0 fw-bold">15</h5>
                  </div>
                  <div>
                    <h6 class="fw-semibold mb-1">Self-Assessment Deadline</h6>
                    <small class="text-muted d-block">Q1 2026 Review Cycle</small>
                    <small class="text-danger fw-semibold"><?php echo number_format($cycle_stats['self_assessment']); ?> employees remaining</small>
                  </div>
                </div>
                <div class="d-flex align-items-start pb-3 mb-3 border-bottom">
                  <div class="rounded bg-light-warning text-warning text-center p-2 me-3" style="min-width:48px;">
                    <small class="d-block fw-semibold" style="font-size:11px;">MAR</small>
                    <h5 class="mb-0 fw-bold">22</h5>
                  </div>
                  <div>
                    <h6 class="fw-semibold mb-1">Manager Review Deadline</h6>
                    <small class="text-muted d-block">Q1 2026 Review Cycle</small>
                    <small class="text-warning fw-semibold"><?php echo number_format($cycle_stats['manager_review']); ?> reviews pending</small>
                  </div>
                </div>
                <div class="d-flex align-items-start">
                  <div class="rounded bg-light-primary text-primary text-center p-2 me-3" style="min-width:48px;">
                    <small class="d-block fw-semibold" style="font-size:11px;">MAR</small>
                    <h5 class="mb-0 fw-bold">31</h5>
                  </div>
                  <div>
                    <h6 class="fw-semibold mb-1">Calibration Meeting</h6>
                    <small class="text-muted d-block">All department heads</small>
                    <small class="text-muted">Conference Room A, 9:00 AM</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- New Review Modal -->
  <div class="modal fade" id="newReviewModal" tabindex="-1" aria-labelledby="newReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="newReviewModalLabel">Create Performance Review</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="reviewName" class="form-label">Cycle Name</label>
                <input type="text" class="form-control" id="reviewName" placeholder="e.g., Q2 2026 Performance Review">
              </div>
              <div class="col-md-6 mb-3">
                <label for="reviewPeriod" class="form-label">Review Period</label>
                <select class="form-select" id="reviewPeriod">
                  <option value="" disabled selected>Select period</option>
                  <option>Q1 2026 (Jan - Mar)</option>
                  <option>Q2 2026 (Apr - Jun)</option>
                  <option>H1 2026 (Jan - Jun)</option>
                  <option>Annual 2026</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="selfAssessDate" class="form-label">Self-Assessment Deadline</label>
                <input type="date" class="form-control" id="selfAssessDate">
              </div>
              <div class="col-md-6 mb-3">
                <label for="mgrReviewDate" class="form-label">Manager Review Deadline</label>
                <input type="date" class="form-control" id="mgrReviewDate">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="reviewScope" class="form-label">Scope</label>
                <select class="form-select" id="reviewScope">
                  <option>All Employees</option>
                  <option>Regular Employees Only</option>
                  <option>Probationary Only</option>
                  <option>Select Departments</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="ratingScale" class="form-label">Rating Scale</label>
                <select class="form-select" id="ratingScale">
                  <option>1 - 5 (Standard)</option>
                  <option>1 - 4 (Simplified)</option>
                  <option>1 - 10 (Detailed)</option>
                </select>
              </div>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="notifyEmployees" checked>
              <label class="form-check-label" for="notifyEmployees">Notify all employees via email</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="includeGoals" checked>
              <label class="form-check-label" for="includeGoals">Include goal-setting phase</label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary">Launch Review Cycle</button>
        </div>
      </div>
    </div>
  </div>

  <!-- New Goal Modal -->
  <div class="modal fade" id="newGoalModal" tabindex="-1" aria-labelledby="newGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="newGoalModalLabel">Create Goal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="goalEmployee" class="form-label">Employee</label>
              <select class="form-select" id="goalEmployee">
                <option value="">Select employee</option>
                <option>Juan Reyes</option>
                <option>Elena Marcos</option>
                <option>Carlos Garcia</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="goalDescription" class="form-label">Goal Description</label>
              <textarea class="form-control" id="goalDescription" rows="3" placeholder="Enter goal description..."></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="goalType" class="form-label">Goal Type</label>
                <select class="form-select" id="goalType">
                  <option value="">Select type</option>
                  <option>Performance</option>
                  <option>Development</option>
                  <option>Behavioral</option>
                  <option>Technical</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="goalTarget" class="form-label">Target Date</label>
                <input type="date" class="form-control" id="goalTarget">
              </div>
            </div>
            <div class="mb-3">
              <label for="goalTargetValue" class="form-label">Target Value (%)</label>
              <input type="number" class="form-control" id="goalTargetValue" min="0" max="100" placeholder="100">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary">Create Goal</button>
        </div>
      </div>
    </div>
  </div>

  <!-- New Feedback Modal -->
  <div class="modal fade" id="newFeedbackModal" tabindex="-1" aria-labelledby="newFeedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="newFeedbackModalLabel">Request 360-Degree Feedback</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="feedbackEmployee" class="form-label">Employee</label>
              <select class="form-select" id="feedbackEmployee">
                <option value="">Select employee</option>
                <option>Juan Reyes</option>
                <option>Elena Marcos</option>
                <option>Carlos Garcia</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="feedbackType" class="form-label">Feedback Type</label>
              <select class="form-select" id="feedbackType">
                <option value="">Select type</option>
                <option>Peer</option>
                <option>Supervisor</option>
                <option>Subordinate</option>
                <option>Patient</option>
                <option>Self</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="feedbackProvider" class="form-label">Feedback Provider</label>
              <select class="form-select" id="feedbackProvider">
                <option value="">Select provider</option>
                <option>Juan Reyes</option>
                <option>Elena Marcos</option>
                <option>Carlos Garcia</option>
              </select>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="anonymousFeedback">
              <label class="form-check-label" for="anonymousFeedback">Anonymous Feedback</label>
            </div>
            <div class="mb-3">
              <label for="feedbackComments" class="form-label">Comments</label>
              <textarea class="form-control" id="feedbackComments" rows="3" placeholder="Additional comments..."></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary">Request Feedback</button>
        </div>
      </div>
    </div>
  </div>

  <!-- New PIP Modal -->
  <div class="modal fade" id="newPIPModal" tabindex="-1" aria-labelledby="newPIPModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="newPIPModalLabel">Create Performance Improvement Plan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="pipEmployee" class="form-label">Employee</label>
                <select class="form-select" id="pipEmployee">
                  <option value="">Select employee</option>
                  <option>Juan Reyes</option>
                  <option>Elena Marcos</option>
                  <option>Carlos Garcia</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="pipSupervisor" class="form-label">Supervisor</label>
                <select class="form-select" id="pipSupervisor">
                  <option value="">Select supervisor</option>
                  <option>Maria Santos</option>
                  <option>Juan Reyes</option>
                  <option>Elena Marcos</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="pipStartDate" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="pipStartDate">
              </div>
              <div class="col-md-6 mb-3">
                <label for="pipEndDate" class="form-label">End Date</label>
                <input type="date" class="form-control" id="pipEndDate">
              </div>
            </div>
            <div class="mb-3">
              <label for="pipIssue" class="form-label">Issue Description</label>
              <textarea class="form-control" id="pipIssue" rows="3" placeholder="Describe the performance issues..."></textarea>
            </div>
            <div class="mb-3">
              <label for="pipActionPlan" class="form-label">Action Plan</label>
              <textarea class="form-control" id="pipActionPlan" rows="3" placeholder="Outline specific actions for improvement..."></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="pipGoals" class="form-label">Target Goals</label>
                <textarea class="form-control" id="pipGoals" rows="2" placeholder="Define measurable goals..."></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label for="pipResources" class="form-label">Resources Required</label>
                <textarea class="form-control" id="pipResources" rows="2" placeholder="List training, tools, or support needed..."></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary">Create PIP</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/load-sidebar.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  
  <script>
    // Initialize performance chart when document is ready
    document.addEventListener('DOMContentLoaded', function() {
      // Performance Chart
      var performanceOptions = {
        series: [{
          name: 'Average Rating',
          data: [3.7, 3.8, 3.9, 4.0, 4.1, <?php echo $avg_rating; ?>],
        }, {
          name: 'Completion Rate',
          data: [85, 87, 89, 91, 93, <?php echo $total_ratings > 0 ? round(($completed_reviews / $active_reviews) * 100) : 95; ?>],
        }],
        chart: {
          type: 'line',
          height: 300,
          toolbar: { show: false },
          stroke: { curve: 'smooth', width: 2 }
        },
        xaxis: {
          categories: ['Q4 2024', 'Q1 2025', 'Q2 2025', 'Q3 2025', 'Q4 2025', 'Q1 2026']
        },
        colors: ['#5D87FF', '#49BEFF'],
        dataLabels: {
          enabled: false
        }
      };

      var performanceChart = new ApexCharts(document.querySelector("#performanceChart"), performanceOptions);
      performanceChart.render();

      // Distribution Chart
      var distributionOptions = {
        series: [<?php echo $outstanding_pct; ?>, <?php echo $exceeds_pct; ?>, <?php echo $meets_pct; ?>, <?php echo $needs_improvement_pct; ?>, <?php echo $unsatisfactory_pct; ?>],
        chart: {
          type: 'pie',
          height: 300,
          toolbar: { show: false }
        },
        labels: ['Outstanding', 'Exceeds', 'Meets', 'Needs Improvement', 'Unsatisfactory'],
        colors: ['#13DEB9', '#5D87FF', '#FFAE1F', '#FA896B', '#FF5C8A'],
        legend: {
          position: 'bottom'
        },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 200
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
      };

      var distributionChart = new ApexCharts(document.querySelector("#distributionChart"), distributionOptions);
      distributionChart.render();
    });
  </script>
</body>

</html>