<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ElectroGuard — Smart Electrical Worker Safety & Maintenance Coordination System</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Leaflet CSS for Map -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <!-- ElectroGuard Industrial Design System -->
  <link rel="stylesheet" href="/assets/css/electroguard.css">
</head>
<body>

  <!-- Top Prototype Disclaimer Banner -->
  <div class="eg-proto-banner">
    <div class="d-flex align-items-center gap-2">
      <span class="eg-proto-badge">ACADEMIC PROTOTYPE</span>
      <span>Team <strong>GodsPlanKangra</strong> &bull; Computer Engineering (3rd Year) &bull; Social-Impact Hackathon</span>
    </div>
    <div class="d-none d-md-block text-warning small">
      <i class="bi bi-exclamation-triangle me-1"></i> Demonstration Environment Only: Does Not Directly Control Live Grid Equipment
    </div>
  </div>

  <!-- Global Toast Notification Container -->
  <div id="appToastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;"></div>

  <!-- ========================================================================= -->
  <!-- 1. PUBLIC LANDING PAGE VIEW                                                -->
  <!-- ========================================================================= -->
  <div id="view-landing" style="display: block;">
    <!-- Landing Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark eg-navbar sticky-top">
      <div class="container">
        <a class="navbar-brand" href="#" onclick="App.showView('landing')">
          <div class="eg-logo-symbol">
            <i class="bi bi-shield-fill-check"></i>
          </div>
          <div>
            <div>ElectroGuard</div>
            <div style="font-size: 0.68rem; font-weight: 400; color: #2DCDDC; letter-spacing: 0.5px;">SMART UTILITY SAFETY SYSTEM</div>
          </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="landingNav">
          <ul class="navbar-nav ms-auto align-items-center gap-3">
            <li class="nav-item"><a class="nav-link text-light" href="#problemSection">The Problem</a></li>
            <li class="nav-item"><a class="nav-link text-light" href="#solutionSection">Safety Pillars</a></li>
            <li class="nav-item"><a class="nav-link text-light" href="#workflowSection">5-Step Workflow</a></li>
            <li class="nav-item"><a class="nav-link text-light" href="#architectureSection">Architecture</a></li>
            <li class="nav-item">
              <button class="btn btn-outline-info btn-sm px-3" onclick="App.openLoginModal()">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In / Roles
              </button>
            </li>
            <li class="nav-item">
              <button class="btn btn-primary btn-sm px-3 shadow-sm" onclick="App.handleDemoLogin('admin')">
                <i class="bi bi-speedometer2 me-1"></i> Launch Control Room
              </button>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <header class="py-5 text-white" style="background: radial-gradient(circle at 80% 20%, #0F2744 0%, #08162B 70%); border-bottom: 1px solid rgba(45, 205, 220, 0.2);">
      <div class="container py-4">
        <div class="row align-items-center g-5">
          <div class="col-lg-7">
            <div class="badge bg-primary bg-opacity-25 text-cyan border border-info border-opacity-25 px-3 py-2 rounded-pill mb-3">
              <i class="bi bi-lightning-charge-fill me-1"></i> Rural Development & Livelihoods Hackathon Initiative
            </div>
            <h1 class="display-4 fw-bold lh-sm mb-3">
              Every Clearance <span style="color: #2DCDDC;">Matters.</span>
            </h1>
            <p class="lead text-light text-opacity-75 mb-4">
              Electrical line workers face fatal electrocution risks when distribution power is restored prematurely while repair work is still underway. ElectroGuard coordinates workers, supervisors, and control rooms under one non-negotiable rule:
            </p>

            <div class="p-3 rounded-3 mb-4 border border-danger border-opacity-50" style="background: rgba(223, 76, 91, 0.1);">
              <div class="d-flex align-items-center gap-2 text-danger fw-bold fs-6">
                <i class="bi bi-shield-x fs-4"></i>
                CENTRAL SAFETY PRINCIPLE:
              </div>
              <div class="text-white mt-1 fw-semibold">
                "No completed clearance workflow, no authorization to proceed with a restoration request."
              </div>
            </div>

            <div class="d-flex flex-wrap gap-3">
              <button class="btn btn-primary btn-lg px-4 shadow" onclick="App.handleDemoLogin('admin')">
                <i class="bi bi-play-circle-fill me-2"></i> Explore Control Room
              </button>
              <button class="btn btn-outline-cyan btn-lg px-4 btn-outline-light" onclick="App.handleDemoLogin('worker', 1)">
                <i class="bi bi-person-badge-fill me-2"></i> Worker Field Portal
              </button>
              <a href="#workflowSection" class="btn btn-link text-cyan text-decoration-none d-flex align-items-center">
                How It Works <i class="bi bi-arrow-right ms-2"></i>
              </a>
            </div>
          </div>

          <div class="col-lg-5">
            <!-- Prototype Schematic Preview Card -->
            <div class="eg-schematic-card border border-info border-opacity-25 shadow-lg">
              <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom border-secondary">
                <span class="text-cyan small fw-bold">FEEDER RESTRICTION STATUS</span>
                <span class="badge bg-danger">INTERLOCKED</span>
              </div>
              <div class="small text-muted">MONITORED REGION:</div>
              <div class="text-white fw-bold fs-5 mb-3">Kangra Demonstration Feeder A</div>

              <div class="bg-black bg-opacity-40 p-3 rounded border border-secondary mb-3 small font-monospace">
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-secondary">Assigned Technicians:</span>
                  <span class="text-white">3 Lineworkers</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-secondary">Clearances Recorded:</span>
                  <span class="text-warning">1 / 3 Submitted</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-secondary">Supervisor Safety Signoff:</span>
                  <span class="text-danger">PENDING</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-secondary">Switching Authorization:</span>
                  <span class="text-danger fw-bold">BLOCKED (Engine)</span>
                </div>
              </div>

              <div class="small text-muted fst-italic">
                Central Rule Engine actively blocks control room energization commands until all assigned field lineworkers sign out safely.
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- The Problem Section -->
    <section id="problemSection" class="py-5 bg-white">
      <div class="container py-4">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <span class="text-primary text-uppercase fw-bold small">Critical Safety Problem</span>
            <h2 class="fw-bold mt-1 mb-3 text-navy">The Human Cost of Coordination Breakdowns</h2>
            <p class="text-secondary">
              In distribution networks and rural feeders, electrical maintenance teams work on high-voltage 11kV and 22kV lines often spanning kilometers across rugged terrain.
            </p>
            <p class="text-secondary">
              Tragically, fatal accidents occur not because technicians lack courage or training, but because of <strong>fragmented coordination channels</strong>:
            </p>
            <ul class="text-secondary ps-3 mb-4">
              <li class="mb-2">Informal, verbal phone calls between substation operators and distant field crews.</li>
              <li class="mb-2">Premature power restoration while lineworkers are still descending towers or retensioning conductors.</li>
              <li class="mb-2">Lack of cryptographic identity verification confirming that the actual worker on the tower is clear.</li>
              <li class="mb-2">Absence of automated interlocking between worker safety declarations and control room switching commands.</li>
            </ul>
          </div>
          <div class="col-lg-6">
            <div class="row g-3">
              <div class="col-sm-6">
                <div class="p-4 rounded-3 border bg-light h-100">
                  <div class="text-danger fs-3 mb-2"><i class="bi bi-telephone-x-fill"></i></div>
                  <h6 class="fw-bold text-dark">Informal Verbal Calls</h6>
                  <p class="small text-secondary mb-0">Misheard substation names or premature verbal confirmations cause fatal miscommunications.</p>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="p-4 rounded-3 border bg-light h-100">
                  <div class="text-danger fs-3 mb-2"><i class="bi bi-clock-history"></i></div>
                  <h6 class="fw-bold text-dark">Uncoordinated Schedules</h6>
                  <p class="small text-secondary mb-0">When one worker finishes early, control rooms assume the entire sector is clear when teammates remain aloft.</p>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="p-4 rounded-3 border bg-light h-100">
                  <div class="text-danger fs-3 mb-2"><i class="bi bi-shield-slash-fill"></i></div>
                  <h6 class="fw-bold text-dark">No Invalidation Mechanism</h6>
                  <p class="small text-secondary mb-0">If a descending lineman discovers a hazardous loose clamp, there is no digital kill-switch to cancel previous clearances.</p>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="p-4 rounded-3 border bg-light h-100">
                  <div class="text-danger fs-3 mb-2"><i class="bi bi-hdd-network-fill"></i></div>
                  <h6 class="fw-bold text-dark">Isolated SCADA Logic</h6>
                  <p class="small text-secondary mb-0">Switching systems operate blind to the real-time presence of human technicians on the line.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Three Solution Pillars -->
    <section id="solutionSection" class="py-5" style="background-color: #F8FAFC;">
      <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
          <span class="text-primary text-uppercase fw-bold small">Our Core Approach</span>
          <h2 class="fw-bold mt-1 text-navy">Three Pillars of ElectroGuard</h2>
          <p class="text-secondary">A coherent, multi-tiered framework designed by computer engineering students to protect frontline electrical workers.</p>
        </div>

        <div class="row g-4">
          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
              <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3 d-inline-block mb-3" style="width: fit-content;">
                <i class="bi bi-person-bounding-box fs-3"></i>
              </div>
              <h5 class="fw-bold text-dark">1. Worker Identity & Verification</h5>
              <p class="text-secondary small mb-3">
                Lineworkers verify their physical presence on site using digital badge QR scans and simulated two-factor OTP verification. Only assigned technicians can sign off on their specific sector.
              </p>
              <div class="mt-auto small font-monospace text-primary">
                &bull; Anti-spoofing assignment check<br>
                &bull; 5-minute cryptographic OTP<br>
                &bull; Mandatory field safety declarations
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
              <div class="p-3 bg-teal bg-opacity-10 text-teal rounded-3 d-inline-block mb-3" style="width: fit-content; color: #0D9D91;">
                <i class="bi bi-diagram-3-fill fs-3"></i>
              </div>
              <h5 class="fw-bold text-dark">2. Central Rule Engine</h5>
              <p class="text-secondary small mb-3">
                Clearance state is calculated on the authoritative server, not trusted from browser counts. If even one worker has not signed off, or if anyone reopens a clearance, restoration remains blocked.
              </p>
              <div class="mt-auto small font-monospace text-teal" style="color: #0D9D91;">
                &bull; Complete multi-worker tally<br>
                &bull; Instant invalidation on reopening<br>
                &bull; Mandatory supervisor signoff
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
              <div class="p-3 bg-cyan bg-opacity-10 text-cyan rounded-3 d-inline-block mb-3" style="width: fit-content; color: #2777E6;">
                <i class="bi bi-cpu-fill fs-3"></i>
              </div>
              <h5 class="fw-bold text-dark">3. IoT Interlocking & Monitoring</h5>
              <p class="text-secondary small mb-3">
                A demonstrable ESP32 low-voltage controller concept links software authorization to hardware switching indicators. If the controller loses heartbeat or drops offline, state becomes UNKNOWN.
              </p>
              <div class="mt-auto small font-monospace text-primary">
                &bull; Fail-safe isolated default state<br>
                &bull; Real-time heartbeat validation<br>
                &bull; Strictly low-voltage safety boundary
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 5-Step Workflow Section -->
    <section id="workflowSection" class="py-5 bg-white">
      <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
          <span class="text-primary text-uppercase fw-bold small">System Flow</span>
          <h2 class="fw-bold mt-1 text-navy">The Five-Step Safety Workflow</h2>
          <p class="text-secondary">How ElectroGuard guides an electrical maintenance operation from job creation to safe completion.</p>
        </div>

        <div class="row g-3">
          <div class="col-lg">
            <div class="p-3 rounded-3 border bg-light h-100 text-center">
              <div class="badge bg-primary rounded-circle mb-2" style="width: 28px; height: 28px; line-height: 20px;">1</div>
              <h6 class="fw-bold text-dark mb-1">Job Creation</h6>
              <p class="small text-muted mb-0">Supervisor creates job, assigns specific feeder & multi-worker roster.</p>
            </div>
          </div>
          <div class="col-lg">
            <div class="p-3 rounded-3 border bg-light h-100 text-center">
              <div class="badge bg-primary rounded-circle mb-2" style="width: 28px; height: 28px; line-height: 20px;">2</div>
              <h6 class="fw-bold text-dark mb-1">Restricted Work</h6>
              <p class="small text-muted mb-0">Feeder locked in RESTRICTED state; restoration commands strictly blocked.</p>
            </div>
          </div>
          <div class="col-lg">
            <div class="p-3 rounded-3 border bg-light h-100 text-center">
              <div class="badge bg-primary rounded-circle mb-2" style="width: 28px; height: 28px; line-height: 20px;">3</div>
              <h6 class="fw-bold text-dark mb-1">Worker Clearance</h6>
              <p class="small text-muted mb-0">Workers complete tasks, scan badges, verify OTP, and declare site clear.</p>
            </div>
          </div>
          <div class="col-lg">
            <div class="p-3 rounded-3 border bg-light h-100 text-center">
              <div class="badge bg-primary rounded-circle mb-2" style="width: 28px; height: 28px; line-height: 20px;">4</div>
              <h6 class="fw-bold text-dark mb-1">Supervisor Review</h6>
              <p class="small text-muted mb-0">Supervisor confirms personnel off-structure and earth grounds removed.</p>
            </div>
          </div>
          <div class="col-lg">
            <div class="p-3 rounded-3 border bg-light h-100 text-center">
              <div class="badge bg-success rounded-circle mb-2" style="width: 28px; height: 28px; line-height: 20px;">5</div>
              <h6 class="fw-bold text-dark mb-1">Restoration Review</h6>
              <p class="small text-muted mb-0">Rule engine validates 100% criteria before permitting simulated energization.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Architecture & Benefits Section -->
    <section id="architectureSection" class="py-5" style="background-color: #F8FAFC;">
      <div class="container py-4">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <span class="text-primary text-uppercase fw-bold small">System Architecture</span>
            <h2 class="fw-bold text-navy mb-3">Designed for Practical Field Deployment</h2>
            <p class="text-secondary">
              Built on a modular, lightweight PHP 8 REST API and normalized relational database. The frontend integrates Leaflet OpenStreetMap with fictional demonstration feeders in Kangra, Himachal Pradesh.
            </p>
            <div class="row g-3 mt-2">
              <div class="col-sm-6">
                <div class="p-3 bg-white rounded border">
                  <div class="fw-bold text-dark"><i class="bi bi-shield-shaded text-primary me-2"></i>For Workers</div>
                  <div class="small text-muted mt-1">Direct safety ownership; ability to reopen clearance and stop work instantly if a hazard appears.</div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="p-3 bg-white rounded border">
                  <div class="fw-bold text-dark"><i class="bi bi-clipboard2-check text-primary me-2"></i>For Supervisors</div>
                  <div class="small text-muted mt-1">Live visibility into lineworker signoffs, automated checklists, and tamper-proof verification.</div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="p-3 bg-white rounded border">
                  <div class="fw-bold text-dark"><i class="bi bi-buildings text-primary me-2"></i>For Electricity Distribution Operations</div>
                  <div class="small text-muted mt-1">Audit trail compliance, reduction in premature switching accidents, and transparent coordination across remote rural feeders.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card border-0 shadow p-4 rounded-4 bg-white">
              <h5 class="fw-bold text-dark mb-3"><i class="bi bi-journal-text text-primary me-2"></i>Project Details</h5>
              <table class="table table-sm small mb-4">
                <tbody>
                  <tr><td class="text-muted">Project Name:</td><td class="fw-bold text-navy">ElectroGuard</td></tr>
                  <tr><td class="text-muted">Tagline:</td><td class="fw-bold text-primary">Every Clearance Matters.</td></tr>
                  <tr><td class="text-muted">Team Name:</td><td class="fw-bold text-dark">GodsPlanKangra</td></tr>
                  <tr><td class="text-muted">Team Members:</td><td><strong>Aayan Parmar</strong> (Leader), <strong>Manjinder Uldeen</strong>, <strong>Aayush Parmar</strong></td></tr>
                  <tr><td class="text-muted">Academic Branch:</td><td>Computer Engineering (3rd Year)</td></tr>
                  <tr><td class="text-muted">Hackathon Category:</td><td>Rural Development & Livelihoods</td></tr>
                  <tr><td class="text-muted">Primary Objective:</td><td>Reduce premature restoration risks through digital clearance & demonstrable IoT interlocking.</td></tr>
                </tbody>
              </table>

              <div class="alert alert-warning py-2 px-3 small border-warning mb-0">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <strong>Safety Disclaimer:</strong> ElectroGuard is an academic prototype. It does not replace physical lockout/tagout (LOTO), earthing rods, or certified utility permit-to-work protocols.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 text-white" style="background-color: var(--eg-navy); border-top: 1px solid rgba(255,255,255,0.08);">
      <div class="container text-center small text-muted">
        <div>&copy; 2026 <strong>ElectroGuard</strong> — Developed by <strong>GodsPlanKangra</strong> (Aayan Parmar, Manjinder Uldeen, Aayush Parmar)</div>
        <div class="mt-1">Computer Engineering &bull; Rural Development & Livelihoods &bull; Academic Prototype</div>
      </div>
    </footer>
  </div>

  <!-- ========================================================================= -->
  <!-- 2. WORKER MOBILE FIELD PORTAL VIEW                                        -->
  <!-- ========================================================================= -->
  <div id="view-worker" style="display: none;">
    <!-- Worker Portal Header -->
    <nav class="navbar navbar-dark eg-navbar sticky-top">
      <div class="container">
        <div class="navbar-brand d-flex align-items-center gap-2">
          <div class="eg-logo-symbol"><i class="bi bi-person-fill-check"></i></div>
          <div>
            <div class="fs-6 fw-bold">ElectroGuard Field Portal</div>
            <div style="font-size: 0.65rem; color: #2DCDDC;">LINEWORKER SAFETY INTERFACE</div>
          </div>
        </div>

        <div class="d-flex align-items-center gap-3">
          <div class="text-end d-none d-sm-block text-white small">
            <div class="fw-bold user-display-name">Rakesh Kumar</div>
            <div class="text-info font-monospace small" id="workerPortalCode">WK-KGR-701</div>
          </div>
          <button class="btn btn-outline-danger btn-sm btn-app-logout">
            <i class="bi bi-box-arrow-right me-1"></i> Exit
          </button>
        </div>
      </div>
    </nav>

    <div class="container py-4" style="max-width: 800px;">
      <!-- Field Worker Card -->
      <div class="card p-3 mb-4 shadow-sm border-0 rounded-3 bg-white">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-3">
              <i class="bi bi-person-badge"></i>
            </div>
            <div>
              <h5 class="fw-bold text-dark mb-0 user-display-name">Rakesh Kumar</h5>
              <div class="text-muted small">Masked Contact: <span id="workerPortalPhone" class="font-monospace">+91 98*** **412</span></div>
              <div class="badge bg-light text-dark border small mt-1">Certified 11kV Distribution Lineman</div>
            </div>
          </div>
          <button class="btn btn-sm btn-outline-primary" onclick="App.showView('admin')">
            <i class="bi bi-arrow-left-right me-1"></i> Switch to Control Room
          </button>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-navy mb-0">Assigned Maintenance Jobs</h5>
        <span class="badge bg-secondary">Live Demo Data</span>
      </div>

      <!-- Container where worker jobs are dynamically rendered -->
      <div id="workerJobsContainer">
        <!-- Rendered via WorkerPortal.loadAssignedJobs() -->
      </div>
    </div>
  </div>

  <!-- ========================================================================= -->
  <!-- 3. ADMINISTRATOR & OPERATIONS DASHBOARD VIEW                               -->
  <!-- ========================================================================= -->
  <div id="view-admin" style="display: none;">
    <!-- Top Navigation Bar -->
    <header class="eg-topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarOffcanvas">
          <i class="bi bi-list fs-5"></i>
        </button>
        <div class="d-flex align-items-center gap-2">
          <div class="eg-logo-symbol" style="width: 28px; height: 28px; font-size: 0.9rem;"><i class="bi bi-shield-fill-check"></i></div>
          <span class="fw-bold text-navy fs-5">ElectroGuard Control Room</span>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 ms-2 font-monospace small">DEMO NETWORK: KANGRA</span>
      </div>

      <div class="d-flex align-items-center gap-3">
        <div class="text-secondary small font-monospace d-none d-md-block" id="liveClockDisplay">
          --:--:--
        </div>

        <div class="dropdown">
          <button class="btn btn-outline-secondary btn-sm position-relative" type="button" onclick="AdminDashboard.switchTab('alerts')">
            <i class="bi bi-bell-fill"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
              3
            </span>
          </button>
        </div>

        <div class="d-flex align-items-center gap-2 ps-2 border-start">
          <div class="text-end d-none d-sm-block">
            <div class="fw-bold small user-display-name text-dark">Er. Aayan Parmar</div>
            <div class="text-muted small user-display-role" style="font-size: 0.72rem;">ADMIN</div>
          </div>
          <button class="btn btn-outline-danger btn-sm btn-app-logout" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
          </button>
        </div>
      </div>
    </header>

    <div class="container-fluid">
      <div class="row">
        <!-- Sidebar Navigation (Desktop) -->
        <nav class="col-lg-2 d-none d-lg-block eg-sidebar p-0">
          <div class="p-3 text-cyan small fw-bold text-uppercase border-bottom border-secondary border-opacity-25">
            Operations Menu
          </div>
          <div class="py-2">
            <a class="eg-sidebar-item active" data-tab="overview"><i class="bi bi-grid-1x2-fill"></i> Overview</a>
            <a class="eg-sidebar-item" data-tab="map"><i class="bi bi-geo-alt-fill"></i> Live Map (Kangra)</a>
            <a class="eg-sidebar-item" data-tab="jobs"><i class="bi bi-briefcase-fill"></i> Maintenance Jobs</a>
            <a class="eg-sidebar-item" data-tab="workers"><i class="bi bi-people-fill"></i> Lineworkers</a>
            <a class="eg-sidebar-item" data-tab="clearance"><i class="bi bi-shield-check"></i> Clearance Review</a>
            <a class="eg-sidebar-item" data-tab="restoration"><i class="bi bi-power"></i> Restoration Requests</a>
            <a class="eg-sidebar-item" data-tab="controllers"><i class="bi bi-cpu-fill"></i> IoT Controller (ESP32)</a>
            <a class="eg-sidebar-item" data-tab="alerts"><i class="bi bi-bell-fill"></i> Safety Alerts</a>
            <a class="eg-sidebar-item" data-tab="audit"><i class="bi bi-clock-history"></i> Audit Trail</a>
            <a class="eg-sidebar-item" data-tab="reports"><i class="bi bi-bar-chart-fill"></i> Reports & Analytics</a>
          </div>
          <div class="p-3 border-top border-secondary border-opacity-25 mt-4">
            <button class="btn btn-sm btn-outline-cyan w-100 text-cyan border-cyan" onclick="App.showView('worker')">
              <i class="bi bi-person-badge me-1"></i> Open Worker View
            </button>
          </div>
        </nav>

        <!-- Sidebar Offcanvas for Mobile Viewports -->
        <div class="offcanvas offcanvas-start eg-sidebar text-white" tabindex="-1" id="adminSidebarOffcanvas">
          <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
            <h5 class="offcanvas-title text-cyan fw-bold">ElectroGuard Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
          </div>
          <div class="offcanvas-body p-0">
            <div class="py-2">
              <a class="eg-sidebar-item" data-tab="overview" data-bs-dismiss="offcanvas"><i class="bi bi-grid-1x2-fill"></i> Overview</a>
              <a class="eg-sidebar-item" data-tab="map" data-bs-dismiss="offcanvas"><i class="bi bi-geo-alt-fill"></i> Live Map</a>
              <a class="eg-sidebar-item" data-tab="jobs" data-bs-dismiss="offcanvas"><i class="bi bi-briefcase-fill"></i> Maintenance Jobs</a>
              <a class="eg-sidebar-item" data-tab="workers" data-bs-dismiss="offcanvas"><i class="bi bi-people-fill"></i> Lineworkers</a>
              <a class="eg-sidebar-item" data-tab="clearance" data-bs-dismiss="offcanvas"><i class="bi bi-shield-check"></i> Clearance Review</a>
              <a class="eg-sidebar-item" data-tab="restoration" data-bs-dismiss="offcanvas"><i class="bi bi-power"></i> Restoration Requests</a>
              <a class="eg-sidebar-item" data-tab="controllers" data-bs-dismiss="offcanvas"><i class="bi bi-cpu-fill"></i> IoT Controller</a>
              <a class="eg-sidebar-item" data-tab="alerts" data-bs-dismiss="offcanvas"><i class="bi bi-bell-fill"></i> Safety Alerts</a>
              <a class="eg-sidebar-item" data-tab="audit" data-bs-dismiss="offcanvas"><i class="bi bi-clock-history"></i> Audit Trail</a>
              <a class="eg-sidebar-item" data-tab="reports" data-bs-dismiss="offcanvas"><i class="bi bi-bar-chart-fill"></i> Reports</a>
            </div>
          </div>
        </div>

        <!-- Main Dashboard View Area -->
        <main class="col-lg-10 p-4">

          <!-- TAB 1: OVERVIEW -->
          <div id="tab-overview" class="eg-tab-pane" style="display: block;">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="fw-bold text-navy mb-1">Operations Control Overview</h4>
                <p class="text-secondary small mb-0">Real-time status of Kangra feeder maintenance, clearances, and interlocks.</p>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" onclick="AdminDashboard.loadOverview()">
                  <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                </button>
                <button class="btn btn-primary btn-sm" onclick="AdminDashboard.openCreateJobModal()">
                  <i class="bi bi-plus-lg me-1"></i> New Maintenance Job
                </button>
              </div>
            </div>

            <!-- Overview Metrics Row -->
            <div class="row g-3 mb-4">
              <div class="col-sm-6 col-xl">
                <div class="eg-metric-card" onclick="AdminDashboard.switchTab('jobs')">
                  <div class="eg-metric-title">Active Maintenance Jobs</div>
                  <div class="eg-metric-value text-primary" id="metricActiveJobs">3</div>
                  <div class="eg-metric-subtext"><i class="bi bi-arrow-right-short text-primary"></i> Kangra division feeders</div>
                </div>
              </div>
              <div class="col-sm-6 col-xl">
                <div class="eg-metric-card" onclick="AdminDashboard.switchTab('clearance')">
                  <div class="eg-metric-title">Pending Clearances</div>
                  <div class="eg-metric-value text-warning" id="metricPendingClearances">2</div>
                  <div class="eg-metric-subtext"><i class="bi bi-hourglass text-warning"></i> Awaiting lineworker signoff</div>
                </div>
              </div>
              <div class="col-sm-6 col-xl">
                <div class="eg-metric-card" onclick="AdminDashboard.switchTab('restoration')">
                  <div class="eg-metric-title">Blocked Restorations</div>
                  <div class="eg-metric-value text-danger" id="metricBlockedRestorations">2</div>
                  <div class="eg-metric-subtext"><i class="bi bi-shield-x text-danger"></i> Interlocked by Rule Engine</div>
                </div>
              </div>
              <div class="col-sm-6 col-xl">
                <div class="eg-metric-card" onclick="AdminDashboard.switchTab('clearance')">
                  <div class="eg-metric-title">Supervisor Signoff Pending</div>
                  <div class="eg-metric-value" id="metricPendingReview">1</div>
                  <div class="eg-metric-subtext"><i class="bi bi-clipboard-check text-info"></i> Workers cleared; procedural check needed</div>
                </div>
              </div>
              <div class="col-sm-6 col-xl">
                <div class="eg-metric-card" onclick="AdminDashboard.switchTab('controllers')">
                  <div class="eg-metric-title">ESP32 Controllers Online</div>
                  <div class="eg-metric-value text-teal" id="metricControllersOnline">3 / 4</div>
                  <div class="eg-metric-subtext"><i class="bi bi-wifi text-success"></i> Low-voltage bench simulation</div>
                </div>
              </div>
            </div>

            <!-- Central Interlock Notice -->
            <div class="alert alert-primary border-primary bg-primary bg-opacity-10 d-flex align-items-center gap-3 p-3 mb-4 rounded-3">
              <i class="bi bi-shield-lock-fill fs-2 text-primary"></i>
              <div>
                <h6 class="fw-bold mb-1 text-navy">Authoritative Rule Engine Interlock Active</h6>
                <div class="small text-secondary">
                  No operator or administrator may authorize power restoration without verified digital clearances from all assigned lineworkers, verified supervisor procedural checks, and an active online IoT controller heartbeat.
                </div>
              </div>
            </div>

            <div class="row g-4">
              <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-navy mb-0">Active Feeder Status (Kangra Network)</h6>
                    <button class="btn btn-link btn-sm text-decoration-none" onclick="AdminDashboard.switchTab('map')">Open Full Map <i class="bi bi-arrow-right"></i></button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                      <thead class="table-light">
                        <tr>
                          <th>Job ID</th>
                          <th>Feeder Area</th>
                          <th>Clearance Tally</th>
                          <th>Rule Status</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="overviewQuickJobsBody">
                        <tr>
                          <td class="font-monospace fw-bold text-primary">EG-JOB-1001</td>
                          <td>Kangra Demonstration Feeder A</td>
                          <td><span class="badge bg-warning text-dark">1 / 3 Submitted</span></td>
                          <td><span class="badge badge-status badge-status-danger">BLOCKED</span></td>
                          <td><button class="btn btn-sm btn-outline-primary" onclick="AdminDashboard.switchTab('jobs')">Inspect</button></td>
                        </tr>
                        <tr>
                          <td class="font-monospace fw-bold text-primary">EG-JOB-1002</td>
                          <td>Nagrota Demonstration Feeder B</td>
                          <td><span class="badge bg-success">3 / 3 Complete</span></td>
                          <td><span class="badge badge-status badge-status-warning">AWAITING REVIEW</span></td>
                          <td><button class="btn btn-sm btn-outline-primary" onclick="AdminDashboard.switchTab('clearance')">Review</button></td>
                        </tr>
                        <tr>
                          <td class="font-monospace fw-bold text-primary">EG-JOB-1003</td>
                          <td>Shahpur Demonstration Feeder C</td>
                          <td><span class="badge bg-danger">Reopened</span></td>
                          <td><span class="badge badge-status badge-status-danger">BLOCKED</span></td>
                          <td><button class="btn btn-sm btn-outline-primary" onclick="AdminDashboard.switchTab('jobs')">Inspect</button></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-navy mb-0">Recent Operational Feed</h6>
                    <button class="btn btn-link btn-sm text-decoration-none" onclick="AdminDashboard.switchTab('audit')">All Logs</button>
                  </div>
                  <div class="eg-activity-feed" id="overviewActivityFeed">
                    <!-- Loaded dynamically -->
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: LIVE MAP -->
          <div id="tab-map" class="eg-tab-pane" style="display: none;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
              <div>
                <h4 class="fw-bold text-navy mb-1">Geographic Feeder Status (Kangra District)</h4>
                <p class="text-secondary small mb-0">Interactive Leaflet OpenStreetMap view of electrical maintenance zones.</p>
              </div>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <input type="text" id="mapSearchInput" class="form-control form-control-sm" placeholder="Search Feeder or Job ID..." style="width: 200px;" oninput="MapManager.renderMarkers()">
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary active" onclick="MapManager.setFilter('ALL')">All</button>
                  <button class="btn btn-outline-danger" onclick="MapManager.setFilter('RESTRICTED')">Restricted</button>
                  <button class="btn btn-outline-warning" onclick="MapManager.setFilter('PENDING')">Review Pending</button>
                  <button class="btn btn-outline-success" onclick="MapManager.setFilter('COMPLETED')">Cleared</button>
                </div>
                <button class="btn btn-outline-primary btn-sm" onclick="MapManager.refresh()">
                  <i class="bi bi-arrow-clockwise"></i>
                </button>
              </div>
            </div>

            <!-- Map Card -->
            <div class="card border-0 shadow-sm rounded-3 p-2 mb-3 bg-white">
              <div id="map"></div>
            </div>

            <!-- Map Legend -->
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 small">
                <div class="d-flex align-items-center gap-4">
                  <span class="d-flex align-items-center gap-2"><span class="led-indicator led-red"></span> Red: Maintenance Active (Restoration Blocked)</span>
                  <span class="d-flex align-items-center gap-2"><span class="led-indicator led-amber"></span> Amber: Clearance Done / Supervisor Review Pending</span>
                  <span class="d-flex align-items-center gap-2"><span class="led-indicator led-green"></span> Green: Workflow Completed (Demonstration Mode)</span>
                  <span class="d-flex align-items-center gap-2"><span class="led-indicator led-grey"></span> Grey: Controller Offline / Unknown State</span>
                </div>
                <div class="text-muted small">
                  * Fictional demonstration coordinates around Kangra, Himachal Pradesh.
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 3: MAINTENANCE JOBS -->
          <div id="tab-jobs" class="eg-tab-pane" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="fw-bold text-navy mb-1">Maintenance Job Management</h4>
                <p class="text-secondary small mb-0">Create, monitor, and assign multi-worker electrical maintenance jobs.</p>
              </div>
              <button class="btn btn-primary btn-sm" onclick="AdminDashboard.openCreateJobModal()">
                <i class="bi bi-plus-lg me-1"></i> Create Maintenance Job
              </button>
            </div>

            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light small">
                    <tr>
                      <th>Job ID</th>
                      <th>Area & Work Description</th>
                      <th>Supervisor</th>
                      <th>Clearances Submitted</th>
                      <th>Status</th>
                      <th>Central Rule Reason</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="jobsTableBody">
                    <!-- Populated via AdminDashboard.loadJobs() -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 4: WORKERS DIRECTORY -->
          <div id="tab-workers" class="eg-tab-pane" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="fw-bold text-navy mb-1">Certified Lineworker Registry</h4>
                <p class="text-secondary small mb-0">Active field technicians eligible for safety clearance assignments.</p>
              </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light small">
                    <tr>
                      <th>Worker Code</th>
                      <th>Full Name</th>
                      <th>Department</th>
                      <th>Certification Level</th>
                      <th>Masked Phone</th>
                      <th>Directory Status</th>
                    </tr>
                  </thead>
                  <tbody id="workersTableBody">
                    <!-- Populated via AdminDashboard.loadWorkers() -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 5: CLEARANCE MANAGEMENT -->
          <div id="tab-clearance" class="eg-tab-pane" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="fw-bold text-navy mb-1">Clearance Review & Hazard Invalidation</h4>
                <p class="text-secondary small mb-0">Inspect individual worker checklist declarations and record supervisor safety reviews.</p>
              </div>
              <button class="btn btn-outline-secondary btn-sm" onclick="AdminDashboard.loadClearanceManagement()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
              </button>
            </div>

            <div id="clearanceCardsContainer">
              <!-- Populated via AdminDashboard.loadClearanceManagement() -->
            </div>
          </div>

          <!-- TAB 6: RESTORATION REQUESTS -->
          <div id="tab-restoration" class="eg-tab-pane" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="fw-bold text-navy mb-1">Feeder Restoration Request Gateway</h4>
                <p class="text-secondary small mb-0">Simulated energization authorization governed strictly by the Central Clearance Rule Engine.</p>
              </div>
            </div>

            <div class="alert alert-secondary small mb-4">
              <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle-fill text-primary me-2"></i>Clearance Principle Interlock:</div>
              Restoration requests cannot be overridden by administrative rank. The button below is only enabled when 100% of assigned lineworkers have verified clearance, supervisor inspection is recorded, and the IoT controller is confirmed online.
            </div>

            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light small">
                    <tr>
                      <th>Job ID</th>
                      <th>Feeder Area</th>
                      <th>Verification Tally</th>
                      <th>IoT Controller</th>
                      <th>Request State</th>
                      <th>Blocking Reason</th>
                      <th>Restoration Action</th>
                    </tr>
                  </thead>
                  <tbody id="restorationTableBody">
                    <!-- Populated via AdminDashboard.loadRestorationRequests() -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 7: IOT CONTROLLER SIMULATION -->
          <div id="tab-controllers" class="eg-tab-pane" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="fw-bold text-navy mb-1">ESP32 IoT Controller Simulation Console</h4>
                <p class="text-secondary small mb-0">Simulate low-voltage switching indicators, hardware disconnects, and latency injection.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" onclick="AdminDashboard.loadControllers()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh States
              </button>
            </div>

            <div class="alert alert-dark small mb-4 bg-dark text-light border-0">
              <i class="bi bi-shield-exclamation text-warning me-2"></i>
              <strong>Hardware Safety Boundary Notice:</strong> This console communicates with an isolated low-voltage ESP32 demonstration indicator circuit. Disconnecting a controller causes the software state to become <code>UNKNOWN</code>, immediately blocking restoration requests.
            </div>

            <div class="row" id="controllersGrid">
              <!-- Populated via AdminDashboard.loadControllers() -->
            </div>
          </div>

          <!-- TAB 8: SAFETY ALERTS -->
          <div id="tab-alerts" class="eg-tab-pane" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="fw-bold text-navy mb-1">Safety Alerts & Notifications</h4>
                <p class="text-secondary small mb-0">Real-time alerts triggered by hazard reopenings, missing clearances, or controller drops.</p>
              </div>
              <button class="btn btn-outline-secondary btn-sm" onclick="Api.alerts.markAllRead().then(() => AdminDashboard.loadAlerts())">
                <i class="bi bi-check2-all me-1"></i> Mark All Read
              </button>
            </div>

            <div id="alertsListContainer">
              <!-- Populated via AdminDashboard.loadAlerts() -->
            </div>
          </div>

          <!-- TAB 9: AUDIT TRAIL -->
          <div id="tab-audit" class="eg-tab-pane" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="fw-bold text-navy mb-1">Immutable Audit Trail</h4>
                <p class="text-secondary small mb-0">Append-only log of every login, OTP verification, clearance signoff, and rule engine block.</p>
              </div>
              <button class="btn btn-outline-primary btn-sm" onclick="AdminDashboard.loadAuditLogs()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Logs
              </button>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-3">
              <div class="row g-2">
                <div class="col-md-5">
                  <input type="text" id="auditSearchInput" class="form-control form-control-sm" placeholder="Search actor, action, or correlation ID..." oninput="AdminDashboard.loadAuditLogs()">
                </div>
                <div class="col-md-3">
                  <select id="auditRoleFilter" class="form-select form-select-sm" onchange="AdminDashboard.loadAuditLogs()">
                    <option value="">All Roles</option>
                    <option value="worker">Worker</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="admin">Administrator</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <select id="auditResultFilter" class="form-select form-select-sm" onchange="AdminDashboard.loadAuditLogs()">
                    <option value="">All Results</option>
                    <option value="SUCCESS">SUCCESS</option>
                    <option value="BLOCKED">BLOCKED</option>
                    <option value="FAILED">FAILED</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light small">
                    <tr>
                      <th>Correlation ID</th>
                      <th>Timestamp</th>
                      <th>Actor</th>
                      <th>Role</th>
                      <th>Action</th>
                      <th>Result</th>
                      <th>Audit Details</th>
                    </tr>
                  </thead>
                  <tbody id="auditTableBody">
                    <!-- Populated via AdminDashboard.loadAuditLogs() -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 10: REPORTS & ANALYTICS -->
          <div id="tab-reports" class="eg-tab-pane" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="fw-bold text-navy mb-1">Safety Analytics & Compliance Reports</h4>
                <p class="text-secondary small mb-0">Aggregated statistics derived from authoritative maintenance and clearance database records.</p>
              </div>
              <a href="/api/reports/export-csv" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Official CSV Report
              </a>
            </div>

            <div class="row g-4 mb-4">
              <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                  <h6 class="fw-bold text-navy mb-3">Maintenance Jobs by Operational Status</h6>
                  <div style="height: 280px; position: relative;">
                    <canvas id="jobStatusChart"></canvas>
                  </div>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                  <h6 class="fw-bold text-navy mb-3">Clearance Submissions vs Restoration Blocks</h6>
                  <div style="height: 280px; position: relative;">
                    <canvas id="clearanceProgressChart"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <div class="alert alert-light border small text-muted">
              <i class="bi bi-info-circle me-1"></i>
              <strong>Prototype Evaluation Disclosure:</strong> Analytics are computed strictly from demonstration records in the local SQLite/MySQL database. No external real-world casualty or safety statistics are fabricated.
            </div>
          </div>

        </main>
      </div>
    </div>
  </div>

  <!-- ========================================================================= -->
  <!-- MODALS                                                                    -->
  <!-- ========================================================================= -->

  <!-- 1. Authentication / Role Switcher Modal -->
  <div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-navy text-white" style="background-color: var(--eg-navy);">
          <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2"></i>ElectroGuard Access</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <p class="text-secondary small mb-3">Choose a demonstration role to instantly inspect the prototype:</p>

          <div class="d-grid gap-2 mb-4">
            <button class="btn btn-outline-primary text-start p-2" data-demo-role="worker" data-worker-index="1">
              <div class="fw-bold"><i class="bi bi-person-badge-fill me-2"></i>Worker: Rakesh Kumar (WK-KGR-701)</div>
              <div class="small text-muted ps-4">Field lineworker on 11kV Kangra Feeder A</div>
            </button>
            <button class="btn btn-outline-info text-start p-2" data-demo-role="supervisor">
              <div class="fw-bold"><i class="bi bi-person-check-fill me-2"></i>Supervisor: Er. Manjinder Uldeen</div>
              <div class="small text-muted ps-4">Feeder Maintenance Division In-Charge</div>
            </button>
            <button class="btn btn-outline-dark text-start p-2" data-demo-role="admin">
              <div class="fw-bold"><i class="bi bi-shield-fill-check me-2"></i>Administrator: Er. Aayan Parmar</div>
              <div class="small text-muted ps-4">Control Room Operations Center</div>
            </button>
          </div>

          <div class="text-center text-muted small mb-3 border-top pt-3">Or sign in with credentials:</div>

          <form id="credentialLoginForm">
            <div class="mb-3">
              <label class="form-label small fw-bold">Email or Username</label>
              <input type="text" id="loginIdentifier" class="form-control form-control-sm" value="admin@electroguard.local">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Password</label>
              <input type="password" id="loginPassword" class="form-control form-control-sm" value="Admin@123">
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100">Sign In</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. Worker Identity & OTP Clearance Modal -->
  <div class="modal fade" id="workerClearanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold"><i class="bi bi-shield-check me-2"></i>Worker Safety Clearance Signoff</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <!-- Step 1: Worker ID Scan -->
          <div id="stepWorkerId">
            <h6 class="fw-bold text-navy mb-2">Step 1: Lineworker Badge Verification</h6>
            <p class="small text-secondary mb-3">Scan your physical QR badge or confirm your certified worker identification code:</p>

            <div class="p-3 bg-light rounded text-center border mb-3">
              <i class="bi bi-qr-code fs-1 text-primary mb-2"></i>
              <div class="small text-muted">Simulated Barcode / QR Scanner Interface</div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Worker Badge Code</label>
              <input type="text" id="workerCodeInput" class="form-control font-monospace" value="WK-KGR-701">
            </div>

            <div id="workerScanResult"></div>

            <button type="button" class="btn btn-primary w-100 mt-2" onclick="WorkerPortal.verifyWorkerId()">
              <i class="bi bi-qr-code-scan me-1"></i> Verify Badge & Continue
            </button>
          </div>

          <!-- Step 2: OTP Verification -->
          <div id="stepOtp" style="display: none;">
            <h6 class="fw-bold text-navy mb-2">Step 2: Two-Factor Verification Code</h6>
            <p class="small text-secondary mb-3">A simulated one-time verification code has been dispatched to your lineworker terminal:</p>

            <div id="simulatedOtpHint" class="mb-3"></div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Enter 6-Digit Code</label>
              <input type="text" id="otpCodeInput" class="form-control text-center font-monospace fs-4 fw-bold" maxlength="6" placeholder="000000">
            </div>

            <div id="otpInputResult"></div>

            <button type="button" class="btn btn-primary w-100 mt-2" onclick="WorkerPortal.verifyOtp()">
              <i class="bi bi-shield-lock-fill me-1"></i> Validate Verification Code
            </button>
          </div>

          <!-- Step 3: Mandatory Checklist -->
          <div id="stepChecklist" style="display: none;">
            <h6 class="fw-bold text-navy mb-2">Step 3: Site Safety Declarations</h6>
            <p class="small text-secondary mb-3">You must confirm all mandatory items before signing digital clearance:</p>

            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="chkWorkComplete" checked>
              <label class="form-check-label small" for="chkWorkComplete">
                <strong>Assigned Maintenance Work is 100% Completed:</strong> All conductors tightened, jumpers bolted, and tools accounted for.
              </label>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="chkSiteCleared" checked>
              <label class="form-check-label small" for="chkSiteCleared">
                <strong>Ground Site & Structure Cleared:</strong> All team members and ladders are completely off the tower and out of the danger zone.
              </label>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="chkToolsRemoved" checked>
              <label class="form-check-label small" for="chkToolsRemoved">
                <strong>Portable Grounding Leads Removed:</strong> Temporary earth discharge clamps detached from all phases.
              </label>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="chkSafetyWarning" checked>
              <label class="form-check-label small text-danger" for="chkSafetyWarning">
                <strong>Acknowledge Physical Boundary:</strong> I understand that this digital declaration informs the control room and does not replace certified manual electrical safety procedures.
              </label>
            </div>

            <button type="button" id="btnSubmitClearanceChecklist" class="btn btn-success w-100 py-2 fw-bold" onclick="WorkerPortal.submitClearanceChecklist()">
              <i class="bi bi-shield-check me-1"></i> Sign & Submit Final Clearance
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 3. Reopen Clearance Modal -->
  <div class="modal fade" id="reopenClearanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Reopen Clearance (Report Hazard)</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <p class="text-secondary small mb-2">
            Reopening clearance for <strong id="reopenJobCodeDisplay" class="text-dark font-monospace"></strong> will:
          </p>
          <ul class="text-danger small ps-3 mb-3">
            <li>Instantly invalidate all previous worker approvals for this feeder.</li>
            <li>Return the feeder to a restricted maintenance lock state.</li>
            <li>Block all pending restoration authorizations.</li>
            <li>Notify the supervisor and control room immediately.</li>
          </ul>

          <div class="mb-3">
            <label class="form-label small fw-bold">Reason for Reopening</label>
            <textarea id="reopenReasonInput" class="form-control form-control-sm" rows="3" placeholder="e.g. Loose jumper clamp discovered at pole 14 during final tower descent..."></textarea>
          </div>

          <button type="button" id="btnConfirmReopen" class="btn btn-danger w-100 py-2 fw-bold" onclick="WorkerPortal.confirmReopenClearance()">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Confirm Reopening & Lock Feeder
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- 4. Supervisor Review Modal -->
  <div class="modal fade" id="supervisorReviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-navy text-white" style="background-color: var(--eg-navy);">
          <h5 class="modal-title fw-bold"><i class="bi bi-clipboard2-check me-2"></i>Supervisor Safety Inspection Review</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="reviewJobIdInput">
          <p class="text-secondary small mb-3">
            Record supervisor safety inspection signoff for <strong id="reviewJobCodeDisplay" class="text-primary font-monospace"></strong>:
          </p>

          <div class="mb-3">
            <label class="form-label small fw-bold">Inspection Finding</label>
            <select id="reviewStatusSelect" class="form-select form-select-sm">
              <option value="APPROVED">APPROVED (All Safety Checks Verified)</option>
              <option value="REJECTED">REJECTED (Remedial Action Required)</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Procedural Notes</label>
            <textarea id="reviewNotesInput" class="form-control form-control-sm" rows="3">Ground discharge clamps removed. Lineworkers accounted for off-structure.</textarea>
          </div>

          <button type="button" class="btn btn-primary w-100" onclick="AdminDashboard.submitSupervisorReview()">
            Record Review
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- 5. Create Maintenance Job Modal -->
  <div class="modal fade" id="createJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-navy text-white" style="background-color: var(--eg-navy);">
          <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Register New Maintenance Job</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Job Code</label>
              <input type="text" id="newJobCodeInput" class="form-control form-control-sm font-monospace" value="EG-JOB-1005">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Demonstration Feeder Area</label>
              <select id="newJobAreaSelect" class="form-select form-select-sm"></select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Work Title</label>
              <input type="text" id="newJobTitleInput" class="form-control form-control-sm" placeholder="e.g. 11kV Conductor Restringing & Insulator Overhaul">
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Work Description</label>
              <textarea id="newJobDescInput" class="form-control form-control-sm" rows="2" placeholder="Details of the scheduled line maintenance..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Safety Notes & Specific Hazards</label>
              <textarea id="newJobSafetyNotes" class="form-control form-control-sm" rows="2" placeholder="e.g. Apply portable earth clamp on phase A, B, C at tower 14..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold">Assign Required Lineworkers (Clearance Mandatory for Each)</label>
              <div id="newJobWorkersContainer" class="p-3 border rounded bg-light" style="max-height: 160px; overflow-y: auto;"></div>
            </div>
          </div>

          <div class="mt-4">
            <button type="button" class="btn btn-primary w-100" onclick="AdminDashboard.submitNewJob()">
              <i class="bi bi-shield-lock-fill me-1"></i> Register Job & Impose Feeder Restriction
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- ElectroGuard Application Scripts -->
  <script src="/assets/js/api-client.js"></script>
  <script src="/assets/js/map-manager.js"></script>
  <script src="/assets/js/charts-manager.js"></script>
  <script src="/assets/js/worker-portal.js"></script>
  <script src="/assets/js/admin-dashboard.js"></script>
  <script src="/assets/js/app.js"></script>
</body>
</html>
