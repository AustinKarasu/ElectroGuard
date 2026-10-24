-- ========================================================
-- ELECTROGUARD SEED DATA
-- Project: ElectroGuard - Smart Electrical Worker Safety & Maintenance Coordination
-- Team: GodsPlanKangra (Aayan Parmar, Manjinder Uldeen, Aayush Parmar)
-- ========================================================

-- Users
-- Password for admin: Admin@123
-- Password for supervisor: Supervisor@123
-- Password for workers: Worker@123
INSERT INTO users (id, username, email, password_hash, role, full_name, phone_masked) VALUES
(1, 'admin', 'admin@electroguard.local', '$2y$10$csI6Owu9rK7xDEg3gqqgge9trosDKiIObAp5RGdZd2jUnEuSUBJIK', 'admin', 'Er. Aayan Parmar (Admin)', '+91 98*** **001'),
(2, 'supervisor', 'supervisor@electroguard.local', '$2y$10$HhN2BykGK.QFex4.o2VHJOx9ha9rZJSJzmU65.MlmFHat8TcOYFMS', 'supervisor', 'Er. Manjinder Uldeen (Supervisor)', '+91 98*** **002'),
(3, 'worker1', 'worker1@electroguard.local', '$2y$10$P9tAvEE772yIXvp9P49pmeYvA/zZRjiRQfWxnwAPHnTTvwjs6ldDi', 'worker', 'Rakesh Kumar (Line Tech)', '+91 98*** **412'),
(4, 'worker2', 'worker2@electroguard.local', '$2y$10$P9tAvEE772yIXvp9P49pmeYvA/zZRjiRQfWxnwAPHnTTvwjs6ldDi', 'worker', 'Sunil Sharma (Line Tech)', '+91 98*** **831'),
(5, 'worker3', 'worker3@electroguard.local', '$2y$10$P9tAvEE772yIXvp9P49pmeYvA/zZRjiRQfWxnwAPHnTTvwjs6ldDi', 'worker', 'Vikas Dogra (Assistant Tech)', '+91 94*** **190'),
(6, 'aayush', 'aayush@electroguard.local', '$2y$10$csI6Owu9rK7xDEg3gqqgge9trosDKiIObAp5RGdZd2jUnEuSUBJIK', 'admin', 'Er. Aayush Parmar (Safety Auditor)', '+91 98*** **003');

-- Worker Profiles
INSERT INTO worker_profiles (id, user_id, worker_code, department, skill_level, badge_hash) VALUES
(1, 3, 'WK-KGR-701', 'Kangra Feeder Maintenance Div.', 'Level 3 Certified High-Voltage Lineman', 'sha256-wk-701-kgr-verified'),
(2, 4, 'WK-KGR-702', 'Kangra Feeder Maintenance Div.', 'Level 2 Certified Distribution Technician', 'sha256-wk-702-kgr-verified'),
(3, 5, 'WK-KGR-703', 'Substation Support Wing', 'Level 1 Certified Assistant Lineman', 'sha256-wk-703-kgr-verified');

-- Maintenance Areas (Kangra, HP demonstration network)
INSERT INTO maintenance_areas (id, code, name, substation, latitude, longitude, voltage_rating, status) VALUES
(1, 'AREA-KGR-01', 'Kangra Demonstration Feeder A', 'Kangra 33/11kV Substation', 32.099800, 76.269100, '11 kV Demonstration Feeder', 'RESTRICTED'),
(2, 'AREA-KGR-02', 'Nagrota Demonstration Feeder B', 'Nagrota Bagwan 33kV Substation', 32.108500, 76.381200, '11 kV Industrial Feeder', 'RESTRICTED'),
(3, 'AREA-KGR-03', 'Shahpur Demonstration Feeder C', 'Shahpur 33/11kV Substation', 32.221000, 76.177000, '11 kV Rural Feeder', 'RESTRICTED'),
(4, 'AREA-KGR-04', 'Dharamshala Road Feeder D', 'Dharamshala 33kV Grid', 32.219000, 76.323400, '22 kV Demonstration Circuit', 'CLEARED_READY');

-- Controllers (ESP32 Demonstration Units)
INSERT INTO controllers (id, controller_uid, area_id, connection_status, relay_state, last_heartbeat, response_delay_ms, fail_next_command, mode) VALUES
(1, 'EG-ESP32-DEMO-01', 1, 'ONLINE', 'ISOLATED', datetime('now'), 0, 0, 'SIMULATION'),
(2, 'EG-ESP32-DEMO-02', 2, 'ONLINE', 'ISOLATED', datetime('now'), 0, 0, 'SIMULATION'),
(3, 'EG-ESP32-DEMO-03', 3, 'OFFLINE', 'UNKNOWN', datetime('now', '-25 minutes'), 0, 0, 'SIMULATION'),
(4, 'EG-ESP32-DEMO-04', 4, 'ONLINE', 'ENERGIZED', datetime('now'), 0, 0, 'SIMULATION');

-- Maintenance Jobs
-- Job 1: 3 assigned workers, 1 submitted clearance, 2 pending -> BLOCKED
INSERT INTO maintenance_jobs (id, job_code, area_id, controller_id, supervisor_id, title, description, status, restoration_status, blocking_reason, safety_notes, planned_start, planned_end) VALUES
(1, 'EG-JOB-1001', 1, 1, 2, '11kV Insulator Replacement & Tree Trimming', 'Replacement of cracked disc insulators at towers 12-16 and tree limb clearance along Kangra Market road section.', 'MAINTENANCE_ACTIVE', 'BLOCKED', 'Blocked: 2 required workers (Sunil Sharma, Vikas Dogra) have not submitted clearance.', 'Standard earthing clamps attached at Pole 12 and Pole 16. Lockout tag attached to isolator handle. Discharging completed before ascent.', datetime('now', '-3 hours'), datetime('now', '+2 hours'));

-- Job 2: 3 assigned workers, all 3 submitted clearances -> Awaiting supervisor review
INSERT INTO maintenance_jobs (id, job_code, area_id, controller_id, supervisor_id, title, description, status, restoration_status, blocking_reason, safety_notes, planned_start, planned_end) VALUES
(2, 'EG-JOB-1002', 2, 2, 2, 'Transformer Bushing Maintenance & Cable Termination', 'Preventive inspection and replacement of worn secondary bushings at Nagrota Commercial Sub-feeder Transformer 2.', 'PENDING_SUPERVISOR_REVIEW', 'AWAITING_REVIEW', 'Blocked: All worker clearances submitted; Supervisor procedural review is pending.', 'Earth discharge rods applied on primary and secondary sides. No worker should approach bushings without rubber gloves class 2.', datetime('now', '-5 hours'), datetime('now', '-30 minutes'));

-- Job 3: 2 assigned workers, 1 submitted and then REOPENED -> Invalidation rule demonstrated
INSERT INTO maintenance_jobs (id, job_code, area_id, controller_id, supervisor_id, title, description, status, restoration_status, blocking_reason, safety_notes, planned_start, planned_end) VALUES
(3, 'EG-JOB-1003', 3, 3, 2, 'Overhead Conductor Restringing & Cross-Arm Realignment', 'Re-tensioning sagging spans between poles 34 to 38 on Shahpur rural feeder.', 'MAINTENANCE_ACTIVE', 'BLOCKED', 'Blocked: Worker clearance was reopened by Rakesh Kumar (Reason: Loose jumper clamp discovered during final pole descent). Previous clearance set invalidated.', 'Temporary stay wires deployed. Controller EG-ESP32-DEMO-03 is reporting OFFLINE/UNKNOWN state.', datetime('now', '-4 hours'), datetime('now', '+1 hour'));

-- Job 4: Completed demonstration job
INSERT INTO maintenance_jobs (id, job_code, area_id, controller_id, supervisor_id, title, description, status, restoration_status, blocking_reason, safety_notes, planned_start, planned_end) VALUES
(4, 'EG-JOB-1004', 4, 4, 2, 'Lightning Arrester Periodic Inspection', 'Routine surge arrester insulation resistance testing and earthing pit resistance measurement.', 'CLEARANCE_COMPLETE', 'SIMULATED_COMPLETION', 'Workflow completed in demonstration mode. (Physical switching subject to utility manual safety protocols)', 'Measurement verified < 2.5 ohms. All personnel evacuated and accounted for.', datetime('now', '-8 hours'), datetime('now', '-6 hours'));

-- Job Worker Assignments
-- Job 1001: 3 workers assigned (Workers 3, 4, 5)
INSERT INTO job_worker_assignments (id, job_id, worker_user_id, is_clearance_required) VALUES
(1, 1, 3, 1),
(2, 1, 4, 1),
(3, 1, 5, 1);

-- Job 1002: 3 workers assigned (Workers 3, 4, 5)
INSERT INTO job_worker_assignments (id, job_id, worker_user_id, is_clearance_required) VALUES
(4, 2, 3, 1),
(5, 2, 4, 1),
(6, 2, 5, 1);

-- Job 1003: 2 workers assigned (Workers 3, 4)
INSERT INTO job_worker_assignments (id, job_id, worker_user_id, is_clearance_required) VALUES
(7, 3, 3, 1),
(8, 3, 4, 1);

-- Job 1004: 1 worker assigned (Worker 3)
INSERT INTO job_worker_assignments (id, job_id, worker_user_id, is_clearance_required) VALUES
(9, 4, 3, 1);

-- Clearance Submissions
-- Job 1001: Worker 3 submitted. (Workers 4 and 5 haven't)
INSERT INTO clearance_submissions (id, job_id, worker_user_id, status, work_completed_declared, site_cleared_declared, tools_removed_declared, safety_warning_acknowledged, submitted_at) VALUES
(1, 1, 3, 'SUBMITTED', 1, 1, 1, 1, datetime('now', '-45 minutes'));

-- Job 1002: All 3 workers submitted!
INSERT INTO clearance_submissions (id, job_id, worker_user_id, status, work_completed_declared, site_cleared_declared, tools_removed_declared, safety_warning_acknowledged, submitted_at) VALUES
(2, 2, 3, 'SUBMITTED', 1, 1, 1, 1, datetime('now', '-50 minutes')),
(3, 2, 4, 'SUBMITTED', 1, 1, 1, 1, datetime('now', '-40 minutes')),
(4, 2, 5, 'SUBMITTED', 1, 1, 1, 1, datetime('now', '-35 minutes'));

-- Job 1003: Worker 3 submitted and REOPENED
INSERT INTO clearance_submissions (id, job_id, worker_user_id, status, work_completed_declared, site_cleared_declared, tools_removed_declared, safety_warning_acknowledged, reopen_reason, submitted_at, reopened_at) VALUES
(5, 3, 3, 'REOPENED', 1, 1, 1, 1, 'Loose jumper clamp discovered at pole 14 during final descent. Linemen re-ascending for retorque.', datetime('now', '-2 hours'), datetime('now', '-1 hour'));

-- Job 1004: Worker 3 submitted
INSERT INTO clearance_submissions (id, job_id, worker_user_id, status, work_completed_declared, site_cleared_declared, tools_removed_declared, safety_warning_acknowledged, submitted_at) VALUES
(6, 4, 3, 'SUBMITTED', 1, 1, 1, 1, datetime('now', '-7 hours'));

-- Supervisor Reviews
INSERT INTO supervisor_reviews (id, job_id, supervisor_id, review_status, procedural_notes, ppe_and_grounding_verified, personnel_accounted_for, reviewed_at) VALUES
(1, 4, 2, 'APPROVED', 'All 1 lineworker accounted for off-structure. Earth leads removed. Ground station cleared.', 1, 1, datetime('now', '-6 hours', '+30 minutes'));

-- Restoration Requests
INSERT INTO restoration_requests (id, job_id, requested_by, status, decision_reason, reviewed_by, simulated_switching_at, created_at) VALUES
(1, 1, 1, 'BLOCKED', 'Blocked: 2 required workers have not submitted clearance.', NULL, NULL, datetime('now', '-40 minutes')),
(2, 2, 1, 'AWAITING_REVIEW', 'All 3 worker clearances submitted. Supervisor review required before restoration authorization review.', NULL, NULL, datetime('now', '-30 minutes')),
(3, 3, 1, 'BLOCKED', 'Blocked: Worker clearance reopened by Rakesh Kumar (WK-KGR-701). Controller is also OFFLINE.', NULL, NULL, datetime('now', '-55 minutes')),
(4, 4, 1, 'SIMULATED_COMPLETION', 'Prototype clearance complete. Simulated relay energized in isolated low-voltage demo environment.', 2, datetime('now', '-6 hours'), datetime('now', '-6 hours', '+10 minutes'));

-- Alerts
INSERT INTO alerts (id, severity, title, message, related_job_id, is_read, created_at) VALUES
(1, 'CRITICAL', 'Restoration Request Blocked', 'Restoration request for Kangra Demonstration Feeder A (EG-JOB-1001) blocked. 2 worker clearances pending.', 1, 0, datetime('now', '-40 minutes')),
(2, 'CRITICAL', 'Clearance Reopened Alert', 'Lineman Rakesh Kumar reopened clearance for Shahpur Feeder C (EG-JOB-1003): Loose jumper clamp discovered at pole 14.', 3, 0, datetime('now', '-1 hour')),
(3, 'WARNING', 'Controller Heartbeat Stale', 'ESP32 Controller EG-ESP32-DEMO-03 for Shahpur area has stopped responding. State marked UNKNOWN.', 3, 0, datetime('now', '-25 minutes')),
(4, 'INFO', 'Clearance Submitted', 'Worker Rakesh Kumar submitted digital clearance checklist for EG-JOB-1001.', 1, 1, datetime('now', '-45 minutes')),
(5, 'WARNING', 'Supervisor Review Pending', 'Job EG-JOB-1002 has received all 3 worker clearances. Awaiting supervisor safety inspection.', 2, 0, datetime('now', '-35 minutes'));

-- Audit Logs
INSERT INTO audit_logs (id, correlation_id, actor_id, actor_name, actor_role, action, entity_type, entity_id, result, reason, ip_address, created_at) VALUES
(1, 'CORR-SYS-001', 1, 'Er. Aayan Parmar', 'admin', 'JOB_CREATED', 'MAINTENANCE_JOB', 'EG-JOB-1001', 'SUCCESS', 'Created maintenance job for Kangra Feeder A with 3 required workers', '127.0.0.1', datetime('now', '-3 hours')),
(2, 'CORR-SYS-002', 3, 'Rakesh Kumar', 'worker', 'OTP_VERIFIED', 'OTP_CHALLENGE', 'EG-JOB-1001', 'SUCCESS', 'Demonstration OTP verified successfully for worker WK-KGR-701', '127.0.0.1', datetime('now', '-46 minutes')),
(3, 'CORR-SYS-003', 3, 'Rakesh Kumar', 'worker', 'CLEARANCE_SUBMITTED', 'CLEARANCE', 'EG-JOB-1001', 'SUCCESS', 'Checklist items verified; digital clearance declaration signed', '127.0.0.1', datetime('now', '-45 minutes')),
(4, 'CORR-SYS-004', 1, 'Er. Aayan Parmar', 'admin', 'RESTORATION_REQUESTED', 'RESTORATION', 'EG-JOB-1001', 'BLOCKED', 'Rule Engine: Blocked because 2 of 3 required clearances are missing', '127.0.0.1', datetime('now', '-40 minutes')),
(5, 'CORR-SYS-005', 3, 'Rakesh Kumar', 'worker', 'CLEARANCE_REOPENED', 'CLEARANCE', 'EG-JOB-1003', 'SUCCESS', 'Worker reported hazard during pole descent; clearance invalidated', '127.0.0.1', datetime('now', '-1 hour')),
(6, 'CORR-SYS-006', 1, 'Er. Aayan Parmar', 'admin', 'RESTORATION_REQUESTED', 'RESTORATION', 'EG-JOB-1003', 'BLOCKED', 'Rule Engine: Blocked because clearance was reopened and controller is OFFLINE', '127.0.0.1', datetime('now', '-55 minutes')),
(7, 'CORR-SYS-007', 2, 'Er. Manjinder Uldeen', 'supervisor', 'SUPERVISOR_REVIEW', 'MAINTENANCE_JOB', 'EG-JOB-1004', 'SUCCESS', 'Verified all site personnel accounted for and ground clamps removed', '127.0.0.1', datetime('now', '-6 hours', '+20 minutes')),
(8, 'CORR-SYS-008', 1, 'Er. Aayan Parmar', 'admin', 'SIMULATED_SWITCHING', 'CONTROLLER', 'EG-ESP32-DEMO-04', 'SUCCESS', 'Simulated low-voltage relay energization completed for demonstration', '127.0.0.1', datetime('now', '-6 hours'));
