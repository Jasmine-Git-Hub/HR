-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 03:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hms_hr_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

CREATE TABLE `applicants` (
  `applicant_id` int(11) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `resume_file_path` varchar(255) DEFAULT NULL,
  `supporting_docs` text DEFAULT NULL,
  `application_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `contract_status` enum('Pending','Sent','Signed','Awaiting Signature','Generating') DEFAULT 'Pending',
  `contract_signed_date` date DEFAULT NULL,
  `salary_offered` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `pre_employment_status` int(11) DEFAULT 0 COMMENT 'Percentage of requirements completed',
  `pre_employment_requirements` text DEFAULT NULL COMMENT 'JSON array of requirements with status',
  `position_applied` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicants`
--

INSERT INTO `applicants` (`applicant_id`, `requisition_id`, `first_name`, `last_name`, `email`, `contact_no`, `resume_file_path`, `supporting_docs`, `application_date`, `status`, `contract_status`, `contract_signed_date`, `salary_offered`, `start_date`, `pre_employment_status`, `pre_employment_requirements`, `position_applied`, `department`) VALUES
(16, NULL, 'John', 'Doe', 'john.doe@email.com', '09123456789', '../uploads/resumes/test.pdf', '[]', '2026-03-10', 'Assessment', 'Pending', NULL, NULL, NULL, 0, NULL, 'Staff Nurse', 'Nursing'),
(17, NULL, 'Benny James', 'De Aquino', 'bennyaquino28@gmail.com', '09482721541', '', '[]', '2026-03-10', 'Interview', 'Pending', NULL, NULL, NULL, 0, NULL, 'Staff Nurse', 'Nursing'),
(18, NULL, 'Mary ', 'Camasura', 'maryjanedeaquino@gmail.com', '09482721541', '', '[]', '2026-03-10', 'Assessment', 'Pending', NULL, NULL, NULL, 0, NULL, 'Medical Technologist', 'Laboratory'),
(19, NULL, 'Mark', 'De Aquino', 'bennyaquino28@gmail.com', '09482721541', '', '[]', '2026-03-10', 'Hired', 'Signed', '2026-03-10', 25000.00, '2026-03-24', 0, NULL, 'Pharmacist', 'Pharmacy'),
(20, NULL, 'Jasmine', 'Curtis', 'jasmine@gmail.com', '09482721542', '', '[]', '2026-03-10', 'Job Offer', 'Pending', NULL, NULL, NULL, 0, NULL, 'IT Support Specialist', 'IT'),
(21, NULL, 'Benedict', 'Chua', 'benedeict@gmail.com', '09484654878', '', '[]', '2026-03-11', 'Hired', 'Signed', '2026-03-10', 25000.00, '2026-03-25', 0, NULL, 'Staff Nurse', 'Nursing'),
(22, NULL, 'Rodrigo', 'Marcos', 'rods@gmail.com', '09482721542', '', '[]', '2026-03-11', 'Hired', 'Signed', '2026-03-10', 3000.00, '2026-03-25', 0, NULL, 'IT Support Specialist', 'IT');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `application_status` enum('Pending','Interview','Hired','Rejected') DEFAULT NULL,
  `interview_date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` bigint(20) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `attendance_date` date DEFAULT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `attendance_status` enum('Present','Late','Absent') DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `employee_id`, `attendance_date`, `time_in`, `time_out`, `attendance_status`, `remarks`) VALUES
(19, 19, '2026-03-07', '2026-03-07 08:02:00', '2026-03-07 17:00:00', 'Present', NULL),
(20, 20, '2026-03-07', '2026-03-07 08:21:00', '2026-03-07 17:15:00', 'Late', NULL),
(21, 21, '2026-03-07', NULL, NULL, 'Absent', NULL),
(22, 22, '2026-03-07', '2026-03-07 07:58:00', '2026-03-07 16:30:00', 'Present', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_taken` text NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `module_name` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `audit_logs`
--
DELIMITER $$
CREATE TRIGGER `trg_audit_logs_to_admin` AFTER INSERT ON `audit_logs` FOR EACH ROW BEGIN
    INSERT INTO hms_admin_db.audit_logs 
        (user_id, action_taken, record_id, module_name, timestamp)
    VALUES 
        (NEW.user_id, NEW.action_taken, NEW.record_id, NEW.module_name, NEW.timestamp);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `benefits`
--

CREATE TABLE `benefits` (
  `benefit_id` int(11) NOT NULL,
  `benefit_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `benefit_type` enum('Monetary','Non-Monetary') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim_reimbursements`
--

CREATE TABLE `claim_reimbursements` (
  `claim_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `claim_type` varchar(50) DEFAULT NULL,
  `claim_amount` decimal(10,2) DEFAULT NULL,
  `claim_date` date DEFAULT NULL,
  `claim_status` enum('Pending','Approved','Rejected') DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competencies`
--

CREATE TABLE `competencies` (
  `competency_id` int(11) NOT NULL,
  `competency_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contribution_matrices`
--

CREATE TABLE `contribution_matrices` (
  `matrix_id` int(11) NOT NULL,
  `contribution_type` varchar(50) DEFAULT NULL,
  `employee_share` decimal(10,2) DEFAULT NULL,
  `employer_share` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `correction_requests`
--

CREATE TABLE `correction_requests` (
  `request_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `issue_description` text NOT NULL,
  `request_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `resolved_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `domain_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `domain_type`, `description`) VALUES
(3, 'Human Resources', 'Administrative', 'Handles employee relations and recruitment'),
(4, 'Nursing', 'Clinical', 'Patient care and nursing services'),
(5, 'Finance', 'Administrative', 'Financial management and payroll'),
(6, 'Administration', 'Administrative', 'Hospital administration and management'),
(7, 'Human Resources', 'Administrative', 'Handles employee relations and recruitment'),
(8, 'Nursing', 'Clinical', 'Patient care and nursing services'),
(9, 'Finance', 'Administrative', 'Financial management and payroll'),
(10, 'Administration', 'Administrative', 'Hospital administration and management'),
(11, 'Pharmacy', 'Clinical', 'Pharmacy and pharmaceutical services'),
(12, 'Laboratory', 'Clinical', 'Medical laboratory and diagnostics'),
(13, 'Rehabilitation', 'Clinical', 'Physical therapy and rehabilitation services'),
(14, 'Radiology', 'Clinical', 'Radiology and imaging services'),
(15, 'IT', 'Administrative', 'Information Technology department');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `employee_no` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `hire_date` date NOT NULL,
  `employment_status` enum('Regular','Probationary','Contractual','Resigned') DEFAULT 'Probationary',
  `employment_type` enum('Full-time','Part-time') DEFAULT NULL,
  `clinical_rank` varchar(50) DEFAULT NULL,
  `profile_status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `department_id`, `position_id`, `supervisor_id`, `employee_no`, `first_name`, `last_name`, `middle_name`, `birth_date`, `gender`, `civil_status`, `contact_no`, `email`, `address`, `hire_date`, `employment_status`, `employment_type`, `clinical_rank`, `profile_status`, `created_at`, `updated_at`) VALUES
(19, 7, 9, NULL, 'EMP001', 'Anna', 'Santos', NULL, '1990-05-15', 'Female', NULL, NULL, 'anna.santos@hospital.com', NULL, '2025-01-15', 'Regular', NULL, NULL, 'Active', '2026-03-07 07:48:27', '2026-03-07 07:48:27'),
(20, 8, 12, NULL, 'EMP002', 'Jude', 'Molina', NULL, '1988-03-22', 'Male', NULL, NULL, 'jude.molina@hospital.com', NULL, '2025-02-01', 'Regular', NULL, NULL, 'Active', '2026-03-07 07:48:27', '2026-03-07 07:48:27'),
(21, 8, 12, NULL, 'EMP003', 'Leah', 'Gomez', NULL, '1992-07-10', 'Female', NULL, NULL, 'leah.gomez@hospital.com', NULL, '2025-01-20', 'Regular', NULL, NULL, 'Active', '2026-03-07 07:48:27', '2026-03-07 07:48:27'),
(22, 9, 14, NULL, 'EMP005', 'Mark', 'Rivera', NULL, '1985-09-18', 'Male', NULL, NULL, 'mark.rivera@hospital.com', NULL, '2025-02-15', 'Probationary', NULL, NULL, 'Active', '2026-03-07 07:48:27', '2026-03-07 07:48:27'),
(23, 4, 12, NULL, 'EMP20260005', 'Benedict', 'Chua', NULL, '2001-03-11', NULL, NULL, '09484654878', 'benedeict@gmail.com', NULL, '2026-03-25', 'Probationary', NULL, NULL, 'Active', '2026-03-11 00:27:54', '2026-03-11 00:27:54'),
(24, 13, 17, NULL, 'EMP20260006', 'Rodrigo', 'Marcos', NULL, '2001-03-11', NULL, NULL, '09482721542', 'rods@gmail.com', NULL, '2026-03-25', 'Probationary', NULL, NULL, 'Active', '2026-03-11 00:45:49', '2026-03-11 00:45:49');

-- --------------------------------------------------------

--
-- Table structure for table `employee_benefits`
--

CREATE TABLE `employee_benefits` (
  `emp_benefit_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `benefit_id` int(11) NOT NULL,
  `coverage_limit` decimal(10,2) DEFAULT NULL,
  `dependent_coverage` text DEFAULT NULL,
  `provider_name` varchar(100) DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_competencies`
--

CREATE TABLE `employee_competencies` (
  `emp_competency_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `competency_id` int(11) NOT NULL,
  `proficiency_level` enum('Beginner','Intermediate','Advanced','Expert') DEFAULT NULL,
  `last_assessed_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_recognitions`
--

CREATE TABLE `employee_recognitions` (
  `recognition_id` int(11) NOT NULL,
  `giver_id` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `award_name` varchar(150) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `date_awarded` date DEFAULT NULL,
  `citation` text DEFAULT NULL,
  `points` int(11) DEFAULT 10,
  `likes_count` int(11) DEFAULT 0,
  `comments_count` int(11) DEFAULT 0,
  `is_public` tinyint(1) DEFAULT 1,
  `awarded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_recognitions`
--

INSERT INTO `employee_recognitions` (`recognition_id`, `giver_id`, `employee_id`, `award_name`, `category`, `date_awarded`, `citation`, `points`, `likes_count`, `comments_count`, `is_public`, `awarded_by`, `created_at`, `updated_at`) VALUES
(1, 23, 24, 'Teamwork Recognition', 'Teamwork', '2026-03-10', 'Very Good', 50, 1, 1, 1, 23, '2026-03-11 02:27:35', '2026-03-11 02:27:44');

-- --------------------------------------------------------

--
-- Table structure for table `employee_specialties`
--

CREATE TABLE `employee_specialties` (
  `emp_specialty_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `specialty_id` int(11) NOT NULL,
  `certification_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_trainings`
--

CREATE TABLE `employee_trainings` (
  `emp_training_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `training_id` int(11) NOT NULL,
  `completion_status` enum('Enrolled','Completed','Failed') DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `feedback_type` enum('Peer','Supervisor','Subordinate','Patient','Self') NOT NULL,
  `feedback_date` date DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `anonymous` tinyint(1) DEFAULT 0,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `id` int(11) NOT NULL,
  `review_id` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `review_period` varchar(20) NOT NULL,
  `goal_description` text NOT NULL,
  `goal_type` enum('Performance','Development','Behavioral','Technical') NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `status` enum('Not Started','In Progress','Completed','Cancelled') DEFAULT 'Not Started',
  `weight` decimal(5,2) DEFAULT 1.00,
  `achieved_score` decimal(5,2) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `target_value` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`id`, `review_id`, `employee_id`, `review_period`, `goal_description`, `goal_type`, `category`, `target_date`, `status`, `weight`, `achieved_score`, `created_by`, `created_at`, `updated_at`, `target_value`) VALUES
(2, 23, 23, 'Q1 2026', 'asdsadsad', 'Behavioral', 'Training', '2026-07-02', 'Completed', 29.00, NULL, NULL, '2026-03-11 01:57:13', '2026-03-11 02:06:55', 100.00),
(3, 24, 24, 'Q1 2026', 'asdasdsad', 'Development', 'Training', '2026-06-11', 'Not Started', 10.00, NULL, NULL, '2026-03-11 02:12:25', '2026-03-11 02:12:25', 100.00);

--
-- Triggers `goals`
--
DELIMITER $$
CREATE TRIGGER `update_goal_counts` AFTER INSERT ON `goals` FOR EACH ROW BEGIN
    UPDATE performance_reviews 
    SET total_goals = (
        SELECT COUNT(*) 
        FROM goals 
        WHERE employee_id = NEW.employee_id
    )
    WHERE employee_id = NEW.employee_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `job_postings`
--

CREATE TABLE `job_postings` (
  `job_id` int(11) NOT NULL,
  `requisition_no` varchar(50) DEFAULT NULL,
  `position_id` int(11) NOT NULL,
  `headcount` int(11) DEFAULT 1,
  `employment_type` enum('Regular','Contractual','Part-Time','Probationary') DEFAULT 'Regular',
  `priority` enum('Normal','Urgent','Critical') DEFAULT 'Normal',
  `requested_by` varchar(100) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `target_start_date` date DEFAULT NULL,
  `justification` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `filled_positions` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `posting_date` date NOT NULL,
  `closing_date` date DEFAULT NULL,
  `job_description` text DEFAULT NULL,
  `status` enum('Open','Closed') DEFAULT 'Open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_postings`
--

INSERT INTO `job_postings` (`job_id`, `requisition_no`, `position_id`, `headcount`, `employment_type`, `priority`, `requested_by`, `request_date`, `target_start_date`, `justification`, `requirements`, `approved_by`, `approval_date`, `filled_positions`, `updated_at`, `posting_date`, `closing_date`, `job_description`, `status`) VALUES
(1, 'REQ-2026-042', 12, 3, 'Regular', 'Urgent', 'Dr. Ana Santos', '2026-03-08', '2026-04-01', 'Additional staff for new wing', 'Registered Nurse license, 2 years experience', NULL, NULL, 0, '2026-03-10 20:03:30', '2026-03-08', '2026-04-08', 'Staff Nurse position for new wing', 'Open'),
(2, 'REQ-2026-041', 10, 1, 'Regular', 'Normal', 'Engr. Reyes', '2026-03-07', '2026-04-15', 'IT support for hospital systems', 'IT graduate, helpdesk experience', NULL, NULL, 0, '2026-03-10 20:03:30', '2026-03-07', '2026-04-07', 'IT Support Specialist', 'Open'),
(3, 'REQ-2026-040', 11, 2, 'Regular', 'Normal', 'Dr. Villanueva', '2026-03-06', '2026-04-10', 'Pharmacy expansion', 'Licensed Pharmacist', NULL, NULL, 0, '2026-03-10 20:03:30', '2026-03-06', '2026-04-06', 'Pharmacist position', 'Open'),
(4, 'REQ-2026-039', 14, 1, 'Probationary', 'Normal', 'Dr. Cruz', '2026-03-05', '2026-03-20', 'Laboratory staff requirement', 'Medical Technologist license', NULL, NULL, 0, '2026-03-10 20:03:30', '2026-03-05', '2026-04-05', 'Medical Technologist', 'Open'),
(5, 'REQ-2026-038', 15, 1, 'Contractual', 'Normal', 'Dr. Garcia', '2026-03-03', '2026-03-30', 'Temporary coverage', 'Radiologist with experience', NULL, NULL, 0, '2026-03-10 20:03:30', '2026-03-03', '2026-04-03', 'Radiologist', 'Closed'),
(6, 'REQ-2026-037', 12, 2, 'Regular', 'Normal', 'Dr. Lim', '2026-03-01', '2026-04-05', 'Rehabilitation department expansion', 'Physical Therapist license', NULL, NULL, 0, '2026-03-10 20:03:30', '2026-03-01', '2026-04-01', 'Physical Therapist', 'Open'),
(20, 'REQ-2026-015', 10, 1, 'Regular', 'Urgent', 'Dr. Sam', '2026-03-10', '2026-03-10', 'needed', 'certs', NULL, NULL, 0, '2026-03-10 21:39:34', '2026-03-10', NULL, NULL, ''),
(21, 'REQ-2026-008', 9, 1, 'Regular', 'Urgent', 'Dr. Mang', '2026-03-10', '2026-03-21', 'needed', 'cert', NULL, NULL, 0, '2026-03-11 00:11:13', '2026-03-10', NULL, NULL, ''),
(22, 'REQ-2026-009', 9, 1, 'Regular', 'Urgent', 'Dr. jasmine', '2026-03-10', '2026-03-10', 'needed', 'certs', NULL, NULL, 0, '2026-03-11 00:45:15', '2026-03-10', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `kpis`
--

CREATE TABLE `kpis` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `target_value` decimal(10,2) DEFAULT NULL,
  `current_value` decimal(10,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `period` varchar(20) NOT NULL,
  `status` enum('On Track','At Risk','Behind','Achieved','Exceeded') DEFAULT 'On Track',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kpis`
--

INSERT INTO `kpis` (`id`, `name`, `description`, `category`, `target_value`, `current_value`, `unit`, `period`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Patient Satisfaction', 'Overall patient satisfaction score', 'Quality', 95.00, 92.00, '%', 'Q1 2026', 'On Track', '2026-03-09 08:41:55', '2026-03-09 08:41:55'),
(2, 'Employee Attendance', 'Monthly average employee attendance rate', 'HR', 98.00, 96.00, '%', 'Q1 2026', 'On Track', '2026-03-09 08:41:55', '2026-03-09 08:41:55'),
(3, 'Training Completion', 'Completion rate for mandatory training courses', 'Development', 85.00, 78.00, '%', 'Q1 2026', 'At Risk', '2026-03-09 08:41:55', '2026-03-09 08:41:55'),
(4, 'Incident Reports', 'Number of workplace incident reports', 'Safety', 5.00, 3.00, 'count', 'Q1 2026', 'Achieved', '2026-03-09 08:41:55', '2026-03-09 08:41:55'),
(5, 'Patient Satisfaction', 'Overall patient satisfaction score', 'Quality', 95.00, 92.00, '%', 'Q1 2026', 'On Track', '2026-03-09 08:47:44', '2026-03-09 08:47:44'),
(6, 'Employee Attendance', 'Monthly average employee attendance rate', 'HR', 98.00, 96.00, '%', 'Q1 2026', 'On Track', '2026-03-09 08:47:44', '2026-03-09 08:47:44'),
(7, 'Training Completion', 'Completion rate for mandatory training courses', 'Development', 85.00, 78.00, '%', 'Q1 2026', 'At Risk', '2026-03-09 08:47:44', '2026-03-09 08:47:44'),
(8, 'Incident Reports', 'Number of workplace incident reports', 'Safety', 5.00, 3.00, 'count', 'Q1 2026', 'Achieved', '2026-03-09 08:47:44', '2026-03-09 08:47:44'),
(9, 'Patient Satisfaction', 'Overall patient satisfaction score', 'Quality', 95.00, 92.00, '%', 'Q1 2026', 'On Track', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(10, 'Employee Attendance', 'Monthly average employee attendance rate', 'HR', 98.00, 96.00, '%', 'Q1 2026', 'On Track', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(11, 'Training Completion', 'Completion rate for mandatory training courses', 'Development', 85.00, 78.00, '%', 'Q1 2026', 'At Risk', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(12, 'Incident Reports', 'Number of workplace incident reports', 'Safety', 5.00, 3.00, 'count', 'Q1 2026', 'Achieved', '2026-03-09 08:52:35', '2026-03-09 08:52:35');

-- --------------------------------------------------------

--
-- Table structure for table `learning_logs`
--

CREATE TABLE `learning_logs` (
  `log_id` bigint(20) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `module_name` varchar(150) DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_balances`
--

CREATE TABLE `leave_balances` (
  `balance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `allotted_credits` decimal(5,2) DEFAULT NULL,
  `used_credits` decimal(5,2) DEFAULT NULL,
  `remaining_credits` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_balances`
--

INSERT INTO `leave_balances` (`balance_id`, `employee_id`, `leave_type`, `allotted_credits`, `used_credits`, `remaining_credits`) VALUES
(11, 19, 'Vacation Leave', 15.00, 3.00, 12.00),
(12, 19, 'Sick Leave', 15.00, 5.00, 7.00),
(13, 19, 'Emergency Leave', 3.00, 0.00, 3.00),
(14, 20, 'Vacation Leave', 15.00, 5.00, 10.00),
(15, 20, 'Sick Leave', 15.00, 1.00, 14.00),
(16, 20, 'Emergency Leave', 3.00, 1.00, 2.00),
(17, 21, 'Vacation Leave', 15.00, 7.00, 8.00),
(18, 21, 'Sick Leave', 15.00, 3.00, 12.00),
(19, 21, 'Emergency Leave', 3.00, 0.00, 3.00),
(20, 22, 'Vacation Leave', 15.00, 2.00, 13.00),
(21, 22, 'Sick Leave', 15.00, 0.00, 15.00),
(22, 22, 'Emergency Leave', 3.00, 0.00, 3.00);

-- --------------------------------------------------------

--
-- Table structure for table `leave_policies`
--

CREATE TABLE `leave_policies` (
  `policy_id` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `days_per_year` int(11) DEFAULT NULL,
  `max_consecutive` int(11) DEFAULT NULL,
  `notice_required` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_policies`
--

INSERT INTO `leave_policies` (`policy_id`, `leave_type`, `days_per_year`, `max_consecutive`, `notice_required`, `description`, `is_active`) VALUES
(1, 'Vacation Leave', 15, 10, '7 days', 'Annual vacation leave for rest and recreation', 1),
(2, 'Sick Leave', 15, 5, 'Same day', 'Sick leave with medical certificate required for more than 2 days', 1),
(3, 'Emergency Leave', 3, 3, 'As soon as possible', 'For urgent and unforeseen matters', 1),
(4, 'Maternity Leave', 60, 60, '30 days', 'Maternity leave for female employees', 1),
(5, 'Paternity Leave', 7, 7, '7 days', 'Paternity leave for male employees', 1),
(6, 'Bereavement Leave', 3, 3, '1 day', 'For death of immediate family member', 1),
(7, 'Study Leave', 10, 10, '15 days', 'For professional development and training', 1);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `leave_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `leave_reason` text DEFAULT NULL,
  `leave_status` enum('Pending','Approved','Rejected') DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`leave_id`, `employee_id`, `employee_name`, `leave_type`, `start_date`, `end_date`, `leave_reason`, `leave_status`, `approved_by`, `approval_date`) VALUES
(1, 21, NULL, 'Vacation', '2026-03-07', '2026-03-14', 'vacation', 'Pending', NULL, NULL),
(2, 20, NULL, 'Emergency', '2026-03-08', '2026-03-09', 'accident', 'Pending', NULL, NULL),
(3, 19, 'Anna Santos', 'Sick Leave', '2026-03-10', '2026-03-12', 'Flu and fever', 'Approved', 19, '2026-03-08'),
(4, 20, 'Jude Molina', 'Vacation Leave', '2026-03-15', '2026-03-20', 'Family vacation', 'Approved', 19, '2026-03-01'),
(5, 22, 'Mark Rivera', 'Emergency Leave', '2026-03-08', '2026-03-08', 'Family emergency', 'Approved', 19, '2026-03-07'),
(6, 19, 'Anna Santos', 'Vacation Leave', '2026-04-01', '2026-04-05', 'Personal time off', 'Rejected', 20, '2026-03-05'),
(7, 21, 'Leah Gomez', 'Sick Leave', '2026-02-20', '2026-02-22', 'Medical checkup', 'Approved', 19, '2026-02-19'),
(8, 19, 'Anna Santos', 'Maternity Leave', '2026-03-08', '2026-04-08', 'Maternity', 'Pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `onboardings`
--

CREATE TABLE `onboardings` (
  `onboarding_id` int(11) NOT NULL,
  `applicant_id` int(11) DEFAULT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `probation_end_date` date DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `buddy_id` int(11) DEFAULT NULL,
  `general_orientation_date` date DEFAULT NULL,
  `dept_orientation_date` date DEFAULT NULL,
  `welcome_package_sent` tinyint(1) DEFAULT 0,
  `it_setup_requested` tinyint(1) DEFAULT 0,
  `it_setup_completed` tinyint(1) DEFAULT 0,
  `workspace_prepared` tinyint(1) DEFAULT 0,
  `pre_orientation_modules_assigned` tinyint(1) DEFAULT 0,
  `pre_orientation_completed` tinyint(1) DEFAULT 0,
  `initial_tasks_assigned` tinyint(1) DEFAULT 0,
  `first_week_feedback` text DEFAULT NULL,
  `progress_percentage` int(11) DEFAULT 0,
  `stage` enum('Welcome Package','IT Setup','Orientation','Training','Evaluation','Completed') DEFAULT 'Welcome Package',
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `orientation_status` enum('Pending','Completed') DEFAULT NULL,
  `onboarding_status` enum('In Progress','Done') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `onboardings`
--

INSERT INTO `onboardings` (`onboarding_id`, `applicant_id`, `requisition_id`, `employee_id`, `start_date`, `probation_end_date`, `department_id`, `position_id`, `buddy_id`, `general_orientation_date`, `dept_orientation_date`, `welcome_package_sent`, `it_setup_requested`, `it_setup_completed`, `workspace_prepared`, `pre_orientation_modules_assigned`, `pre_orientation_completed`, `initial_tasks_assigned`, `first_week_feedback`, `progress_percentage`, `stage`, `notes`, `updated_at`, `orientation_status`, `onboarding_status`) VALUES
(1, 16, 1, 20, '2026-03-15', '2026-06-15', 8, 12, 20, '2026-03-16', '2026-03-17', 1, 1, 0, 1, 1, 0, 0, NULL, 20, 'Welcome Package', NULL, '2026-03-10 21:52:46', 'Pending', 'In Progress'),
(2, 17, 1, 21, '2026-03-10', '2026-06-10', 8, 12, 21, '2026-03-11', '2026-03-12', 1, 1, 1, 1, 1, 0, 0, NULL, 100, 'Completed', NULL, '2026-03-10 22:00:19', 'Completed', 'Done'),
(3, 18, 4, 22, '2026-03-03', '2026-06-03', 4, 14, 22, '2026-03-04', '2026-03-05', 1, 1, 1, 1, 1, 1, 1, NULL, 100, 'Completed', NULL, '2026-03-10 23:26:11', 'Completed', 'Done'),
(6, 19, NULL, NULL, '2026-03-12', '2026-06-12', 4, 16, 20, '2026-03-21', '2026-03-19', 1, 1, 0, 1, 1, 0, 0, NULL, 100, 'Completed', NULL, '2026-03-10 23:39:23', 'Completed', 'Done'),
(7, 21, NULL, 23, '2026-03-05', '2026-06-05', 4, 12, 20, '2026-03-11', '2026-03-13', 1, 1, 0, 1, 1, 0, 0, NULL, 100, 'Completed', NULL, '2026-03-11 00:27:54', 'Completed', 'Done'),
(8, 22, NULL, 24, '2026-03-11', '2026-06-11', 4, 17, 23, '2026-03-12', '2026-03-20', 1, 1, 0, 1, 1, 0, 0, NULL, 100, 'Completed', NULL, '2026-03-11 00:45:49', 'Completed', 'Done');

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `payroll_id` bigint(20) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `pay_period_start` date DEFAULT NULL,
  `pay_period_end` date DEFAULT NULL,
  `basic_salary` decimal(10,2) DEFAULT NULL,
  `overtime_pay` decimal(10,2) DEFAULT NULL,
  `gross_pay` decimal(10,2) DEFAULT NULL,
  `total_deductions` decimal(10,2) DEFAULT NULL,
  `net_pay` decimal(10,2) DEFAULT NULL,
  `processed_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payroll_status` enum('Draft','Approved','Paid') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_deductions`
--

CREATE TABLE `payroll_deductions` (
  `deduction_id` bigint(20) NOT NULL,
  `payroll_id` bigint(20) NOT NULL,
  `deduction_type` varchar(50) DEFAULT NULL,
  `deduction_amount` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pay_periods`
--

CREATE TABLE `pay_periods` (
  `period_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `cutoff_type` enum('1st Quincena','2nd Quincena') DEFAULT NULL,
  `is_processed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `performance_improvement_plans`
--

CREATE TABLE `performance_improvement_plans` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `issue_description` text NOT NULL,
  `action_plan` text DEFAULT NULL,
  `target_goals` text DEFAULT NULL,
  `resources_required` text DEFAULT NULL,
  `review_frequency` enum('Daily','Weekly','Bi-weekly','Monthly') DEFAULT 'Weekly',
  `progress_score` decimal(5,2) DEFAULT NULL,
  `status` enum('Active','Completed','Extended','Terminated','Cancelled') DEFAULT 'Active',
  `extension_count` int(11) DEFAULT 0,
  `supervisor_notes` text DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `performance_reviews`
--

CREATE TABLE `performance_reviews` (
  `review_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `review_period_start` date DEFAULT NULL,
  `review_period_end` date DEFAULT NULL,
  `review_date` date DEFAULT NULL,
  `self_assessment_deadline` date DEFAULT NULL,
  `manager_review_deadline` date DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `review_period` varchar(20) DEFAULT NULL,
  `review_cycle` varchar(50) DEFAULT NULL,
  `goals_met` int(11) DEFAULT NULL,
  `total_goals` int(11) DEFAULT NULL,
  `status` enum('Pending','Self-Assessment','Manager Review','Calibration','Completed','Probationary') DEFAULT 'Pending',
  `self_assessment_completed` tinyint(1) DEFAULT 0,
  `manager_review_completed` tinyint(1) DEFAULT 0,
  `calibration_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_reviews`
--

INSERT INTO `performance_reviews` (`review_id`, `employee_id`, `reviewer_id`, `review_period_start`, `review_period_end`, `review_date`, `self_assessment_deadline`, `manager_review_deadline`, `rating`, `remarks`, `review_period`, `review_cycle`, `goals_met`, `total_goals`, `status`, `self_assessment_completed`, `manager_review_completed`, `calibration_completed`, `created_at`, `updated_at`) VALUES
(17, 19, 19, '2026-01-01', '2026-03-31', NULL, '2026-03-11', '2026-03-13', 4.00, NULL, 'Q1 2026', 'Performance Review', 0, 0, 'Manager Review', 0, 0, 0, '2026-03-10 23:54:23', '2026-03-11 02:04:32'),
(18, 20, 19, '2026-01-01', '2026-03-31', NULL, '2026-03-11', '2026-03-13', 5.00, NULL, 'Q1 2026', 'Performance Review', 0, 0, 'Calibration', 0, 0, 0, '2026-03-10 23:54:23', '2026-03-11 02:04:32'),
(19, 21, 19, '2026-01-01', '2026-03-31', NULL, '2026-03-11', '2026-03-13', 4.00, NULL, 'Q1 2026', 'Performance Review', 0, 0, 'Calibration', 0, 0, 0, '2026-03-10 23:54:23', '2026-03-11 02:04:32'),
(22, 22, 19, '2026-01-01', '2026-03-31', NULL, '2026-03-13', '2026-03-27', NULL, NULL, 'Q1 2026', 'performance review', 0, 0, 'Pending', 0, 0, 0, '2026-03-11 00:46:27', '2026-03-11 02:04:32'),
(23, 23, 19, '2026-01-01', '2026-03-31', NULL, '2026-03-13', '2026-03-27', 5.00, NULL, 'Q1 2026', 'performance review', 1, 1, 'Calibration', 0, 0, 0, '2026-03-11 00:46:27', '2026-03-11 02:07:04'),
(24, 24, 19, '2026-01-01', '2026-03-31', NULL, '2026-03-13', '2026-03-27', 5.00, NULL, 'Q1 2026', 'performance review', 0, 1, 'Calibration', 0, 0, 0, '2026-03-11 00:46:27', '2026-03-11 02:12:25');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `position_title` varchar(100) NOT NULL,
  `is_clinical` tinyint(1) DEFAULT 0,
  `position_status` enum('Active','Frozen','Abolished') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `department_id`, `grade_id`, `position_title`, `is_clinical`, `position_status`) VALUES
(9, 7, 1, 'HR Manager', 0, 'Active'),
(10, 7, 1, 'HR Assistant', 0, 'Active'),
(11, 8, 2, 'Chief Nurse', 1, 'Active'),
(12, 8, 2, 'Staff Nurse', 1, 'Active'),
(13, 9, 3, 'Finance Manager', 0, 'Active'),
(14, 9, 3, 'Accountant', 0, 'Active'),
(15, 10, 3, 'Administrative Officer', 0, 'Active'),
(16, 11, 2, 'Pharmacist', 1, 'Active'),
(17, 15, 2, 'IT Support Specialist', 0, 'Active'),
(18, 10, 2, 'Pharmacist', 1, 'Active'),
(19, 11, 2, 'IT Support Specialist', 0, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `professional_licenses`
--

CREATE TABLE `professional_licenses` (
  `license_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `license_name` varchar(100) NOT NULL,
  `license_no` varchar(50) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `expiration_date` date NOT NULL,
  `license_status` enum('Active','Expired') DEFAULT NULL,
  `scanned_copy_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_cycles`
--

CREATE TABLE `review_cycles` (
  `id` int(11) NOT NULL,
  `period` varchar(20) NOT NULL,
  `stage` enum('Goal Setting','Self-Assessment','Manager Review','Calibration','Finalized') NOT NULL,
  `employee_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Active','Completed','Cancelled') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_cycles`
--

INSERT INTO `review_cycles` (`id`, `period`, `stage`, `employee_id`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(61, 'Q1 2026', 'Goal Setting', 19, '2026-01-01', '2026-03-31', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(62, 'Q1 2026', 'Goal Setting', 20, '2026-01-01', '2026-03-31', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(63, 'Q1 2026', 'Goal Setting', 21, '2026-01-01', '2026-03-31', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(64, 'Q1 2026', 'Goal Setting', 22, '2026-01-01', '2026-03-31', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(65, 'Q1 2026', 'Self-Assessment', 19, '2026-02-01', '2026-03-15', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(66, 'Q1 2026', 'Self-Assessment', 20, '2026-02-01', '2026-03-15', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(67, 'Q1 2026', 'Self-Assessment', 21, '2026-02-01', '2026-03-15', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(68, 'Q1 2026', 'Self-Assessment', 22, '2026-02-01', '2026-03-15', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(69, 'Q1 2026', 'Manager Review', 19, '2026-02-15', '2026-03-22', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(70, 'Q1 2026', 'Manager Review', 20, '2026-02-15', '2026-03-22', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(71, 'Q1 2026', 'Manager Review', 21, '2026-02-15', '2026-03-22', '', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(72, 'Q1 2026', 'Manager Review', 22, '2026-02-15', '2026-03-22', '', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(73, 'Q1 2026', 'Calibration', 19, '2026-03-01', '2026-03-31', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(74, 'Q1 2026', 'Calibration', 20, '2026-03-01', '2026-03-31', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(75, 'Q1 2026', 'Calibration', 21, '2026-03-01', '2026-03-31', '', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(76, 'Q1 2026', 'Calibration', 22, '2026-03-01', '2026-03-31', '', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(77, 'Q1 2026', 'Finalized', 19, '2026-03-15', '2026-03-31', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(78, 'Q1 2026', 'Finalized', 20, '2026-03-15', '2026-03-31', 'Completed', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(79, 'Q1 2026', 'Finalized', 21, '2026-03-15', '2026-03-31', '', '2026-03-09 08:52:35', '2026-03-09 08:52:35'),
(80, 'Q1 2026', 'Finalized', 22, '2026-03-15', '2026-03-31', '', '2026-03-09 08:52:35', '2026-03-09 08:52:35');

-- --------------------------------------------------------

--
-- Table structure for table `roster_schedules`
--

CREATE TABLE `roster_schedules` (
  `roster_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `station_assignment` varchar(100) DEFAULT NULL,
  `is_on_call` tinyint(1) DEFAULT 0,
  `roster_status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_matrices`
--

CREATE TABLE `salary_matrices` (
  `grade_id` int(11) NOT NULL,
  `salary_grade` varchar(50) NOT NULL,
  `step_increment` int(11) DEFAULT 1,
  `basic_salary_amount` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_matrices`
--

INSERT INTO `salary_matrices` (`grade_id`, `salary_grade`, `step_increment`, `basic_salary_amount`, `effective_date`) VALUES
(1, 'Grade 1', 1, 25000.00, '2025-01-01'),
(2, 'Grade 2', 1, 30000.00, '2025-01-01'),
(3, 'Grade 3', 1, 35000.00, '2025-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `shift_id` int(11) NOT NULL,
  `shift_name` varchar(50) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_night_differential` tinyint(1) DEFAULT NULL,
  `shift_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `specialties`
--

CREATE TABLE `specialties` (
  `specialty_id` int(11) NOT NULL,
  `specialty_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `succession_plans`
--

CREATE TABLE `succession_plans` (
  `plan_id` int(11) NOT NULL,
  `target_position_id` int(11) NOT NULL,
  `candidate_employee_id` int(11) NOT NULL,
  `readiness_level` varchar(50) DEFAULT NULL,
  `evaluation_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timesheets`
--

CREATE TABLE `timesheets` (
  `timesheet_id` bigint(20) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `pay_period_id` int(11) NOT NULL,
  `total_hours_worked` decimal(5,2) DEFAULT NULL,
  `total_ot_hours` decimal(5,2) DEFAULT NULL,
  `total_late_minutes` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trainings`
--

CREATE TABLE `trainings` (
  `training_id` int(11) NOT NULL,
  `training_name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cpd_units` decimal(5,2) DEFAULT NULL,
  `mandatory_flag` tinyint(1) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`applicant_id`),
  ADD KEY `requisition_id` (`requisition_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `applicant_id` (`applicant_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `benefits`
--
ALTER TABLE `benefits`
  ADD PRIMARY KEY (`benefit_id`);

--
-- Indexes for table `claim_reimbursements`
--
ALTER TABLE `claim_reimbursements`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `competencies`
--
ALTER TABLE `competencies`
  ADD PRIMARY KEY (`competency_id`);

--
-- Indexes for table `contribution_matrices`
--
ALTER TABLE `contribution_matrices`
  ADD PRIMARY KEY (`matrix_id`);

--
-- Indexes for table `correction_requests`
--
ALTER TABLE `correction_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `employee_no` (`employee_no`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `supervisor_id` (`supervisor_id`);

--
-- Indexes for table `employee_benefits`
--
ALTER TABLE `employee_benefits`
  ADD PRIMARY KEY (`emp_benefit_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `benefit_id` (`benefit_id`);

--
-- Indexes for table `employee_competencies`
--
ALTER TABLE `employee_competencies`
  ADD PRIMARY KEY (`emp_competency_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `competency_id` (`competency_id`);

--
-- Indexes for table `employee_recognitions`
--
ALTER TABLE `employee_recognitions`
  ADD PRIMARY KEY (`recognition_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `giver_id` (`giver_id`);

--
-- Indexes for table `employee_specialties`
--
ALTER TABLE `employee_specialties`
  ADD PRIMARY KEY (`emp_specialty_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `specialty_id` (`specialty_id`);

--
-- Indexes for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  ADD PRIMARY KEY (`emp_training_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `training_id` (`training_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_employee` (`employee_id`),
  ADD KEY `idx_feedback_provider` (`provider_id`),
  ADD KEY `idx_feedback_type` (`feedback_type`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `review_id` (`review_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `job_postings_ibfk_3` (`approved_by`);

--
-- Indexes for table `kpis`
--
ALTER TABLE `kpis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `learning_logs`
--
ALTER TABLE `learning_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD PRIMARY KEY (`balance_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `leave_policies`
--
ALTER TABLE `leave_policies`
  ADD PRIMARY KEY (`policy_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `onboardings`
--
ALTER TABLE `onboardings`
  ADD PRIMARY KEY (`onboarding_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `onboardings_ibfk_applicant` (`applicant_id`),
  ADD KEY `onboardings_ibfk_requisition` (`requisition_id`),
  ADD KEY `onboardings_ibfk_department` (`department_id`),
  ADD KEY `onboardings_ibfk_position` (`position_id`),
  ADD KEY `onboardings_ibfk_buddy` (`buddy_id`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `period_id` (`period_id`);

--
-- Indexes for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD PRIMARY KEY (`deduction_id`),
  ADD KEY `payroll_id` (`payroll_id`);

--
-- Indexes for table `pay_periods`
--
ALTER TABLE `pay_periods`
  ADD PRIMARY KEY (`period_id`);

--
-- Indexes for table `performance_improvement_plans`
--
ALTER TABLE `performance_improvement_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pip_employee` (`employee_id`),
  ADD KEY `idx_pip_supervisor` (`supervisor_id`),
  ADD KEY `idx_pip_status` (`status`);

--
-- Indexes for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `grade_id` (`grade_id`);

--
-- Indexes for table `professional_licenses`
--
ALTER TABLE `professional_licenses`
  ADD PRIMARY KEY (`license_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `review_cycles`
--
ALTER TABLE `review_cycles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `roster_schedules`
--
ALTER TABLE `roster_schedules`
  ADD PRIMARY KEY (`roster_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `shift_id` (`shift_id`);

--
-- Indexes for table `salary_matrices`
--
ALTER TABLE `salary_matrices`
  ADD PRIMARY KEY (`grade_id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`shift_id`);

--
-- Indexes for table `specialties`
--
ALTER TABLE `specialties`
  ADD PRIMARY KEY (`specialty_id`);

--
-- Indexes for table `succession_plans`
--
ALTER TABLE `succession_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `target_position_id` (`target_position_id`),
  ADD KEY `candidate_employee_id` (`candidate_employee_id`);

--
-- Indexes for table `timesheets`
--
ALTER TABLE `timesheets`
  ADD PRIMARY KEY (`timesheet_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `pay_period_id` (`pay_period_id`);

--
-- Indexes for table `trainings`
--
ALTER TABLE `trainings`
  ADD PRIMARY KEY (`training_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `applicant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefits`
--
ALTER TABLE `benefits`
  MODIFY `benefit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `claim_reimbursements`
--
ALTER TABLE `claim_reimbursements`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competencies`
--
ALTER TABLE `competencies`
  MODIFY `competency_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contribution_matrices`
--
ALTER TABLE `contribution_matrices`
  MODIFY `matrix_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `correction_requests`
--
ALTER TABLE `correction_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `employee_benefits`
--
ALTER TABLE `employee_benefits`
  MODIFY `emp_benefit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_competencies`
--
ALTER TABLE `employee_competencies`
  MODIFY `emp_competency_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_recognitions`
--
ALTER TABLE `employee_recognitions`
  MODIFY `recognition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_specialties`
--
ALTER TABLE `employee_specialties`
  MODIFY `emp_specialty_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  MODIFY `emp_training_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `job_postings`
--
ALTER TABLE `job_postings`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `kpis`
--
ALTER TABLE `kpis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `learning_logs`
--
ALTER TABLE `learning_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_balances`
--
ALTER TABLE `leave_balances`
  MODIFY `balance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `leave_policies`
--
ALTER TABLE `leave_policies`
  MODIFY `policy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `onboardings`
--
ALTER TABLE `onboardings`
  MODIFY `onboarding_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `payroll_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  MODIFY `deduction_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pay_periods`
--
ALTER TABLE `pay_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `performance_improvement_plans`
--
ALTER TABLE `performance_improvement_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `professional_licenses`
--
ALTER TABLE `professional_licenses`
  MODIFY `license_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_cycles`
--
ALTER TABLE `review_cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `roster_schedules`
--
ALTER TABLE `roster_schedules`
  MODIFY `roster_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_matrices`
--
ALTER TABLE `salary_matrices`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `shift_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `specialties`
--
ALTER TABLE `specialties`
  MODIFY `specialty_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `succession_plans`
--
ALTER TABLE `succession_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timesheets`
--
ALTER TABLE `timesheets`
  MODIFY `timesheet_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trainings`
--
ALTER TABLE `trainings`
  MODIFY `training_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applicants`
--
ALTER TABLE `applicants`
  ADD CONSTRAINT `applicants_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `job_postings` (`job_id`);

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `job_postings` (`job_id`);

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `claim_reimbursements`
--
ALTER TABLE `claim_reimbursements`
  ADD CONSTRAINT `claim_reimbursements_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `claim_reimbursements_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `correction_requests`
--
ALTER TABLE `correction_requests`
  ADD CONSTRAINT `correction_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`supervisor_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `employee_benefits`
--
ALTER TABLE `employee_benefits`
  ADD CONSTRAINT `employee_benefits_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_benefits_ibfk_2` FOREIGN KEY (`benefit_id`) REFERENCES `benefits` (`benefit_id`);

--
-- Constraints for table `employee_competencies`
--
ALTER TABLE `employee_competencies`
  ADD CONSTRAINT `employee_competencies_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_competencies_ibfk_2` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`competency_id`);

--
-- Constraints for table `employee_recognitions`
--
ALTER TABLE `employee_recognitions`
  ADD CONSTRAINT `employee_recognitions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_recognitions_ibfk_giver` FOREIGN KEY (`giver_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_specialties`
--
ALTER TABLE `employee_specialties`
  ADD CONSTRAINT `employee_specialties_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_specialties_ibfk_2` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`specialty_id`);

--
-- Constraints for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  ADD CONSTRAINT `employee_trainings_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_trainings_ibfk_2` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`training_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feedback_provider` FOREIGN KEY (`provider_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goals_ibfk_creator` FOREIGN KEY (`created_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `goals_ibfk_review` FOREIGN KEY (`review_id`) REFERENCES `performance_reviews` (`review_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD CONSTRAINT `job_postings_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `job_postings_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `learning_logs`
--
ALTER TABLE `learning_logs`
  ADD CONSTRAINT `learning_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD CONSTRAINT `leave_balances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `onboardings`
--
ALTER TABLE `onboardings`
  ADD CONSTRAINT `onboardings_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `onboardings_ibfk_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `onboardings_ibfk_buddy` FOREIGN KEY (`buddy_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `onboardings_ibfk_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `onboardings_ibfk_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `onboardings_ibfk_requisition` FOREIGN KEY (`requisition_id`) REFERENCES `job_postings` (`job_id`) ON DELETE SET NULL;

--
-- Constraints for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `payrolls_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `pay_periods` (`period_id`);

--
-- Constraints for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD CONSTRAINT `payroll_deductions_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`payroll_id`);

--
-- Constraints for table `performance_improvement_plans`
--
ALTER TABLE `performance_improvement_plans`
  ADD CONSTRAINT `fk_pip_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pip_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD CONSTRAINT `performance_reviews_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `performance_reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `positions_ibfk_2` FOREIGN KEY (`grade_id`) REFERENCES `salary_matrices` (`grade_id`);

--
-- Constraints for table `professional_licenses`
--
ALTER TABLE `professional_licenses`
  ADD CONSTRAINT `professional_licenses_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `review_cycles`
--
ALTER TABLE `review_cycles`
  ADD CONSTRAINT `review_cycles_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `roster_schedules`
--
ALTER TABLE `roster_schedules`
  ADD CONSTRAINT `roster_schedules_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `roster_schedules_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `roster_schedules_ibfk_3` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`shift_id`);

--
-- Constraints for table `succession_plans`
--
ALTER TABLE `succession_plans`
  ADD CONSTRAINT `succession_plans_ibfk_1` FOREIGN KEY (`target_position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `succession_plans_ibfk_2` FOREIGN KEY (`candidate_employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `timesheets`
--
ALTER TABLE `timesheets`
  ADD CONSTRAINT `timesheets_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `timesheets_ibfk_2` FOREIGN KEY (`pay_period_id`) REFERENCES `pay_periods` (`period_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
