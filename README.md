# 📝 YP Exam Portal

> An online examination & student management portal — built for the **YP Technical Assessment**.

A portal for lecturers to manage & assign exams, which students take.

---

## 👋 About Me

**Author:** Mohamad Syazani Bin Zulkhairi<br>
**Email:** syazanizul@gmail.com<br>
**Phone:** 019-209 9853

---

## 🎯 The Brief

Build a portal for online examination + student management with:

- **Roles** — Lecturer & Student
- **Authentication** — secure login per user
- **Exam Creation** — multiple-choice + open-text questions
- **Class Management** — students grouped into classes
- **Subject Management** — classes linked to multiple subjects
- **Access Control** — students only see exams for their class
- **Time Limit** — exams expire (e.g. 15 minutes)
- **Extras** — bonus features as I see fit

---

## 🛠️ Tech Stack

- **Laravel 11**
- **Laravel Breeze** (auth scaffold, Vue + Inertia stack)
- **Vue 3** + **Inertia.js**
- **MySQL**
- **Tailwind CSS** + **Vite**

---

## 🚀 Getting Started

### Prerequisites

- PHP **8.2+**
- Composer
- Node.js **18+** & npm
- MySQL (or any DB you prefer — adjust `.env`)

### Setup

**1. Clone**

```bash
git clone https://github.com/syazsolo/yp-exam-portal.git
cd yp-exam-portal
```

**2. Install dependencies**

```bash
composer install
npm install
```

**3. Environment**

```bash
cp .env.example .env
php artisan key:generate
```

**4. Configure DB** — edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yp_exam_portal
DB_USERNAME=root
DB_PASSWORD=
```

**5. Create the database**, then run migrations + seeders:

```bash
php artisan migrate --seed
```

**6. Start the servers** — two terminals (Laravel + Vite for Vue/Inertia hot reload):

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

**7. Open** 👉 `http://localhost:8000`

---

### Production build

For a one-off frontend build:

```bash
npm run build
```

---

## 🧪 Tests

Tests use a separate database (`yp_exam_portal_testing`). Setup once:

```bash
cp .env.testing.example .env.testing
php artisan key:generate --env=testing
# create the test database (yp_exam_portal_testing) in your DB of choice
```

Then any time:

```bash
php artisan test          # run the suite
composer ci               # pint --test + tests + frontend build
composer pint             # format PHP
```

---

## 📌 Status

🚧 **Work in progress.** Currently scaffolded with Laravel 11 + Breeze (Vue + Inertia). Role-based auth (Lecturer / Student) landed. Exam/class/subject features land iteratively over the 4-day window.

This README will be updated as the project grows.