markdown
# 🏪 Multi-Tenant Point of Sale (POS) System

A robust, secure, multi-tenant Point of Sale system built with Laravel, Filament, and PostgreSQL with Row-Level Security (RLS).

![Laravel](https://img.shields.io/badge/Laravel-13.x-red.svg)
![Filament](https://img.shields.io/badge/Filament-5.x-blue.svg)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16.x-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.5.x-purple.svg)

## 📋 Overview

This POS system is designed for businesses operating across multiple countries and currencies. It supports various business types including:
-Sports Clubs
-Supermarkets
-Pharmacies/Chemists
-Fast Food Restaurants

### Key Features

- **Row-Level Security (RLS)**: Database-level tenant isolation using PostgreSQL RLS
- **Multi-Currency Support**: KES, USD, GBP, ZAR, UGX, TZS, RWF
- **Multi-Tenant**: Complete isolation between tenants (clubs, supermarkets, etc.)
- **Role-Based Access**: Admin, Supervisor, Cashier roles with granular permissions
- **Cash Payment Processing**: With change calculation and denomination tracking
- **Supervisor Authorization**: PIN-based authorization for product removal
- **Audit Trail**: Complete logging of all supervisor authorizations
- **Inventory Management**: Stock tracking with low stock alerts
- **Receipt Generation**: Automatic receipt numbering and printing
- **Cash Drawer Management**: Track cash movements and reconciliation

## Tech Stack

- **Backend**: Laravel 13.x
- **Admin Panel**: Filament 5.6
- **Database**: PostgreSQL 16.x with RLS
- **Authentication**: Laravel Auth with Filament
- **UI**: Livewire + Tailwind CSS
- **Payment Processing**: Cash, Card, M-PESA, Bank Transfer

## Installation

### Prerequisites

- PHP 8.5+
- Composer
- PostgreSQL 16+
- Node.js & NPM (for frontend assets)

### Step 1: Clone the Repository


git clone https://github.com/wamwagii/pos_ww.git
cd pos_ww

- Step 2: Install Dependencies

composer install
npm install

- Step 3: Environment Configuration

cp .env.example .env
Update your .env file:

env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=multitenant_pos
DB_USERNAME=app_user
DB_PASSWORD=SecurePassword123!

- Step 4: Database Setup
The system uses PostgreSQL with Row-Level Security.


 **Connect to PostgreSQL**
psql -U postgres

 **Run the migrations**
php artisan migrate

- Step 5: Create Admin User

php artisan tinker
php
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

User::create([
    'id' => (string) Str::uuid(),
    'tenant_id' => '11111111-1111-1111-1111-111111111111',
    'email' => 'admin@example.com',
    'password_hash' => Hash::make('password123'),
    'first_name' => 'Admin',
    'last_name' => 'User',
    'role' => 'admin',
    'is_active' => true,
]);

- Step 6: Publish Assets

php artisan filament:assets

npm run build

- Step 7: Start the Application

php artisan serve

Access the application at http://localhost:8000/pos

**Row-Level Security (RLS)**

Every table has tenant_id and RLS policies ensuring tenants can only access their own data:

sql

CREATE POLICY tenant_isolation_select ON products
    FOR SELECT
    USING (tenant_id = current_setting('app.current_tenant')::UUID);

User Roles

**Role**	**Permissions**

- Admin -> Full access, manage tenants, users, products, and settings

- Supervisor ->	Manage products, approve product removals, view reports

- Cashier -> Process orders, view products, basic sales operations

**Security Features**

- Row-Level Security at the database level

- Tenant isolation enforced by PostgreSQL

- Supervisor PIN authorization for critical actions

- Complete audit trail for all supervisor actions

- Password hashing with Bcrypt

- Session management with PostgreSQL

- CSRF protection

- XSS prevention

**Key Workflows**

- Creating an Order

Cashier selects products and adds to cart

Customer pays (cash, card, M-PESA, etc.)

System calculates total, tax, and change

Receipt is generated and printed

Inventory is automatically updated

- Removing a Product (Supervisor Required)

Cashier attempts to remove a product from an order

Supervisor enters their PIN code

System validates the supervisor's identity

Product is marked as removed

Authorization is logged for audit

- Configuration

Tenant Context
The system uses PostgreSQL session variables for tenant isolation:

php
// Set tenant context
DB::statement("SELECT set_config('app.current_tenant', ?, true)", [$tenantId]);

// Set user context
DB::statement("SELECT set_config('app.current_user_id', ?, true)", [$userId]);
Middleware
php
// app/Http/Middleware/SetTenantDatabaseContext.php
public function handle(Request $request, Closure $next)
{
    $tenant = Filament::getTenant();
    if ($tenant) {
        DB::statement("SELECT set_config('app.current_tenant', ?, true)", [$tenant->getKey()]);
    }
    return $next($request);
}

- Database Schema
Key Tables
tenants
Column	Type	Description
id	UUID	Primary key
name	VARCHAR	Tenant name
domain	VARCHAR	Unique domain
country	VARCHAR	Country
currency_code	VARCHAR(3)	Default currency
is_active	BOOLEAN	Active status
users
Column	Type	Description
id	UUID	Primary key
tenant_id	UUID	Foreign key to tenants
email	VARCHAR	Login email
password_hash	VARCHAR	Hashed password
role	VARCHAR	admin/supervisor/cashier
pin_code	VARCHAR(6)	Supervisor PIN
orders
Column	Type	Description
id	UUID	Primary key
tenant_id	UUID	Foreign key to tenants
order_number	VARCHAR	Unique order number
total_amount	DECIMAL	Order total
payment_method	VARCHAR	Cash/Card/M-PESA
amount_tendered	DECIMAL	Cash received
change_due	DECIMAL	Change to return


- Testing

**Run all tests**
php artisan test

**Run specific test suite**
php artisan test --testsuite=Feature

- Deployment

Database Migration

php artisan migrate --force


Optimize for Production

php artisan optimize
php artisan view:cache
php artisan config:cache
php artisan route:cache



**License**
This project is licensed under the MIT License - see the LICENSE file for details.


