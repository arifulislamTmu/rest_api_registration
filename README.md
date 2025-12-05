# REST API - User Registration with Email Notification

A Laravel REST API that handles user registration and sends welcome emails asynchronously using Gmail SMTP.

## Features

- ✅ RESTful API design with JSON responses
- ✅ User registration endpoint (`POST /api/register`)
- ✅ Asynchronous email sending using queues (non-blocking)
- ✅ Gmail SMTP integration for sending emails
- ✅ PostgreSQL database
- ✅ Comprehensive error handling and validation
- ✅ Well-commented code
- ✅ **Automated testing with PHPUnit (12 test cases)**

## Requirements

- PHP >= 8.1
- Composer
- PostgreSQL
- Laravel 10.x
- Gmail account with App Password enabled
- PHPUnit (included with Laravel)

## Installation & Setup

### 1. Clone the Repository

```bash
git clone <your-repo-url>
cd Rest_api_fresh
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy the `.env` file and update the following configurations:

#### Database Configuration (PostgreSQL)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rest_api_db
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

#### Mail Configuration (Gmail)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_gmail_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Important:** For Gmail, you need to generate an App Password:
1. Go to your Google Account settings
2. Enable 2-Step Verification
3. Go to Security → 2-Step Verification → App passwords
4. Generate a new app password for "Mail"
5. Use this password in `MAIL_PASSWORD`

#### Queue Configuration

```env
QUEUE_CONNECTION=database
```

### 4. Create PostgreSQL Database

```bash
# Connect to PostgreSQL
psql -U postgres

# Create the database
CREATE DATABASE rest_api_db;

# Exit
\q
```

### 5. Run Migrations

```bash
php artisan migrate
```

This will create:
- `users` table
- `jobs` table (for queue management)
- `failed_jobs` table
- Other Laravel default tables

### 6. Start the Queue Worker

The queue worker processes background jobs (email sending). Run this in a separate terminal:

```bash
php artisan queue:work
```

**Keep this terminal running** to process queued emails.

### 7. Start the Development Server

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`

## API Documentation

### Register User

Creates a new user account and sends a welcome email asynchronously.

**Endpoint:** `POST /api/register`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "User registered successfully. A welcome email has been sent.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "created_at": "2025-12-03T10:30:00.000000Z"
        }
    }
}
```

**Validation Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password field confirmation does not match."]
    }
}
```

**Server Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Registration failed",
    "error": "Error message details"
}
```

### Field Validations

- `name`: Required, string, max 255 characters
- `email`: Required, valid email format, unique in database, max 255 characters
- `password`: Required, min 8 characters, must be confirmed
- `password_confirmation`: Required, must match password

## Testing the API

### Manual Testing

#### Using cURL

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Using Postman

1. Create a new POST request
2. URL: `http://127.0.0.1:8000/api/register`
3. Headers:
   - `Content-Type`: `application/json`
   - `Accept`: `application/json`
4. Body (raw JSON):
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Automated Testing

The project includes comprehensive automated tests using PHPUnit to ensure code quality and reliability.

#### Run All Tests

```bash
php artisan test
```

#### Run Specific Test Suite

```bash
# Run only registration tests
php artisan test --filter=RegistrationTest

# Run with detailed output
php artisan test --filter=RegistrationTest --verbose
```

#### Test Coverage

The test suite includes **12 test cases** covering:

1. ✅ Successful registration with valid data
2. ✅ Registration fails with duplicate email
3. ✅ Registration fails with invalid email format
4. ✅ Registration fails with short password (< 8 characters)
5. ✅ Registration fails when password confirmation doesn't match
6. ✅ Registration fails with missing required fields
7. ✅ Registration fails with name exceeding 255 characters
8. ✅ Welcome email notification is queued after registration
9. ✅ Notification is sent to the correct user
10. ✅ Validation errors have correct JSON structure
11. ✅ User count increases after successful registration
12. ✅ Registration works with special characters in name

#### Test Results

```
Tests:    12 passed (45 assertions)
Duration: ~3-4 seconds
```

All tests validate:
- ✅ API response structure and status codes
- ✅ Database integrity and data persistence
- ✅ Validation rules enforcement
- ✅ Email notification queuing
- ✅ Error handling and messages

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           └── RegisterController.php    # Handles user registration
├── Models/
│   └── User.php                          # User model
└── Notifications/
    └── WelcomeEmailNotification.php      # Welcome email notification

routes/
└── api.php                               # API routes definition

database/
└── migrations/
    └── *_create_users_table.php          # User table migration

tests/
└── Feature/
    └── RegistrationTest.php              # Automated test suite (12 tests)
```

## How It Works

1. **User sends registration request** → API receives POST data
2. **Validation** → Checks if email is unique and password is confirmed
3. **User creation** → Creates user record in PostgreSQL database
4. **Queue notification** → Dispatches email job to queue (non-blocking)
5. **Immediate response** → Returns success response to user
6. **Background email** → Queue worker picks up job and sends email via Gmail

The email sending is asynchronous, so the API response is fast and doesn't wait for the email to be sent.

## Troubleshooting

### Emails Not Sending

1. Check queue worker is running: `php artisan queue:work`
2. Verify Gmail credentials in `.env`
3. Check `jobs` table for pending jobs: `SELECT * FROM jobs;`
4. Check `failed_jobs` table for errors: `SELECT * FROM failed_jobs;`
5. View Laravel logs: `storage/logs/laravel.log`

### Database Connection Issues

1. Verify PostgreSQL is running
2. Check database credentials in `.env`
3. Ensure database `rest_api_db` exists
4. Test connection: `php artisan migrate:status`

### Queue Issues

1. Clear failed jobs: `php artisan queue:flush`
2. Retry failed jobs: `php artisan queue:retry all`
3. Restart queue worker: Stop and restart `php artisan queue:work`

## Additional Commands

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# View routes
php artisan route:list

# Run in production with queue worker
php artisan queue:work --daemon

# Monitor queue in real-time
php artisan queue:listen
```

## Security Notes

- Never commit `.env` file to version control
- Use App Passwords for Gmail, not your actual password
- Enable 2-Factor Authentication on your Gmail account
- In production, use a proper queue driver like Redis or SQS
- Always validate and sanitize user input

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
