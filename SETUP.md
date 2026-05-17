# 🚀 Expenseflow Quick Setup Guide

This guide contains the exact sequence of commands to spin up Expenseflow locally from scratch in under two minutes.

## Prerequisites
Ensure the following are installed and active on your system:
- **PHP** (v8.2 or newer)
- **Node.js & NPM**
- **Composer**
- A local **MySQL server** (e.g., XAMPP, Herd, Laravel Valet)

## Database Creation
Create an empty local MySQL database mapped to your app. For example, name it: `spendwise_dummy`.

---

## Command Sequence

### Step 1: Clone & Navigate
Pull the code directly into your local machine and enter the directory.
```bash
git clone https://github.com/your-username/expenseflow.git
cd expenseflow
```

### Step 2: Core Dependencies
Install backend PHP libraries and compile frontend styles/JS via Vite.
```bash
composer install
npm install
npm run build
```

### Step 3: Environment Setup
Duplicate the example environment file and generate a unique application encryption key.
```bash
cp .env.example .env
php artisan key:generate
```
> [!IMPORTANT]
> **Manual Action Required:**
> Open the newly created `.env` file in your editor and configure your database credentials ensuring they map to your local setup:
> `DB_DATABASE=spendwise_dummy`
> `DB_USERNAME=root`
> `DB_PASSWORD=`

### Step 4: Architect Data & Build Universe
Run the core migrations. Using the `--seed` flag will automatically inject the complete pre-mapped system including test users, random categorized expenses, collaborative Groups, active Alert Rules, and historical budgets.
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

### Step 5: Run the Engines!
Boot up the PHP development server.
```bash
php artisan serve
```

---

## 🔑 Login Credentials

The application is now live at `http://127.0.0.1:8000`. You can test multi-user group synchronization immediately using the pre-built seeded accounts:

- **Admin Account**: 
  - Email: `admin@expenseflow.com`
  - Password: `password`
- **Secondary Buddy Account**: 
  - Email: `buddy@expenseflow.com`
  - Password: `password`
