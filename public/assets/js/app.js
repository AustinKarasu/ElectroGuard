/**
 * ElectroGuard Main Application Core
 * Manages view routing, demonstration role switching, live clock, and notifications.
 */

const App = {
  currentUser: null,
  activeView: 'landing', // 'landing', 'worker', 'admin'

  async init() {
    this.startLiveClock();
    this.bindGlobalEvents();
    await this.checkAuth();
  },

  startLiveClock() {
    const clockEl = document.getElementById('liveClockDisplay');
    const update = () => {
      if (clockEl) {
        const now = new Date();
        clockEl.textContent = now.toLocaleDateString('en-IN', {
          weekday: 'short', day: 'numeric', month: 'short', year: 'numeric',
          hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        });
      }
    };
    update();
    setInterval(update, 1000);
  },

  bindGlobalEvents() {
    // Quick Demo Role Switchers
    document.querySelectorAll('[data-demo-role]').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const role = btn.getAttribute('data-demo-role');
        const workerIndex = btn.getAttribute('data-worker-index') || 1;
        await this.handleDemoLogin(role, workerIndex);
      });
    });

    // Login Form
    const loginForm = document.getElementById('credentialLoginForm');
    if (loginForm) {
      loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const identifier = document.getElementById('loginIdentifier').value;
        const password = document.getElementById('loginPassword').value;
        await this.handleLogin(identifier, password);
      });
    }

    // Logout Button
    document.querySelectorAll('.btn-app-logout').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        await this.handleLogout();
      });
    });
  },

  async checkAuth() {
    try {
      const res = await Api.auth.me();
      if (res.authenticated && res.user) {
        this.currentUser = res.user;
        this.updateUserUI();
        if (this.currentUser.role === 'worker') {
          this.showView('worker');
        } else {
          this.showView('admin');
        }
      } else {
        this.currentUser = null;
        this.showView('landing');
      }
    } catch (err) {
      console.error('Check auth error:', err);
      this.showView('landing');
    }
  },

  async handleDemoLogin(role, workerIndex = 1) {
    try {
      const res = await Api.auth.demoLogin(role, workerIndex);
      if (res.success) {
        this.currentUser = res.user;
        this.updateUserUI();
        this.showToast('Demonstration Login', `Logged in as ${res.user.full_name} (${res.user.role.toUpperCase()})`, 'success');
        
        // Hide login modal if open
        const loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
        if (loginModal) loginModal.hide();

        if (role === 'worker') {
          this.showView('worker');
        } else {
          this.showView('admin');
        }
      }
    } catch (err) {
      alert(`Demo login failed: ${err.message}`);
    }
  },

  async handleLogin(identifier, password) {
    try {
      const res = await Api.auth.login(identifier, password);
      if (res.success) {
        this.currentUser = res.user;
        this.updateUserUI();
        this.showToast('Login Successful', `Welcome, ${res.user.full_name}`, 'success');
        
        const loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
        if (loginModal) loginModal.hide();

        if (res.user.role === 'worker') {
          this.showView('worker');
        } else {
          this.showView('admin');
        }
      }
    } catch (err) {
      alert(`Login failed: ${err.message}`);
    }
  },

  async handleLogout() {
    try {
      await Api.auth.logout();
      this.currentUser = null;
      this.showToast('Logged Out', 'Your session has ended safely.', 'info');
      this.showView('landing');
    } catch (err) {
      console.error('Logout error:', err);
      this.showView('landing');
    }
  },

  updateUserUI() {
    if (!this.currentUser) return;
    const nameEls = document.querySelectorAll('.user-display-name');
    const roleEls = document.querySelectorAll('.user-display-role');

    nameEls.forEach(el => el.textContent = this.currentUser.full_name);
    roleEls.forEach(el => el.textContent = this.currentUser.role.toUpperCase());
  },

  showView(viewName) {
    this.activeView = viewName;

    // View Containers
    const landingView = document.getElementById('view-landing');
    const workerView = document.getElementById('view-worker');
    const adminView = document.getElementById('view-admin');

    if (landingView) landingView.style.display = (viewName === 'landing') ? 'block' : 'none';
    if (workerView) workerView.style.display = (viewName === 'worker') ? 'block' : 'none';
    if (adminView) adminView.style.display = (viewName === 'admin') ? 'block' : 'none';

    window.scrollTo({ top: 0, behavior: 'smooth' });

    if (viewName === 'worker') {
      WorkerPortal.init();
    } else if (viewName === 'admin') {
      AdminDashboard.init();
    }
  },

  openLoginModal() {
    const modal = new bootstrap.Modal(document.getElementById('loginModal'));
    modal.show();
  },

  showToast(title, message, type = 'info') {
    const toastContainer = document.getElementById('appToastContainer');
    if (!toastContainer) return;

    const bgClass = type === 'success' ? 'bg-success text-white' : (type === 'danger' ? 'bg-danger text-white' : 'bg-primary text-white');
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center ${bgClass} border-0 mb-2 shadow`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          <strong>${title}</strong><br>${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;

    toastContainer.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }
};

window.App = App;

// Bootstrap on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  App.init();
});
