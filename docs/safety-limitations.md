# ElectroGuard — Safety Limitations and Prototype Boundary Disclosure
**Project:** ElectroGuard | **Team:** GodsPlanKangra  
**Academic Program:** Computer Engineering (3rd Year)  

---

## 1. Prototype Status & Academic Context

ElectroGuard is an educational, research-oriented prototype designed by third-year Computer Engineering students for a social-impact hackathon. 

- It is **not** a certified utility SCADA or safety system.
- It has **not** received government or electricity regulatory commission certification.
- No real-world accident prevention statistics or utility partnerships are claimed.

---

## 2. Distinction Between Digital Workflow and Physical Electrical Isolation

A software platform can organize human communication, enforce accountability, and prevent accidental administrative restoration requests. **However, software alone cannot physically de-energize or isolate high-voltage power lines.**

In any real-world electricity distribution system:
1. **Mechanical Lockout/Tagout (LOTO):** Physical padlocks, safety tags, and mechanical lockouts on circuit breakers and isolator switches are mandatory.
2. **Grounding & Discharge:** Physical discharge rods and portable earth clamps must be attached to all phases at the work location before any technician climbs or touches a conductor.
3. **Physical Voltage Testing:** Lineworkers must test lines with approved high-voltage detectors before touching them, regardless of what any screen, database, or map indicates.
4. **Independent Authority:** Physical switching must always be coordinated through certified Substation Shift In-Charges using official permit-to-work (PTW) logbooks.

---

## 3. Hardware Boundary and Low-Voltage Safety

The hardware demonstration provided in this project uses an ESP32 micro-controller operating strictly at low voltages (3.3V / 5V DC) with low-power LEDs.

- **DO NOT** connect student-built micro-controllers or hobby relays directly to mains electrical equipment or distribution lines.
- The green LED represents an authorized **software simulation** only, demonstrating that clearance conditions have been verified by the software before a simulated signal is permitted.
- Any future integration into actual utility distribution automation requires utility-grade RTUs (Remote Terminal Units), IEC 60870-5-104 / DNP3 industrial protocols, optical isolation, and utility engineering approval.
