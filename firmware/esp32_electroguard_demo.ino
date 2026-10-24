/*
 * =========================================================================================
 * PROJECT: ELECTROGUARD - Smart Electrical Worker Safety and Maintenance Coordination
 * TEAM: GodsPlanKangra (Aayan Parmar, Manjinder Uldeen, Aayush Parmar)
 * FIRMWARE: ESP32 Low-Voltage Demonstration Controller
 * FILE: esp32_electroguard_demo.ino
 * =========================================================================================
 * 
 * IMPORTANT SAFETY AND HARDWARE BOUNDARY NOTICE:
 * -----------------------------------------------------------------------------------------
 * DO NOT CONNECT THIS ESP32 OR ANY HOBBY RELAY DIRECTLY TO LIVE MAINS ELECTRICITY OR
 * POWER DISTRIBUTION INFRASTRUCTURE.
 * 
 * This firmware is strictly a low-voltage (3.3V / 5V DC) educational demonstration.
 * Output states are indicated via low-voltage LEDs or an optoisolated low-voltage bench circuit.
 * In a real utility grid, actual physical electrical isolation and lockout/tagout procedures
 * (LOTO) governed by authorized electrical engineers and certified utility equipment must be
 * followed. A software flag or micro-controller does not guarantee physical line isolation.
 * =========================================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// Wi-Fi Credentials for demonstration access point
const char* WIFI_SSID     = "ElectroGuard_Demo_AP";
const char* WIFI_PASSWORD = "SafeWorkingGrid2026";

// ElectroGuard Central Server Endpoint (Adjust IP for local testing)
const char* SERVER_URL    = "http://192.168.1.100:8000/api/controllers/1/status";
const char* CONTROLLER_ID = "EG-ESP32-DEMO-01";

// Pin Assignments for Low-Voltage Status Indicators
const int PIN_LED_RESTRICTED = 25; // RED: Active maintenance restriction / Restoration BLOCKED
const int PIN_LED_PENDING    = 26; // AMBER: Worker clearance or supervisor review pending
const int PIN_LED_SIMULATED  = 27; // GREEN: Simulated low-voltage indicator (Only when authorized)
const int PIN_LED_HEARTBEAT  = 2;  // BLUE: Built-in ESP32 LED for network heartbeat pulse

unsigned long lastHeartbeat = 0;
const unsigned long HEARTBEAT_INTERVAL_MS = 5000;

void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println("\n========================================================");
  Serial.println("  ELECTROGUARD ESP32 DEMO CONTROLLER INITIALIZING");
  Serial.println("  Team: GodsPlanKangra | Prototype ID: " + String(CONTROLLER_ID));
  Serial.println("  Safety Mode: ISOLATED LOW-VOLTAGE BENCH CIRCUIT ONLY");
  Serial.println("========================================================");

  pinMode(PIN_LED_RESTRICTED, OUTPUT);
  pinMode(PIN_LED_PENDING, OUTPUT);
  pinMode(PIN_LED_SIMULATED, OUTPUT);
  pinMode(PIN_LED_HEARTBEAT, OUTPUT);

  // Default Fail-Safe State: Maintenance Restriction ACTIVE (Red LED ON)
  digitalWrite(PIN_LED_RESTRICTED, HIGH);
  digitalWrite(PIN_LED_PENDING, LOW);
  digitalWrite(PIN_LED_SIMULATED, LOW);
  digitalWrite(PIN_LED_HEARTBEAT, LOW);

  Serial.println("[STATUS] Default fail-safe state active: RESTRICTED / ISOLATED.");

  // Attempt WiFi Connection
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  Serial.print("[WIFI] Connecting to network...");
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 10) {
    delay(500);
    Serial.print(".");
    digitalWrite(PIN_LED_HEARTBEAT, !digitalRead(PIN_LED_HEARTBEAT));
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n[WIFI] Connected! Assigned IP: " + WiFi.localIP().toString());
  } else {
    Serial.println("\n[WIFI] Offline mode active. Operating autonomous simulation fail-safe.");
  }
}

void loop() {
  unsigned long currentMillis = millis();

  // Pulse Heartbeat LED
  if (currentMillis - lastHeartbeat >= HEARTBEAT_INTERVAL_MS) {
    lastHeartbeat = currentMillis;
    digitalWrite(PIN_LED_HEARTBEAT, HIGH);
    delay(50);
    digitalWrite(PIN_LED_HEARTBEAT, LOW);

    if (WiFi.status() == WL_CONNECTED) {
      sendHeartbeatAndFetchState();
    } else {
      Serial.println("[ESP32 HEARTBEAT] Pulse emitted. Controller state: FAIL-SAFE ISOLATED.");
    }
  }

  // Handle Serial Simulation Commands for bench demonstration without WiFi
  if (Serial.available()) {
    String cmd = Serial.readStringUntil('\n');
    cmd.trim();
    cmd.toUpperCase();

    if (cmd == "STATE_RESTRICTED") {
      digitalWrite(PIN_LED_RESTRICTED, HIGH);
      digitalWrite(PIN_LED_PENDING, LOW);
      digitalWrite(PIN_LED_SIMULATED, LOW);
      Serial.println("[ESP32] Switched to RESTRICTED (Red LED ON)");
    } else if (cmd == "STATE_PENDING") {
      digitalWrite(PIN_LED_RESTRICTED, LOW);
      digitalWrite(PIN_LED_PENDING, HIGH);
      digitalWrite(PIN_LED_SIMULATED, LOW);
      Serial.println("[ESP32] Switched to PENDING (Amber LED ON)");
    } else if (cmd == "STATE_SIMULATED_ON") {
      // Allowed only when explicitly commanded through authorized simulated switching
      digitalWrite(PIN_LED_RESTRICTED, LOW);
      digitalWrite(PIN_LED_PENDING, LOW);
      digitalWrite(PIN_LED_SIMULATED, HIGH);
      Serial.println("[ESP32] Low-voltage indicator active: SIMULATED RESTORATION (Green LED ON)");
    } else if (cmd == "STATUS") {
      Serial.println("Controller: " + String(CONTROLLER_ID) + " | WiFi: " + (WiFi.status() == WL_CONNECTED ? "ONLINE" : "OFFLINE"));
    } else {
      Serial.println("[ESP32] Unknown command. Supported: STATE_RESTRICTED, STATE_PENDING, STATE_SIMULATED_ON, STATUS");
    }
  }

  delay(20);
}

void sendHeartbeatAndFetchState() {
  HTTPClient http;
  http.begin(SERVER_URL);
  http.addHeader("Content-Type", "application/json");

  StaticJsonDocument<200> doc;
  doc["controller_uid"] = CONTROLLER_ID;
  doc["status"] = "ONLINE";
  doc["mode"] = "BENCH_SIMULATION";

  String requestBody;
  serializeJson(doc, requestBody);

  int httpCode = http.POST(requestBody);
  if (httpCode > 0) {
    String payload = http.getString();
    Serial.println("[ESP32 SYNC] Server response: " + payload);
  } else {
    Serial.println("[ESP32 SYNC] HTTP request failed: " + String(httpCode));
    // Fail-safe back to isolated red state
    digitalWrite(PIN_LED_RESTRICTED, HIGH);
    digitalWrite(PIN_LED_SIMULATED, LOW);
  }
  http.end();
}
