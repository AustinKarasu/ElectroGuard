/**
 * ElectroGuard Charts Manager
 * Visualizes maintenance data, clearances, and controller metrics using Chart.js.
 */

const ChartsManager = {
  statusChart: null,
  clearanceChart: null,

  async init() {
    try {
      const res = await Api.reports.summary();
      if (res.success) {
        this.renderStatusChart(res.data);
        this.renderClearanceChart(res.data);
      }
    } catch (err) {
      console.error('Failed to load chart metrics:', err);
    }
  },

  renderStatusChart(data) {
    const ctx = document.getElementById('jobStatusChart');
    if (!ctx) return;

    if (this.statusChart) {
      this.statusChart.destroy();
    }

    this.statusChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Restricted (Active Work)', 'Supervisor Review Pending', 'Clearance Complete'],
        datasets: [{
          data: [data.active_jobs, data.pending_supervisor, data.clearance_complete],
          backgroundColor: ['#DF4C5B', '#EEA62E', '#1C9B63'],
          borderWidth: 2,
          borderColor: '#FFFFFF'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 12,
              padding: 12,
              font: { size: 11, family: 'system-ui' }
            }
          }
        },
        cutout: '68%'
      }
    });
  },

  renderClearanceChart(data) {
    const ctx = document.getElementById('clearanceProgressChart');
    if (!ctx) return;

    if (this.clearanceChart) {
      this.clearanceChart.destroy();
    }

    this.clearanceChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Submitted Clearances', 'Reopened Clearances', 'Blocked Restorations', 'Eligible Requests'],
        datasets: [{
          label: 'Count',
          data: [
            data.total_clearances_submitted,
            data.total_clearances_reopened,
            data.blocked_restorations,
            data.eligible_restorations
          ],
          backgroundColor: ['#2777E6', '#DF4C5B', '#EEA62E', '#1C9B63'],
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        }
      }
    });
  }
};

window.ChartsManager = ChartsManager;
