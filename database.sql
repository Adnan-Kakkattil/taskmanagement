-- ============================================
-- TaskFlow Database Schema
-- Task Management System with Teams, Projects, and Tasks
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS taskflow_db;
USE taskflow_db;

-- ============================================
-- USERS TABLE
-- Stores user account information
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    avatar VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TEAMS TABLE
-- Stores team information
-- ============================================
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TEAM_MEMBERS TABLE
-- Many-to-Many: Users to Teams
-- Stores which users belong to which teams
-- ============================================
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('leader', 'member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_user (team_id, user_id),
    INDEX idx_team_id (team_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROJECTS TABLE
-- Stores project information
-- Projects can be assigned to teams
-- ============================================
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('planning', 'active', 'on_hold', 'completed') DEFAULT 'planning',
    team_id INT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_team_id (team_id),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TASKS TABLE
-- Stores task information
-- Tasks can be assigned to users and belong to projects
-- ============================================
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    project_id INT DEFAULT NULL,
    assigned_to INT DEFAULT NULL,
    status ENUM('pending', 'progress', 'done') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    due_date DATE DEFAULT NULL,
    created_by INT DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_project_id (project_id),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SESSIONS TABLE (Optional - for session management)
-- ============================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT SAMPLE DATA
-- ============================================

-- Insert Sample Users
-- Default password for all: 'password' (hashed with bcrypt)
INSERT INTO users (full_name, email, password, role) VALUES
('Admin User', 'admin@taskflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Alex Morgan', 'alex@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Sarah Johnson', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Mike Wilson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member');

-- Insert Sample Teams
INSERT INTO teams (name, description, created_by) VALUES
('Design Team', 'Creative design and UI/UX team', 1),
('Engineering Team', 'Software development and engineering', 1),
('Marketing Team', 'Marketing and communications', 1),
('Product Team', 'Product management and strategy', 1);

-- Insert Sample Team Members
INSERT INTO team_members (team_id, user_id, role) VALUES
(1, 2, 'leader'),  -- John Doe leads Design Team
(1, 3, 'member'),  -- Jane Smith in Design Team
(2, 4, 'leader'),  -- Alex Morgan leads Engineering Team
(2, 5, 'member'),  -- Sarah Johnson in Engineering Team
(3, 6, 'leader'),  -- Mike Wilson leads Marketing Team
(3, 2, 'member');  -- John Doe also in Marketing Team

-- Insert Sample Projects
INSERT INTO projects (name, description, status, team_id, created_by, start_date, end_date) VALUES
('Website Redesign', 'Complete redesign of company website', 'active', 1, 1, '2025-01-01', '2025-03-31'),
('Mobile App Development', 'New mobile application for iOS and Android', 'active', 2, 1, '2025-01-15', '2025-06-30'),
('Marketing Campaign Q1', 'Q1 marketing campaign launch', 'planning', 3, 1, '2025-02-01', '2025-03-31'),
('API Integration', 'Third-party API integration project', 'active', 2, 1, '2025-01-10', '2025-02-28');

-- Insert Sample Tasks
INSERT INTO tasks (name, description, project_id, assigned_to, status, priority, due_date, created_by) VALUES
('Design System Update', 'Update the design system with new components', 1, 2, 'progress', 'high', '2025-01-24', 1),
('Integration API Fix', 'Fix bugs in the integration API', 4, 4, 'pending', 'high', '2025-01-25', 1),
('Client Meeting Prep', 'Prepare presentation for client meeting', 1, 3, 'done', 'medium', '2025-01-22', 1),
('Homepage Animation', 'Add animations to homepage', 1, 2, 'progress', 'low', '2025-01-30', 1),
('Database Migration', 'Migrate database to new server', 4, 4, 'pending', 'high', '2025-01-24', 1),
('Social Media Strategy', 'Develop Q1 social media strategy', 3, 6, 'progress', 'medium', '2025-02-05', 1),
('User Testing', 'Conduct user testing sessions', 2, 5, 'pending', 'medium', '2025-02-10', 1);

-- ============================================
-- VIEWS (Optional - for easier queries)
-- ============================================

-- View: User Tasks Summary
CREATE OR REPLACE VIEW user_tasks_summary AS
SELECT 
    u.id as user_id,
    u.full_name,
    u.email,
    COUNT(t.id) as total_tasks,
    SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
    SUM(CASE WHEN t.status = 'progress' THEN 1 ELSE 0 END) as in_progress_tasks,
    SUM(CASE WHEN t.status = 'done' THEN 1 ELSE 0 END) as completed_tasks
FROM users u
LEFT JOIN tasks t ON u.id = t.assigned_to
GROUP BY u.id, u.full_name, u.email;

-- View: Project Tasks Summary
CREATE OR REPLACE VIEW project_tasks_summary AS
SELECT 
    p.id as project_id,
    p.name as project_name,
    COUNT(t.id) as total_tasks,
    SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
    SUM(CASE WHEN t.status = 'progress' THEN 1 ELSE 0 END) as in_progress_tasks,
    SUM(CASE WHEN t.status = 'done' THEN 1 ELSE 0 END) as completed_tasks
FROM projects p
LEFT JOIN tasks t ON p.id = t.project_id
GROUP BY p.id, p.name;

-- View: Team Members with Details
CREATE OR REPLACE VIEW team_members_details AS
SELECT 
    tm.id,
    tm.team_id,
    t.name as team_name,
    tm.user_id,
    u.full_name,
    u.email,
    tm.role,
    tm.joined_at
FROM team_members tm
JOIN teams t ON tm.team_id = t.id
JOIN users u ON tm.user_id = u.id
ORDER BY t.name, tm.role DESC, u.full_name;

-- ============================================
-- STORED PROCEDURES (Optional - for common operations)
-- ============================================

DELIMITER //

-- Procedure: Get User Dashboard Stats
CREATE PROCEDURE IF NOT EXISTS GetUserDashboardStats(IN user_id INT)
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM tasks WHERE assigned_to = user_id) as total_tasks,
        (SELECT COUNT(*) FROM tasks WHERE assigned_to = user_id AND status = 'pending') as pending_tasks,
        (SELECT COUNT(*) FROM tasks WHERE assigned_to = user_id AND status = 'progress') as in_progress_tasks,
        (SELECT COUNT(*) FROM tasks WHERE assigned_to = user_id AND status = 'done') as completed_tasks,
        (SELECT COUNT(*) FROM team_members WHERE user_id = user_id) as team_count,
        (SELECT COUNT(*) FROM projects WHERE created_by = user_id) as projects_created;
END //

-- Procedure: Assign Task to User
CREATE PROCEDURE IF NOT EXISTS AssignTaskToUser(
    IN task_id INT,
    IN user_id INT
)
BEGIN
    UPDATE tasks 
    SET assigned_to = user_id, updated_at = CURRENT_TIMESTAMP
    WHERE id = task_id;
END //

-- Procedure: Assign Project to Team
CREATE PROCEDURE IF NOT EXISTS AssignProjectToTeam(
    IN project_id INT,
    IN team_id INT
)
BEGIN
    UPDATE projects 
    SET team_id = team_id, updated_at = CURRENT_TIMESTAMP
    WHERE id = project_id;
END //

DELIMITER ;

-- ============================================
-- TRIGGERS (Optional - for automatic updates)
-- ============================================

DELIMITER //

-- Trigger: Update completed_at when task status changes to 'done'
CREATE TRIGGER IF NOT EXISTS task_completed_trigger
BEFORE UPDATE ON tasks
FOR EACH ROW
BEGIN
    IF NEW.status = 'done' AND OLD.status != 'done' THEN
        SET NEW.completed_at = CURRENT_TIMESTAMP;
    END IF;
    IF NEW.status != 'done' THEN
        SET NEW.completed_at = NULL;
    END IF;
END //

DELIMITER ;

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================

-- Notes:
-- 1. All passwords are hashed using bcrypt (PHP password_hash function)
-- 2. Default password for sample users: 'password'
-- 3. Foreign keys use ON DELETE CASCADE or SET NULL as appropriate
-- 4. Indexes are added for frequently queried columns
-- 5. All timestamps are automatically managed
-- 6. Views and stored procedures are optional but helpful for complex queries
