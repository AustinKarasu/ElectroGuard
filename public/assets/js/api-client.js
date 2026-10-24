/**
 * ElectroGuard API Client Module
 * Provides unified communication with REST API backend endpoints.
 */

const Api = {
  baseUrl: '/api',

  async request(endpoint, options = {}) {
    const config = {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(options.headers || {})
      },
      ...options
    };

    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, config);
      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        const errorMsg = data.error?.message || data.error || data.message || `HTTP Error ${response.status}`;
        const err = new Error(errorMsg);
        err.status = response.status;
        err.data = data;
        throw err;
      }

      return data;
    } catch (err) {
      console.error(`API Error on ${endpoint}:`, err);
      throw err;
    }
  },

  // Authentication
  auth: {
    login: (identifier, password) => Api.request('/auth/login', { method: 'POST', body: JSON.stringify({ identifier, password }) }),
    demoLogin: (role, worker_index = 1) => Api.request('/auth/demo-login', { method: 'POST', body: JSON.stringify({ role, worker_index }) }),
    logout: () => Api.request('/auth/logout', { method: 'POST' }),
    me: () => Api.request('/auth/me')
  },

  // Jobs
  jobs: {
    list: () => Api.request('/jobs'),
    get: (id) => Api.request(`/jobs/${id}`),
    create: (payload) => Api.request('/jobs', { method: 'POST', body: JSON.stringify(payload) }),
    workerJobs: () => Api.request('/workers/me/jobs'),
    getAreas: () => Api.request('/areas'),
    getWorkers: () => Api.request('/workers')
  },

  // Clearance & OTP
  clearance: {
    verifyWorkerId: (worker_code, job_id) => Api.request('/clearance/verify-worker-id', { method: 'POST', body: JSON.stringify({ worker_code, job_id }) }),
    requestOtp: (jobId, purpose = 'CLEARANCE') => Api.request(`/jobs/${jobId}/otp/request`, { method: 'POST', body: JSON.stringify({ purpose }) }),
    verifyOtp: (jobId, code) => Api.request(`/jobs/${jobId}/otp/verify`, { method: 'POST', body: JSON.stringify({ code }) }),
    submit: (jobId, declarations) => Api.request(`/jobs/${jobId}/clearances`, { method: 'POST', body: JSON.stringify(declarations) }),
    reopen: (jobId, reason) => Api.request(`/jobs/${jobId}/clearances/reopen`, { method: 'POST', body: JSON.stringify({ reason }) }),
    supervisorReview: (jobId, notes, status = 'APPROVED') => Api.request(`/jobs/${jobId}/review`, { method: 'POST', body: JSON.stringify({ notes, status }) })
  },

  // Restoration
  restoration: {
    list: () => Api.request('/restoration-requests'),
    execute: (jobId) => Api.request(`/jobs/${jobId}/restoration-requests/execute`, { method: 'POST' })
  },

  // Controllers / IoT
  controllers: {
    list: () => Api.request('/controllers'),
    updateStatus: (id, status, delay = 0, failNext = 0) => Api.request(`/controllers/${id}/status`, {
      method: 'POST',
      body: JSON.stringify({ status, response_delay_ms: delay, fail_next_command: failNext })
    }),
    sendCommand: (id, command) => Api.request(`/controllers/${id}/command`, { method: 'POST', body: JSON.stringify({ command }) })
  },

  // Alerts
  alerts: {
    list: (severity = null) => Api.request(`/alerts${severity ? `?severity=${severity}` : ''}`),
    markRead: (id) => Api.request(`/alerts/${id}/read`, { method: 'POST' }),
    markAllRead: () => Api.request('/alerts/mark-all-read', { method: 'POST' })
  },

  // Audit Logs
  audit: {
    list: (params = {}) => {
      const qs = new URLSearchParams(params).toString();
      return Api.request(`/audit-logs${qs ? `?${qs}` : ''}`);
    }
  },

  // Reports
  reports: {
    summary: () => Api.request('/reports/summary'),
    exportCsvUrl: () => `${Api.baseUrl}/reports/export-csv`
  }
};

window.Api = Api;
