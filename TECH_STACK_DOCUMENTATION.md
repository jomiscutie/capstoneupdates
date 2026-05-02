# NORSU OJT DTR - Tech Stack Documentation

This document lists the technology stack used by the **Web-Based Facial Recognition Attendance and Daily Time Record System for NORSU OJT Students**.

## 1) Core Platform

- **Backend Framework:** Laravel 12.x
- **Programming Language:** PHP 8.2+
- **Database:** MySQL (active deployment)
- **ORM:** Eloquent ORM (Laravel)
- **Authentication foundation:** Laravel Breeze

## 2) Frontend and UI

- **Server-side templating:** Blade
- **Build tool:** Vite 7.x
- **UI frameworks/libraries:**
  - Bootstrap 5.3
  - Tailwind CSS 3.x
  - Alpine.js
- **Icons:** Bootstrap Icons
- **HTTP client:** Axios
- **Additional frontend dependencies present:** React and ReactDOM

## 3) Face Recognition Stack

- **Primary face recognition library:** `@vladmandic/face-api` (face-api.js ecosystem)
- **Models used:**
  - Tiny Face Detector
  - Face Landmark 68
  - Face Recognition
  - Face Expression
- **Model and runtime assets location:** `public/vendor/face-api/`

## 4) Attendance and Verification Architecture

- **Client-side workflow:** camera capture, liveness checks, facial descriptor generation, and submission.
- **Server-side workflow:** verification of provided face encoding against enrolled face encoding and attendance business-rule enforcement.
- **Fallback mechanism:** password verification with reason capture (where allowed by workflow).

## 5) Offline and PWA Features

- **Web App Manifest:** `public/manifest.json`
- **Service Worker:** `public/sw.js`
- **Offline queue handling:** `public/js/offline-queue.js`
- **Purpose:** support unstable connectivity and deferred attendance syncing.

## 6) Reporting and Documents

- **PDF generation library:** `barryvdh/laravel-dompdf`
- **Use cases:** reports, printable forms, completion/certificate output.

## 7) Session, Queue, and Cache Layer

- **Session driver:** environment-configurable (commonly database in current setup)
- **Queue connection:** environment-configurable (database supported and used in typical setup)
- **Cache store:** environment-configurable (database/redis supported)
- **Redis support:** available via Laravel config when enabled

## 8) Development, Testing, and Code Quality Tools

- **Testing framework:** PHPUnit 11.x
- **Code style/lint formatter:** Laravel Pint
- **Debug log tailing:** Laravel Pail
- **Containerized local environment option:** Laravel Sail
- **NPM-based frontend workflow:** Vite dev/build pipeline

## 9) Runtime Environment Notes

- **Timezone used by the system:** Asia/Manila
- **Typical local stack:** XAMPP + Laravel + MySQL
- **Production readiness considerations:** HTTPS, secure session/cookie settings, DB backups, queue worker supervision, and log monitoring.

## 10) Why This Stack Fits the Project

- Laravel + MySQL provides stable, maintainable CRUD/business-rule handling.
- Face-api stack enables practical browser-based face verification for attendance.
- Service worker + offline queue improves reliability in low/unstable network conditions.
- Bootstrap/Tailwind improves development speed and consistent UI.
- DomPDF supports institutional reporting and printable documentation requirements.

---

## Quick Panelist Summary (One-Liner)

The system uses **Laravel (PHP) + MySQL** for backend/data, **Blade + Vite + Bootstrap/Tailwind** for frontend, **face-api.js** for facial verification, **PWA/offline queue** for resilience, and **DomPDF** for printable reports.

