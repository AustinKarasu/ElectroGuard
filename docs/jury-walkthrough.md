# ElectroGuard — Hackathon Jury Demonstration Script (5-Minute Walkthrough)
**Project:** ElectroGuard | **Team:** GodsPlanKangra  
**Presenters:** Aayan Parmar (Leader), Manjinder Uldeen, Aayush Parmar  

---

### Step 1: Landing Page & Problem Context (1 Minute)
1. Open `http://localhost:8000/`.
2. Present the tagline: **"Every Clearance Matters."**
3. Explain the problem:
   > *"In electrical maintenance, fatal electrocutions often happen because someone in the control room re-energizes a feeder while lineworkers are still on the line. Traditional coordination relies on unstructured phone calls or informal messages. ElectroGuard enforces a hard digital rule: **No completed clearance workflow, no authorization to proceed with a restoration request.**"*
4. Highlight the three solution pillars and the prototype disclaimer.
5. Click **"Explore Prototype"** or **"Launch Control Room"**.

---

### Step 2: Administrator Dashboard & Live Map (1 Minute)
1. Show the Overview metrics: Active Jobs, Workers Assigned, Pending Clearances, Blocked Restoration Requests.
2. Click on the **Live Map** tab.
3. Show the Kangra demonstration network with OpenStreetMap tiles:
   - Point to `Kangra Demonstration Feeder A` (RED Marker: Maintenance Active, Restoration Blocked).
   - Point to `Nagrota Demonstration Feeder B` (AMBER Marker: All workers cleared, awaiting supervisor review).
   - Point to `Shahpur Demonstration Feeder C` (RED Marker: Worker reopened clearance, Controller OFFLINE).
4. Click on **Kangra Demonstration Feeder A** marker to open the details modal showing 3 required workers, 1 submitted, 2 pending.

---

### Step 3: Worker Portal & Clearance Submission (1.5 Minutes)
1. Switch role to **Worker** (Click demo selector -> "Rakesh Kumar" or "Sunil Sharma").
2. Show the field-worker mobile view:
   - Display Job ID `EG-JOB-1001`, feeder details, supervisor, and clear safety precautions.
3. Click **"Scan Worker ID"**:
   - Show the ID barcode / QR scanner demonstration.
   - Verify that worker ID `WK-KGR-702` (Sunil Sharma) is verified against server job assignments.
4. Click **"Request Verification Code"**:
   - Show simulated two-factor OTP generation.
   - Enter the 6-digit code.
5. Complete the 4-point digital clearance checklist:
   - Work completed declared.
   - Ground rods and tools removed.
   - Personnel off structures.
   - Acknowledge safety warning.
6. Click **"Sign & Submit Clearance"**.
7. Observe instant progress update: Now 2 of 3 clearances are submitted.

---

### Step 4: Rule Engine Blocking Demonstration & Reopening (1 Minute)
1. Switch back to **Administrator / Control Room**.
2. Navigate to **Restoration Requests**.
3. Attempt to click **"Authorize Restoration Switching"** on `EG-JOB-1001`:
   - The Central Clearance Rule Engine instantly blocks the action!
   - Alert displays: *"Blocked: 1 required worker (Vikas Dogra) has not submitted clearance."*
4. Show `EG-JOB-1003` (Shahpur Feeder):
   - Explain the Reopening scenario: Worker Rakesh Kumar reopened his clearance after noticing a loose jumper clamp during pole descent.
   - Point out that reopening immediately invalidated all approvals and locked the feeder back into a restricted maintenance state.

---

### Step 5: IoT Controller Simulation & Audit Log (30 Seconds)
1. Go to **IoT Controller** tab:
   - Show the ESP32 status dashboard (`EG-ESP32-DEMO-01` Online vs `EG-ESP32-DEMO-03` Offline).
   - Click **"Simulate Hardware Disconnect"** on Controller 1:
     - Notice how its state becomes **UNKNOWN** immediately.
     - The restoration engine now blocks any switching requests for that feeder.
2. Go to **Audit Logs**:
   - Show the immutable, append-only event trail with exact server timestamps, actor roles, actions, and correlation IDs.
3. Conclude with safety ethics:
   > *"ElectroGuard coordinates safety workflows and prevents premature software authorization. It respects physical electrical isolation (LOTO) and empowers every worker with a voice that cannot be overridden by administrative shortcuts."*
