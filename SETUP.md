# TaskFlow Setup Guide

## Database Setup

### 1. Create the .env file

Create a `.env` file in the root directory with your database credentials:

```env
# TaskFlow Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=taskflow_db

# Application Settings
APP_ENV=development
APP_DEBUG=true
```

**Note:** Update the values according to your database configuration:
- `DB_HOST`: Your database host (usually `localhost`)
- `DB_USER`: Your database username
- `DB_PASS`: Your database password (leave empty if no password)
- `DB_NAME`: Your database name

### 2. Create the Database

1. Open your MySQL/MariaDB command line or phpMyAdmin
2. Run the SQL script:
   ```bash
   mysql -u root -p < database.sql
   ```
   Or import `database.sql` through phpMyAdmin

### 3. Default Login Credentials

After running the database.sql, you can login with these test accounts:

- **Admin:**
  - Email: `admin@taskflow.com`
  - Password: `password`

- **Test Users:**
  - Email: `john@example.com`
  - Password: `password`
  - Email: `jane@example.com`
  - Password: `password`
  - Email: `alex@example.com`
  - Password: `password`

**Important:** Change these passwords in production!

### 4. File Structure

```
taskmanagement/
├── .env                 # Database credentials (create this)
├── .env.example         # Example .env file
├── config.php           # Configuration file (reads from .env)
├── api.php              # API endpoints
├── database.sql         # Database schema
├── index.php            # Landing page
├── login.php            # Login page
├── signup.php           # Signup page
├── dashboard.php        # Dashboard
├── tasks.php            # Tasks page
├── projects.php         # Projects page
├── team.php             # Team management
├── calender.php         # Calendar
└── profile.php          # User profile
```

## Features

### Team Management
- Create teams
- Assign members to teams
- Assign projects to teams

### Project Management
- Create projects
- Assign projects to teams
- View project details

### Task Management
- Create tasks
- Assign tasks to team members
- Set priority and status
- Set due dates

## API Endpoints

All API calls are handled through `api.php`:

- `create_team` - Create a new team
- `assign_member_to_team` - Assign user to team
- `create_project` - Create a new project
- `assign_project_to_team` - Assign project to team
- `create_task` - Create a new task
- `assign_task_to_member` - Assign task to user
- `get_users` - Get all users (for dropdowns)
- `get_teams` - Get all teams (for dropdowns)
- `get_projects` - Get all projects (for dropdowns)

## Troubleshooting

### Database Connection Error
- Check your `.env` file has correct credentials
- Ensure MySQL/MariaDB is running
- Verify the database `taskflow_db` exists

### Session Issues
- Make sure `config.php` is included in pages that need authentication
- Check PHP session settings in `php.ini`

### API Not Working
- Ensure `api.php` is accessible
- Check browser console for JavaScript errors
- Verify database connection is working

