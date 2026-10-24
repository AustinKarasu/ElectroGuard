# ElectroGuard — REST API Specification
**Project:** ElectroGuard | **Team:** GodsPlanKangra  
**Base URL:** `http://localhost:8000/api`  
**Content-Type:** `application/json`  

---

## 1. Authentication Endpoints

### POST `/api/auth/login`
Authenticate using username or email with password.
- **Request Body:**
  ```json
  {
    "identifier": "admin@electroguard.local",
    "password": "Admin@123"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "message": "Login successful.",
    "user": {
      "id": 1,
      "username": "admin",
      "email": "admin@electroguard.local",
      "role": "admin",
      "full_name": "Er. Aayan Parmar (Admin)",
      "phone_masked": "+91 98*** **001"
    }
  }
  ```

### POST `/api/auth/demo-login`
Quick demonstration role switcher (admin, supervisor, worker).
- **Request Body:**
  ```json
  {
    "role": "worker",
    "worker_index": 1
  }
  ```

### GET `/api/auth/me`
Retrieve currently authenticated session user profile and worker badge information.

### POST `/api/auth/logout`
Terminate the current session.

---

## 2. Maintenance Jobs Endpoints

### GET `/api/jobs`
Retrieve all maintenance jobs with authoritative clearance evaluation.
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "job_code": "EG-JOB-1001",
        "area_name": "Kangra Demonstration Feeder A",
        "title": "11kV Insulator Replacement & Tree Trimming",
        "status": "MAINTENANCE_ACTIVE",
        "restoration_status": "BLOCKED",
        "evaluation": {
          "required_count": 3,
          "submitted_count": 1,
          "is_eligible": false,
          "blocking_reason": "Blocked: 2 required worker(s) have not submitted clearance."
        }
      }
    ]
  }
  ```

### GET `/api/jobs/{id}`
Retrieve a single maintenance job with assigned workers, clearances, and supervisor notes.

### POST `/api/jobs`
Create a new maintenance job with multi-worker clearance requirement.
- **Request Body:**
  ```json
  {
    "job_code": "EG-JOB-1005",
    "area_id": 1,
    "supervisor_id": 2,
    "title": "Substation Bushing Replacement",
    "description": "Routine transformer overhaul",
    "safety_notes": "Portable earth clamps installed at tower 12 and 14",
    "worker_ids": [3, 4]
  }
  ```

---

## 3. Worker Clearance & OTP Endpoints

### POST `/api/clearance/verify-worker-id`
Verify worker barcode / QR badge against job assignment records.
- **Request Body:**
  ```json
  {
    "worker_code": "WK-KGR-701",
    "job_id": 1
  }
  ```

### POST `/api/jobs/{id}/otp/request`
Issue a cryptographic 6-digit one-time verification challenge.
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "challenge_id": 12,
      "purpose": "CLEARANCE",
      "expires_at": "2026-10-24 20:30:00",
      "demo_code": "481920",
      "message": "Simulated verification code issued for prototype demonstration."
    }
  }
  ```

### POST `/api/jobs/{id}/otp/verify`
Validate entered verification code.
- **Request Body:**
  ```json
  {
    "code": "481920"
  }
  ```

### POST `/api/jobs/{id}/clearances`
Submit digital clearance declarations (work completed, site cleared, tools removed, safety acknowledgment).

### POST `/api/jobs/{id}/clearances/reopen`
Reopen a previously submitted clearance with reason, invalidating dependent restoration authorizations.
- **Request Body:**
  ```json
  {
    "reason": "Loose ground wire clamp observed on phase B crossarm."
  }
  ```

### POST `/api/jobs/{id}/review`
Record supervisor procedural safety inspection (PPE verified, personnel accounted for).

---

## 4. Restoration Request Endpoints

### GET `/api/restoration-requests`
List all restoration requests with current eligibility and blocking reasons.

### POST `/api/jobs/{id}/restoration-requests/execute`
Attempt authorized simulated restoration switching.
- **Response when Blocked (422 Unprocessable Entity):**
  ```json
  {
    "success": false,
    "error": {
      "code": "CLEARANCE_INCOMPLETE",
      "message": "Blocked: 2 required worker(s) have not submitted clearance.",
      "details": { ... }
    }
  }
  ```

---

## 5. IoT Controller Simulation Endpoints

### GET `/api/controllers`
List all registered ESP32 controllers, connection status, relay states, and heartbeats.

### POST `/api/controllers/{id}/status`
Toggle simulated controller state (`ONLINE`, `OFFLINE`, `STALE`, `UNKNOWN`).

### POST `/api/controllers/{id}/command`
Send low-voltage test pulse to simulated ESP32 controller.

---

## 6. Audit & Analytics Endpoints

### GET `/api/audit-logs`
Query immutable append-only audit trail with optional `search`, `role`, and `result` filters.

### GET `/api/reports/summary`
Retrieve aggregate counts for dashboard charts and metrics.

### GET `/api/reports/export-csv`
Download official maintenance and clearance CSV report.
