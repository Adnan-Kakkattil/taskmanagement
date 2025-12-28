<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $pdo = getDBConnection();
    
    switch ($action) {
        case 'create_team':
            requireLogin();
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $userId = getCurrentUserId();
            
            $stmt = $pdo->prepare("INSERT INTO teams (name, description, created_by) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $userId]);
            $teamId = $pdo->lastInsertId();
            
            // Add creator as team leader
            $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, 'leader')");
            $stmt->execute([$teamId, $userId]);
            
            echo json_encode(['success' => true, 'team_id' => $teamId, 'message' => 'Team created successfully']);
            break;
            
        case 'assign_member_to_team':
            requireLogin();
            $teamId = $_POST['team_id'] ?? 0;
            $userId = $_POST['user_id'] ?? 0;
            $role = $_POST['role'] ?? 'member';
            
            $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE role = ?");
            $stmt->execute([$teamId, $userId, $role, $role]);
            
            echo json_encode(['success' => true, 'message' => 'Member assigned to team successfully']);
            break;
            
        case 'create_project':
            requireLogin();
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $teamId = $_POST['team_id'] ?? null;
            $status = $_POST['status'] ?? 'planning';
            $startDate = $_POST['start_date'] ?? null;
            $endDate = $_POST['end_date'] ?? null;
            $userId = getCurrentUserId();
            
            $stmt = $pdo->prepare("INSERT INTO projects (name, description, team_id, status, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $teamId, $status, $startDate, $endDate, $userId]);
            
            echo json_encode(['success' => true, 'message' => 'Project created successfully']);
            break;
            
        case 'assign_project_to_team':
            requireLogin();
            $projectId = $_POST['project_id'] ?? 0;
            $teamId = $_POST['team_id'] ?? 0;
            
            $stmt = $pdo->prepare("UPDATE projects SET team_id = ? WHERE id = ?");
            $stmt->execute([$teamId, $projectId]);
            
            echo json_encode(['success' => true, 'message' => 'Project assigned to team successfully']);
            break;
            
        case 'create_task':
            requireLogin();
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $projectId = $_POST['project_id'] ?? null;
            $assignedTo = $_POST['assigned_to'] ?? null;
            $status = $_POST['status'] ?? 'pending';
            $priority = $_POST['priority'] ?? 'medium';
            $dueDate = $_POST['due_date'] ?? null;
            $userId = getCurrentUserId();
            
            $stmt = $pdo->prepare("INSERT INTO tasks (name, description, project_id, assigned_to, status, priority, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $projectId, $assignedTo, $status, $priority, $dueDate, $userId]);
            
            echo json_encode(['success' => true, 'message' => 'Task created successfully']);
            break;
            
        case 'assign_task_to_member':
            requireLogin();
            $taskId = $_POST['task_id'] ?? 0;
            $userId = $_POST['user_id'] ?? 0;
            
            $stmt = $pdo->prepare("UPDATE tasks SET assigned_to = ? WHERE id = ?");
            $stmt->execute([$userId, $taskId]);
            
            echo json_encode(['success' => true, 'message' => 'Task assigned to member successfully']);
            break;
            
        case 'get_users':
            requireLogin();
            $users = getAllUsers();
            echo json_encode(['success' => true, 'users' => $users]);
            break;
            
        case 'get_teams':
            requireLogin();
            $teams = getAllTeams();
            echo json_encode(['success' => true, 'teams' => $teams]);
            break;
            
        case 'get_projects':
            requireLogin();
            $projects = getAllProjects();
            echo json_encode(['success' => true, 'projects' => $projects]);
            break;
            
        case 'get_team_members':
            requireLogin();
            $teamId = $_GET['team_id'] ?? 0;
            $members = getTeamMembers($teamId);
            echo json_encode(['success' => true, 'members' => $members]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

