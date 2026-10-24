# ElectroGuard — Quality Assurance & Test Plan
**Project:** ElectroGuard | **Team:** GodsPlanKangra  

This document outlines the 15 acceptance test scenarios implemented and executed within ElectroGuard.

| Test # | Test Scenario | Expected Outcome | Automated Test Status |
|---|---|---|---|
| **TEST 1** | Job requires 3 workers; only 1 submits clearance. | Restoration request blocked; message specifies 2 pending clearances. | **PASSED** |
| **TEST 2** | All 3 workers submit valid clearance. | Request blocked awaiting supervisor procedural review. | **PASSED** |
| **TEST 3** | Unassigned worker attempts to submit clearance for a job. | Backend rejects submission with 403 / unauthorized error. | **PASSED** |
| **TEST 4** | Invalid OTP submitted. | Verification fails, attempt counter increments, event logged. | **PASSED** |
| **TEST 5** | Submitted clearance is reopened with reason. | Clearance set invalidated; job returned to active maintenance; restoration blocked. | **PASSED** |
| **TEST 6** | IoT controller disconnects (OFFLINE/STALE). | State displayed as UNKNOWN; restoration switching prevented. | **PASSED** |
| **TEST 7** | Ordinary worker attempts administrator action. | Backend blocks request with 403 Forbidden. | **PASSED** |
| **TEST 8** | Duplicate clearance submissions sent rapidly. | Database upsert prevents duplicate rows or inconsistent counts. | **PASSED** |
| **TEST 9** | Restoration request rejected. | Reason recorded in audit log with correlation ID. | **PASSED** |
| **TEST 10** | Browser refresh / server restart. | Relational data persists accurately. | **PASSED** |
| **TEST 11** | Mobile viewport inspection (< 768px). | Layout adapts, sidebar folds into offcanvas drawer, map remains interactive. | **PASSED** |
| **TEST 12** | Audit log opened. | Historical trail of logins, scans, OTPs, clearances, and blocks accurately displayed. | **PASSED** |
| **TEST 13** | Controller reports stale heartbeat (> 10m). | UI marks state UNKNOWN, distinguishes from safe/off. | **PASSED** |
| **TEST 14** | Client-side manipulation of clearance count. | Backend evaluates database rows independently; ignores client payload. | **PASSED** |
| **TEST 15** | Job reopened after clearance. | All dependent request states recalculated centrally. | **PASSED** |

### Execution
Automated backend verification is executed via:
```bash
php tests/AcceptanceTests.php
```
