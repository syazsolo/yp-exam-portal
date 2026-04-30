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
- **Laravel Breeze** (auth scaffold, Blade)
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

```bash
# 1. Clone
git clone https://github.com/syazsolo/yp-exam-portal.git
cd yp-exam-portal

# 2. Install PHP deps
composer install

# 3. Install JS deps
npm install

# 4. Environment
cp .env.example .env
php artisan key:generate
```

### Configure DB

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yp_exam_portal
DB_USERNAME=root
DB_PASSWORD=
```

Create the database, then:

```bash
php artisan migrate --seed
```

### Run It

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite
npm run dev
```

Open 👉 `http://localhost:8000`

---

## 🧪 Tests

```bash
php artisan test
```

---

## 📌 Status

🚧 **Work in progress.** Currently scaffolded with Laravel 11 + Breeze. Features will land iteratively over the 4-day window.

This README will be updated as the project grows.