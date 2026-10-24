/**
 * ElectroGuard Worker Portal Module
 * Optimized field interface for electrical lineworkers and technicians.
 */

const WorkerPortal = {
  currentJob: null,
  activeWorker: null,
  otpChallenge: null,
  isIdVerified: false,
  isOtpVerified: false,

  async init() {
    await this.loadWorkerProfile();
    await this.loadAssignedJobs();
  },

  async loadWorkerProfile() {
    try {
      const me = await Api.auth.me();
      if (me.authenticated && me.user) {
        this.activeWorker = me.user;
        const nameEl = document.getElementById('workerPortalName');
        const codeEl = document.getElementById('workerPortalCode');
        const phoneEl = document.getElementById('workerPortalPhone');

        if (nameEl) nameEl.textContent = me.user.full_name;
        if (codeEl) codeEl.textContent = me.user.worker_profile?.worker_code || 'WK-KGR-701';
        if (phoneEl) phoneEl.textContent = me.user.phone_masked || '+91 98*** **412';
      }
    } catch (err) {
      console.error('Failed to load worker profile:', err);
    }
  },

  async loadAssignedJobs() {
    const container = document.getElementById('workerJobsContainer');
    if (!container) return;

    try {
      container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
      const res = await Api.jobs.workerJobs();
      if (!res.success || !res.data || res.data.length === 0) {
        container.innerHTML = `
          <div class="card p-4 text-center text-muted">
            <i class="bi bi-briefcase fs-1 mb-2"></i>
            <h5>No Active Maintenance Jobs Assigned</h5>
            <p class="mb-0">You currently have no active work assignments requiring safety clearance.</p>
          </div>
        `;
        return;
      }

      this.currentJob = res.data[0]; // Select primary active assignment
      let html = '';

      res.data.forEach(job => {
        const evalData = job.evaluation || {};
        const isMyCleared = job.my_clearance_status === 'SUBMITTED';
        const isMyReopened = job.my_clearance_status === 'REOPENED';
        const statusBadge = isMyCleared
          ? '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Your Clearance Signed</span>'
          : (isMyReopened
              ? '<span class="badge bg-danger"><i class="bi bi-arrow-clockwise me-1"></i>Clearance Reopened</span>'
              : '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Action Required: Clearance Pending</span>');

        html += `
          <div class="card mb-3 border-${isMyCleared ? 'success' : (isMyReopened ? 'danger' : 'primary')} shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <div>
                <span class="fw-bold text-dark fs-5">${job.job_code}</span>
                <span class="ms-2 text-muted">| ${job.area_name}</span>
              </div>
              <div>${statusBadge}</div>
            </div>
            <div class="card-body">
              <h5 class="card-title text-primary">${job.title}</h5>
              <p class="card-text text-secondary mb-3">${job.description || 'No work description provided.'}</p>
              
              <div class="row g-2 mb-3 font-monospace small">
                <div class="col-sm-6">
                  <div class="p-2 bg-light rounded border">
                    <span class="text-muted d-block">Substation:</span>
                    <strong>${job.substation}</strong>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="p-2 bg-light rounded border">
                    <span class="text-muted d-block">Supervisor In-Charge:</span>
                    <strong>${job.supervisor_name}</strong>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="p-2 bg-light rounded border">
                    <span class="text-muted d-block">Required Lineworkers:</span>
                    <strong>${evalData.required_count || 1} Technicians</strong> (${evalData.submitted_count || 0} Submitted)
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="p-2 bg-light rounded border">
                    <span class="text-muted d-block">Restoration Eligibility:</span>
                    <strong class="${evalData.is_eligible ? 'text-success' : 'text-danger'}">${evalData.restoration_status || 'BLOCKED'}</strong>
                  </div>
                </div>
              </div>

              <div class="alert alert-secondary py-2 px-3 small mb-3">
                <i class="bi bi-shield-exclamation me-1 text-warning"></i>
                <strong>Safety Notes:</strong> ${job.safety_notes || 'Verify local earth discharge before tower ascent.'}
              </div>

              ${isMyReopened ? `
                <div class="alert alert-danger py-2 px-3 small mb-3">
                  <strong>Clearance Reopened:</strong> ${job.my_reopen_reason || 'Reopened by technician for field inspection.'}
                </div>
              ` : ''}

              <div class="d-flex flex-wrap gap-2">
                ${!isMyCleared ? `
                  <button class="btn btn-primary" onclick="WorkerPortal.openVerificationModal(${job.id}, '${job.job_code}')">
                    <i class="bi bi-qr-code-scan me-1"></i> Verify Identity & Sign Clearance
                  </button>
                ` : `
                  <button class="btn btn-outline-danger" onclick="WorkerPortal.openReopenModal(${job.id}, '${job.job_code}')">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Reopen Clearance (Report Hazard)
                  </button>
                  <button class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-check2-all me-1"></i> Clearance Recorded (${new Date(job.my_submitted_at).toLocaleTimeString()})
                  </button>
                `}
              </div>
            </div>
          </div>
        `;
      });

      container.innerHTML = html;
    } catch (err) {
      console.error('Failed to load worker jobs:', err);
      container.innerHTML = `<div class="alert alert-danger">Error loading assigned jobs: ${err.message}</div>`;
    }
  },

  openVerificationModal(jobId, jobCode) {
    this.currentJobId = jobId;
    this.currentJobCode = jobCode;
    this.isIdVerified = false;
    this.isOtpVerified = false;

    // Reset modal steps
    document.getElementById('stepWorkerId').style.display = 'block';
    document.getElementById('stepOtp').style.display = 'none';
    document.getElementById('stepChecklist').style.display = 'none';

    document.getElementById('workerScanResult').innerHTML = '';
    document.getElementById('otpInputResult').innerHTML = '';
    document.getElementById('workerCodeInput').value = this.activeWorker?.worker_profile?.worker_code || 'WK-KGR-701';

    const modal = new bootstrap.Modal(document.getElementById('workerClearanceModal'));
    modal.show();
  },

  async verifyWorkerId() {
    const code = document.getElementById('workerCodeInput').value.trim();
    const resultEl = document.getElementById('workerScanResult');
    resultEl.innerHTML = '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Verifying worker badge against utility records...';

    try {
      const res = await Api.clearance.verifyWorkerId(code, this.currentJobId);
      if (res.success) {
        this.isIdVerified = true;
        resultEl.innerHTML = `
          <div class="alert alert-success mt-2 py-2">
            <i class="bi bi-check-circle-fill me-1"></i> <strong>Badge Verified:</strong> ${res.worker.full_name} (${res.worker.skill_level})
          </div>
        `;
        setTimeout(() => {
          document.getElementById('stepWorkerId').style.display = 'none';
          document.getElementById('stepOtp').style.display = 'block';
          this.requestOtp();
        }, 800);
      }
    } catch (err) {
      resultEl.innerHTML = `<div class="alert alert-danger mt-2 py-2"><i class="bi bi-x-circle-fill me-1"></i> ${err.message}</div>`;
    }
  },

  async requestOtp() {
    const hintEl = document.getElementById('simulatedOtpHint');
    hintEl.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Issuing verification code...';

    try {
      const res = await Api.clearance.requestOtp(this.currentJobId, 'CLEARANCE');
      if (res.success) {
        this.otpChallenge = res.data;
        hintEl.innerHTML = `
          <div class="alert alert-info py-2 px-3 small border-info">
            <i class="bi bi-phone-fill me-1"></i> <strong>Prototype Simulated Two-Factor Code:</strong>
            <span class="fs-5 fw-bold text-primary ms-2 font-monospace">${res.data.demo_code}</span>
            <div class="text-muted small mt-1">Simulates secure SMS/channel verification for lineworker demonstration. (Expires in 5 minutes)</div>
          </div>
        `;
        document.getElementById('otpCodeInput').value = res.data.demo_code;
      }
    } catch (err) {
      hintEl.innerHTML = `<div class="alert alert-danger py-2 small">${err.message}</div>`;
    }
  },

  async verifyOtp() {
    const code = document.getElementById('otpCodeInput').value.trim();
    const resultEl = document.getElementById('otpInputResult');
    resultEl.innerHTML = '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Validating verification code...';

    try {
      const res = await Api.clearance.verifyOtp(this.currentJobId, code);
      if (res.success) {
        this.isOtpVerified = true;
        resultEl.innerHTML = '<div class="alert alert-success mt-2 py-2"><i class="bi bi-check-circle-fill me-1"></i> Code Verified Successfully.</div>';
        setTimeout(() => {
          document.getElementById('stepOtp').style.display = 'none';
          document.getElementById('stepChecklist').style.display = 'block';
        }, 600);
      }
    } catch (err) {
      resultEl.innerHTML = `<div class="alert alert-danger mt-2 py-2"><i class="bi bi-x-circle-fill me-1"></i> ${err.message}</div>`;
    }
  },

  async submitClearanceChecklist() {
    const chkWork = document.getElementById('chkWorkComplete').checked;
    const chkSite = document.getElementById('chkSiteCleared').checked;
    const chkTools = document.getElementById('chkToolsRemoved').checked;
    const chkWarning = document.getElementById('chkSafetyWarning').checked;

    if (!chkWork || !chkSite || !chkTools || !chkWarning) {
      alert('You must confirm all mandatory safety checklist declarations before submitting clearance.');
      return;
    }

    const btn = document.getElementById('btnSubmitClearanceChecklist');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Recording clearance...';

    try {
      const res = await Api.clearance.submit(this.currentJobId, {
        work_completed: 1,
        site_cleared: 1,
        tools_removed: 1,
        safety_warning_acknowledged: 1
      });

      if (res.success) {
        bootstrap.Modal.getInstance(document.getElementById('workerClearanceModal')).hide();
        App.showToast('Clearance Declaration Recorded', 'Your clearance was signed and submitted to the central rule engine.', 'success');
        await this.loadAssignedJobs();
      }
    } catch (err) {
      alert(`Clearance submission failed: ${err.message}`);
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Sign & Submit Final Clearance';
    }
  },

  openReopenModal(jobId, jobCode) {
    this.currentJobId = jobId;
    document.getElementById('reopenJobCodeDisplay').textContent = jobCode;
    document.getElementById('reopenReasonInput').value = '';
    const modal = new bootstrap.Modal(document.getElementById('reopenClearanceModal'));
    modal.show();
  },

  async confirmReopenClearance() {
    const reason = document.getElementById('reopenReasonInput').value.trim();
    if (!reason) {
      alert('A specific reason must be provided to reopen clearance and reinstate safety restrictions.');
      return;
    }

    const btn = document.getElementById('btnConfirmReopen');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Reopening...';

    try {
      const res = await Api.clearance.reopen(this.currentJobId, reason);
      if (res.success) {
        bootstrap.Modal.getInstance(document.getElementById('reopenClearanceModal')).hide();
        App.showToast('Clearance Reopened', 'Maintenance restrictions reinstated. Dependent authorizations invalidated.', 'danger');
        await this.loadAssignedJobs();
      }
    } catch (err) {
      alert(`Failed to reopen clearance: ${err.message}`);
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Confirm Reopening & Lock Feeder';
    }
  }
};

window.WorkerPortal = WorkerPortal;
