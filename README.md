# School Management System - Complete Setup Guide

# System Overview
A comprehensive school management system built with Laravel 13, Filament 5.6, and PostgreSQL/SQLite. This system manages students, staff, classes, subjects, fee structures, invoices, payments, results, and more.

 # Features
Student Management - Register, track, and manage student information with photos

Staff Management - Manage teachers and administrative staff

Class Management - Manage Grades 1-12 with multiple streams

Subject Management - Core and elective subjects with department allocation

Fee Structure - Configure term-based fee structures with payment plans

Invoice System - Auto-generated invoices with balance tracking

Payment Processing - M-Pesa, bank transfer, cash, cheque, and card payments

Receipt Generation - Auto-generated PDF receipts with email notifications

Result Management - Record and print student results with grade calculation

Reporting - Print fee structures, invoices, and receipts

Parent Portal - Parent/guardian association with students

# Requirements
PHP >= 8.2

Composer

Node.js & NPM (for asset compilation, optional)

SQLite (development) or PostgreSQL/MySQL (production)

Web server (Apache/Nginx) or PHP artisan serve

# Installation Guide
# Step 1: Clone the Repository
bash
git clone https://github.com/wamwagii/smsv2.git
cd smsv2
# Step 2: Install PHP Dependencies
bash
composer install
# Step 3: Install NPM Dependencies (Optional - for custom CSS/JS)
bash
npm install
npm run build
# Step 4: Environment Configuration
Copy the example environment file and configure your database:

bash
cp .env.example .env
Edit the .env file and update the database settings:

env
# For SQLite (Development)
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database/smsv2.sqlite

# For PostgreSQL (Production)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smsv2
DB_USERNAME=your_username
DB_PASSWORD=your_password

# For MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smsv2
DB_USERNAME=root
DB_PASSWORD=your_password

# Mail Configuration (for payment receipts)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@school.com
MAIL_FROM_NAME="School Management System"
# Step 5: Generate Application Key
bash
php artisan key:generate
# Step 6: Create Database
For SQLite:

bash
# Create the SQLite database file
touch database/smsv2.sqlite
For PostgreSQL/MySQL:
Create the database manually using your database management tool or command line.

# Step 7: Run Migrations
bash
php artisan migrate
# Step 8: Seed the Database
bash
php artisan db:seed
This will create:

Academic years (2024, 2025, 2026)

Departments (8 departments)

Classes (Grades 1-12)

Subjects (20+ subjects)

Staff members (8 staff)

Students (160+ students)

Parents/Guardians (8 parents)

Student-Parent relationships

Fee structures (12 structures)

Invoices (500+ invoices)

Payments (200+ payments)

# Step 9: Create Admin User
bash
php artisan make:filament-user
Follow the prompts:

Name: Administrator

Email: admin@school.com (or your preferred email)

Password: password (or your preferred password)

# Step 10: Create Storage Link (for photos and receipts)
bash
php artisan storage:link
# Step 11: Start the Development Server
bash
php artisan serve
# Step 12: Run Queue Worker (for email notifications)
bash
# In a separate terminal window
php artisan queue:work
🚪 Access the System

Open your browser and navigate to: http://localhost:8000

You'll be redirected to the Filament admin panel

Login with the credentials you created:

text
Email: admin@school.com
Password: password
