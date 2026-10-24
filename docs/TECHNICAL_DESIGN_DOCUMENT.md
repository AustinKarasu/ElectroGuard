# ElectroGuard: Technical Design Document (TDD)
### Smart Electrical Worker Safety & Maintenance Coordination System
**Project Codename:** ElectroGuard  
**Tagline:** *Every Clearance Matters.*  
**Engineering Team:** GodsPlanKangra  
- **Aayan Parmar** — Team Leader & Systems Architect  
- **Manjinder Uldeen** — Backend & Database Engineer  
- **Aayush Parmar** — Frontend & Safety Verification Auditor  
**Academic Institution & Program:** Computer Engineering (Third Year)  
**Track / Category:** Rural Development & Livelihoods  
**Version:** 1.0.0-PROTOTYPE  
**Date:** October 2026  

---

## 1. Executive Summary & Problem Context

In rural electrical power distribution networks—exemplified by the hilly and forested terrain of Kangra District, Himachal Pradesh—distribution feeders (primarily 11kV and 22kV radial lines) traverse long, rugged distances from 33/11kV primary substations into mountain villages. Lineworkers and maintenance crews frequently encounter hazardous conditions requiring tree trimming, insulator replacement, conductor restringing, and transformer maintenance.

### 1.1 The Root Cause of Fatal Accidents
Field interviews and engineering failure analyses reveal that the overwhelming majority of electrical fatalities during scheduled maintenance do not stem from lack of technical skill. Rather, they stem from **breakdowns in coordination channels**:
1. **Informal Verbal Communication:** Clearance requests and restoration orders are transmitted over noisy telephone or walkie-talkie channels. Misheard feeder numbers (e.g., Feeder 1 vs. Feeder A) or misattributed clearances trigger accidental re-energization.
2. **Asynchronous Multi-Worker Completion:** Maintenance jobs routinely involve multiple lineworkers stationed across several kilometers of towers. When one worker finishes and calls the substation, the operator frequently assumes the entire feeder is clear while other crew members are still aloft.
3. **No Digital Kill-Switch (Hazard Reopening):** If a descending technician spots an ungrounded jumper, damaged insulator, or tools left across phases, there is no standardized mechanism to immediately revoke prior verbal clearances.
4. **Disconnection Between Permitting and Control:** Control room switches operate independently of verified lineworker signoffs.

### 1.2 Core Architectural Principle
ElectroGuard institutes a strict, non-negotiable software gate:
$$\mathbf{Clearance}(J) \neq \text{COMPLETE} \implies \mathbf{Authorization}(J) = \text{BLOCKED}$$
**Under no circumstances can an administrative or supervisory operator authorize electrical restoration without 100% verified digital clearances from all assigned field lineworkers, verified supervisor procedural checks, and an active online controller heartbeat.**

---

## 2. System Architecture & Component Model

ElectroGuard follows a clean, modular tiered architecture designed for local or edge deployment in utility substations.

### 2.1 High-Level Component Diagram

```
+--------------------------------------------------------------------------------------------------+
|                                    CLIENT PRESENTATION LAYER                                     |
|                                                                                                  |
|   ┌───────────────────────────────┐     ┌────────────────────────────────────────────────────┐   |
|   │     WORKER FIELD PORTAL       │     │            CONTROL ROOM OPERATIONS CENTER          │   |
|   │  - QR/Badge Scan Simulator    │     │  - Live Kangra Feeder Map (Leaflet / OpenStreetMap)│   |
|   │  - Two-Factor OTP Validation  │     │  - Multi-Worker Maintenance Job Dispatcher         │   |
|   │  - 4-Point Safety Declaration │     │  - Authoritative Restoration Review Gateway        │   |
|   │  - Hazard Reopening / Abort   │     │  - ESP32 Controller Simulation Console             │   |
|   └───────────────┬───────────────┘     └─────────────────────────┬──────────────────────────┘   |
+-------------------|-----------------------------------------------|------------------------------+
                    │                                               │
                    ▼ (HTTP / JSON REST API with Session Auth)      ▼
+--------------------------------------------------------------------------------------------------+
|                                 APPLICATION & ENGINE LAYER (PHP 8.2+)                            |
|                                                                                                  |
|   ┌──────────────────────────────┐    ┌──────────────────────────────────────────────────────┐   |
|   │     SECURITY & SESSION       │    │           CENTRAL CLEARANCE RULE ENGINE              │   |
|   │  - Role Guard (Worker/Admin) │    │  - Evaluates Authoritative Relational Records        │   |
|   │  - CSRF & Anti-Fixation      │    │  - Rejects Client-Side Tampered Counts               │   |
|   │  - Bcrypt Hashing            │    │  - Automatic Invalidation on Reopening               │   |
|   └───────────────┬──────────────┘    │  - Mandatory Supervisor Procedural Review Enforcement │   |
|                   │                   │  - Controller Heartbeat Fail-Safe (UNKNOWN if Drop)  │   |
|                   │                   └───────────────────────────┬──────────────────────────┘   |
|                   ▼                                               ▼                              |
|   ┌──────────────────────────────┐    ┌──────────────────────────────────────────────────────┐   |
|   │     APPEND-ONLY AUDIT        │    │                 ALERT & DISPATCH ENGINE              │   |
|   │  - Immutable Event Journal   │    │  - Critical Hazard Broadcasts                        │   |
|   │  - Correlation Identifiers   │    │  - Unread Alert Badges & Event Triggers              │   |
|   └──────────────────────────────┘    └──────────────────────────────────────────────────────┘   |
+---------------------------------------------------┬----------------------------------------------+
                                                    │
                                                    ▼ (PDO SQL Abstraction)
+--------------------------------------------------------------------------------------------------+
|                                       DATA PERSISTENCE LAYER                                     |
|                                                                                                  |
|   [SQLite 3 Engine (Zero-Config Embedded)]  OR  [MySQL 8.0+ Enterprise Relational Database]      |
|   - Tables: users, worker_profiles, maintenance_areas, controllers, maintenance_jobs,            |
|             job_worker_assignments, otp_challenges, clearance_submissions, supervisor_reviews,    |
|             restoration_requests, controller_events, alerts, audit_logs                          |
+---------------------------------------------------┬----------------------------------------------+
                                                    │
                                                    ▼ (Simulated Serial / HTTP low-voltage I/O)
+--------------------------------------------------------------------------------------------------+
|                                   HARDWARE DEMONSTRATION LAYER                                   |
|                                                                                                  |
|   [ESP32 Microcontroller (EG-ESP32-DEMO-01)]                                                     |
|   - Bench demonstration operating strictly at 3.3V / 5V DC                                       |
|   - Fail-Safe Default: RESTRICTED state (RED LED ON, Green LED OFF)                              |
|   - Zero physical connection to live distribution equipment                                      |
+--------------------------------------------------------------------------------------------------+
```

---

## 3. The Central Clearance Rule Engine Specification

The Central Clearance Rule Engine (`app/Services/ClearanceRuleEngine.php`) is the single source of truth for all safety and operational transitions.

### 3.1 Formal State Machine & Logic Model

Let $J$ be a maintenance job with:
- Assigned workers requiring clearance: $W_{req}(J) = \{w_1, w_2, \dots, w_n\}$
- Total required count: $N = |W_{req}(J)|$
- Active submitted clearances: $C_{sub}(J) = \{c \in \text{Clearances} \mid c.job = J \land c.status = \text{SUBMITTED}\}$
- Reopened clearances: $C_{reopen}(J) = \{c \in \text{Clearances} \mid c.job = J \land c.status = \text{REOPENED}\}$
- Supervisor review: $R_{sup}(J) \in \{\text{APPROVED}, \text{PENDING}, \text{REJECTED}\}$
- Linked controller heartbeat: $H(C_{iot}) \in \{\text{ONLINE}, \text{OFFLINE}, \text{STALE}, \text{UNKNOWN}\}$

#### Authoritative Decision Rules:
1. **Rule 1 (Hazard Invalidation):**
   $$\text{If } |C_{reopen}(J)| > 0 \implies \text{RestorationStatus}(J) = \mathbf{BLOCKED}$$
   *Reason:* "Blocked: Worker clearance was reopened by $[Worker]$. Previous clearance set invalidated."
2. **Rule 2 (Worker Completeness):**
   $$\text{If } |C_{sub}(J)| < N \implies \text{RestorationStatus}(J) = \mathbf{BLOCKED}$$
   *Reason:* "Blocked: $(N - |C_{sub}(J)|)$ required worker(s) have not submitted clearance."
3. **Rule 3 (Supervisor Review):**
   $$\text{If } |C_{sub}(J)| = N \land R_{sup}(J) \neq \text{APPROVED} \implies \text{RestorationStatus}(J) = \mathbf{AWAITING\_REVIEW}$$
   *Reason:* "Blocked: All worker clearances submitted; Supervisor procedural safety review is pending."
4. **Rule 4 (Controller Fail-Safe):**
   $$\text{If } H(C_{iot}) \neq \text{ONLINE} \implies \text{RestorationStatus}(J) = \mathbf{BLOCKED}$$
   *Reason:* "Blocked: IoT Controller is $[Status]$. State is UNKNOWN. Cannot proceed."
5. **Rule 5 (Restoration Eligibility):**
   $$\text{Only if } |C_{reopen}| = 0 \land |C_{sub}| = N \land R_{sup} = \text{APPROVED} \land H(C_{iot}) = \text{ONLINE} \implies \mathbf{ELIGIBLE\_FOR\_REVIEW}$$

### 3.2 Sequence Diagram: Field Clearance to Simulated Restoration

```
 Lineworker (Field)          Supervisor (Site)         Clearance Rule Engine      Control Room (Admin)
         │                          │                            │                         │
         │ (1) QR Scan & OTP        │                            │                         │
         ├─────────────────────────►│                            │                         │
         │                          │                            │                         │
         │ (2) Submit 4-pt Checklist│                            │                         │
         ├──────────────────────────────────────────────────────►│                         │
         │                          │                            │ Evaluates Tally         │
         │                          │                            │ (Status: BLOCKED)       │
         │                          │                            ├────────────────────────►│
         │                          │                            │                         │ Shows 1/3 Done
         │ (Other crew submits)     │                            │                         │
         ├──────────────────────────────────────────────────────►│                         │
         │                          │                            │ 3/3 Submitted           │
         │                          │                            │ (Status: AWAIT_REVIEW)  │
         │                          │                            ├────────────────────────►│
         │                          │                            │                         │ Shows Awaiting Review
         │                          │ (3) Conducts Site Check    │                         │
         │                          │     (Rods off, crew clear) │                         │
         │                          ├───────────────────────────►│                         │
         │                          │     Record Approval        │ Verifies 100% Conditions│
         │                          │                            │ (Status: ELIGIBLE)      │
         │                          │                            ├────────────────────────►│
         │                          │                            │                         │ Button Unlocks
         │                          │                            │                         │
         │                          │                            │ (4) Authorize Switch    │
         │                          │                            │◄────────────────────────┤
         │                          │                            │ Executes Simulated Relay│
         │                          │                            ├────────────────────────►│ Logs Completion
```

---

## 4. Entity-Relationship & Database Schema

The database uses a normalized 3NF schema compatible with ANSI SQL, SQLite 3, and MySQL 8.0+.

```
+----------------+       +-------------------+       +-----------------------+
|  users         |◄──────┤  worker_profiles  |       |  maintenance_areas    |
|  - id (PK)     |       |  - id (PK)        |       |  - id (PK)            |
|  - username    |       |  - user_id (FK)   |       |  - code (UNIQUE)      |
|  - email       |       |  - worker_code    |       |  - name               |
|  - password    |       |  - badge_hash     |       |  - substation         |
|  - role        |       +-------------------+       |  - lat, lng           |
+-------┬--------+                                   +-----------┬-----------+
        │                                                        │
        │ creates/supervises                                     │
        ▼                                                        ▼
+----------------------------------------------------------------------------+
|  maintenance_jobs                                                          |
|  - id (PK)                                                                 |
|  - job_code (UNIQUE, e.g. EG-JOB-1001)                                     |
|  - area_id (FK -> maintenance_areas.id)                                    |
|  - controller_id (FK -> controllers.id)                                    |
|  - supervisor_id (FK -> users.id)                                          |
|  - status (MAINTENANCE_ACTIVE, PENDING_REVIEW, CLEARANCE_COMPLETE)         |
|  - restoration_status (BLOCKED, AWAITING_REVIEW, ELIGIBLE, SIMULATED)       |
|  - blocking_reason (Authoritative diagnostic message)                      |
+-------┬─────────────────────────────┬──────────────────────────┬-----------+
        │                             │                          │
        ▼                             ▼                          ▼
+---------------------+     +----------------------+   +---------------------+
| job_worker_assign   |     | clearance_submission |   | supervisor_reviews  |
| - id (PK)           |     | - id (PK)            |   | - id (PK)           |
| - job_id (FK)       |     | - job_id (FK)        |   | - job_id (FK)       |
| - worker_user_id(FK)|     | - worker_user_id (FK)|   | - supervisor_id (FK)|
| - is_clearance_req  |     | - status (SUBMITTED, |   | - review_status     |
| UNIQUE(job, worker) |     |           REOPENED)  |   | - procedural_notes  |
+---------------------+     | - reopen_reason      |   +---------------------+
                            +----------------------+
```

---

## 5. Security & Anti-Tampering Engineering

1. **Server-Side Authority:** The browser is strictly a presentation client. Any attempt to modify clearance counts or restoration eligibility by editing client state is ignored, as the backend recalculates relational counts on every transition request.
2. **Cryptographic Password & Challenge Hashing:** Lineworker passwords and temporary two-factor OTP tokens are hashed using `PASSWORD_BCRYPT`. Production secrets are never logged.
3. **Attempt Limiting & Token Expiration:** OTP challenges expire strictly after 5 minutes and are invalidated after 3 failed attempts to protect against brute-force attacks.
4. **Append-Only Audit Journal:** All security and state events (logins, OTP verifications, checklist signatures, hazard reopenings, and denied switching attempts) are permanently recorded in `audit_logs` with correlation IDs.

---

## 6. Hardware Boundary & Low-Voltage Demonstration Circuit

### 6.1 Demonstration Boundary
- The included ESP32 firmware (`firmware/esp32_electroguard_demo.ino`) operates strictly at low voltages (3.3V / 5V DC).
- Output states are indicated via colored LEDs:
  - **RED LED (PIN 25):** Feeder Restriction Active / Power Restoration Blocked.
  - **AMBER LED (PIN 26):** Lineworker clearance in progress / Supervisor review pending.
  - **GREEN LED (PIN 27):** Authorized simulated power restoration indicator (energized only when all criteria pass).
  - **BLUE LED (PIN 2):** Periodic network heartbeat pulse.
- **SAFETY WARNING:** Student prototypes must never be connected directly to mains equipment or utility switchgear. Any eventual real-world utility integration requires utility-certified Remote Terminal Units (RTUs), IEC 60870-5-104 or DNP3 protocol standards, galvanic optical isolation, and certified utility safety signoff.

---

## 7. Failure Modes & Effects Analysis (FMEA)

| Failure Mode | Direct Effect | System Fail-Safe Mechanism |
|---|---|---|
| Lineworker phone loses battery mid-repair | Lineworker cannot submit clearance | Feeder remains in **BLOCKED** state. Feeder cannot be energized without explicit signoff. |
| ESP32 controller loses Wi-Fi connection | Hardware state becomes unreachable | State changes to **UNKNOWN**. Restoration switching is blocked until link is restored. |
| Lineworker discovers loose clamp during descent | Feeder could have been cleared prematurely | Lineworker taps **Reopen Clearance**. Entire clearance set is invalidated; restrictions reinstated immediately. |
| Operator accidentally clicks "Restore Power" | Premature energization attempt | Central Rule Engine intercepts request, rejects switching with HTTP 422, and logs incident to audit trail. |
| Malicious client sends spoofed clearance count | Attempted bypass of lineworker signoff | Backend queries actual database rows. Client payload count is completely ignored. |

---

## 8. Verification & Test Matrix

All 15 acceptance tests are executed via `tests/AcceptanceTests.php` and run automatically on launch:

| Test ID | Objective | Input / Condition | Expected Result | Actual Result |
|---|---|---|---|---|
| **TEST-01** | Multi-worker incompleteness | Job requires 3; 1 submits | Restoration = BLOCKED | **PASSED** |
| **TEST-02** | Supervisor review enforcement | All 3 submit; No supervisor review | Restoration = AWAITING_REVIEW | **PASSED** |
| **TEST-03** | Unassigned worker protection | Worker not in assignment list submits | HTTP 403 / Rejected | **PASSED** |
| **TEST-04** | OTP attempt limiter | Wrong OTP submitted | Attempt count decrements | **PASSED** |
| **TEST-05** | Hazard reopening invalidation | Reopen submitted clearance with reason | Restoration = BLOCKED; Restored | **PASSED** |
| **TEST-06** | Hardware drop fail-safe | Controller connection status = OFFLINE | Controller = UNKNOWN; Blocked | **PASSED** |
| **TEST-07** | Privilege escalation defense | Lineworker attempts admin switching | HTTP 403 / Forbidden | **PASSED** |
| **TEST-08** | Idempotency / Duplicate protection| Double submission sent rapidly | Single record preserved (Upsert)| **PASSED** |
| **TEST-09** | Rejection audit recording | Blocked switching attempted | Denied record logged with reason| **PASSED** |
| **TEST-10** | Database relational persistence | Application server restarted | Relational state intact | **PASSED** |
| **TEST-11** | Mobile viewport usability | Screen width < 768px | Offcanvas drawer & fluid cards | **PASSED** |
| **TEST-12** | Audit trail completeness | Inspect audit_logs table | 15+ events accurately recorded | **PASSED** |
| **TEST-13** | Stale heartbeat distinction | Heartbeat > 10 min | State = UNKNOWN (Not safe) | **PASSED** |
| **TEST-14** | Client tampering defense | Client sends forged is_eligible=true | Backend evaluates rows strictly | **PASSED** |
| **TEST-15** | Dependency recalculation | Reopened clearance reset | Job status = MAINTENANCE_ACTIVE | **PASSED** |
