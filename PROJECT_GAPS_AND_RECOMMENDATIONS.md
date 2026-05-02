# NORSU OJT DTR — Gaps & Best Recommendations

This document lists what the project is currently lacking and suggests concrete solutions. Use it as a checklist for improvements.

---

## 1. Security

### 1.1 Login / registration rate limiting
**Gap:** Student and coordinator login/register routes have no throttle. Attackers can brute-force passwords or spam registration.

**Recommendation:**
- Add throttle middleware to login and register routes, e.g. `throttle:5,1` (5 attempts per minute).
- In `routes/web.php` wrap the guest routes in a throttle group or add `->middleware('throttle:5,1')` to the login/register POST routes.

### 1.2 Time-in / time-out abuse
**Gap:** A logged-in student could spam time-in/time-out requests.

**Recommendation:**
- Enforce “one morning time-in, one afternoon time-in, one time-out per day” in the controller (you already do). Optionally add a throttle for the time-in/time-out routes (e.g. 10 requests per minute per user).

### 1.3 Production settings
**Gap:** `.env.example` shows `APP_DEBUG=true`. In production, debug must be off.

**Recommendation:**
- Set `APP_DEBUG=false` in production and document in README that production must use `APP_DEBUG=false` and a strong `APP_KEY`.

### 1.4 Sensitive actions logging
**Gap:** No logging of sensitive actions (coordinator confirmations, failed logins, duplicate registration attempts).

**Recommendation:**
- Use `Log::info()` or `Log::warning()` for: OJT completion confirmations, failed student/coordinator logins, duplicate student_no registration attempts. Optionally add a simple audit table (who, what, when) for coordinator actions.

---

## 2. Password reset (students)

**Status:** Addressed. Coordinators can set a temporary password for any student in their program from the OJT Completion page (“Set password” button). Students who forget their password contact their coordinator; no email field or email-based reset is used. This is the recommended approach for this system.

---

## 3. Testing

**Gap:** Only the default Laravel example test exists. No tests for attendance, auth, duplicate check, OJT completion, or reports.

**Recommendation:**
- Add **feature tests** for:
  - Student registration (success, duplicate student_no rejected).
  - Student login (success, wrong password, wrong course).
  - Time-in / time-out (success, already timed in, validation errors).
  - Coordinator: OJT confirm, duplicate-check page loads, report generation.
- Add **unit tests** for:
  - `Student::getTotalRenderedHoursAttribute` and `parseHoursRenderedToMinutes`.
- Run tests in CI (e.g. GitHub Actions) on push.

---

## 4. Documentation

**Gap:** README is the default Laravel README. No project-specific setup, environment variables, or deployment steps.

**Recommendation:**
- Replace or extend README with:
  - Project name and short description (NORSU OJT DTR).
  - Requirements (PHP 8.2+, Node, MySQL/SQLite).
  - Setup: `composer install`, `npm install`, `npm run vendor:copy`, `cp .env.example .env`, `php artisan key:generate`, DB config, `php artisan migrate`, optional `php artisan db:seed`.
  - Env vars: `APP_NAME`, `APP_URL`, `DB_*`, `MAIL_*` if used, and note `APP_DEBUG=false` in production.
  - How to run: `php artisan serve`, and that students/coordinators need to be seeded or registered.
  - Link to existing docs (e.g. `FACIAL_RECOGNITION_SETUP.md`, `UI_REDESIGN_DOCUMENTATION.md`).

---

## 5. Error handling and logging

**Gap:** Few try/catch blocks; no custom 404/500 views; little use of Laravel’s logging for business logic.

**Recommendation:**
- Add custom Blade views: `resources/views/errors/404.blade.php` and `500.blade.php` (with NORSU branding and “Back to home”).
- In controllers, wrap critical sections (e.g. PDF generation, face verification, sync) in try/catch; log the exception and return a user-friendly message instead of exposing stack traces.
- Set `config/app.php` timezone to `Asia/Manila` so all date helpers match your business logic (you already use Asia/Manila in controllers).

---

## 6. Code structure and maintainability

### 6.1 Student dashboard logic in Blade
**Gap:** Student dashboard is returned by a closure that only returns `view('student.dashboard')`. Attendance and logs are queried inside the Blade file (`Attendance::where(...)`). This mixes logic and presentation and is harder to test.

**Recommendation:**
- Introduce a `StudentDashboardController` (or use `StudentAuthController`). In the controller, load today’s attendance and monthly logs and pass them to the view as variables. Keep Blade for display only.

### 6.2 Repeated coordinator program filter
**Gap:** “Only students in coordinator’s program” is repeated in several controllers with the same query pattern.

**Recommendation:**
- Add a scope on `Student`, e.g. `scopeForCoordinator($query, $coordinator)`, or a helper that returns the student IDs for the coordinator’s program. Use it in AttendanceController, OjtCompletionController, DuplicateCheckController, ReportController, etc., to avoid duplication.

### 6.3 Form Requests
**Gap:** Validation for time-in, time-out, OJT confirm, and report generation is done inside controllers. Large controllers are harder to read and reuse.

**Recommendation:**
- Create Form Requests (e.g. `TimeInRequest`, `TimeOutRequest`, `OjtConfirmRequest`, `GenerateReportRequest`) and use `$request->validated()` in controllers. Moves validation rules and messages to one place and keeps controllers thin.

---

## 7. Features and data

### 7.1 Coordinator: reset student password
**Gap:** Coordinators cannot reset a student’s password. Only option is “contact coordinator,” with no tool for the coordinator.

**Recommendation:**
- Add a “Reset password” action on a coordinator view (e.g. duplicate-check or a simple “Student list”): coordinator selects a student and sets a new temporary password; show it once (or send by other means). Optionally require the student to change it on next login (would need a “must_change_password” flag).

### 7.2 Audit trail for OJT confirmation
**Gap:** You store who confirmed and when (`ojt_confirmed_by`, `ojt_completion_confirmed_at`), but there’s no general audit log for other coordinator actions.

**Recommendation:**
- Optional: add an `activity_log` or `audit_log` table (user type, user id, action, model, model id, old/new values, ip, timestamp). Log OJT confirm, password resets, and other sensitive actions. Enables “who did what and when” for disputes or audits.

### 7.3 Export attendance (CSV/Excel)
**Gap:** Coordinators can only view attendance in the UI and generate a PDF report per student. No bulk export (e.g. all students’ attendance for a month in one file).

**Recommendation:**
- Add an “Export” on the coordinator attendance or report page: e.g. “Download as CSV” for the current month (and optionally selected program). Use Laravel’s response stream or a simple CSV builder so coordinators can open data in Excel.

### 7.4 Soft deletes
**Gap:** Deleting a student or an attendance record is permanent. Accidental or wrong deletes cannot be recovered.

**Recommendation:**
- Use Laravel soft deletes (`SoftDeletes` on `Student` and optionally on `Attendance`). Add `deleted_at` via migration. Then “delete” only hides records; you can add an admin/coordinator “Restore” or “Trash” view later if needed.

### 7.5 Student email
**Decision:** Not used. Password reset is handled by the coordinator (set temporary password from OJT Completion). No optional email field on students; registration form does not collect email.

---

## 8. Offline and PWA

**Gap:** Face-api.js is still loaded from CDN. Registration and face verification on the student dashboard depend on it; offline registration or verification when the CDN is down will fail.

**Recommendation:**
- Host face-api.min.js (and optionally the model files) locally under `public/js` or `public/vendor`, update script tags to use `asset(...)`, and add those URLs to the service worker cache so the app works fully offline for key flows.

---

## 9. UX and accessibility

**Gap:** Modals and forms may not be fully keyboard- and screen-reader-friendly; no explicit “session expired” or idle timeout.

**Recommendation:**
- Ensure face verification and other modals are focus-trapped and have `aria-label` / `aria-describedby` where appropriate; after submit, move focus back to a sensible element.
- Optional: add a simple session timeout (e.g. after 30 minutes of inactivity show a “Session expired, please log in again” and redirect to login). Can be done with a small JS heartbeat or Laravel session lifetime + a check on next request.

---

## 10. Deployment and operations

**Gap:** No documented backup strategy or production checklist.

**Recommendation:**
- Document in README or a `DEPLOYMENT.md`: (1) Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` to the real URL. (2) Use a strong `APP_KEY` and secure `SESSION_*` and `DB_*`. (3) Regular DB backups (e.g. cron for `mysqldump` or SQLite copy). (4) Run `php artisan migrate --force` on deploy and optionally `php artisan config:cache` and `route:cache`. (5) If using queues, run `php artisan queue:work` (or similar) in production.

---

## 11. Configuration consistency

**Gap:** App uses `Asia/Manila` in controllers with `Carbon::now('Asia/Manila')` and `date_default_timezone_set('Asia/Manila')`, while `config/app.php` timezone is `UTC`.

**Recommendation:**
- Set `config/app.php` to `'timezone' => 'Asia/Manila'` (or `env('APP_TIMEZONE', 'Asia/Manila')`). Then you can use `now()` and `Carbon::now()` without passing the timezone everywhere and avoid mistakes.

---

## Priority summary

| Priority | Area                | Action |
|----------|---------------------|--------|
| High     | Security            | Add throttle to login/register; set APP_DEBUG=false in prod. |
| High     | Passwords           | Coordinator “reset student password” (implemented; no email-based reset). |
| Medium   | Testing             | Feature tests for auth, time-in/out, OJT, duplicate check. |
| Medium   | Documentation       | Project README with setup, env, and deployment. |
| Medium   | Code structure      | Student dashboard controller; coordinator scope; Form Requests. |
| Medium   | Errors              | Custom 404/500; try/catch and logging for critical actions. |
| Low      | Features            | CSV export; soft deletes; audit log. |
| Low      | Offline             | Local face-api.js and models. |
| Low      | UX                  | Session timeout; accessibility in modals. |

Implementing the high-priority items first will improve security and operability; then you can work through medium and low items as needed.
