# NextWave Task Backend API

A comprehensive Laravel Lumen backend API for user and task management with JWT authentication, soft deletes, and comprehensive error handling.

## Features

- ✅ **JWT Authentication** - Secure token-based authentication
- ✅ **User Management** - Complete CRUD operations for users
- ✅ **Task Management** - Complete CRUD operations for tasks
- ✅ **Soft Deletes** - Data safety with soft delete functionality
- ✅ **API Logging** - Middleware to log all API requests
- ✅ **CORS Support** - Cross-origin request handling
- ✅ **Standardized Responses** - Consistent API response format
- ✅ **Validation** - Comprehensive input validation
- ✅ **Error Handling** - Thorough error handling for all operations
- ✅ **Unit Tests** - Basic test coverage for authentication

## Requirements

- PHP 8.1+
- MySQL 5.7+
- Composer
- Laravel Lumen 10.x

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   # Copy .env.example to .env (if not exists)
   cp .env.example .env
   ```

4. **Configure database**
   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nextwave_task
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Generate JWT secret**
   ```bash
   # Add this to your .env file
   JWT_SECRET=1e74030680a6be8ec426219618ac293b45015e6c03c57ba2125c3a5de7bebf50
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Start the server**
   ```bash
   php -S localhost:8000 -t public
   ```

## API Endpoints

### Authentication Endpoints

#### Register User
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Get User Profile
```http
GET /api/me
Authorization: Bearer {token}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

#### Refresh Token
```http
POST /api/refresh
Authorization: Bearer {token}
```

### User Management Endpoints

#### Get All Users
```http
GET /api/users
Authorization: Bearer {token}
```

#### Create User
```http
POST /api/users
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123",
    "role": "user"
}
```

#### Get User by ID
```http
GET /api/users/{id}
Authorization: Bearer {token}
```

#### Update User
```http
PUT /api/users/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Name",
    "email": "updated@example.com",
    "role": "admin"
}
```

#### Delete User
```http
DELETE /api/users/{id}
Authorization: Bearer {token}
```

### Task Management Endpoints

#### Get User Tasks
```http
GET /api/tasks
Authorization: Bearer {token}
```

#### Create Task
```http
POST /api/tasks
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Complete Project",
    "description": "Finish the project documentation",
    "priority": "high",
    "due_date": "2024-01-15"
}
```

#### Get Task by ID
```http
GET /api/tasks/{id}
Authorization: Bearer {token}
```

#### Update Task
```http
PUT /api/tasks/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Updated Task Title",
    "description": "Updated description",
    "status": "in_progress",
    "priority": "medium"
}
```

#### Update Task Status
```http
PATCH /api/tasks/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "completed"
}
```

#### Delete Task
```http
DELETE /api/tasks/{id}
Authorization: Bearer {token}
```

#### Get Tasks for Specific User
```http
GET /api/users/{id}/tasks
Authorization: Bearer {token}
```

## Response Format

All API responses follow a standardized format:

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        // Validation errors (if any)
    }
}
```

## Data Models

### User Model
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `role` - User role (admin/user)
- `is_active` - Account status
- `email_verified_at` - Email verification timestamp
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp
- `deleted_at` - Soft delete timestamp

### Task Model
- `id` - Primary key
- `user_id` - Foreign key to users table
- `title` - Task title
- `description` - Task description
- `status` - Task status (pending/in_progress/completed/cancelled)
- `priority` - Task priority (low/medium/high/urgent)
- `due_date` - Task due date
- `completed_at` - Completion timestamp
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp
- `deleted_at` - Soft delete timestamp

## Testing

### Run Unit Tests
```bash
php artisan test
```

### Manual API Testing

You can test the API using curl or any API client like Postman:

1. **Register a new user:**
   ```bash
   curl -X POST http://localhost:8000/api/register \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Test User",
       "email": "test@example.com",
       "password": "password123",
       "password_confirmation": "password123"
     }'
   ```

2. **Login:**
   ```bash
   curl -X POST http://localhost:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{
       "email": "test@example.com",
       "password": "password123"
     }'
   ```

3. **Create a task (use token from login):**
   ```bash
   curl -X POST http://localhost:8000/api/tasks \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN_HERE" \
     -d '{
       "title": "Test Task",
       "description": "This is a test task",
       "priority": "high"
     }'
   ```

## Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── UserController.php
│   │   │   └── TaskController.php
│   │   └── Middleware/
│   │       ├── ApiLoggingMiddleware.php
│   │       └── CorsMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Task.php
│   └── Traits/
│       └── ApiResponse.php
├── config/
│   ├── auth.php
│   └── jwt.php
├── database/
│   └── migrations/
│       ├── create_users_table.php
│       └── create_tasks_table.php
├── routes/
│   └── web.php
├── tests/
│   └── AuthTest.php
└── bootstrap/
    └── app.php
```

## Security Features

- JWT token-based authentication
- Password hashing using bcrypt
- Input validation and sanitization
- CORS protection
- Soft deletes for data safety
- Role-based access control

## Performance Features

- API request logging with timestamps
- Database query optimization
- Pagination for large datasets
- Efficient relationship loading

## Error Handling

The API includes comprehensive error handling for:
- Validation errors (422)
- Authentication errors (401)
- Authorization errors (403)
- Not found errors (404)
- Server errors (500)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This project is licensed under the MIT License.
