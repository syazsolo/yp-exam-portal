# YP Exam Portal

A robust online examination and student management system.

> Assessment Demo: This project is intended to be reviewed through the Docker demo stack below.

## 🛠️ Tech Stack

Backend: Laravel 11 (PHP 8.4 recommended for the committed lock file)

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

### Docker Demo Stack

For assessors who want to try the seeded app immediately, this is the one-shot installation path. It only requires Docker Desktop or Docker Engine with Docker Compose v2 running; PHP, Composer, Node, and MySQL do not need to be installed on the host machine.

```bash
docker compose -f docker-compose.demo.yml up --build
```

The first run can take a few minutes while Docker downloads base images and builds the Laravel/Vite image. Keep the terminal open. When the `init` service prints `Demo is ready.`, open http://localhost:8000. If port 8000 is already in use, stop the other app first and rerun the command.

The stack builds the Laravel app, starts MySQL, applies migrations and seeds the database on first run, and starts the Laravel scheduler so timed exam activation and closure jobs run every minute. On subsequent `up` runs, existing data is preserved and only new migrations are applied. To reset the demo to a fresh seeded state, recreate the MySQL volume (see below).

Seeded login accounts all use the password `qwertyuiop`:

```text
Admin:    admin@gmail.com
Lecturer: lecturer1@gmail.com
Student:  student1@gmail.com
```

To stop the stack:

```bash
docker compose -f docker-compose.demo.yml down --remove-orphans
```

To also remove the MySQL volume:

```bash
docker compose -f docker-compose.demo.yml down -v --remove-orphans
docker compose -f docker-compose.demo.yml up --build
```

To run the Laravel test suite through Docker:

```bash
docker compose -f docker-compose.demo.yml run --rm --build test
```

The test service creates and uses a separate `yp_exam_portal_testing` database, so running tests does not mutate the seeded demo database.

### Local Installation

```bash
git clone https://github.com/syazsolo/yp-exam-portal.git
cd yp-exam-portal
composer install && npm install
```

### Local Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Configure your DB_DATABASE, DB_USERNAME, and DB_PASSWORD in .env before proceeding.

### Local Initialize

```bash
php artisan migrate # or 'php artisan migrate --seed' for development
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
docker compose -f docker-compose.demo.yml run --rm --build test # Run test suite through Docker
composer pint        # Format PHP code
npm run format:check # Validate frontend formatting
```

## 📌 Author

Mohamad Syazani

Full-Stack Software Engineer

syazanizul@gmail.com | 019-209 9853
