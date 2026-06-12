# School Management System 

# Features

**Student Management** - Register, track, and manage student information with photos

**Staff Management** - Manage teachers and administrative staff

**Class Management** - Manage Grades 1-12 with multiple streams

**Subject Management** - Core and elective subjects with department allocation

**Fee Structure** - Configure term-based fee structures with payment plans

**Invoice System** - Auto-generated invoices with balance tracking

**Payment Processing** - M-Pesa, bank transfer, cash, cheque, and card payments

**Receipt Generation** - Auto-generated PDF receipts with email notifications

**Result Management** - Record and print student results with grade calculation

Reporting - Print fee structures, invoices, and receipts

Parent Portal - Parent/guardian association with students

# Requirements
PHP >= 8.2

Composer

Node.js & NPM (for asset compilation, optional)

SQLite (development) or PostgreSQL/MySQL (production)

Web server (Apache/Nginx) or PHP artisan serve

# Installation Guide

Step 1: Clone the Repository

cd smsv2

Step 2: Install PHP Dependencies

composer install

Step 3: Install NPM Dependencies (Optional - for custom CSS/JS)

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
- DB_CONNECTION=pgsql
- DB_HOST=127.0.0.1
- DB_PORT=5432
- DB_DATABASE=smsv2
- DB_USERNAME=your_username
- DB_PASSWORD=your_password

For MySQL
- DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_DATABASE=smsv2
- DB_USERNAME=root
- DB_PASSWORD=your_password

Mail Configuration (for payment receipts)
- MAIL_MAILER=smtp
- MAIL_HOST=smtp.gmailcom
- MAIL_PORT=587
- MAIL_USERNAME=your_email@gmailcom
- MAIL_PASSWORD=your_app_password
- MAIL_ENCRYPTION=tls
- MAIL_FROM_ADDRESS=noreply@schoolcom
- MAIL_FROM_NAME="School Management System"

Step 5: Generate Application Key
bash
php artisan key:generate

Step 6: Create Database

- For SQLite:

Create the SQLite database file
touch database/smsv2.sqlite

- For PostgreSQL/MySQL:
Create the database manually using your database management tool or command line.

Step 7: Run Migrations

php artisan migrate

Step 8: Seed the Database

php artisan db:seed

This will create:
- Academic years
- Departments 
- Classes 
- Subjects
- Staff members
- Students
- Parents/Guardians
- Student-Parent relationships
- Fee structures
- Invoices
- Payments

Step 9: Create Admin User

php artisan make:filament-user
  
Follow the prompts:

- Name: Administrator

- Email: admin@schoolcom (or your preferred email)

- Password: password (or your preferred password)

Step 10: Create Storage Link (for photos and receipts)

 php artisan storage:link

Step 11: Start the Development Server

 php artisan serve

Step 12: Run Queue Worker (for email notifications)

php artisan queue:work

Access the System
Open your browser and navigate to: http//localhost:8000

You'll be redirected to the Filament admin panel

Login with the credentials you created:

text
Email: admin@schoolcom
Password: password

# System Modules

**1. Students Module**

  - View all students with photos

  - Add/Edit/Delete students

  - Search and filter by class, status, gender

  - Bulk actions (change status, change class)

  - View student details

**2. Staff Module**

- Manage teachers and admin staff

- Track employment details

- Assign departments and subjects

- Emergency contact information

**3. Classes Module**

- Manage Grades

- Set class capacity

- Assign class teachers

- Track enrollment

**4. Fee Structure Module**

- Configure fees per grade

- Set term-based payment plans

- Print individual or bulk fee structures

- Activate/Deactivate fee structures

**5. Invoice Module**

- Auto-generated invoice numbers

- Term-based fee splitting

- Track payments and balances

- Print invoices

- Overdue invoice detection

**6. Payment Module**

- Multiple payment methods (M-Pesa, Bank, Cash, Cheque, Card)

- Auto-generated receipt numbers

- PDF receipt generation

- Email receipts to parents

- Link payments to invoices or record general payments

- Parent/guardian filtering by student

**7. Results Module**

- Record student exam results

- Auto-calculate percentages and grades

- Print result slips

- Bulk result entry (CSV upload)

# Printing Features

**Fee Structures:**

- Print single fee structure

- Print all fee structures

- Print selected fee structures (bulk)

**Invoices:**

- Print individual invoices

**Receipts:**

- Print payment receipts (auto-generated PDF)

**Result Slips:**

- Print individual result slips

 Email Notifications
 
When a payment is marked as "Completed", the system automatically:

Generates a PDF receipt

Sends an email to the parent/guardian

Attaches the PDF receipt to the email

# Troubleshooting

**Database Issues**
bash
Reset database (will delete all data)
php artisan migrate:fresh --seed

**Clear migration cache**
php artisan optimize:clear
composer dump-autoload

**Storage Link Issues**
bash
Recreate storage link
php artisan storage:link

**Permission Issues (Linux/Mac)**
bash
chmod -R 775 storage bootstrap/cache
chmod 666 database/smsv2.sqlite

**Queue Worker Not Running**
bash
Start queue worker in development
php artisan queue:work --once

Or run in background
php artisan queue:work &

# Testing
Access different modules after login:

- Students: http//localhost:8000/admin/students

- Staff: http//localhost:8000/admin/staff

- Fee Structures: http//localhost:8000/admin/fee-structures

- Invoices: http//localhost:8000/admin/invoices

- Payments: http//localhost:8000/admin/payments

- Results: http//localhost:8000/admin/results


# License
This project is licensed under the MIT License.

# Support
For issues or questions:

Check the Troubleshooting section above

Run php artisan optimize:clear to clear cache

Ensure all migrations are run: php artisan migrate:status

Check Laravel logs: storage/logs/laravel.log

 Quick Start Summary
 
**1. Clone repository**
git clone https://github.com/wamwagii/smsv2.git
cd smsv2

**2. Install dependencies**
composer install

**3. Configure environment**
cp .env.example .env
php artisan key:generate

**4. Setup database**
touch database/smsv2.sqlite  # For SQLite
php artisan migrate
php artisan db:seed

**5. Create admin user**
php artisan make:filament-user

**6. Create storage link***
php artisan storage:link

**7. Start server**
php artisan serve

**8. Start queue worker** (for emails)
php artisan queue:work
Access the admin panel at http//localhost:8000 and login with your credentials.


# Technical Support
- This Software is Free and Open Source, Without any Warranty.
- Even if the software is free, Technical Support is NOT free.

# Service	Price
- Technical Support (per issue)	$50 USD
- Contact for Technical Support
- If you need paid technical support, contact me on Telegram:

<p align="start"> <a href="https://t.me/wamwagii" target="_blank"> <img src="https://img.shields.io/badge/Telegram-26A5E4?style=for-the-badge&logo=telegram&logoColor=white" alt="Telegram"> </a> </p>

# Free Support

You can ask questions for free at:

GitHub Discussions page

Telegram Group (link available on request)

Note: Please use free channels for general questions and troubleshooting.

# Paid support is only for:
- Custom feature development
- Server deployment assistance
- Bug fixing within your specific environment
- Urgent production issues
- Integration with third-party services
