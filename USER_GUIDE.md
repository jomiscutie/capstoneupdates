# NORSU OJT DTR User Guide

This guide explains how the system works for students, coordinators, and system administrators.

---

## 1) What This System Does

NORSU OJT DTR tracks OJT attendance with:
- Time In / Time Out recording
- Face verification with camera
- Password fallback verification
- Verification snapshot capture
- Attendance logs and reports
- Coordinator review and student management

---

## 2) User Roles

### Student
- Logs in using student number and password
- Records attendance (time in / time out)
- Views attendance logs and rendering progress

### Coordinator
- Verifies pending student registrations
- Reviews attendance logs, analytics, and reports
- Sets student temporary passwords
- Updates required OJT hours
- Removes students from coordinator settings (if needed)

### System Admin (recommended operational role)
- Supports coordinator account recovery (if coordinator forgets password)
- Handles high-level account and system maintenance

---

## 3) Student Flow

### A. Login
1. Open the unified login page.
2. Enter student number and password.
3. Click **Sign in**.

### B. Time In / Time Out
1. On Student Dashboard, click **Time In** or **Time Out**.
2. Face verification modal opens.
3. Show your face clearly to camera.
4. Click **Verify & Submit** when enabled.

### C. Password Fallback (if camera/face is unavailable)
1. In the verification modal, use **Verify with password instead**.
2. Enter account password.
3. Submit to record attendance.

### D. Verification Snapshot
- A snapshot is captured on successful verification.
- Snapshot buttons can appear as:
  - Morning
  - Afternoon
  - Time out

### E. Logs and Progress
- Open **Attendance Logs and Progress** from the sidebar.
- Use Month/Week filters to view records.
- Check rendering progress (rendered, required, remaining hours).

---

## 4) Coordinator Flow

### A. Login
1. Open unified login page.
2. Enter coordinator email and password.
3. Click **Sign in**.

### B. Verify Students
1. Go to **Pending Verification**.
2. Search student by name/number/course.
3. Approve or reject registration.

### C. Attendance Monitoring
1. Open **Attendance Logs**.
2. Use month/week and search filters.
3. View snapshots and attendance details.

### D. OJT Completion
1. Open **OJT Completion**.
2. Confirm completion status.
3. Set or update required OJT hours.
4. Download certificates if available.

### E. Coordinator Settings
1. Update coordinator password.
2. Set temporary student passwords.
3. Remove student account (with confirmation) when required.

---

## 5) Forgot Password Guide

### Student Forgot Password
- Student must contact their coordinator.
- Coordinator sets a temporary password from system tools.
- Student logs in and changes password in **Settings**.

### Coordinator/Admin Forgot Password (Best Practice)
- Use a designated system admin/master account for coordinator password reset.
- If no reset UI exists, IT can perform a secure manual reset via server/database.
- Do not share coordinator accounts.

---

## 6) Offline Behavior

- If internet is unstable, attendance can be queued offline.
- Once online again, queued records sync automatically.
- Users should still verify synced records in logs.

---

## 7) Common Issues and Fixes

### Face verification not enabling
- Improve lighting
- Face the camera directly
- Keep still for detection
- Try password fallback if needed

### Camera not available
- Allow browser camera permission
- Close other apps using the camera
- Reload page and retry

### No attendance shown
- Check active filter (month/week)
- Verify selected date range
- Refresh after sync if recently recorded offline

---

## 8) Security Tips

- Use strong passwords and update regularly.
- Never share accounts.
- Log out after using shared/public devices.
- Reset temporary passwords immediately after first login.

---

## 9) Quick Checklist

### Students
- [ ] Can log in
- [ ] Can time in/out
- [ ] Can view logs and progress
- [ ] Can change password

### Coordinators
- [ ] Can verify students
- [ ] Can review attendance and reports
- [ ] Can manage student settings
- [ ] Can handle student password concerns

