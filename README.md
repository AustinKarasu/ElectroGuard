# ElectroGuard — Smart Electrical Worker Safety & Maintenance Coordination System

**Tagline:** *Every Clearance Matters.*  
**Team Name:** GodsPlanKangra  
**Academic Branch:** Computer Engineering (Third Year)  
**Project Category:** Rural Development & Livelihoods  
**Team Members:**
- **Aayan Parmar** — Team Leader & Systems Architect
- **Manjinder Uldeen** — Backend & Database Engineer
- **Aayush Parmar** — Frontend & Safety Verification Auditor

---

## 1. Project Background & Motivation

Electrical line workers and maintenance technicians in rural distribution divisions face grave, often fatal electrical shocks when high-voltage power is prematurely restored to distribution feeders while repair work is still underway. Traditional utility operations rely on informal, verbal telephone confirmations between distant field crews and substation operators. Misheard feeder names, uncoordinated multi-worker schedules, and the complete lack of digital interlocking result in catastrophic accidents.

**ElectroGuard** is a responsive web application prototype and IoT conceptual architecture designed to eliminate premature restoration through one core principle:

> **"No completed clearance workflow, no authorization to proceed with a restoration request."**

---

## 2. Distinction of Safety Concepts & Prototype Boundary

ElectroGuard strictly separates three distinct operational layers:
1. **Maintenance Clearance:** Field worker declaration and supervisor procedural safety verification that tasks are finished, grounds are removed, and personnel have exited the structure.
2. **Restoration Request Authorization:** Digital coordination layer that determines whether an administrative restoration request may proceed to review based on authoritative database records.
3. **Physical Electrical Switching:** The actual energization of electrical lines. **ElectroGuard does not claim that an OTP, database entry, map, or simulated relay physically isolates an electrical line.** Physical isolation requires certified utility procedures, mechanical lockout/tagout (LOTO), portable earthing discharge rods, and qualified utility staff.

---

## 3. Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3, Leaflet Map with OpenStreetMap tiles, Chart.js, Bootstrap Icons.
- **Backend:** PHP 8.2+ with REST-style API endpoints, PDO database abstraction layer.
- **Database:** SQLite 3 (zero-configuration local default) and MySQL 8.0+ compatibility (via standard SQL schema).
- **IoT Firmware:** ESP32 Arduino C++ sketch (`firmware/esp32_electroguard_demo.ino`) for bench-level low-voltage (3.3V/5V DC) LED status indicators.

---

## 4. Environment Variables Specification

The application configuration is managed via `.env` in the project root. If `.env` is absent, the system automatically uses SQLite in zero-configuration mode.

| Variable Name | Default Value | Supported Values | Description |
|---|---|---|---|
| `APP_NAME` | `"ElectroGuard"` | String | Application display name |
| `APP_ENV` | `prototype` | `prototype`, `production`, `local` | Operating environment mode |
| `APP_DEBUG` | `true` | `true`, `false` | Enables detailed error logging |
| `APP_URL` | `http://localhost:8000` | URL string | Base local URL for server requests |
| `DB_CONNECTION` | `sqlite` | `sqlite`, `mysql` | Active PDO database driver |
| `DB_HOST` | `127.0.0.1` | IP / Hostname | MySQL server host (used when `DB_CONNECTION=mysql`) |
| `DB_PORT` | `3306` | Numeric port | MySQL server port |
| `DB_DATABASE` | `electroguard` | Database name | MySQL database name |
| `DB_USERNAME` | `root` | Username string | MySQL user |
| `DB_PASSWORD` | *(empty)* | Password string | MySQL password |
| `SESSION_LIFETIME`| `7200` | Seconds (integer) | Session expiration timeout (default: 2 hours) |

### Switching to MySQL (e.g. XAMPP MySQL):
1. In `C:\xampp\phpmyadmin` (or MySQL CLI), create a database named `electroguard`:
   ```sql
   CREATE DATABASE electroguard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. In `.env`, change:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=electroguard
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Run the migration script:
   ```bash
   php database/migrate.php
   ```

---

## 5. Quick Start Guide (Windows / XAMPP / PHP)

### Prerequisites
- PHP 8.0 or higher (Installed natively or via XAMPP in `C:\xampp\php\php.exe`).
- Modern web browser (Chrome, Edge, Firefox).

### Method A: One-Click Startup (Recommended)
Double-click the `start_server.bat` file in the root directory. It will automatically:
1. Detect PHP from `C:\xampp\php\php.exe` or system PATH.
2. Run database migrations and seed the Kangra demonstration network.
3. Start the local server at **`http://localhost:8000`**.

### Method B: Manual CLI Startup
```bash
# 1. Navigate to project root
cd C:\Users\karasu\Videos\ElectroProto

# 2. Initialize / Seed the Database
C:\xampp\php\php.exe database/migrate.php
# (or simply: php database/migrate.php)

# 3. Launch PHP Built-in Server
C:\xampp\php\php.exe -S localhost:8000 -t public
# (or simply: php -S localhost:8000 -t public)
```

Now open **`http://localhost:8000`** in your browser.

---

## 6. Demonstration Accounts & Roles

ElectroGuard provides one-click role switcher buttons directly in the navigation bar and sign-in dialog:

| Role | Username / Email | Password | Assigned Demonstration Responsibility |
|---|---|---|---|
| **Administrator** | `admin@electroguard.local` | `Admin@123` | Control room operations, restoration requests, IoT monitor |
| **Supervisor** | `supervisor@electroguard.local` | `Supervisor@123` | Procedural safety inspection, field crew oversight |
| **Worker 1** | `worker1@electroguard.local` | `Worker@123` | Rakesh Kumar (`WK-KGR-701`), Kangra Feeder A |
| **Worker 2** | `worker2@electroguard.local` | `Worker@123` | Sunil Sharma (`WK-KGR-702`), Kangra Feeder A |
| **Worker 3** | `worker3@electroguard.local` | `Worker@123` | Vikas Dogra (`WK-KGR-703`), Kangra Feeder A |

---

## 7. Demonstration Scenarios (Pre-Seeded in Kangra Network)

- **Job `EG-JOB-1001` (Kangra Feeder A):**
  - **Condition:** 3 assigned lineworkers. Only Worker 1 has signed off.
  - **Rule Engine Result:** **BLOCKED**. Switching button displays: *"Blocked: 2 required worker(s) (Sunil Sharma, Vikas Dogra) have not submitted clearance."*
- **Job `EG-JOB-1002` (Nagrota Feeder B):**
  - **Condition:** All 3 lineworkers have signed off. Supervisor review is pending.
  - **Rule Engine Result:** **AWAITING REVIEW**. Even with 100% worker signoff, administrative restoration is interlocked until supervisor inspects site.
- **Job `EG-JOB-1003` (Shahpur Feeder C):**
  - **Condition:** Worker Rakesh Kumar signed off, but then **REOPENED** the clearance after discovering a loose jumper clamp during pole descent.
  - **Rule Engine Result:** **BLOCKED**. Invalidation rule demonstrated: reopening immediately revoked previous approvals and returned the feeder to restricted status. Also, Controller `EG-ESP32-DEMO-03` is disconnected (OFFLINE).
- **Job `EG-JOB-1004` (Dharamshala Road Feeder D):**
  - **Condition:** Single lineworker maintenance, fully cleared, supervisor verified.
  - **Rule Engine Result:** **SIMULATED COMPLETION**. Authorized simulated switching complete in low-voltage demonstration mode.

---

## 8. Running the Automated Acceptance Test Suite

ElectroGuard includes an automated test runner validating all 15 acceptance criteria:

Double-click `run_tests.bat` or run:
```bash
C:\xampp\php\php.exe tests/AcceptanceTests.php
```

All 15 tests execute and report status:
1. Missing worker clearances block restoration requests.
2. Completed worker clearances still require supervisor review.
3. Unassigned workers cannot submit clearance.
4. Invalid / expired OTPs fail and are audit-logged.
5. Reopening clearance invalidates approvals and restores restrictions.
6. Offline IoT controller changes state to `UNKNOWN` and blocks switching.
7. Role-based privilege checks reject unauthorized worker actions.
8. Duplicate clearance submissions are handled idempotently.
9. Denied restoration requests log specific rejection reasons.
10. Relational database persistence verified across restarts.
11. Responsive layout verified on mobile viewports.
12. Audit log reflects exact historical event sequence.
13. Stale controller heartbeats distinguish `UNKNOWN` from safe.
14. Server-side rule engine blocks client-side tampering.
15. Reopened jobs recalculate all dependent request states.

---

## 9. Technical Documentation & Artifacts

- [**`docs/TECHNICAL_DESIGN_DOCUMENT.md`**](file:///C:/Users/karasu/Videos/ElectroProto/docs/TECHNICAL_DESIGN_DOCUMENT.md) — Comprehensive technical design document with architectural diagrams, mathematical rule engine models, FMEA, and database models.
- [**`docs/jury-walkthrough.md`**](file:///C:/Users/karasu/Videos/ElectroProto/docs/jury-walkthrough.md) — Step-by-step 5-minute hackathon presentation script for GodsPlanKangra.
- [**`docs/architecture.md`**](file:///C:/Users/karasu/Videos/ElectroProto/docs/architecture.md) — System architecture, component models, and data flow.
- [**`docs/api.md`**](file:///C:/Users/karasu/Videos/ElectroProto/docs/api.md) — REST API endpoints specification and schemas.
- [**`docs/safety-limitations.md`**](file:///C:/Users/karasu/Videos/ElectroProto/docs/safety-limitations.md) — Hardware boundaries and physical LOTO distinction.
- [**`docs/test-plan.md`**](file:///C:/Users/karasu/Videos/ElectroProto/docs/test-plan.md) — Acceptance test matrix and verification plan.
- [**`LICENSE`**](file:///C:/Users/karasu/Videos/ElectroProto/LICENSE) — MIT License with GodsPlanKangra copyright and safety disclaimer.
- [**`firmware/esp32_electroguard_demo.ino`**](file:///C:/Users/karasu/Videos/ElectroProto/firmware/esp32_electroguard_demo.ino) — ESP32 low-voltage bench indicator sketch.

---

## 10. Resetting Database

To reset the database back to clean demonstration seed data at any time:
```bash
C:\xampp\php\php.exe database/migrate.php
```
