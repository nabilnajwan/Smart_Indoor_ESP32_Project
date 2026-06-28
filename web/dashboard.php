<?php
session_start();

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["device_id"])) {
    header("Location: login.php");
    exit;
}

$loggedUser = $_SESSION["full_name"];
$userDeviceId = $_SESSION["device_id"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Indoor Command Center</title>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
  .chart-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
  margin-top: 18px;
}

.chart-card {
  background: rgba(30, 41, 59, 0.72);
  border: 1px solid rgba(148, 163, 184, 0.15);
  border-radius: 18px;
  padding: 16px;
}

.chart-card h3 {
  margin: 0;
  font-size: 1rem;
  color: #e5f0ff;
}

.chart-card p {
  margin: 6px 0 14px;
  color: #94a3b8;
  font-size: 0.85rem;
  line-height: 1.5;
}

.chart-card canvas {
  width: 100% !important;
  height: 230px !important;
}

@media (max-width: 1100px) {
  .chart-grid {
    grid-template-columns: 1fr;
  }
}
    * {
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: #07111f;
      color: #e5f0ff;
    }

    .layout {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 250px 1fr;
    }

    .sidebar {
      position: sticky;
      top: 0;
      height: 100vh;
      padding: 24px 18px;
      background: linear-gradient(180deg, #0b1b2f, #07111f);
      border-right: 1px solid rgba(148, 163, 184, 0.25);
    }

    .brand {
      margin-bottom: 34px;
    }

    .brand h2 {
      margin: 0;
      font-size: 1.15rem;
      letter-spacing: 0.5px;
    }

    .brand p {
      margin: 6px 0 0;
      font-size: 0.8rem;
      color: #94a3b8;
    }

    .nav {
      display: grid;
      gap: 12px;
    }

    .nav button {
      width: 100%;
      padding: 13px 14px;
      border: 0;
      border-radius: 14px;
      text-align: left;
      cursor: pointer;
      color: #cbd5e1;
      background: transparent;
      font-weight: 650;
      transition: 0.2s ease;
    }

    .nav button:hover,
    .nav button.active {
      color: white;
      background: linear-gradient(135deg, rgba(14, 165, 233, 0.28), rgba(59, 130, 246, 0.18));
      box-shadow: inset 3px 0 0 #38bdf8;
    }

    .main {
      padding: 28px;
      background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.25), transparent 28%),
        radial-gradient(circle at top right, rgba(34, 197, 94, 0.14), transparent 26%),
        #07111f;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 18px;
      margin-bottom: 24px;
    }

    .topbar h1 {
      margin: 0;
      font-size: 2rem;
    }

    .topbar p {
      margin: 6px 0 0;
      color: #94a3b8;
    }

    .time-card {
      min-width: 190px;
      padding: 16px 18px;
      border-radius: 18px;
      background: rgba(15, 23, 42, 0.82);
      border: 1px solid rgba(148, 163, 184, 0.25);
      text-align: right;
    }

    .time-card strong {
      display: block;
      font-size: 1.3rem;
    }

    .time-card span {
      color: #94a3b8;
      font-size: 0.85rem;
    }

    .section {
      scroll-margin-top: 24px;
      margin-bottom: 24px;
    }

    .hero {
      display: grid;
      grid-template-columns: 1.35fr 0.65fr;
      gap: 22px;
    }

    .panel {
      background: rgba(15, 23, 42, 0.82);
      border: 1px solid rgba(148, 163, 184, 0.25);
      border-radius: 24px;
      padding: 22px;
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28);
    }

    .status-row {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: flex-start;
      flex-wrap: wrap;
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 13px;
      border-radius: 999px;
      font-size: 0.85rem;
      font-weight: 800;
      color: #bbf7d0;
      border: 1px solid rgba(34, 197, 94, 0.45);
      background: rgba(34, 197, 94, 0.16);
    }

    .status-warning {
      color: #fde68a;
      border-color: rgba(245, 158, 11, 0.5);
      background: rgba(245, 158, 11, 0.18);
    }

    .status-critical {
      color: #fecaca;
      border-color: rgba(239, 68, 68, 0.55);
      background: rgba(239, 68, 68, 0.18);
    }

    .hardware-online,
    .safe-text {
      color: #22c55e !important;
    }

    .hardware-offline,
    .danger-text {
      color: #ef4444 !important;
    }

    .warning-text {
      color: #facc15 !important;
    }

    .hero-title {
      margin-top: 24px;
      font-size: 1.8rem;
      font-weight: 800;
    }

    .hero-text {
      max-width: 720px;
      color: #cbd5e1;
      line-height: 1.7;
    }

    .quick-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
      margin-top: 22px;
    }

    .quick-card {
      padding: 16px;
      border-radius: 18px;
      background: rgba(30, 41, 59, 0.72);
      border: 1px solid rgba(148, 163, 184, 0.15);
    }

    .quick-card span {
      display: block;
      color: #94a3b8;
      font-size: 0.82rem;
      margin-bottom: 8px;
    }

    .quick-card strong {
      font-size: 1.1rem;
    }

    .thermo {
      height: 100%;
      min-height: 260px;
      display: grid;
      place-items: center;
      text-align: center;
    }

    .circle {
      width: 210px;
      height: 210px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background:
        conic-gradient(#38bdf8 0deg 245deg, rgba(148, 163, 184, 0.22) 245deg 360deg);
      position: relative;
    }

    .circle::before {
      content: "";
      position: absolute;
      width: 165px;
      height: 165px;
      border-radius: 50%;
      background: #07111f;
      border: 1px solid rgba(148, 163, 184, 0.18);
    }

    .circle-content {
      position: relative;
      z-index: 1;
    }

    .circle-content strong {
      display: block;
      font-size: 2.8rem;
    }

    .circle-content span {
      color: #94a3b8;
    }

    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
    }

    .kpi {
      min-height: 170px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .kpi .icon {
      width: 46px;
      height: 46px;
      display: grid;
      place-items: center;
      border-radius: 16px;
      background: rgba(56, 189, 248, 0.14);
      font-size: 1.4rem;
    }

    .kpi h3 {
      color: #94a3b8;
      margin: 15px 0 8px;
      font-size: 0.95rem;
    }

    .kpi .value {
      font-size: 2rem;
      font-weight: 850;
    }

    .kpi .note {
      color: #94a3b8;
      margin-top: 8px;
      font-size: 0.85rem;
    }

    .two-col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 22px;
    }

    .section-title {
      margin: 0;
      font-size: 1.25rem;
    }

    .section-sub {
      color: #94a3b8;
      margin: 8px 0 0;
      line-height: 1.6;
    }

    .control-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-top: 18px;
    }

    button.action {
      border: 0;
      border-radius: 14px;
      padding: 13px;
      cursor: pointer;
      color: white;
      font-weight: 800;
      transition: 0.2s ease;
    }

    button.action:hover {
      transform: translateY(-2px);
      opacity: 0.9;
    }

    .btn-auto {
      background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    .btn-on {
      background: linear-gradient(135deg, #ef4444, #b91c1c);
    }

    .btn-off {
      background: linear-gradient(135deg, #64748b, #334155);
    }

    .settings-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 14px;
      margin-top: 18px;
    }

    label {
      font-size: 0.84rem;
      color: #cbd5e1;
      font-weight: 700;
    }

    input,
    select {
      width: 100%;
      margin-top: 7px;
      padding: 12px;
      border-radius: 13px;
      color: white;
      background: rgba(2, 6, 23, 0.55);
      border: 1px solid rgba(148, 163, 184, 0.28);
      outline: none;
    }

    .save-btn {
      width: 100%;
      margin-top: 16px;
      background: linear-gradient(135deg, #0ea5e9, #2563eb);
    }

    .analysis-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      margin-top: 18px;
    }

    .analysis-box {
      padding: 16px;
      border-radius: 18px;
      background: rgba(30, 41, 59, 0.72);
      border: 1px solid rgba(148, 163, 184, 0.15);
    }

    .analysis-box span {
      color: #94a3b8;
      display: block;
      font-size: 0.82rem;
      margin-bottom: 8px;
    }

    .analysis-box strong {
      font-size: 1.35rem;
    }

    .recommendation {
      margin-top: 18px;
      padding: 16px;
      border-radius: 18px;
      line-height: 1.65;
      color: #dbeafe;
      background: rgba(14, 165, 233, 0.12);
      border: 1px solid rgba(14, 165, 233, 0.25);
    }

    .recommendation.critical {
      color: #fecaca;
      background: rgba(239, 68, 68, 0.16);
      border-color: rgba(239, 68, 68, 0.35);
    }

    .recommendation.warning {
      color: #fde68a;
      background: rgba(245, 158, 11, 0.14);
      border-color: rgba(245, 158, 11, 0.35);
    }

    .recommendation.safe {
      color: #bbf7d0;
      background: rgba(34, 197, 94, 0.13);
      border-color: rgba(34, 197, 94, 0.32);
    }

    .action-list {
      margin: 10px 0 0;
      padding-left: 18px;
    }

    .action-list li {
      margin-bottom: 6px;
    }

    .table-wrap {
      margin-top: 18px;
      overflow-x: auto;
    }

    table {
      width: 100%;
      min-width: 760px;
      border-collapse: collapse;
    }

    th,
    td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid rgba(148, 163, 184, 0.18);
      font-size: 0.9rem;
    }

    th {
      color: #bfdbfe;
      background: rgba(30, 41, 59, 0.82);
    }

    .error {
      display: none;
      margin-bottom: 18px;
      padding: 14px;
      border-radius: 16px;
      color: #fecaca;
      font-weight: 800;
      background: rgba(239, 68, 68, 0.16);
      border: 1px solid rgba(239, 68, 68, 0.34);
    }

    canvas {
      margin-top: 18px;
    }

    .footer {
      text-align: center;
      color: #94a3b8;
      padding: 20px 0 5px;
    }

    @media (max-width: 1100px) {
      .layout {
        grid-template-columns: 1fr;
      }

      .sidebar {
        position: static;
        height: auto;
      }

      .nav {
        grid-template-columns: repeat(3, 1fr);
      }

      .hero,
      .two-col {
        grid-template-columns: 1fr;
      }

      .kpi-grid,
      .analysis-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 650px) {
      .main {
        padding: 16px;
      }

      .topbar {
        flex-direction: column;
        align-items: stretch;
      }

      .time-card {
        text-align: left;
      }

      .nav,
      .quick-grid,
      .kpi-grid,
      .analysis-grid,
      .settings-grid,
      .control-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
<div class="layout">

  <aside class="sidebar">
  <div class="brand">
    <h2>Smart Indoor</h2>
    <p>Command Center</p>
  </div>

  <div class="nav">
    <button class="active" onclick="goToSection('overview')">🏠 Main Dashboard</button>
    <button onclick="goToSection('sensors')">🌡️ Live Sensors</button>
    <button onclick="goToSection('control')">🔔 Buzzer Control</button>
    <button onclick="goToSection('settings')">⚙️ Settings</button>
    <button onclick="goToSection('analysis')">📊 Analysis</button>
    <button onclick="goToSection('history')">🗂️ Records</button>
  </div>

  <div style="margin-top: 30px;">
    <a href="logout.php" style="color:#fca5a5;text-decoration:none;font-weight:bold;">
      🚪 Logout
    </a>
  </div>
</aside>

  <main class="main">

    <div class="topbar">
      <div>
        <h1>Indoor Environment Command Center</h1>
        <p>Live ESP32 monitoring dashboard for temperature, humidity, gas, light, alarm control and analysis.</p>
      </div>

      <div class="time-card">
        <strong id="clock">--:--</strong>
        <span id="date">Loading date...</span>
      </div>
    </div>

    <div class="error" id="errorBox">Unable to connect to API.</div>

    <section class="section hero" id="overview">
      <div class="panel">
        <div class="status-row">
          <div id="statusPill" class="status-pill">● WAITING FOR DATA</div>
          <div style="color:#94a3b8;font-size:0.9rem;">Device: <strong id="deviceLabel">INDOOR_001</strong></div>
        </div>

        <div class="hero-title">Live Overview</div>

        <p class="hero-text">
          This dashboard works as the main control center for the Smart Indoor Environment Monitoring System.
          It displays real-time sensor readings from ESP32, stores historical records in the database, and provides
          recommended actions based on gas, temperature, humidity, light and buzzer condition.
        </p>

        <div class="quick-grid">
          <div class="quick-card">
            <span>Hardware Connection</span>
            <strong id="connection">CHECKING</strong>
          </div>

          <div class="quick-card">
            <span>Gas Status</span>
            <strong id="gasQuick">--</strong>
          </div>

          <div class="quick-card">
            <span>Last Sensor Upload</span>
            <strong id="lastSmall">--</strong>
          </div>
        </div>
      </div>

      <div class="panel thermo">
        <div class="circle">
          <div class="circle-content">
            <strong id="tempMain">--°</strong>
            <span>Temperature</span>
          </div>
        </div>
      </div>
    </section>

    <section class="section kpi-grid" id="sensors">
      <div class="panel kpi">
        <div>
          <div class="icon">🌡️</div>
          <h3>Temperature</h3>
          <div class="value" id="temp">-- &deg;C</div>
        </div>
        <div class="note" id="tempNote">DHT11 temperature reading</div>
      </div>

      <div class="panel kpi">
        <div>
          <div class="icon">💧</div>
          <h3>Humidity</h3>
          <div class="value" id="hum">-- %</div>
        </div>
        <div class="note" id="humidityNote">DHT11 humidity reading</div>
      </div>

      <div class="panel kpi">
        <div>
          <div class="icon">💨</div>
          <h3>Air Quality</h3>
          <div class="value" id="gas">--</div>
        </div>
        <div class="note" id="gasNote">MQ-2 gas condition</div>
      </div>

      <div class="panel kpi">
        <div>
          <div class="icon">💡</div>
          <h3>Light Level</h3>
          <div class="value" id="light">--</div>
        </div>
        <div class="note" id="lightStatus">LDR sensor reading</div>
      </div>
    </section>

    <section class="section two-col">
      <div class="panel" id="control">
        <h2 class="section-title">Buzzer Control</h2>
        <p class="section-sub">
          AUTO mode allows ESP32 to activate the buzzer based on gas or warning conditions.
          OFF will stop the buzzer from the dashboard.
        </p>

        <div class="control-grid">
          <button class="action btn-auto" onclick="setOutputMode('AUTO')">AUTO</button>
          <button class="action btn-on" onclick="setOutputMode('ON')">FORCE ON</button>
          <button class="action btn-off" onclick="setOutputMode('OFF')">FORCE OFF</button>
        </div>

        <div class="quick-grid">
          <div class="quick-card">
            <span>Buzzer Status</span>
            <strong id="buzz">--</strong>
          </div>

          <div class="quick-card">
            <span>Output Mode</span>
            <strong id="outputModeText">--</strong>
          </div>

          <div class="quick-card">
            <span>Alert Status</span>
            <strong id="alertText">--</strong>
          </div>
        </div>
      </div>

      <div class="panel" id="settings">
        <h2 class="section-title">Device Settings</h2>
        <p class="section-sub">These settings are saved in the database and fetched by the ESP32.</p>

        <div class="settings-grid">
          <div>
            <label>Temperature Threshold (&deg;C)</label>
            <input type="number" id="temp_threshold" value="32" step="0.1">
          </div>

          <div>
            <label>Air Quality Threshold</label>
            <input type="number" id="air_threshold" value="0">
          </div>

          <div>
            <label>Light Threshold</label>
            <input type="number" id="light_threshold" value="3000">
          </div>

          <div>
            <label>Upload Interval (Seconds)</label>
            <input type="number" id="upload_interval" value="10">
          </div>

          <div>
            <label>Alert Enabled</label>
            <select id="alert_enabled">
              <option value="1">Enabled</option>
              <option value="0">Disabled</option>
            </select>
          </div>

          <div>
            <label>Clap OLED Control</label>
            <select id="clap_enabled">
              <option value="1">Enabled</option>
              <option value="0">Disabled</option>
            </select>
          </div>
        </div>

        <button class="action save-btn" onclick="saveSettings()">SAVE SETTINGS</button>
      </div>
    </section>

    <section class="section panel" id="analysis">
      <h2 class="section-title">Real-Time Insight and Action Recommendation</h2>
      <p class="section-sub">Generated from the latest hardware reading and recent database records.</p>

      <div class="analysis-grid">
        <div class="analysis-box">
          <span>Average Temperature</span>
          <strong id="avgTemp">-- &deg;C</strong>
        </div>

        <div class="analysis-box">
          <span>Gas Danger Events</span>
          <strong id="gasDangerCount">--</strong>
        </div>

        <div class="analysis-box">
          <span>Dim or Dark Events</span>
          <strong id="dimDarkCount">--</strong>
        </div>

        <div class="analysis-box">
          <span>Warning / Critical Count</span>
          <strong id="warningCount">--</strong>
        </div>
      </div>

      <div class="recommendation" id="recommendation">
        Waiting for sensor data to generate recommendation.
      </div>
    </section>

<section class="section panel" id="chart">
  <h2 class="section-title">Environment Trend</h2>
  <p class="section-sub">
    Separate real-time trend graphs for temperature, humidity and light level from recent database records.
  </p>

  <div class="chart-grid">
    <div class="chart-card">
      <h3>Temperature Trend</h3>
      <p>Shows recent room temperature changes from the DHT11 sensor.</p>
      <canvas id="tempChart"></canvas>
    </div>

    <div class="chart-card">
      <h3>Humidity Trend</h3>
      <p>Shows recent humidity changes from the DHT11 sensor.</p>
      <canvas id="humidityChart"></canvas>
    </div>

    <div class="chart-card">
      <h3>Light Level Trend</h3>
      <p>Shows recent brightness changes from the LDR sensor.</p>
      <canvas id="lightChart"></canvas>
    </div>
  </div>
</section>

    <section class="section panel" id="history">
      <h2 class="section-title">Historical Data Records</h2>
      <p class="section-sub">Latest sensor data stored in the database.</p>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Time</th>
              <th>Temp</th>
              <th>Humidity</th>
              <th>Air</th>
              <th>Light</th>
              <th>Status</th>
              <th>Buzzer</th>
            </tr>
          </thead>

          <tbody id="historyTable">
            <tr>
              <td colspan="7">No data loaded yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <div class="footer" id="lastUpdate">Last Updated: --</div>

  </main>
</div>

<script>
  const DEVICE_ID = "<?php echo htmlspecialchars($userDeviceId, ENT_QUOTES); ?>";

  const latestApi = `api/api_get_data.php?device_id=${DEVICE_ID}`;
  const historyApi = `api/api_get_history.php?device_id=${DEVICE_ID}&limit=20`;
  const commandApi = `api/api_get_command.php?device_id=${DEVICE_ID}`;
  const updateSettingsApi = `api/api_update_settings.php`;
  
  let settingsDirty = false;
  let userEditingSettings = false;
  let localHistory = [];
  let latestReading = null;
  let latestSettings = {
    temp_threshold: 32,
    light_threshold: 3000,
    upload_interval: 10,
    alert_enabled: 1,
    output_mode: "AUTO"
  };
  let lastHardwareStatus = "UNKNOWN";

function createLineChart(canvasId, label, yAxisLabel, valueSuffix = "") {
  const context = document.getElementById(canvasId).getContext("2d");

  return new Chart(context, {
    type: "line",
    data: {
      labels: [],
      datasets: [
        {
          label: label,
          data: [],
          tension: 0.35,
          borderWidth: 3,
          pointRadius: 3,
          pointHoverRadius: 5
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      plugins: {
        legend: {
          display: true,
          labels: {
            color: "#e5f0ff"
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return label + ": " + context.parsed.y + valueSuffix;
            }
          }
        }
      },
      scales: {
        x: {
          ticks: {
            color: "#94a3b8"
          },
          grid: {
            color: "rgba(148, 163, 184, 0.12)"
          }
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: yAxisLabel,
            color: "#94a3b8"
          },
          ticks: {
            color: "#94a3b8",
            callback: function(value) {
              return value + valueSuffix;
            }
          },
          grid: {
            color: "rgba(148, 163, 184, 0.12)"
          }
        }
      }
    }
  });
}

const tempChart = createLineChart("tempChart", "Temperature", "Temperature", " °C");
const humidityChart = createLineChart("humidityChart", "Humidity", "Humidity", " %");
const lightChart = createLineChart("lightChart", "Light Level", "Light Level", "");
  
  function setupSettingsEditingProtection() {
  const settingInputs = document.querySelectorAll(
    "#settings input, #settings select"
  );

  settingInputs.forEach(input => {
    input.addEventListener("focus", () => {
      userEditingSettings = true;
    });

    input.addEventListener("input", () => {
      settingsDirty = true;
    });

    input.addEventListener("change", () => {
      settingsDirty = true;
    });

    input.addEventListener("blur", () => {
      setTimeout(() => {
        userEditingSettings = false;
      }, 500);
    });
  });
}

  function goToSection(id) {
    const section = document.getElementById(id);

    if (section) {
      section.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
    }

    document.querySelectorAll(".nav button").forEach(button => {
      button.classList.remove("active");
    });

    const clickedButton = Array.from(document.querySelectorAll(".nav button"))
      .find(button => button.getAttribute("onclick") && button.getAttribute("onclick").includes(id));

    if (clickedButton) {
      clickedButton.classList.add("active");
    }
  }

  window.addEventListener("scroll", () => {
    const ids = ["overview", "sensors", "control", "settings", "analysis", "history"];
    let current = "overview";

    ids.forEach(id => {
      const section = document.getElementById(id);

      if (section && window.scrollY >= section.offsetTop - 180) {
        current = id;
      }
    });

    document.querySelectorAll(".nav button").forEach(button => {
      button.classList.remove("active");

      const onclick = button.getAttribute("onclick") || "";

      if (onclick.includes(current)) {
        button.classList.add("active");
      }
    });
  });

  function updateClock() {
    const now = new Date();

    document.getElementById("clock").innerText = now.toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit"
    });

    document.getElementById("date").innerText = now.toLocaleDateString([], {
      weekday: "long",
      year: "numeric",
      month: "short",
      day: "numeric"
    });
  }

  function showError(message) {
    const errorBox = document.getElementById("errorBox");
    errorBox.style.display = "block";
    errorBox.innerText = message;
  }

  function hideError() {
    document.getElementById("errorBox").style.display = "none";
  }

  function safeNumber(value, fallback = 0) {
    const number = Number(value);
    return Number.isFinite(number) ? number : fallback;
  }

  function getLightStatus(value) {
    value = Number(value);

    const lightThreshold = safeNumber(latestSettings.light_threshold, 3000);

    if (value > lightThreshold) {
      return "DARK";
    }

    if (value > lightThreshold / 2) {
      return "DIM";
    }

    return "BRIGHT";
  }

  function getHardwareStatus(data) {
    let hardwareStatus = data.hardware_status ? String(data.hardware_status).toUpperCase() : "";
    let secondsAgo = data.seconds_ago !== undefined && data.seconds_ago !== null ? Number(data.seconds_ago) : null;

    if (!hardwareStatus || hardwareStatus === "UNKNOWN") {
      const uploadInterval = safeNumber(data.upload_interval || latestSettings.upload_interval, 10);
      const offlineLimit = Math.max(uploadInterval * 3, 20);

      if (secondsAgo !== null && secondsAgo <= offlineLimit) {
        hardwareStatus = "ONLINE";
      } else {
        hardwareStatus = "OFFLINE";
      }
    }

    if (secondsAgo === null || !Number.isFinite(secondsAgo)) {
      secondsAgo = 0;
    }

    return {
      hardwareStatus,
      secondsAgo
    };
  }

  function updateHardwareConnection(data) {
    const connection = document.getElementById("connection");
    const result = getHardwareStatus(data);

    lastHardwareStatus = result.hardwareStatus;

    connection.innerText = result.hardwareStatus;
    connection.classList.remove("hardware-online", "hardware-offline");

    if (result.hardwareStatus === "ONLINE") {
      connection.classList.add("hardware-online");
      hideError();
    } else {
      connection.classList.add("hardware-offline");
      showError("Hardware is OFFLINE. Last sensor data received " + result.secondsAgo + " seconds ago.");
    }

    document.getElementById("lastSmall").innerText = result.secondsAgo + "s ago";

    return result;
  }

  function updateSystemStatus(systemStatus, hardwareStatus) {
    const pill = document.getElementById("statusPill");
    pill.className = "status-pill";

    if (hardwareStatus === "OFFLINE") {
      pill.classList.add("status-critical");
      pill.innerText = "● HARDWARE OFFLINE";
      return;
    }

    if (systemStatus === "CRITICAL") {
      pill.classList.add("status-critical");
      pill.innerText = "● SYSTEM CRITICAL";
    } else if (systemStatus === "WARNING") {
      pill.classList.add("status-warning");
      pill.innerText = "● SYSTEM WARNING";
    } else {
      pill.innerText = "● SYSTEM NORMAL";
    }
  }

  function updateGasStatus(gasValue) {
    const gasText = Number(gasValue) === 0 ? "DANGER" : "SAFE";

    const gas = document.getElementById("gas");
    const gasQuick = document.getElementById("gasQuick");
    const gasNote = document.getElementById("gasNote");

    gas.innerText = gasText;
    gasQuick.innerText = gasText;

    gas.classList.remove("safe-text", "danger-text");
    gasQuick.classList.remove("safe-text", "danger-text");

    if (gasText === "DANGER") {
      gas.classList.add("danger-text");
      gasQuick.classList.add("danger-text");
      gasNote.innerText = "Gas detected by MQ-2";
    } else {
      gas.classList.add("safe-text");
      gasQuick.classList.add("safe-text");
      gasNote.innerText = "Air quality is currently safe";
    }

    return gasText;
  }

  function updateLatestCards(data) {
    latestReading = data;

    const connectionInfo = updateHardwareConnection(data);

    const temperature = safeNumber(data.temperature);
    const humidity = safeNumber(data.humidity);
    const light = safeNumber(data.light_level);
    const systemStatus = data.system_status || "NORMAL";

    document.getElementById("deviceLabel").innerText = DEVICE_ID;

    document.getElementById("tempMain").innerHTML = temperature.toFixed(1) + "&deg;";
    document.getElementById("temp").innerHTML = temperature.toFixed(1) + " &deg;C";
    document.getElementById("hum").innerText = humidity.toFixed(0) + " %";

    const gasText = updateGasStatus(data.air_quality);

    document.getElementById("light").innerText = light;
    document.getElementById("lightStatus").innerText = getLightStatus(light);

    document.getElementById("buzz").innerText = data.output_status || "--";

    document.getElementById("lastUpdate").innerText = "Last Updated: " + (data.created_at || "--");

    updateSystemStatus(systemStatus, connectionInfo.hardwareStatus);
    updateLiveSensorNotes(data, gasText);
  }

  function updateLiveSensorNotes(data, gasText) {
    const temperature = safeNumber(data.temperature);
    const humidity = safeNumber(data.humidity);
    const light = safeNumber(data.light_level);
    const tempThreshold = safeNumber(latestSettings.temp_threshold, 32);
    const lightState = getLightStatus(light);

    const tempNote = document.getElementById("tempNote");
    const humidityNote = document.getElementById("humidityNote");
    const lightStatus = document.getElementById("lightStatus");

    if (temperature >= tempThreshold) {
      tempNote.innerText = "High temperature. Improve ventilation.";
      tempNote.className = "note warning-text";
    } else {
      tempNote.innerText = "Temperature is within limit.";
      tempNote.className = "note safe-text";
    }

    if (humidity >= 80) {
      humidityNote.innerText = "Humidity is high. Reduce moisture.";
      humidityNote.className = "note warning-text";
    } else if (humidity <= 35) {
      humidityNote.innerText = "Humidity is low. Room may feel dry.";
      humidityNote.className = "note warning-text";
    } else {
      humidityNote.innerText = "Humidity is comfortable.";
      humidityNote.className = "note safe-text";
    }

    if (lightState === "DARK") {
      lightStatus.innerText = "DARK. Switch on room light.";
      lightStatus.className = "note warning-text";
    } else if (lightState === "DIM") {
      lightStatus.innerText = "DIM. Increase lighting if studying.";
      lightStatus.className = "note warning-text";
    } else {
      lightStatus.innerText = "BRIGHT. Lighting is sufficient.";
      lightStatus.className = "note safe-text";
    }
  }

  function addLocalHistory(data) {
    localHistory.unshift(data);

    if (localHistory.length > 20) {
      localHistory.pop();
    }
  }

function updateChart(rows) {
  const orderedRows = [...rows].reverse();

  const labels = orderedRows.map(row => {
    if (!row.created_at) return "";
    return row.created_at.substring(11, 19);
  });

  const temperatureData = orderedRows.map(row => safeNumber(row.temperature));
  const humidityData = orderedRows.map(row => safeNumber(row.humidity));
  const lightData = orderedRows.map(row => safeNumber(row.light_level));

  tempChart.data.labels = labels;
  tempChart.data.datasets[0].data = temperatureData;
  tempChart.update();

  humidityChart.data.labels = labels;
  humidityChart.data.datasets[0].data = humidityData;
  humidityChart.update();

  lightChart.data.labels = labels;
  lightChart.data.datasets[0].data = lightData;
  lightChart.update();
}

  function updateHistoryTable(rows) {
    const table = document.getElementById("historyTable");
    table.innerHTML = "";

    if (!rows || rows.length === 0) {
      table.innerHTML = "<tr><td colspan='7'>No historical data available.</td></tr>";
      return;
    }

    rows.forEach(row => {
      const gasText = Number(row.air_quality) === 0 ? "DANGER" : "SAFE";

      table.innerHTML += `
        <tr>
          <td>${row.created_at}</td>
          <td>${safeNumber(row.temperature).toFixed(1)} &deg;C</td>
          <td>${safeNumber(row.humidity).toFixed(0)} %</td>
          <td>${gasText}</td>
          <td>${row.light_level}</td>
          <td>${row.system_status}</td>
          <td>${row.output_status}</td>
        </tr>
      `;
    });
  }

  function updateAnalysis(rows) {
    if (!rows || rows.length === 0) return;

    const temperatures = rows.map(row => Number(row.temperature)).filter(value => !isNaN(value));
    const lights = rows.map(row => Number(row.light_level)).filter(value => !isNaN(value));

    if (temperatures.length === 0 || lights.length === 0) return;

    const avgTemp = temperatures.reduce((a, b) => a + b, 0) / temperatures.length;

    const warningCount = rows.filter(row =>
      row.system_status === "WARNING" || row.system_status === "CRITICAL"
    ).length;

    const gasDangerCount = rows.filter(row => Number(row.air_quality) === 0).length;

    const dimDarkCount = rows.filter(row => {
      const lightState = getLightStatus(row.light_level);
      return lightState === "DIM" || lightState === "DARK";
    }).length;

    document.getElementById("avgTemp").innerHTML = avgTemp.toFixed(1) + " &deg;C";
    document.getElementById("gasDangerCount").innerText = gasDangerCount;
    document.getElementById("dimDarkCount").innerText = dimDarkCount;
    document.getElementById("warningCount").innerText = warningCount;

    generateRecommendation(rows[0], rows);
  }

  function generateRecommendation(latest, rows) {
    const recommendationBox = document.getElementById("recommendation");

    recommendationBox.className = "recommendation";

    if (!latest) {
      recommendationBox.innerText = "Waiting for sensor data to generate recommendation.";
      return;
    }

    const temperature = safeNumber(latest.temperature);
    const humidity = safeNumber(latest.humidity);
    const gasValue = Number(latest.air_quality);
    const lightValue = safeNumber(latest.light_level);

    const tempThreshold = safeNumber(latestSettings.temp_threshold, 32);
    const outputMode = String(latestSettings.output_mode || "AUTO").toUpperCase();
    const alertEnabled = Number(latestSettings.alert_enabled ?? 1);
    const lightState = getLightStatus(lightValue);

    if (lastHardwareStatus === "OFFLINE") {
      recommendationBox.classList.add("critical");
      recommendationBox.innerHTML = `
        <strong>Action Required: Hardware Offline</strong>
        <ul class="action-list">
          <li>Check ESP32 power supply.</li>
          <li>Check WiFi connection and make sure the ESP32 is connected to the correct network.</li>
          <li>Do not trust the displayed sensor values until new data is received.</li>
        </ul>
      `;
      return;
    }

    if (gasValue === 0) {
      recommendationBox.classList.add("critical");
      recommendationBox.innerHTML = `
        <strong>Critical Gas Alert: Immediate Action Needed</strong>
        <ul class="action-list">
          <li>Open windows or improve ventilation immediately.</li>
          <li>Move away from the area if the gas smell is strong.</li>
          <li>Check possible gas or smoke source.</li>
          ${outputMode === "OFF" ? "<li><strong>Warning:</strong> Buzzer is currently forced OFF from dashboard. Change it to AUTO for safety.</li>" : ""}
          ${alertEnabled === 0 ? "<li><strong>Warning:</strong> Alert is disabled. Enable alert from the settings panel.</li>" : ""}
        </ul>
      `;
      return;
    }

    if (temperature >= tempThreshold) {
      recommendationBox.classList.add("warning");
      recommendationBox.innerHTML = `
        <strong>Temperature Warning: Room is Hot</strong>
        <ul class="action-list">
          <li>Improve airflow by opening a window or turning on a fan.</li>
          <li>Lower the temperature threshold only if the current limit is not suitable.</li>
          <li>Continue monitoring the dashboard until temperature returns below ${tempThreshold.toFixed(1)} &deg;C.</li>
        </ul>
      `;
      return;
    }

    if (humidity >= 80) {
      recommendationBox.classList.add("warning");
      recommendationBox.innerHTML = `
        <strong>Humidity Warning: Room is Too Humid</strong>
        <ul class="action-list">
          <li>Improve ventilation to reduce moisture.</li>
          <li>Check if the room has wet clothes, water leakage, or poor air circulation.</li>
          <li>Use a fan or dehumidifier if available.</li>
        </ul>
      `;
      return;
    }

    if (humidity <= 35) {
      recommendationBox.classList.add("warning");
      recommendationBox.innerHTML = `
        <strong>Humidity Notice: Room Air is Dry</strong>
        <ul class="action-list">
          <li>Consider adding moisture to the room</li>
          <li>Avoid placing the sensor too close to heat sources.</li>
          <li>Continue monitoring humidity trend from the chart.</li>
        </ul>
      `;
      return;
    }

    if (lightState === "DARK") {
      recommendationBox.classList.add("warning");
      recommendationBox.innerHTML = `
        <strong>Lighting Warning: Room is Dark</strong>
        <ul class="action-list">
          <li>Switch on the room light for better visibility.</li>
          <li>If this area is for studying or working, move closer to a brighter light source.</li>
        </ul>
      `;
      return;
    }

    if (lightState === "DIM") {
      recommendationBox.classList.add("warning");
      recommendationBox.innerHTML = `
        <strong>Lighting Notice: Room is Dim</strong>
        <ul class="action-list">
          <li>Increase lighting if users are reading, studying, or doing detailed work.</li>
          <li>The environment is still usable, but better lighting is recommended.</li>
        </ul>
      `;
      return;
    }

    recommendationBox.classList.add("safe");
    recommendationBox.innerHTML = `
      <strong>Environment Stable</strong>
      <ul class="action-list">
        <li>Gas condition is SAFE.</li>
        <li>Temperature and humidity are within normal range.</li>
        <li>Lighting is sufficient.</li>
        <li>No immediate action is required.</li>
      </ul>
    `;
  }

  async function fetchLiveData() {
    try {
      const response = await fetch(latestApi + "&t=" + Date.now());
      const data = await response.json();

      if (data.error) {
        showError("No latest sensor data found. Hardware may be offline or database is empty.");
        document.getElementById("connection").innerText = "OFFLINE";
        document.getElementById("connection").classList.add("hardware-offline");
        updateSystemStatus("CRITICAL", "OFFLINE");
        return;
      }

      if (data.temp_threshold !== undefined) latestSettings.temp_threshold = data.temp_threshold;
      if (data.light_threshold !== undefined) latestSettings.light_threshold = data.light_threshold;
      if (data.upload_interval !== undefined) latestSettings.upload_interval = data.upload_interval;
      if (data.alert_enabled !== undefined) latestSettings.alert_enabled = data.alert_enabled;
      if (data.output_mode !== undefined) latestSettings.output_mode = data.output_mode;

      updateLatestCards(data);
      addLocalHistory(data);

    } catch (error) {
      showError("API is OFFLINE or cannot be reached.");
      document.getElementById("connection").innerText = "API OFFLINE";
      document.getElementById("connection").classList.remove("hardware-online");
      document.getElementById("connection").classList.add("hardware-offline");
      updateSystemStatus("CRITICAL", "OFFLINE");
      console.error(error);
    }
  }

  async function fetchHistoryData() {
    try {
      const response = await fetch(historyApi + "&t=" + Date.now());
      const rows = await response.json();

      if (Array.isArray(rows) && rows.length > 0) {
        updateHistoryTable(rows);
        updateChart(rows);
        updateAnalysis(rows);
      } else {
        updateHistoryTable(localHistory);
        updateChart(localHistory);
        updateAnalysis(localHistory);
      }

    } catch (error) {
      updateHistoryTable(localHistory);
      updateChart(localHistory);
      updateAnalysis(localHistory);
    }
  }

 async function fetchSettings(forceUpdate = false) {
  try {
    // Do not overwrite the form while user is editing
    if (!forceUpdate && (settingsDirty || userEditingSettings)) {
      return;
    }

    const response = await fetch(commandApi + "&t=" + Date.now());
    const data = await response.json();

    if (data.error) return;

    latestSettings = {
      ...latestSettings,
      ...data
    };

    document.getElementById("temp_threshold").value = data.temp_threshold ?? 32;
    document.getElementById("air_threshold").value = data.air_threshold ?? 0;
    document.getElementById("light_threshold").value = data.light_threshold ?? 3000;
    document.getElementById("upload_interval").value = data.upload_interval ?? 10;
    document.getElementById("alert_enabled").value = data.alert_enabled ?? 1;
    document.getElementById("clap_enabled").value = data.clap_enabled ?? 1;

    document.getElementById("outputModeText").innerText = data.output_mode ?? "AUTO";
    document.getElementById("alertText").innerText =
      Number(data.alert_enabled) === 1 ? "ENABLED" : "DISABLED";

    if (latestReading) {
      generateRecommendation(latestReading, localHistory);
    }

  } catch (error) {
    console.log("Settings API not ready.");
  }
}

async function saveSettings() {
  const params = new URLSearchParams();

  params.append("device_id", DEVICE_ID);
  params.append("temp_threshold", document.getElementById("temp_threshold").value);
  params.append("air_threshold", document.getElementById("air_threshold").value);
  params.append("light_threshold", document.getElementById("light_threshold").value);
  params.append("upload_interval", document.getElementById("upload_interval").value);
  params.append("alert_enabled", document.getElementById("alert_enabled").value);
  params.append("clap_enabled", document.getElementById("clap_enabled").value);

  try {
    const response = await fetch(updateSettingsApi, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: params.toString()
    });

    const result = await response.json();

    if (result.success) {
      settingsDirty = false;
      userEditingSettings = false;

      alert("Settings saved successfully.");

      // Force reload only after successful save
      fetchSettings(true);
    } else {
      alert("Settings update failed: " + (result.error || "Unknown error"));
    }

  } catch (error) {
    alert("Settings API cannot be reached.");
    console.error(error);
  }
}

  async function setOutputMode(mode) {
    const params = new URLSearchParams();

    params.append("device_id", DEVICE_ID);
    params.append("output_mode", mode);

    try {
      const response = await fetch(updateSettingsApi, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: params.toString()
      });

      const result = await response.json();

      if (result.success) {
        document.getElementById("outputModeText").innerText = mode;
        latestSettings.output_mode = mode;

        if (latestReading) {
          generateRecommendation(latestReading, localHistory);
        }

        fetchSettings();
      } else {
        alert("Failed to change output mode.");
      }

    } catch (error) {
      alert("Control API cannot be reached.");
    }
  }

  async function refreshLiveDashboard() {
    await fetchLiveData();
  }

async function refreshSlowData() {
  await fetchHistoryData();
  await fetchSettings(false);
}

  updateClock();
  fetchSettings(true);
  fetchLiveData();
  fetchHistoryData();
  setupSettingsEditingProtection();

  setInterval(updateClock, 1000);
  setInterval(refreshLiveDashboard, 1000);
  setInterval(refreshSlowData, 3000);
</script>
</body>
</html>