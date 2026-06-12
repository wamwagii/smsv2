# School Management System 

System Overview
A comprehensive school management system built with Laravel 13, Filament 5.6, and PostgreSQL/SQLite. This system manages students, staff, classes, subjects, fee structures, invoices, payments, results, and more.

 Features
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

Requirements
PHP >= 8.2

Composer

Node.js & NPM (for asset compilation, optional)

SQLite (development) or PostgreSQL/MySQL (production)

Web server (Apache/Nginx) or PHP artisan serve

# Installation Guide

Step 1: Clone the Repository
bash
git clone https://github.com/wamwagii/smsv2.git
cd smsv2

Step 2: Install PHP Dependencies
bash
composer install

Step 3: Install NPM Dependencies (Optional - for custom CSS/JS)
bash
npm install
npm run build

Step 4: Environment Configuration
Copy the example environment file and configure your database:

bash
cp .env.example .env
Edit the .env file and update the database settings:

# env
For SQLite (Development)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/smsv2.sqlite

For PostgreSQL (Production)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smsv2
DB_USERNAME=your_username
DB_PASSWORD=your_password

For MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smsv2
DB_USERNAME=root
DB_PASSWORD=your_password

Mail Configuration (for payment receipts)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@school.com
MAIL_FROM_NAME="smsv2"

Step 5: Generate Application Key
bash
php artisan key:generate

Step 6: Create Database
For SQLite:

bash
Create the SQLite database file
touch database/smsv2.sqlite
For PostgreSQL/MySQL:
Create the database manually using your database management tool or command line.

Step 7: Run Migrations
bash
php artisan migrate

Step 8: Seed the Database
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

Step 9: Create Admin User
bash
php artisan make:filament-user
Follow the prompts:

Name: Administrator

Email: admin@school.com (or your preferred email)

Password: password (or your preferred password)

Step 10: Create Storage Link (for photos and receipts)
bash
php artisan storage:link

Step 11: Start the Development Server
bash
php artisan serve

Step 12: Run Queue Worker (for email notifications)
bash
In a separate terminal window
php artisan queue:work


Open your browser and navigate to: http://localhost:8000

You'll be redirected to the Filament admin panel

Login with the credentials you created:

text
Email: admin@school.com
Password: password

# System Modules
1. Students Module
View all students with photos

Add/Edit/Delete students

Search and filter by class, status, gender

Bulk actions (change status, change class)

View student details

2. Staff Module
Manage teachers and admin staff

Track employment details

Assign departments and subjects

Emergency contact information

3. Classes Module
Manage Grades 1-12

Set class capacity

Assign class teachers

Track enrollment

4. Fee Structure Module
Configure fees per grade

Set term-based payment plans

Print individual or bulk fee structures

Activate/Deactivate fee structures

5. Invoice Module
Auto-generated invoice numbers

Term-based fee splitting

Track payments and balances

Print invoices

Overdue invoice detection

6. Payment Module
Multiple payment methods (M-Pesa, Bank, Cash, Cheque, Card)

Auto-generated receipt numbers

PDF receipt generation

Email receipts to parents

Link payments to invoices or record general payments

Parent/guardian filtering by student

7. Results Module
Record student exam results

Auto-calculate percentages and grades

Print result slips

Bulk result entry (CSV upload)
# Printing Features
Fee Structures:

Print single fee structure

Print all fee structures

Print selected fee structures (bulk)

Invoices:

Print individual invoices

Receipts:

Print payment receipts (auto-generated PDF)

Result Slips:

Print individual result slips

# Email Notifications
When a payment is marked as "Completed", the system automatically:

Generates a PDF receipt

Sends an email to the parent/guardian

Attaches the PDF receipt to the email

# Troubleshooting
Database Issues
bash
Reset database (will delete all data)
php artisan migrate:fresh --seed

Clear migration cache
php artisan optimize:clear
composer dump-autoload

Storage Link Issues
bash
Recreate storage link
php artisan storage:link

Permission Issues (Linux/Mac)
bash
chmod -R 775 storage bootstrap/cache
chmod 666 database/smsv2.sqlite
Queue Worker Not Running
bash
# Start queue worker in development
php artisan queue:work --once

Or run in background
php artisan queue:work &

# Testing
Access different modules after login:

Students: http://localhost:8000/admin/students

Staff: http://localhost:8000/admin/staff

Fee Structures: http://localhost:8000/admin/fee-structures

Invoices: http://localhost:8000/admin/invoices

Payments: http://localhost:8000/admin/payments

Results: http://localhost:8000/admin/results


# License
This project is licensed under the MIT License.

# Support
For issues or questions:

Check the Troubleshooting section above

Run php artisan optimize:clear to clear cache

Ensure all migrations are run: php artisan migrate:status

Check Laravel logs: storage/logs/laravel.log

Developed for Kenyan School Management - Supporting Grades 1-12 with M-Pesa integration, automated invoicing, and comprehensive reporting.

# Technical Support
This Software is Free and Open Source, Without any Warranty.
Even if the software is free, Technical Support is NOT free.

# Service	Price
Technical Support (per issue)	$50 USD



# Contact for Technical Support
If you need paid technical support, contact me on Telegram:

Telegram: (https://t.me/wamwagii )

# Free Support
You can ask questions for free at:

GitHub Discussions page

Telegram Group (link available on request)

Note: Please use free channels for general questions and troubleshooting.

# Paid support is only for:

Custom feature development

Server deployment assistance

Bug fixing within your specific environment

Urgent production issues

Integration with third-party services
