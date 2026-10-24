/**
 * ElectroGuard Leaflet Map Manager
 * Handles interactive geographic visualization of Kangra demonstration electrical maintenance areas.
 */

const MapManager = {
  map: null,
  markersLayer: null,
  jobsData: [],
  currentFilter: 'ALL',

  init(containerId = 'map') {
    if (this.map) {
      this.map.invalidateSize();
      return;
    }

    const container = document.getElementById(containerId);
    if (!container) return;

    // Centered around Kangra, Himachal Pradesh (32.1° N, 76.27° E)
    this.map = L.map(containerId).setView([32.14, 76.28], 11);

    // OpenStreetMap standard tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&copy; OpenStreetMap contributors | <b>DEMO DATA (Kangra District)</b>'
    }).addTo(this.map);

    this.markersLayer = L.layerGroup().addTo(this.map);
    this.loadData();
  },

  async loadData() {
    try {
      const res = await Api.jobs.list();
      if (res.success) {
        this.jobsData = res.data;
        this.renderMarkers();
      }
    } catch (err) {
      console.error('Failed to load map jobs data:', err);
    }
  },

  getMarkerColor(job) {
    const status = job.evaluation?.restoration_status || job.restoration_status;
    const controller = job.evaluation?.controller_status || job.controller_status;

    if (controller === 'OFFLINE' || controller === 'STALE' || controller === 'UNKNOWN') {
      return '#64748B'; // Grey (Unknown/Offline)
    }
    if (status === 'BLOCKED') {
      return '#DF4C5B'; // Red (Restricted / Blocked)
    }
    if (status === 'AWAITING_REVIEW') {
      return '#EEA62E'; // Amber (Review Pending)
    }
    if (status === 'SIMULATED_COMPLETION' || status === 'ELIGIBLE_FOR_REVIEW') {
      return '#1C9B63'; // Green (Workflow Completed in Simulation)
    }
    return '#2777E6'; // Blue
  },

  createCustomIcon(color) {
    return L.divIcon({
      className: 'custom-map-pin',
      html: `<div style="
        background-color: ${color};
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 3px solid #FFFFFF;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
      "><div style="width: 6px; height: 6px; background: white; border-radius: 50%;"></div></div>`,
      iconSize: [22, 22],
      iconAnchor: [11, 11]
    });
  },

  renderMarkers() {
    if (!this.map || !this.markersLayer) return;
    this.markersLayer.clearLayers();

    const searchTerm = (document.getElementById('mapSearchInput')?.value || '').toLowerCase();

    this.jobsData.forEach(job => {
      // Filter check
      const status = job.evaluation?.restoration_status || job.restoration_status;
      if (this.currentFilter !== 'ALL') {
        if (this.currentFilter === 'RESTRICTED' && status !== 'BLOCKED') return;
        if (this.currentFilter === 'PENDING' && status !== 'AWAITING_REVIEW') return;
        if (this.currentFilter === 'COMPLETED' && status !== 'SIMULATED_COMPLETION' && status !== 'ELIGIBLE_FOR_REVIEW') return;
      }

      // Search check
      const matchSearch = job.job_code.toLowerCase().includes(searchTerm) ||
                          job.area_name.toLowerCase().includes(searchTerm) ||
                          job.title.toLowerCase().includes(searchTerm);
      if (!matchSearch) return;

      const lat = parseFloat(job.latitude);
      const lng = parseFloat(job.longitude);
      if (isNaN(lat) || isNaN(lng)) return;

      const markerColor = this.getMarkerColor(job);
      const icon = this.createCustomIcon(markerColor);

      const marker = L.marker([lat, lng], { icon }).addTo(this.markersLayer);

      const evalData = job.evaluation || {};
      const requiredCount = evalData.required_count || 1;
      const submittedCount = evalData.submitted_count || 0;
      const blockingReason = evalData.blocking_reason || 'Maintenance active';
      const controllerUid = evalData.controller_uid || 'EG-ESP32-DEMO-01';
      const controllerStatus = evalData.controller_status || 'ONLINE';

      const popupContent = `
        <div style="font-family: system-ui, sans-serif; min-width: 260px; padding: 4px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <span style="font-weight: 700; font-size: 0.95rem; color: #08162B;">${job.job_code}</span>
            <span style="font-size: 0.72rem; padding: 2px 6px; border-radius: 4px; background: #0F2744; color: white;">DEMO FEEDER</span>
          </div>
          <div style="font-weight: 600; color: #1E293B; margin-bottom: 4px;">${job.area_name}</div>
          <div style="font-size: 0.8rem; color: #64748B; margin-bottom: 8px;">${job.substation}</div>
          
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px; margin-bottom: 8px; font-size: 0.8rem;">
            <div><strong>Clearance Status:</strong> ${submittedCount} / ${requiredCount} Submitted</div>
            <div><strong>Supervisor:</strong> ${job.supervisor_name}</div>
            <div><strong>Controller:</strong> ${controllerUid} (${controllerStatus})</div>
          </div>

          <div style="font-size: 0.78rem; padding: 6px; border-radius: 4px; background: ${markerColor}15; border-left: 3px solid ${markerColor}; color: #1E293B; margin-bottom: 8px;">
            <strong>Rule Engine Status:</strong><br>${blockingReason}
          </div>

          <div style="font-size: 0.7rem; color: #94A3B8; font-style: italic; border-top: 1px solid #E2E8F0; padding-top: 4px;">
            * Notice: Software status does not guarantee physical line isolation.
          </div>
        </div>
      `;

      marker.bindPopup(popupContent);
    });
  },

  setFilter(filter) {
    this.currentFilter = filter;
    this.renderMarkers();
  },

  refresh() {
    this.loadData();
  }
};

window.MapManager = MapManager;
