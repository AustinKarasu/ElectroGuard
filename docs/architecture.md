# ElectroGuard — System Architecture Documentation
**Project:** ElectroGuard (Smart Electrical Worker Safety and Maintenance Coordination System)  
**Team:** GodsPlanKangra  
- Aayan Parmar — Team Leader  
- Manjinder Uldeen — Team Member  
- Aayush Parmar — Team Member  
**Academic Program:** Computer Engineering (3rd Year)  
**Category:** Rural Development & Livelihoods  
**Tagline:** *Every Clearance Matters.*  

---

## 1. System Overview

ElectroGuard is a prototype utility-management and industrial safety coordination platform designed to address the fatal risk of premature electrical power restoration while lineworkers and technicians are actively working on distribution feeders.

The core principle implemented throughout the system is:
> **"No completed clearance workflow, no authorization to proceed with a restoration request."**

### Core Architectural Concepts
ElectroGuard strictly separates three distinct operational layers:
1. **Maintenance Clearance:** Field worker declaration and supervisor procedural safety verification that tasks are complete, grounds are removed, and personnel have exited the hazard zone.
2. **Restoration Request Authorization:** Digital coordination layer that determines whether an administrative restoration request may proceed to review based on authoritative database records.
3. **Physical Electrical Switching:** The actual energization of electrical lines. ElectroGuard **does not** claim to physically isolate high-voltage power lines; physical isolation requires certified utility procedures, mechanical lockout/tagout (LOTO), and qualified utility staff.

---

## 2. High-Level Architecture Diagram

```
+-----------------------------------------------------------------------------------+
|                              PRESENTATION LAYER                                    |
|                                                                                   |
|  [Public Landing Page]  <--->  [Worker Mobile Portal]  <--->  [Admin Control Room] |
|   (Impact & Architecture)       (ID Scan, OTP, Clearance)       (Map, Jobs, IoT, Logs)  |
+-----------------------------------------------------------------------------------+
                                         │
                                         ▼ (HTTP / JSON REST APIs)
+-----------------------------------------------------------------------------------+
|                               APPLICATION LAYER                                   |
|                                                                                   |
|  [Auth & Role Guard]    [Clearance Controller]    [Restoration Gateway]            |
|          │                        │                         │                     |
|          ▼                        ▼                         ▼                     |
|  ┌─────────────────────────────────────────────────────────────────────────────┐  |
|  │                     CENTRAL CLEARANCE RULE ENGINE                           │  |
|  │  - Evaluates Authoritative Worker Clearances (Submitted vs Reopened)        │  |
|  │  - Enforces Supervisor Safety Verification Requirement                      │  |
|  │  - Validates IoT Controller Heartbeat & Online State                        │  |
|  │  - Blocks Incomplete Restoration Authorizations                             │  |
|  └─────────────────────────────────────────────────────────────────────────────┘  |
|          │                        │                         │                     |
|  [Audit Logger (Append-Only)]     [Alert Dispatcher]        [Controller Simulator]|
+-----------------------------------------------------------------------------------+
                                         │
                                         ▼ (PDO SQL Transactions)
+-----------------------------------------------------------------------------------+
|                               PERSISTENCE LAYER                                   |
|                                                                                   |
|  [SQLite 3 Engine (Local Default)]   /   [MySQL 8.0+ Enterprise Database]         |
|  - Users & Worker Profiles       - Maintenance Areas & Feeders                    |
|  - Maintenance Jobs              - Job Worker Assignments                         |
|  - OTP Verification Challenges   - Digital Clearance Submissions                  |
|  - Supervisor Safety Reviews     - Controller Registry & Heartbeats               |
|  - Alerts & Notifications        - Immutable Audit Event Trail                    |
+-----------------------------------------------------------------------------------+
                                         │
                                         ▼ (Simulated Low-Voltage I/O)
+-----------------------------------------------------------------------------------+
|                        HARDWARE DEMONSTRATION LAYER                                |
|                                                                                   |
|  [ESP32 Demonstration Controller (EG-ESP32-DEMO-01)]                               |
|  - Low-Voltage (3.3V/5V DC) Bench Demonstration Only                              |
|  - Fail-Safe Default: RESTRICTED State (Red LED Active)                           |
|  - Isolated Switching Indicator: GREEN LED (Authorized Simulation Only)          |
|  - Zero Connection to Live Grid Infrastructure                                    |
+-----------------------------------------------------------------------------------+
```

---

## 3. Component Breakdown

### A. Central Clearance Rule Engine (`ClearanceRuleEngine.php`)
The single source of truth for all safety and clearance transitions:
- **Rule 1 (Completeness):** Requires all assigned lineworkers on a job to independently verify and sign their clearance checklist.
- **Rule 2 (Anti-Tampering):** Clearance completeness is always computed from authoritative database records. Browser-supplied counts are never trusted.
- **Rule 3 (Invalidation on Reopening):** If any worker reopens or revokes a clearance (e.g. discovers an ungrounded tool or damaged jumper during pole descent), the previous clearance set is instantly invalidated, the job is returned to restricted status, and any pending restoration request is blocked.
- **Rule 4 (Supervisor Procedural Review):** Even with 100% worker clearances submitted, a restoration request cannot proceed until an assigned supervisor conducts a procedural check (PPE verified, personnel off-structure, earthing clamps removed).
- **Rule 5 (Fail-Safe Controller Verification):** If the IoT controller is offline, stale, or reports an UNKNOWN state, the rule engine blocks restoration eligibility.

### B. Worker Portal & Verification
- Field-optimized responsive interface.
- Worker ID barcode/QR simulation with server verification of active job assignment.
- Two-Factor OTP challenge verification with cryptographic hashing, attempt limiting (3 tries), and 5-minute expiry.
- Mandatory 4-point safety declaration checklist.

### C. Administrator & Operations Control Center
- Real Leaflet OpenStreetMap view of Kangra demonstration network with color-coded live feeder status (Red = Restricted, Amber = Review Pending, Green = Cleared in Demo).
- Complete Maintenance Job creator with multi-worker assignment.
- Restoration Request review center displaying precise blocking reasons.
- ESP32 Controller simulation console allowing manual injection of offline states, network latency, and command failures.
- Searchable append-only audit trail and analytical reporting charts.

---

## 4. Hardware Safety Boundary & Ethics
1. **Strictly Low-Voltage (3.3V / 5V):** The hardware demonstration uses micro-controller GPIO pins connected to colored LEDs.
2. **Never Connected to Mains:** Under no circumstances should hobby micro-controllers or relay modules be connected to high-voltage lines.
3. **Prototype Disclosure:** The application displays clear demonstration badges across all views to prevent any confusion with certified commercial or government utility SCADA systems.
