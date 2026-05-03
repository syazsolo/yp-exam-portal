# YP Exam Portal

A robust online examination and student management system.

> Staging Environment: [Deployment Link (TBA)]<br>
> Note: Refinements and UI polishing are ongoing through the staging link.

## 🛠️ Tech Stack

Backend: Laravel 11 (PHP 8.2+)

Frontend: Vue 3 + Inertia.js + Tailwind CSS

Auth: Laravel Breeze (Inertia/Vue stack)

Database: MySQL

## 🎯 Technical Highlights

Beyond the core requirements of Role-Based Access Control (Admin/Lecturer/Student) and Exam Management, this implementation includes:

Mobile-First Design: Fully responsive interface across all roles.

Setup Diagnostics: Custom boot-time health checks (DB connectivity, migration status, and environment configuration) to ensure zero-friction deployment.

Component Playground: A hidden development route (/playground) used for prototyping and testing modular UI components (e.g., Data Tables).

Administrative CLI: Custom command-line interface for secure Admin creation: php artisan admin:create.

## 🚀 Quick Start

### 1. Installation

```bash
git clone https://github.com/syazsolo/yp-exam-portal.git
cd yp-exam-portal
composer install && npm install
```

### 2. Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Configure your DB_DATABASE, DB_USERNAME, and DB_PASSWORD in .env before proceeding.

### 3. Initialize

```bash
php artisan migrate --seed
npm run build # or 'npm run dev' for development
php artisan serve
```

## 🧪 Quality Assurance & CI

The project enforces code quality through a strict CI pipeline.

### The composer ci Command

This is the primary quality gate. It executes the following in sequence:

PHP Pint: Style enforcement and linting.

Prettier: Frontend formatting check.

PHPUnit: Feature and Unit testing suite.

Vite Build: Asset compilation check.

### Testing Utilities

```bash
php artisan test     # Run test suite
composer pint        # Format PHP code
npm run format:check # Validate frontend formatting
```

## 📌 Author

Mohamad Syazani

Full-Stack Software Engineer

syazanizul@gmail.com | 019-209 9853
