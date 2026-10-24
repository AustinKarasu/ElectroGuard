/**
 * ElectroGuard Admin Dashboard Module
 * Central operations coordination, live map, jobs, clearance review, restoration, and IoT simulation.
 */

const AdminDashboard = {
  currentTab: 'overview',
  activeJobs: [],
  controllersList: [],

  async init() {
    this.bindEvents();
    await this.loadOverview();
  },

  bindEvents() {
    // Navigation sidebar clicks
    document.querySelectorAll('.eg-sidebar-item[data-tab]').forEach(item => {
      item.addEventListener('click', (e) => {
        e.preventDefault();
        const tab = item.getAttribute('data-tab');
        this.switchTab(tab);
      });
    });
  },

  switchTab(tabName) {
    this.currentTab = tabName;

    // Update sidebar active state
    document.querySelectorAll('.eg-sidebar-item[data-tab]').forEach(item => {
      if (item.getAttribute('data-tab') === tabName) {
        item.classList.add('active');
      } else {
        item.classList.remove('active');
      }
    });

    // Hide all tab panes
    document.querySelectorAll('.eg-tab-pane').forEach(pane => {
      pane.style.display = 'none';
    });

    // Show selected pane
    const target = document.getElementById(`tab-${tabName}`);
    if (target) {
      target.style.display = 'block';
    }

    // Lazy load specific tab content
    if (tabName === 'overview') this.loadOverview();
    if (tabName === 'map') {
      setTimeout(() => MapManager.init(), 100);
    }
    if (tabName === 'jobs') this.loadJobs();
    if (tabName === 'workers') this.loadWorkers();
    if (tabName === 'clearance') this.loadClearanceManagement();
    if (tabName === 'restoration') this.loadRestorationRequests();
    if (tabName === 'controllers') this.loadControllers();
    if (tabName === 'alerts') this.loadAlerts();
    if (tabName === 'audit') this.loadAuditLogs();
    if (tabName === 'reports') {
      setTimeout(() => ChartsManager.init(), 100);
    }
  },

  async loadOverview() {
    try {
      const summaryRes = await Api.reports.summary();
      if (summaryRes.success) {
        const d = summaryRes.data;
        document.getElementById('metricActiveJobs').textContent = d.active_jobs;
        document.getElementById('metricPendingClearances').textContent = d.total_jobs - d.clearance_complete;
        document.getElementById('metricBlockedRestorations').textContent = d.blocked_restorations;
        document.getElementById('metricPendingReview').textContent = d.pending_supervisor;
        document.getElementById('metricControllersOnline').textContent = `${d.online_controllers} / ${d.online_controllers + d.offline_controllers}`;
      }

      // Load Recent Activity Feed
      const auditRes = await Api.audit.list({ limit: 8 });
      const feedContainer = document.getElementById('overviewActivityFeed');
      if (feedContainer && auditRes.success) {
        let feedHtml = '';
        auditRes.data.slice(0, 7).forEach(log => {
          const isSuccess = log.result === 'SUCCESS';
          const badgeClass = isSuccess ? 'bg-success' : (log.result === 'BLOCKED' ? 'bg-danger' : 'bg-warning text-dark');
          feedHtml += `
            <div class="eg-activity-item">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-bold small text-dark">${log.action}</span>
                <span class="badge ${badgeClass} text-uppercase" style="font-size: 0.68rem;">${log.result}</span>
              </div>
              <div class="small text-secondary mb-1">${log.reason || 'Event executed'}</div>
              <div class="small text-muted font-monospace" style="font-size: 0.72rem;">
                <i class="bi bi-person-circle me-1"></i>${log.actor_name} (${log.actor_role}) &bull; ${new Date(log.created_at).toLocaleTimeString()}
              </div>
            </div>
          `;
        });
        feedContainer.innerHTML = feedHtml || '<div class="text-muted small">No recent activity.</div>';
      }
    } catch (err) {
      console.error('Failed to load overview:', err);
    }
  },

  async loadJobs() {
    const tbody = document.getElementById('jobsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

    try {
      const res = await Api.jobs.list();
      if (!res.success || !res.data) return;

      this.activeJobs = res.data;
      let html = '';

      res.data.forEach(job => {
        const evalData = job.evaluation || {};
        const reqCount = evalData.required_count || 1;
        const subCount = evalData.submitted_count || 0;
        const reopCount = evalData.reopened_count || 0;
        const restStatus = evalData.restoration_status || job.restoration_status;

        let statusBadge = '<span class="badge badge-status badge-status-danger">BLOCKED</span>';
        if (restStatus === 'AWAITING_REVIEW') {
          statusBadge = '<span class="badge badge-status badge-status-warning">AWAITING REVIEW</span>';
        } else if (restStatus === 'ELIGIBLE_FOR_REVIEW' || restStatus === 'SIMULATED_COMPLETION') {
          statusBadge = '<span class="badge badge-status badge-status-success">ELIGIBLE / COMPLETE</span>';
        }

        html += `
          <tr>
            <td class="fw-bold text-primary font-monospace">${job.job_code}</td>
            <td>
              <div class="fw-semibold text-dark">${job.area_name}</div>
              <div class="small text-muted">${job.title}</div>
            </td>
            <td>
              <span class="small font-monospace">${job.supervisor_name}</span>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height: 6px;">
                  <div class="progress-bar ${subCount === reqCount && reopCount === 0 ? 'bg-success' : 'bg-primary'}"
                       style="width: ${(subCount / reqCount) * 100}%"></div>
                </div>
                <span class="small fw-bold">${subCount}/${reqCount}</span>
              </div>
              ${reopCount > 0 ? '<span class="badge bg-danger mt-1" style="font-size: 0.65rem;">1 Reopened</span>' : ''}
            </td>
            <td>${statusBadge}</td>
            <td class="small text-secondary" style="max-width: 240px;">
              ${evalData.blocking_reason || 'Maintenance active'}
            </td>
            <td>
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                  Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="#" onclick="AdminDashboard.openReviewModal(${job.id}, '${job.job_code}')"><i class="bi bi-clipboard-check me-2"></i>Supervisor Review</a></li>
                  <li><a class="dropdown-item" href="#" onclick="AdminDashboard.openJobDetailsModal(${job.id})"><i class="bi bi-info-circle me-2"></i>View Full Audit Details</a></li>
                </ul>
              </div>
            </td>
          </tr>
        `;
      });

      tbody.innerHTML = html;
    } catch (err) {
      console.error('Failed to load jobs table:', err);
      tbody.innerHTML = `<tr><td colspan="7" class="text-danger py-3">Error loading jobs: ${err.message}</td></tr>`;
    }
  },

  async openCreateJobModal() {
    try {
      // Load areas and workers for form selects
      const [areasRes, workersRes] = await Promise.all([
        Api.jobs.getAreas(),
        Api.jobs.getWorkers()
      ]);

      const areaSelect = document.getElementById('newJobAreaSelect');
      areaSelect.innerHTML = '<option value="">-- Select Demonstration Feeder --</option>';
      if (areasRes.success) {
        areasRes.data.forEach(a => {
          areaSelect.innerHTML += `<option value="${a.id}">${a.name} (${a.substation})</option>`;
        });
      }

      const workersContainer = document.getElementById('newJobWorkersContainer');
      workersContainer.innerHTML = '';
      if (workersRes.success) {
        workersRes.data.forEach(w => {
          workersContainer.innerHTML += `
            <div class="form-check mb-1">
              <input class="form-check-input new-job-worker-chk" type="checkbox" value="${w.id}" id="chkWorker_${w.id}" checked>
              <label class="form-check-label small" for="chkWorker_${w.id}">
                <strong>${w.full_name}</strong> &bull; <span class="font-monospace text-muted">${w.worker_code}</span> (${w.skill_level})
              </label>
            </div>
          `;
        });
      }

      // Suggest unique job code
      document.getElementById('newJobCodeInput').value = `EG-JOB-${Math.floor(1005 + Math.random() * 8000)}`;

      const modal = new bootstrap.Modal(document.getElementById('createJobModal'));
      modal.show();
    } catch (err) {
      alert(`Could not open create job modal: ${err.message}`);
    }
  },

  async submitNewJob() {
    const jobCode = document.getElementById('newJobCodeInput').value.trim();
    const areaId = document.getElementById('newJobAreaSelect').value;
    const title = document.getElementById('newJobTitleInput').value.trim();
    const desc = document.getElementById('newJobDescInput').value.trim();
    const safety = document.getElementById('newJobSafetyNotes').value.trim();

    const workerIds = [];
    document.querySelectorAll('.new-job-worker-chk:checked').forEach(chk => {
      workerIds.push(parseInt(chk.value));
    });

    if (!jobCode || !areaId || !title || workerIds.length === 0) {
      alert('Please fill in Job Code, Area, Title, and assign at least one worker.');
      return;
    }

    try {
      const res = await Api.jobs.create({
        job_code: jobCode,
        area_id: parseInt(areaId),
        title: title,
        description: desc,
        safety_notes: safety,
        worker_ids: workerIds
      });

      if (res.success) {
        bootstrap.Modal.getInstance(document.getElementById('createJobModal')).hide();
        App.showToast('Job Created', `Maintenance job ${jobCode} registered with ${workerIds.length} required lineworkers.`, 'success');
        this.loadJobs();
      }
    } catch (err) {
      alert(`Failed to create job: ${err.message}`);
    }
  },

  async loadClearanceManagement() {
    const container = document.getElementById('clearanceCardsContainer');
    if (!container) return;

    try {
      const res = await Api.jobs.list();
      if (!res.success) return;

      let html = '';
      res.data.forEach(job => {
        const evalData = job.evaluation || {};
        const reqWorkers = evalData.missing_workers || [];
        const validClearances = evalData.valid_submissions || [];
        const reopenedClearances = evalData.reopened_submissions || [];

        html += `
          <div class="card mb-4 border shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <div>
                <span class="fw-bold font-monospace text-primary fs-6">${job.job_code}</span> &bull; 
                <span class="fw-semibold">${job.area_name}</span>
              </div>
              <div>
                <span class="badge ${evalData.is_eligible ? 'bg-success' : 'bg-danger'}">${evalData.restoration_status}</span>
              </div>
            </div>
            <div class="card-body">
              <h6 class="text-dark mb-2">${job.title}</h6>
              <div class="alert alert-light border small py-2 mb-3">
                <i class="bi bi-info-circle-fill text-primary me-1"></i>
                <strong>Central Rule Engine Reason:</strong> ${evalData.blocking_reason}
              </div>

              <h6 class="small text-muted text-uppercase fw-bold mb-2">Worker Clearance Statuses:</h6>
              <div class="list-group list-group-flush border rounded mb-3 small">
                ${validClearances.map(c => `
                  <div class="list-group-item d-flex justify-content-between align-items-center bg-light">
                    <div>
                      <i class="bi bi-check-circle-fill text-success me-2"></i>
                      <strong>${c.full_name}</strong> (${c.worker_code})
                      <span class="text-muted ms-2">&bull; Submitted: ${new Date(c.submitted_at).toLocaleTimeString()}</span>
                    </div>
                    <span class="badge bg-success">CLEARANCE VALID</span>
                  </div>
                `).join('')}

                ${reopenedClearances.map(c => `
                  <div class="list-group-item d-flex justify-content-between align-items-center bg-danger-subtle">
                    <div>
                      <i class="bi bi-arrow-clockwise text-danger me-2"></i>
                      <strong>${c.full_name}</strong> (${c.worker_code})
                      <div class="text-danger small ms-4">Hazard Reported: ${c.reopen_reason}</div>
                    </div>
                    <span class="badge bg-danger">REOPENED / INVALIDATED</span>
                  </div>
                `).join('')}

                ${reqWorkers.filter(w => w.status === 'PENDING').map(w => `
                  <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <i class="bi bi-hourglass text-warning me-2"></i>
                      <strong>${w.name}</strong> (${w.code})
                    </div>
                    <span class="badge bg-warning text-dark">PENDING SUBMISSION</span>
                  </div>
                `).join('')}
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <div class="small">
                  <strong>Supervisor Review:</strong> 
                  ${evalData.is_supervisor_approved ? '<span class="text-success"><i class="bi bi-shield-check me-1"></i>Approved</span>' : '<span class="text-warning"><i class="bi bi-shield-slash me-1"></i>Pending</span>'}
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="AdminDashboard.openReviewModal(${job.id}, '${job.job_code}')">
                  <i class="bi bi-pencil-square me-1"></i> Supervisor Procedural Review
                </button>
              </div>
            </div>
          </div>
        `;
      });

      container.innerHTML = html;
    } catch (err) {
      console.error('Failed to load clearance cards:', err);
    }
  },

  openReviewModal(jobId, jobCode) {
    document.getElementById('reviewJobIdInput').value = jobId;
    document.getElementById('reviewJobCodeDisplay').textContent = jobCode;
    document.getElementById('reviewNotesInput').value = 'Ground clamps removed. All personnel safely off-structure and accounted for.';
    const modal = new bootstrap.Modal(document.getElementById('supervisorReviewModal'));
    modal.show();
  },

  async submitSupervisorReview() {
    const jobId = document.getElementById('reviewJobIdInput').value;
    const notes = document.getElementById('reviewNotesInput').value.trim();
    const status = document.getElementById('reviewStatusSelect').value;

    try {
      const res = await Api.clearance.supervisorReview(jobId, notes, status);
      if (res.success) {
        bootstrap.Modal.getInstance(document.getElementById('supervisorReviewModal')).hide();
        App.showToast('Supervisor Review Recorded', `Safety check for Job #${jobId} recorded as ${status}.`, 'info');
        this.loadClearanceManagement();
        this.loadJobs();
      }
    } catch (err) {
      alert(`Supervisor review rejected: ${err.message}`);
    }
  },

  async loadRestorationRequests() {
    const tbody = document.getElementById('restorationTableBody');
    if (!tbody) return;

    try {
      const res = await Api.jobs.list();
      if (!res.success) return;

      let html = '';
      res.data.forEach(job => {
        const evalData = job.evaluation || {};
        const isEligible = evalData.is_eligible;
        const restStatus = evalData.restoration_status;

        html += `
          <tr>
            <td class="font-monospace fw-bold text-dark">${job.job_code}</td>
            <td>
              <div class="fw-semibold">${job.area_name}</div>
              <div class="small text-muted">${job.voltage_rating || '11kV Feeder'}</div>
            </td>
            <td>
              <div class="small">
                <strong>${evalData.submitted_count}/${evalData.required_count}</strong> Workers Cleared<br>
                Supervisor: ${evalData.is_supervisor_approved ? '<span class="text-success">Verified</span>' : '<span class="text-warning">Pending</span>'}
              </div>
            </td>
            <td>
              <span class="badge ${evalData.is_controller_online ? 'bg-success' : 'bg-secondary'} font-monospace">
                ${evalData.controller_uid}: ${evalData.controller_status}
              </span>
            </td>
            <td>
              <span class="badge ${isEligible || restStatus === 'SIMULATED_COMPLETION' ? 'badge-status-success' : 'badge-status-danger'}">
                ${restStatus}
              </span>
            </td>
            <td class="small" style="max-width: 250px;">
              ${evalData.blocking_reason}
            </td>
            <td>
              ${restStatus === 'SIMULATED_COMPLETION' ? `
                <button class="btn btn-sm btn-outline-success" disabled>
                  <i class="bi bi-check2-circle me-1"></i> Simulated Energization Active
                </button>
              ` : `
                <button class="btn btn-sm ${isEligible ? 'btn-success' : 'btn-outline-danger'}"
                        onclick="AdminDashboard.attemptRestorationSwitching(${job.id}, '${job.job_code}')">
                  <i class="bi bi-power me-1"></i> ${isEligible ? 'Authorize Simulated Switching' : 'Blocked (Rule Engine)'}
                </button>
              `}
            </td>
          </tr>
        `;
      });

      tbody.innerHTML = html;
    } catch (err) {
      console.error('Failed to load restoration table:', err);
    }
  },

  async attemptRestorationSwitching(jobId, jobCode) {
    if (!confirm(`Attempt simulated restoration switching authorization for Job ${jobCode}?\n\nNotice: ElectroGuard Rule Engine will independently verify all worker clearances and controller connectivity before authorizing.`)) {
      return;
    }

    try {
      const res = await Api.restoration.execute(jobId);
      if (res.success) {
        App.showToast('Simulated Restoration Authorized', res.message, 'success');
        this.loadRestorationRequests();
        this.loadJobs();
      }
    } catch (err) {
      alert(`RESTORATION REQUEST DENIED BY RULE ENGINE:\n\n${err.message}`);
      this.loadRestorationRequests();
    }
  },

  async loadControllers() {
    const container = document.getElementById('controllersGrid');
    if (!container) return;

    try {
      const res = await Api.controllers.list();
      if (!res.success) return;

      this.controllersList = res.data;
      let html = '';

      res.data.forEach(c => {
        const isOnline = c.connection_status === 'ONLINE';
        const isStale = c.connection_status === 'STALE';
        const ledColor = isOnline ? 'led-green' : (isStale ? 'led-amber' : 'led-red');

        html += `
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="eg-schematic-card h-100">
              <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-3">
                <span class="text-cyan fw-bold">${c.controller_uid}</span>
                <span class="led-indicator ${ledColor}"></span>
              </div>
              <div class="small text-muted mb-1">MONITORED FEEDER AREA:</div>
              <div class="text-white fw-bold mb-3">${c.area_name || 'Standby Controller'}</div>

              <div class="eg-schematic-grid mb-3">
                <div class="eg-schematic-item">
                  <div class="eg-schematic-label">COMM LINK:</div>
                  <div class="eg-schematic-value ${isOnline ? 'text-success' : 'text-danger'}">${c.connection_status}</div>
                </div>
                <div class="eg-schematic-item">
                  <div class="eg-schematic-label">OUTPUT STATE:</div>
                  <div class="eg-schematic-value">${c.relay_state}</div>
                </div>
              </div>

              <div class="small text-muted mb-3">
                Last Heartbeat: <span class="text-light">${new Date(c.last_heartbeat).toLocaleTimeString()}</span>
              </div>

              <div class="border-top border-secondary pt-3 d-flex flex-wrap gap-2">
                <button class="btn btn-sm btn-outline-info" onclick="AdminDashboard.toggleControllerStatus(${c.id}, '${isOnline ? 'OFFLINE' : 'ONLINE'}')">
                  <i class="bi bi-power me-1"></i> Toggle ${isOnline ? 'Disconnect' : 'Connect'}
                </button>
                <button class="btn btn-sm btn-outline-light" onclick="AdminDashboard.testControllerPulse(${c.id})">
                  <i class="bi bi-broadcast me-1"></i> Low-Voltage Pulse
                </button>
              </div>
            </div>
          </div>
        `;
      });

      container.innerHTML = html;
    } catch (err) {
      console.error('Failed to load controllers:', err);
    }
  },

  async toggleControllerStatus(controllerId, newStatus) {
    try {
      const res = await Api.controllers.updateStatus(controllerId, newStatus);
      if (res.success) {
        App.showToast('Controller State Changed', res.message, 'info');
        this.loadControllers();
      }
    } catch (err) {
      alert(`Failed to update controller: ${err.message}`);
    }
  },

  async testControllerPulse(controllerId) {
    try {
      const res = await Api.controllers.sendCommand(controllerId, 'TEST_PULSE');
      if (res.success) {
        App.showToast('Controller Acknowledged', res.message, 'success');
      }
    } catch (err) {
      alert(`Controller Error: ${err.message}`);
    }
  },

  async loadWorkers() {
    const tbody = document.getElementById('workersTableBody');
    if (!tbody) return;

    try {
      const res = await Api.jobs.getWorkers();
      if (!res.success) return;

      let html = '';
      res.data.forEach(w => {
        html += `
          <tr>
            <td class="font-monospace fw-bold text-primary">${w.worker_code}</td>
            <td class="fw-semibold text-dark">${w.full_name}</td>
            <td class="small text-secondary">${w.department}</td>
            <td><span class="badge bg-light text-dark border">${w.skill_level}</span></td>
            <td class="font-monospace small text-muted">${w.phone_masked}</td>
            <td><span class="badge bg-success">ACTIVE & CERTIFIED</span></td>
          </tr>
        `;
      });
      tbody.innerHTML = html;
    } catch (err) {
      console.error('Failed to load workers table:', err);
    }
  },

  async loadAlerts() {
    const container = document.getElementById('alertsListContainer');
    if (!container) return;

    try {
      const res = await Api.alerts.list();
      if (!res.success) return;

      let html = '';
      res.data.forEach(a => {
        const isCritical = a.severity === 'CRITICAL';
        const isWarning = a.severity === 'WARNING';
        const alertClass = isCritical ? 'border-danger' : (isWarning ? 'border-warning' : 'border-info');
        const badgeClass = isCritical ? 'bg-danger' : (isWarning ? 'bg-warning text-dark' : 'bg-info');

        html += `
          <div class="card mb-2 ${alertClass} shadow-sm ${a.is_read ? 'opacity-75' : ''}">
            <div class="card-body p-3 d-flex justify-content-between align-items-center">
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="badge ${badgeClass}">${a.severity}</span>
                  <span class="fw-bold text-dark">${a.title}</span>
                  <span class="text-muted small">&bull; ${new Date(a.created_at).toLocaleString()}</span>
                </div>
                <div class="text-secondary small">${a.message}</div>
              </div>
              <div>
                ${!a.is_read ? `
                  <button class="btn btn-sm btn-outline-secondary" onclick="AdminDashboard.markAlertRead(${a.id})">
                    <i class="bi bi-check2"></i> Mark Read
                  </button>
                ` : `
                  <span class="text-muted small"><i class="bi bi-check2-all"></i> Read</span>
                `}
              </div>
            </div>
          </div>
        `;
      });

      container.innerHTML = html || '<div class="text-muted small">No active safety alerts.</div>';
    } catch (err) {
      console.error('Failed to load alerts:', err);
    }
  },

  async markAlertRead(alertId) {
    try {
      await Api.alerts.markRead(alertId);
      this.loadAlerts();
    } catch (err) {
      console.error('Failed to mark alert as read:', err);
    }
  },

  async loadAuditLogs() {
    const tbody = document.getElementById('auditTableBody');
    if (!tbody) return;

    const search = document.getElementById('auditSearchInput')?.value || '';
    const role = document.getElementById('auditRoleFilter')?.value || '';
    const result = document.getElementById('auditResultFilter')?.value || '';

    try {
      const res = await Api.audit.list({ search, role, result });
      if (!res.success) return;

      let html = '';
      res.data.forEach(log => {
        const isSuccess = log.result === 'SUCCESS';
        const badgeClass = isSuccess ? 'bg-success' : (log.result === 'BLOCKED' ? 'bg-danger' : 'bg-warning text-dark');

        html += `
          <tr>
            <td class="font-monospace small text-muted">${log.correlation_id}</td>
            <td class="small">${new Date(log.created_at).toLocaleString()}</td>
            <td class="small fw-semibold">${log.actor_name}</td>
            <td><span class="badge bg-light text-dark border small text-uppercase">${log.actor_role}</span></td>
            <td class="font-monospace small fw-bold text-primary">${log.action}</td>
            <td><span class="badge ${badgeClass} text-uppercase">${log.result}</span></td>
            <td class="small text-secondary" style="max-width: 320px;">${log.reason || '-'}</td>
          </tr>
        `;
      });

      tbody.innerHTML = html;
    } catch (err) {
      console.error('Failed to load audit logs:', err);
    }
  }
};

window.AdminDashboard = AdminDashboard;
