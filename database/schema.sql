-- ========================================================
-- ELECTROGUARD DATABASE SCHEMA
-- Project: ElectroGuard - Smart Electrical Worker Safety & Maintenance Coordination
-- Team: GodsPlanKangra (Aayan Parmar, Manjinder Uldeen, Aayush Parmar)
-- Compatible with MySQL 8.0+ and SQLite 3+
-- ========================================================

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL, -- 'admin', 'supervisor', 'worker'
    full_name VARCHAR(100) NOT NULL,
    phone_masked VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS worker_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    worker_code VARCHAR(30) NOT NULL UNIQUE,
    department VARCHAR(100) DEFAULT 'Kangra Feeder Maintenance Div.',
    skill_level VARCHAR(50) DEFAULT 'Certified 11kV Line Technician',
    badge_hash VARCHAR(64) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS maintenance_areas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    substation VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 6) NOT NULL,
    longitude DECIMAL(10, 6) NOT NULL,
    voltage_rating VARCHAR(30) DEFAULT '11 kV Demonstration Line',
    status VARCHAR(30) DEFAULT 'RESTRICTED', -- 'RESTRICTED', 'PENDING_CLEARANCE', 'CLEARED_READY'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS controllers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    controller_uid VARCHAR(50) NOT NULL UNIQUE,
    area_id INTEGER,
    connection_status VARCHAR(20) DEFAULT 'ONLINE', -- 'ONLINE', 'OFFLINE', 'STALE', 'UNKNOWN'
    relay_state VARCHAR(20) DEFAULT 'ISOLATED', -- 'ISOLATED', 'ENERGIZED', 'UNKNOWN'
    last_heartbeat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    response_delay_ms INTEGER DEFAULT 0,
    fail_next_command INTEGER DEFAULT 0,
    mode VARCHAR(30) DEFAULT 'SIMULATION',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (area_id) REFERENCES maintenance_areas(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS maintenance_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_code VARCHAR(30) NOT NULL UNIQUE,
    area_id INTEGER NOT NULL,
    controller_id INTEGER,
    supervisor_id INTEGER NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    status VARCHAR(40) DEFAULT 'MAINTENANCE_ACTIVE', -- 'MAINTENANCE_ACTIVE', 'PENDING_SUPERVISOR_REVIEW', 'CLEARANCE_COMPLETE', 'CLOSED'
    restoration_status VARCHAR(40) DEFAULT 'BLOCKED', -- 'NOT_REQUESTED', 'BLOCKED', 'AWAITING_REVIEW', 'ELIGIBLE_FOR_REVIEW', 'SIMULATED_COMPLETION', 'REJECTED', 'CANCELLED'
    blocking_reason VARCHAR(255) DEFAULT 'Maintenance active: Worker clearances pending',
    safety_notes TEXT,
    planned_start TIMESTAMP,
    planned_end TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (area_id) REFERENCES maintenance_areas(id),
    FOREIGN KEY (controller_id) REFERENCES controllers(id),
    FOREIGN KEY (supervisor_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS job_worker_assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id INTEGER NOT NULL,
    worker_user_id INTEGER NOT NULL,
    is_clearance_required INTEGER DEFAULT 1,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(job_id, worker_user_id),
    FOREIGN KEY (job_id) REFERENCES maintenance_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (worker_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS otp_challenges (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    otp_hint VARCHAR(10), -- For prototype demonstration display only
    purpose VARCHAR(30) DEFAULT 'CLEARANCE', -- 'CLEARANCE', 'REOPEN'
    attempt_count INTEGER DEFAULT 0,
    max_attempts INTEGER DEFAULT 3,
    is_verified INTEGER DEFAULT 0,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES maintenance_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS clearance_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id INTEGER NOT NULL,
    worker_user_id INTEGER NOT NULL,
    status VARCHAR(20) DEFAULT 'SUBMITTED', -- 'SUBMITTED', 'REOPENED', 'REVOKED'
    work_completed_declared INTEGER DEFAULT 1,
    site_cleared_declared INTEGER DEFAULT 1,
    tools_removed_declared INTEGER DEFAULT 1,
    safety_warning_acknowledged INTEGER DEFAULT 1,
    reopen_reason TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reopened_at TIMESTAMP,
    UNIQUE(job_id, worker_user_id),
    FOREIGN KEY (job_id) REFERENCES maintenance_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (worker_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS supervisor_reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id INTEGER NOT NULL,
    supervisor_id INTEGER NOT NULL,
    review_status VARCHAR(20) DEFAULT 'APPROVED', -- 'APPROVED', 'REJECTED', 'CHANGES_REQUESTED'
    procedural_notes TEXT,
    ppe_and_grounding_verified INTEGER DEFAULT 1,
    personnel_accounted_for INTEGER DEFAULT 1,
    reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES maintenance_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS restoration_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id INTEGER NOT NULL,
    requested_by INTEGER NOT NULL,
    status VARCHAR(40) DEFAULT 'BLOCKED', -- 'BLOCKED', 'AWAITING_REVIEW', 'ELIGIBLE_FOR_REVIEW', 'REJECTED', 'CANCELLED', 'SIMULATED_COMPLETION'
    decision_reason TEXT,
    reviewed_by INTEGER,
    simulated_switching_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES maintenance_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS controller_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    controller_id INTEGER NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    previous_state VARCHAR(30),
    new_state VARCHAR(30),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (controller_id) REFERENCES controllers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS alerts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    severity VARCHAR(20) NOT NULL, -- 'CRITICAL', 'WARNING', 'INFO'
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    related_job_id INTEGER,
    is_read INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (related_job_id) REFERENCES maintenance_jobs(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    correlation_id VARCHAR(50) NOT NULL,
    actor_id INTEGER,
    actor_name VARCHAR(100) NOT NULL,
    actor_role VARCHAR(30) NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(30) NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    result VARCHAR(20) NOT NULL, -- 'SUCCESS', 'BLOCKED', 'FAILED'
    reason TEXT,
    ip_address VARCHAR(45) DEFAULT '127.0.0.1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
