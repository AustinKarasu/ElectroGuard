# ElectroGuard — Smart Electrical Worker Safety & Maintenance Coordination System

> **Tagline:** *Every Clearance Matters.*  
> **Central Principle:** *"No completed clearance workflow, no authorization to proceed with a restoration request."*  
> **Repository:** [https://github.com/AustinKarasu/ElectroGuard](https://github.com/AustinKarasu/ElectroGuard)  
> **Project Category:** Rural Development & Livelihoods — Social Impact Hackathon  
> **Academic Program:** Computer Engineering (Third Year)  

---

## 1. Project Identity & Team Roster

Developed by **Team GodsPlanKangra** for electrical lineworker safety in rural distribution networks:

| Name | Role | Academic Branch | Responsibility |
|---|---|---|---|
| **Aayan Parmar** | **Team Leader & Systems Architect** | Computer Engineering (3rd Year) | Overall system architecture, security models, clearance rule engine |
| **Manjinder Uldeen** | **Backend & Database Engineer** | Computer Engineering (3rd Year) | REST API endpoints, relational schema, PDO database abstraction |
| **Aayush Parmar** | **Frontend & Safety Auditor** | Computer Engineering (3rd Year) | Industrial UI/UX, Leaflet map integration, acceptance test suite |

---

## 2. Complete Credentials & Access Directory

ElectroGuard includes pre-seeded demonstration accounts covering all roles. You can authenticate either by clicking the **Quick Role Selector** buttons in the navigation bar/dialog or by entering the exact credentials below:

### 2.1 User Accounts Table

| Role | Display Name | Username | Email Address | Password | Masked Phone | Worker Code / Badge ID | Permissions & Access Scope |
|---|---|---|---|---|---|---|---|
| **Administrator** | Er. Aayan Parmar | `admin` | `admin@electroguard.local` | `Admin@123` | `+91 98*** **001` | *N/A (Control Room)* | Full control room access, job creation, restoration authorization, IoT controller simulation, audit log review, safety reports |
| **Safety Auditor** | Er. Aayush Parmar | `aayush` | `aayush@electroguard.local` | `Admin@123` | `+91 98*** **003` | *N/A (Safety Wing)* | System-wide audit inspection, compliance reporting, CSV export, job monitoring |
| **Supervisor** | Er. Manjinder Uldeen | `supervisor` | `supervisor@electroguard.local` | `Supervisor@123` | `+91 98*** **002` | *N/A (Division In-Charge)*| Field crew oversight, job assignment, mandatory procedural safety reviews (earthing removal, personnel evacuation) |
| **Worker 1 (Lineman)** | Rakesh Kumar | `worker1` | `worker1@electroguard.local` | `Worker@123` | `+91 98*** **412` | `WK-KGR-701` | Mobile field portal, badge QR scan, OTP verification, 4-point safety checklist signing, hazard reopening |
| **Worker 2 (Lineman)** | Sunil Sharma | `worker2` | `worker2@electroguard.local` | `Worker@123` | `+91 98*** **831` | `WK-KGR-702` | Mobile field portal, badge verification, clearance submission & hazard reporting |
| **Worker 3 (Assistant)**| Vikas Dogra | `worker3` | `worker3@electroguard.local` | `Worker@123` | `+91 94*** **190` | `WK-KGR-703` | Mobile field portal, digital clearance signoff on assigned feeder tasks |

> **Password Note:** All passwords are cryptographically hashed using standard PHP `PASSWORD_BCRYPT`.

---

### 2.2 Database Configuration & Credentials

| Setting | SQLite (Default Zero-Config) | MySQL / XAMPP (Optional Mode) |
|---|---|---|
| **Driver (`DB_CONNECTION`)** | `sqlite` | `mysql` |
| **Host (`DB_HOST`)** | *Embedded File* | `127.0.0.1` |
| **Port (`DB_PORT`)** | *N/A* | `3306` |
| **Database Name (`DB_DATABASE`)** | `database/electroguard.sqlite` | `electroguard` |
| **Username (`DB_USERNAME`)** | *N/A* | `root` |
| **Password (`DB_PASSWORD`)** | *N/A* | *(blank by default in XAMPP)* |

---

### 2.3 IoT / ESP32 Demonstration Credentials & Hardware Pins

| Parameter | Configuration Value | Purpose |
|---|---|---|
| **Wi-Fi SSID** | `ElectroGuard_Demo_AP` | Demonstration access point for ESP32 controller |
| **Wi-Fi Password** | `SafeWorkingGrid2026` | WPA2 Pre-shared key for hardware bench unit |
| **Controller UID 1** | `EG-ESP32-DEMO-01` | Monitored unit for Kangra Feeder A (Online) |
| **Controller UID 2** | `EG-ESP32-DEMO-02` | Monitored unit for Nagrota Feeder B (Online) |
| **Controller UID 3** | `EG-ESP32-DEMO-03` | Monitored unit for Shahpur Feeder C (Simulated Disconnect / Offline) |
| **Controller UID 4** | `EG-ESP32-DEMO-04` | Monitored unit for Dharamshala Feeder D (Energized in Demo) |
| **Red LED Pin** | GPIO 25 | **RESTRICTED** State: Active maintenance / Power restoration blocked |
| **Amber LED Pin**| GPIO 26 | **PENDING** State: Worker clearance or supervisor review in progress |
| **Green LED Pin**| GPIO 27 | **SIMULATED SWITCHING**: Low-voltage indicator active (Only after authorized review) |
| **Blue LED Pin** | GPIO 2 | **HEARTBEAT**: Periodic network link pulse |

---

## 3. Detailed Problem Context & Engineering Solution

### 3.1 The Problem in Rural Distribution Networks
In mountainous and rural electricity distribution regions (such as Kangra District in Himachal Pradesh), 11kV and 22kV radial feeders extend across steep ridges, gorges, and dense pine forests. Scheduled maintenance—clearing tree branches, replacing cracked disc insulators, and repairing blown surge arresters—requires linemen to physically climb towers and work directly on high-voltage conductors.

**Why fatal electrocutions still occur:**
1. **Informal Verbal Clearance:** Substation operators and linemen communicate over noisy, unrecorded cell calls or walkie-talkies. Misunderstandings of feeder IDs lead to premature re-energization while workers are still aloft.
2. **Asynchronous Crew Completion:** When 3 workers are assigned to a feeder spanning 5 kilometers, Worker 1 finishing early and calling in has frequently led operators to restore power while Workers 2 and 3 are still in contact with the conductors.
3. **No Hazard Reopening (Kill-Switch):** If a descending technician spots a damaged cross-arm clamp, there is no standardized digital mechanism to immediately revoke earlier clearances and lock the feeder down.
4. **Permitting Blind to Hardware:** Control rooms lack automated interlocks ensuring that physical line isolation and safety signoffs are mathematically verified before switching can proceed.

### 3.2 The ElectroGuard Solution
ElectroGuard replaces verbal uncertainty with an authoritative digital clearance workflow:
- **Zero Client Trust:** Clearance counts are calculated strictly on the server from relational database records; client-side tampering is impossible.
- **Multi-Worker Interlock:** Every assigned worker must independently verify identity and sign off on site safety.
- **Reopening Kill-Switch:** If any worker reopens a clearance, the entire clearance set is invalidated and restoration requests are blocked.
- **Mandatory Supervisor Review:** Even with 100% worker signoff, restoration remains blocked until an assigned supervisor records verified procedural checks (ground rods removed, personnel accounted for).
- **IoT Fail-Safe:** If an IoT controller disconnects or reports stale heartbeat, its state becomes `UNKNOWN`, automatically blocking restoration.

---

## 4. Pre-Seeded Demonstration Scenarios

ElectroGuard comes pre-configured with four realistic scenarios representing the Kangra distribution network:

```
+───────────────────────────────────────────────────────────────────────────────────+
|                           KANGRA DEMONSTRATION NETWORK                            |
|                                                                                   |
|  [EG-JOB-1001: Kangra Feeder A]       [EG-JOB-1002: Nagrota Feeder B]             |
|   Workers: 3 Assigned                  Workers: 3 Assigned                        |
|   Clearances: 1 of 3 Submitted         Clearances: 3 of 3 Submitted               |
|   Supervisor: Not reviewed             Supervisor: PENDING                        |
|   Restoration: BLOCKED                 Restoration: AWAITING REVIEW               |
|                                                                                   |
|  [EG-JOB-1003: Shahpur Feeder C]      [EG-JOB-1004: Dharamshala Feeder D]         |
|   Workers: 2 Assigned                  Workers: 1 Assigned                        |
|   Clearances: REOPENED (Hazard)        Clearances: 1 of 1 Submitted               |
|   Controller: OFFLINE / UNKNOWN        Supervisor: APPROVED                       |
|   Restoration: BLOCKED                 Restoration: SIMULATED COMPLETION          |
+───────────────────────────────────────────────────────────────────────────────────+
```

### Scenario Breakdown:
1. **`EG-JOB-1001` (Kangra Feeder A — 11kV):**
   - **Work:** Insulator replacement & tree trimming.
   - **Workers:** Rakesh Kumar (Submitted), Sunil Sharma (Pending), Vikas Dogra (Pending).
   - **Rule Engine Diagnostic:** *"Blocked: 2 required worker(s) (Sunil Sharma, Vikas Dogra) have not submitted clearance."*
2. **`EG-JOB-1002` (Nagrota Feeder B — 11kV):**
   - **Work:** Transformer bushing overhaul.
   - **Workers:** All 3 technicians have submitted verified clearances.
   - **Rule Engine Diagnostic:** *"Blocked: All worker clearances submitted; Supervisor procedural safety review is pending."*
3. **`EG-JOB-1003` (Shahpur Feeder C — 11kV):**
   - **Work:** Conductor re-tensioning.
   - **Hazard Invalidation:** Lineman Rakesh Kumar reopened clearance after spotting a loose jumper clamp during pole descent.
   - **Rule Engine Diagnostic:** *"Blocked: Worker clearance was reopened by Rakesh Kumar. Previous clearance set invalidated. Controller is OFFLINE."*
4. **`EG-JOB-1004` (Dharamshala Feeder D — 22kV):**
   - **Work:** Lightning surge arrester testing.
   - **Status:** All criteria satisfied, supervisor inspected, authorized simulated energization completed in demonstration mode.

---

## 5. Environment Variables Specification (`.env`)

The application is configured via `.env` in the root folder. If `.env` is omitted, default values are loaded automatically:

```ini
# Application Mode & URLs
APP_NAME="ElectroGuard"
APP_ENV="prototype"
APP_DEBUG=true
APP_URL="http://localhost:8000"

# Database Configuration ('sqlite' or 'mysql')
DB_CONNECTION=sqlite

# MySQL Settings (Only active when DB_CONNECTION=mysql)
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=electroguard
DB_USERNAME=root
DB_PASSWORD=

# Session Lifetime (seconds)
SESSION_LIFETIME=7200
```

---

## 6. Local Setup & Startup Guide

### Prerequisites
- PHP 8.0 or higher (Installed natively or in `C:\xampp\php\php.exe`).
- Any modern web browser (Google Chrome, Microsoft Edge, Mozilla Firefox).

### Method A: One-Click Startup (Windows)
Double-click [**`start_server.bat`**](file:///C:/Users/karasu/Videos/ElectroProto/start_server.bat) in the project folder. It will:
1. Auto-detect PHP from `C:\xampp\php\php.exe` or system PATH.
2. Initialize and seed the SQLite database (`database/electroguard.sqlite`).
3. Start the server at **`http://localhost:8000`**.

### Method B: Manual Command Line Startup (Windows / Linux / macOS)
```bash
# 1. Clone or navigate to the repository directory
cd /path/to/ElectroGuard

# 2. Run Database Migration & Seeding
php database/migrate.php
# (Windows XAMPP: C:\xampp\php\php.exe database/migrate.php)

# 3. Start the PHP Built-in Server
php -S localhost:8000 -t public
# (Windows XAMPP: C:\xampp\php\php.exe -S localhost:8000 -t public)

# 4. Open in browser:
# http://localhost:8000
```

---

## 7. Automated Acceptance Test Suite (15/15 Tests Passed)

ElectroGuard includes a comprehensive test runner executing 15 automated acceptance tests:

To run the test suite, double-click [**`run_tests.bat`**](file:///C:/Users/karasu/Videos/ElectroProto/run_tests.bat) or execute:
```bash
php tests/AcceptanceTests.php
```

### Verified Test Matrix:
- **TEST 1:** Multi-worker incompleteness (1/3 cleared) $\to$ Restoration **BLOCKED**.
- **TEST 2:** All workers cleared but supervisor inspection pending $\to$ Request **AWAITING REVIEW**.
- **TEST 3:** Unassigned worker attempts clearance submission $\to$ Backend **REJECTS** (HTTP 403).
- **TEST 4:** Invalid/expired OTP submission $\to$ Verification **FAILS** and attempt logged.
- **TEST 5:** Hazard reopening triggered $\to$ Previous approvals **INVALIDATED**, restrictions restored.
- **TEST 6:** IoT controller disconnects $\to$ State becomes **UNKNOWN**, switching blocked.
- **TEST 7:** Ordinary worker attempts admin switching $\to$ Backend **FORBIDS** action (HTTP 403).
- **TEST 8:** Duplicate rapid submissions $\to$ Database upsert preserves integrity.
- **TEST 9:** Blocked restoration attempt $\to$ Specific rejection reason logged to audit trail.
- **TEST 10:** Data persistence across server restarts $\to$ Relational store verified consistent.
- **TEST 11:** Mobile viewport testing ($< 768\text{px}$) $\to$ Fluid layout and offcanvas drawer active.
- **TEST 12:** Audit log integrity $\to$ Historical sequence of actions accurately recorded.
- **TEST 13:** Stale controller heartbeat ($> 10\text{m}$) $\to$ Displayed as `UNKNOWN` (not assumed safe).
- **TEST 14:** Client-side tampered clearance count $\to$ Server ignores payload, evaluates DB rows.
- **TEST 15:** Reopened job resets dependent states $\to$ Status restored to `MAINTENANCE_ACTIVE`.

---

## 8. REST API Reference

All endpoints return structured JSON with consistent error formatting:

### Authentication
- `POST /api/auth/login` — Sign in with identifier and password.
- `POST /api/auth/demo-login` — Quick role switcher (`admin`, `supervisor`, `worker`).
- `GET /api/auth/me` — Retrieve active session user profile and worker badge info.
- `POST /api/auth/logout` — Safely terminate session.

### Jobs & Maintenance
- `GET /api/jobs` — Retrieve all jobs with dynamically evaluated clearance status.
- `GET /api/jobs/{id}` — Single job details with assigned crew and clearances.
- `POST /api/jobs` — Create new maintenance job with multi-worker requirement.
- `GET /api/workers/me/jobs` — Retrieve assigned jobs for the logged-in field worker.
- `GET /api/areas` — List Kangra demonstration feeder locations.
- `GET /api/workers` — List certified lineworkers directory.

### Clearance & OTP Verification
- `POST /api/clearance/verify-worker-id` — Verify worker QR/barcode against assignment list.
- `POST /api/jobs/{id}/otp/request` — Generate 5-minute cryptographic OTP challenge.
- `POST /api/jobs/{id}/otp/verify` — Validate OTP with attempt limiting (3 max).
- `POST /api/jobs/{id}/clearances` — Submit signed 4-point site clearance declarations.
- `POST /api/jobs/{id}/clearances/reopen` — Invalidate clearance and report site hazard.
- `POST /api/jobs/{id}/review` — Record supervisor procedural safety inspection.

### Restoration Gateway & IoT Simulation
- `GET /api/restoration-requests` — View restoration requests and eligibility status.
- `POST /api/jobs/{id}/restoration-requests/execute` — Execute authorized simulated switching.
- `GET /api/controllers` — List registered ESP32 units with heartbeat and relay status.
- `POST /api/controllers/{id}/status` — Toggle controller state (`ONLINE`, `OFFLINE`, `STALE`).
- `POST /api/controllers/{id}/command` — Send simulated low-voltage test pulse.

### Audit & Reports
- `GET /api/alerts` — Query unread and historical safety alerts.
- `POST /api/alerts/{id}/read` — Acknowledge safety alert.
- `GET /api/audit-logs` — Search append-only audit trail with correlation IDs.
- `GET /api/reports/summary` — Aggregate metrics for Chart.js dashboards.
- `GET /api/reports/export-csv` — Download official maintenance and clearance CSV report.

---

## 9. Hardware Boundary & Safety Disclosure

1. **Academic Demonstration Notice:** ElectroGuard is an educational, research-oriented prototype developed for a social-impact hackathon. It is not certified for commercial grid SCADA operations.
2. **Low-Voltage Bench Circuit Only:** The ESP32 demonstration controller operates strictly at 3.3V / 5V DC using low-power LEDs. **Under no circumstances should student-built micro-controllers or hobby relays be connected to live mains electricity or utility distribution equipment.**
3. **Physical Isolation Mandatory:** Software status does not replace physical electrical isolation, mechanical Lockout/Tagout (LOTO), portable earth discharge clamps, or certified Permit-To-Work (PTW) protocols.

---

## 10. Repository File Structure

```
ElectroGuard/
├── app/
│   ├── Config/
│   │   └── Database.php             # Dual SQLite & MySQL PDO connection manager
│   ├── Controllers/
│   │   ├── AuthController.php       # Authentication & demo role login
│   │   ├── JobController.php        # Job creation & worker assignments
│   │   ├── ClearanceController.php  # OTP, ID scan, clearance submit & reopen
│   │   ├── RestorationController.php# Restoration request gateway
│   │   ├── ControllerSimulationController.php # ESP32 simulator console
│   │   ├── AlertController.php      # Safety alerts & notifications
│   │   ├── AuditController.php      # Searchable append-only audit logs
│   │   └── ReportController.php     # Metrics & CSV report exporter
│   ├── Middleware/
│   │   └── AuthMiddleware.php       # Session security & role verification
│   ├── Services/
│   │   ├── ClearanceRuleEngine.php  # Authoritative Central Rule Engine
│   │   ├── OtpService.php           # Cryptographic OTP challenge manager
│   │   ├── AuditLogger.php          # Immutable audit logging with correlation IDs
│   │   └── AlertService.php         # Real-time alert dispatcher
│   └── Views/
│       └── app.php                  # Complete single-page application view template
├── database/
│   ├── schema.sql                   # Normalized 3NF ANSI SQL schema
│   ├── seed.sql                     # Comprehensive Kangra network demo dataset
│   └── migrate.php                  # Database migration & reset CLI script
├── docs/
│   ├── TECHNICAL_DESIGN_DOCUMENT.md # Full technical design document & diagrams
│   ├── architecture.md              # Architecture components & data flows
│   ├── api.md                       # Comprehensive REST API specifications
│   ├── safety-limitations.md        # Safety boundaries & LOTO disclosures
│   ├── test-plan.md                 # Acceptance test matrix & verification plan
│   └── jury-walkthrough.md          # 5-minute hackathon jury presentation script
├── firmware/
│   └── esp32_electroguard_demo.ino  # ESP32 Arduino sketch with status LEDs
├── public/
│   ├── index.php                    # Front controller & REST API gateway
│   └── assets/
│       ├── css/electroguard.css     # Custom industrial design system
│       └── js/
│           ├── api-client.js        # Unified API HTTP client module
│           ├── map-manager.js       # Leaflet OpenStreetMap manager
│           ├── charts-manager.js    # Chart.js analytics engine
│           ├── worker-portal.js     # Field lineworker mobile interface
│           ├── admin-dashboard.js   # Control room operations interface
│           └── app.js               # Application core & demo switchers
├── tests/
│   └── AcceptanceTests.php          # Automated 15-test acceptance suite
├── .env.example                     # Sample environment configuration
├── .gitignore                       # Git ignore rules (protects SQLite & secrets)
├── LICENSE                          # MIT License with GodsPlanKangra copyright
├── README.md                        # Master project documentation
├── run_tests.bat                    # One-click test runner
└── start_server.bat                 # One-click local server launcher
```

---

## 11. Resetting the Database

To reset all jobs, worker clearances, and controllers back to clean demonstration seeds at any time:
```bash
php database/migrate.php
```
*(Windows XAMPP: `C:\xampp\php\php.exe database/migrate.php`)*

---

## 12. License

This project is licensed under the **MIT License** — see the [**`LICENSE`**](file:///C:/Users/karasu/Videos/ElectroProto/LICENSE) file for full details.  
Copyright (c) 2026 **GodsPlanKangra** (Aayan Parmar, Manjinder Uldeen, Aayush Parmar).
