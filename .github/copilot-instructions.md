# Akuntha Project - AI Coding Agent Instructions

## Project Overview
**Akuntha** is a Laravel 8 web application for enterprise resource planning, specializing in attendance tracking, product management, customer management, and manufacturing workflows. The system uses role-based access control (RBAC) via Spatie Permissions and features both admin and customer portals.

## Architecture & Key Components

### Core Tech Stack
- **Framework**: Laravel 8.75
- **Language**: PHP 7.3+ / 8.0+
- **Database**: MySQL (via `config/database.php`)
- **Authentication**: Laravel Sanctum + custom role-based middleware
- **Authorization**: Spatie Laravel Permission (v5.11)
- **Data Export**: Maatwebsite Excel (v3.1)
- **PDF Generation**: DomPDF (v2.2)
- **Payment Integration**: Stripe (v10.21)
- **File Storage**: Local filesystem (configured in `config/filesystems.php`)

### Directory Structure
```
app/
  ├─ Http/
  │  ├─ Controllers/
  │  │  ├─ backend/          # Admin/staff controllers (50+ controllers)
  │  │  └─ frontend/         # Customer-facing controllers
  │  ├─ Middleware/          # Custom middleware (AdminMiddleware, CustomerMiddleware)
  │  ├─ Requests/            # Form request validation classes
  │  └─ Kernel.php           # HTTP middleware registration
  ├─ Models/                 # 50+ Eloquent models (Attendance, Product, Customer, etc.)
  ├─ Events/                 # ClockInOutNotification (broadcasts to 'admin-notifications' channel)
  ├─ Notifications/          # ClockInOutDBNotification (stores in DB)
  ├─ Helpers/
  │  ├─ helpers.php          # Global utility functions (auto-loaded)
  │  └─ LocationHelper.php    # Geographic coordinate utilities (auto-loaded)
  └─ Exports/                # AttendanceExport (Maatwebsite Excel exporter)
database/
  ├─ migrations/             # Schema definitions (70+ migrations)
  └─ seeders/                # CreateAdminUserSeeder, PermissionTableSeeder
resources/
  ├─ views/
  │  ├─ backend/pages/       # Admin dashboard and management pages
  │  └─ frontend/auth/       # Login and authentication views
  └─ css/, js/, lang/        # Styling and localization
```

## Critical Patterns & Conventions

### 1. **Namespace Inconsistency Warning** ⚠️
Backend controllers use **inconsistent casing**:
- `AttendanceController.php` uses `namespace App\Http\Controllers\Backend;` (capital B)
- Most others use `namespace App\Http\Controllers\backend;` (lowercase b)

**Action**: When creating new backend controllers, use **lowercase `backend`** to match the majority. When importing, check the actual file to determine the correct namespace.

### 2. **Role-Based Access Control (RBAC)**
Authorization uses **Spatie Permission** with role/permission matrices:
- **Roles**: Admin, Staff, Customer (defined in seeders)
- **Permissions**: Role-based (e.g., `add_role`, `delete_role`, `edit_role`, `show_role`)
- **Middleware chains**: 
  ```php
  Route::middleware(['auth', 'role:admin'])->group(/* ... */);
  Route::middleware(['auth', 'isAdmin'])->group(/* ... */);  // Custom middleware
  ```

**Models**: `User` model uses `HasRoles` trait from Spatie. Always check user roles before sensitive operations.

### 3. **Attendance Tracking System**
The `Attendance` model tracks clock-in/clock-out with rich metadata:
- **Timestamps**: `clock_in_at`, `clock_out_at` (nullable)
- **Media**: `clock_in_photo`, `clock_out_photo` (base64 stored as paths)
- **Network Data**: `clock_in_ip`, `clock_out_ip` (IPv6-safe, 45 chars)
- **Location**: `clock_in_lat`, `clock_in_lng`, `clock_out_lat`, `clock_out_lng` (decimal, 10,7)
- **User Agent**: Captured for audit trails

**Key Methods in AttendanceController**:
- `clockIn()` – Stores attendance with photo via base64 decoding (`storeBase64Image()`)
- `clockOut()` – Ends shift, triggers `ClockInOutNotification` event
- Broadcasts to 'admin-notifications' private channel using Laravel Events

### 4. **Event-Driven Notifications**
Two parallel notification systems:
1. **Broadcasting Events** (`app/Events/ClockInOutNotification.php`):
   - Broadcasts to `private:admin-notifications` channel
   - Method name: `broadcastAs()` returns `'clock.in.out'` (critical for client-side listeners)
   
2. **Database Notifications** (`app/Notifications/ClockInOutDBNotification.php`):
   - Stores in `notifications` table (UUID primary key)
   - Fetched via `Auth::user()->notifications`

3. **Push Notifications** (Browser Push - NEW):
   - `app/Notifications/PushNotification.php` - Notification class
   - `app/Notifications/Channels/PushChannel.php` - Custom notification channel
   - `public/service-worker.js` - Service worker for handling browser notifications
   - `app/Models/PushSubscription.php` - Stores user subscriptions
   - Works even when browser tab is closed (requires user permission)
   - Enable/disable via `/push/settings` page

**When implementing real-time features**: Use all three – broadcast for instant UI updates, DB notifications for persistence, push for browser alerts.

**Sending push notifications**:
```php
use App\Notifications\PushNotification;

$user->notify(new PushNotification(
    title: 'Clock In Alert',
    body: 'User has clocked in',
    data: ['user_id' => 1, 'type' => 'attendance']
));
```

### 5. **Data Export Pipeline**
- **Class**: `app/Exports/AttendanceExport.php` (implements Maatwebsite contract)
- **Usage**: Invoked in reports/dashboards via `Excel::download(new AttendanceExport(), 'file.xlsx')`
- **Pattern**: Create new Export classes for each major entity (Customer, Product, etc.)

### 6. **Helper Functions**
Auto-loaded helpers (defined in `composer.json` `autoload.files`):
- `app/Helpers/helpers.php` – Global utilities (e.g., date formatting, currency conversion)
- `app/Helpers/LocationHelper.php` – Geo-distance calculations, coordinate utilities

**Usage**: Call directly without imports: `getDistance($lat1, $lng1, $lat2, $lng2)`

### 7. **Database Relationships**
- **User → Attendance**: One-to-many via `user_id` foreign key
- **User → Roles**: Many-to-many via `model_has_roles` (Spatie-managed)
- **Roles → Permissions**: Many-to-many via `role_has_permissions` (Spatie-managed)

**Always use Eloquent relationships**: 
```php
$user->attendance()->where('created_at', now()->toDateString())->first();
$user->roles()->pluck('name');
```

### 8. **Controller Patterns**
Backend controllers extend `BaseController` with:
- Constructor middleware: `$this->middleware('auth');`
- Role checks: `if ($user->role === 'admin') { ... }`
- Response patterns: JSON for AJAX, views for web

**No service layer observed** – business logic typically in controllers. Keep methods focused and extract complex logic to private methods within the controller.

## Developer Workflows

### Running the Application
```bash
# Start development server
php artisan serve  # Runs on http://localhost:8000

# Run migrations (first-time setup)
php artisan migrate
php artisan db:seed --class=CreateAdminUserSeeder
php artisan db:seed --class=PermissionTableSeeder

# Clear cached config (after .env changes)
php artisan config:clear
```

### Database Operations
```bash
# Create migration
php artisan make:migration create_tablename_table

# Rollback & re-migrate (dangerous in production!)
php artisan migrate:rollback
php artisan migrate:refresh --seed

# Debug database
php artisan tinker  # Interactive REPL for testing queries
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AttendanceTest.php
```

### Build Assets
```bash
# Compile CSS/JS using Laravel Mix
npm run dev      # Development build
npm run prod     # Production build (minified)
npm run watch    # Watch for changes
```

## Integration Points & External Dependencies

### 1. **Stripe Integration** (Payment Processing)
- Config: `config/services.php` (API keys from `.env`)
- Usage: For subscription/payment features (referenced in models but implementation details in backend)
- **Action when implementing**: Verify Stripe keys in `.env`, use SDK via `Stripe\Stripe::setApiKey()`

### 2. **Broadcasting** (Real-Time Notifications)
- Config: `config/broadcasting.php`
- Driver: Likely Pusher (via `pusher-php-server` ^7.2 in composer.json)
- **Action**: Ensure Pusher credentials in `.env` for event broadcasting to work

### 3. **Mail Service**
- Config: `config/mail.php`
- Uses Laravel's mail facade for notifications (password resets, alerts)
- **Channels**: SMTP (configured in `.env` via `MAIL_*` variables)

### 4. **Permission Caching**
- Spatie caches permissions by default
- **Critical**: After role/permission changes, must clear cache:
  ```php
  app[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
  ```

## Testing Patterns

No unit test examples found in codebase. When creating tests:
1. Follow PSR-4 namespace: `Tests\Feature\FeatureName` or `Tests\Unit\ClassName`
2. Use Laravel's `TestCase` from `tests/TestCase.php`
3. Common test helpers:
   ```php
   $this->actingAs($user)                    // Auth as user
   $this->assertDatabaseHas('table', [...])  // DB assertions
   ```

## Common Gotchas & Debugging Tips

1. **Namespace case matters**: Controllers in `backend/` folder use either casing – check imports carefully
2. **Permission not working?** Clear the permission cache: `php artisan cache:clear`
3. **Base64 images failing**: Ensure file storage is writable: `chmod -R 775 storage/`
4. **Migrations won't run**: Check if migration files are in the right folder and follow naming: `YYYY_MM_DD_HHMMSS_description.php`
5. **Events not broadcasting**: Verify Pusher config in `.env` and that queue driver is set (check `config/queue.php`)

## Key Files to Review

- `routes/web.php` – All web routes and their middleware/auth requirements
- `app/Http/Kernel.php` – Global middleware stack and route middleware aliases
- `config/permission.php` – Spatie Permission configuration
- `app/Models/User.php` – User authentication model
- `database/migrations/` – Latest migration shows active schema
- `.env.example` – Environment variable requirements (check against actual `.env`)

---

**Last Updated**: January 2, 2025 | **Laravel Version**: 8.75 | **PHP Version**: 7.3+ / 8.0+
