#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <DHT.h>

// ================= WIFI + BACKEND =================
const char* WIFI_SSID = "";
const char* WIFI_PASS = "";

const char* SERVER_BASE = "https://nnjwan.my/sensor_najwan/api";
const char* DEVICE_ID = "INDOOR_01";

// ================= PIN CONFIGURATION =================
#define SDA_PIN 21          // OLED SDA
#define SCL_PIN 22          // OLED SCL

#define DHT_PIN 13          // DHT11 data pin
#define DHT_TYPE DHT11

#define MQ_PIN 34           // MQ-2 digital output DO
#define LDR_PIN 35          // LDR analog output AO

#define SOUND_PIN 32        // Sound sensor D0 for clap OLED page control
#define MUTE_BTN_PIN 27     // Push button for mute / acknowledge alarm
#define BUZZER_PIN 26       // Buzzer positive pin

// Change this to LOW if your sound sensor output becomes LOW when clap is detected
#define SOUND_ACTIVE_STATE HIGH

// ================= OBJECTS =================
Adafruit_SSD1306 display(128, 64, &Wire, -1);
DHT dht(DHT_PIN, DHT_TYPE);

// ================= SYSTEM VARIABLES =================
bool oledOK = false;

volatile int page = 1;
volatile bool alarmLatched = false;
volatile bool alarmAck = false;

// Debounce timer for mute button interrupt
volatile unsigned long lastMutePress = 0;

// Sensor values
float temp = 0;
float hum = 0;
float tempLimit = 32;

int mq = 1;
int lightVal = 0;
int lightLimit = 3000;
int airLimit = 0;

int uploadSec = 10;
int alertEnabled = 1;
int clapEnabled = 1;

String lightStatus = "UNKNOWN";
String mqStatus = "NORMAL";
String status = "NORMAL";
String buzzerStatus = "OFF";
String outputMode = "AUTO";

// ================= TIMER VARIABLES =================
unsigned long tRead = 0;
unsigned long tOLED = 0;
unsigned long tUpload = 0;
unsigned long tSettings = 0;
unsigned long tBuzz = 0;

// ================= CLAP CONTROL VARIABLES =================
volatile bool clapDetected = false;
volatile unsigned long lastClapISR = 0;

unsigned long lastClapTime = 0;
const unsigned long clapCooldown = 500; // faster response

#define SOUND_ACTIVE_STATE HIGH

// ================= SOUND SENSOR INTERRUPT =================
void IRAM_ATTR soundSensorISR() {
  unsigned long now = millis();

  if (now - lastClapISR > 250) {
    clapDetected = true;
    lastClapISR = now;
  }
}

// ================= MUTE BUTTON INTERRUPT =================
void IRAM_ATTR muteButtonISR() {
  unsigned long now = millis();

  if (now - lastMutePress > 300) {
    alarmLatched = false;
    alarmAck = true;
    lastMutePress = now;
  }
}

// ================= CLAP PAGE CONTROL =================
void checkClapPageControl() {
  if (!clapEnabled) return;

  if (clapDetected) {
    clapDetected = false;

    unsigned long now = millis();

    if (now - lastClapTime > clapCooldown) {
      page++;

      if (page > 4) {
        page = 1;
      }

      lastClapTime = now;

      Serial.print("Clap detected. OLED page changed to: ");
      Serial.println(page);

      // Update OLED immediately after page changes
      showOLED();
      tOLED = now;
    }
  }
}

// ================= WIFI CONNECTION =================
void connectWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;

  Serial.println("\nResetting WiFi connection...");
  WiFi.disconnect(true);
  delay(1000);

  WiFi.mode(WIFI_STA);
  Serial.print("Connecting to: ");
  Serial.println(WIFI_SSID);

  if (strlen(WIFI_PASS) == 0) {
    WiFi.begin(WIFI_SSID);
  } else {
    WiFi.begin(WIFI_SSID, WIFI_PASS);
  }

  for (int i = 0; WiFi.status() != WL_CONNECTED && i < 30; i++) {
    delay(500);
    Serial.print(".");
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println(" connected!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println(" failed. Will retry...");
  }
}

// ================= HTTP GET REQUEST =================
bool httpGET(String url, String &res) {
  if (WiFi.status() != WL_CONNECTED) return false;

  HTTPClient http;
  WiFiClientSecure client;

  client.setInsecure();

  http.begin(client, url);
  http.setFollowRedirects(HTTPC_FORCE_FOLLOW_REDIRECTS);
  http.setRedirectLimit(10);

  int code = http.GET();

  if (code > 0) {
    res = http.getString();
  } else {
    res = "";
  }

  http.end();

  return code == 200;
}

// ================= OLED SETUP =================
void setupOLED() {
  Wire.begin(SDA_PIN, SCL_PIN);

  Wire.beginTransmission(0x3C);

  if (Wire.endTransmission() == 0) {
    oledOK = display.begin(SSD1306_SWITCHCAPVCC, 0x3C);
  } else {
    Wire.beginTransmission(0x3D);

    if (Wire.endTransmission() == 0) {
      oledOK = display.begin(SSD1306_SWITCHCAPVCC, 0x3D);
    }
  }

  if (oledOK) {
    display.clearDisplay();
    display.setTextColor(SSD1306_WHITE);
    display.setTextSize(1);
    display.setCursor(0, 0);
    display.println("Smart Indoor");
    display.println("System Ready");
    display.println();
    display.println("Clap to change");
    display.println("OLED page");
    display.display();
    delay(1500);
  } else {
    Serial.println("OLED not detected.");
  }
}

// ================= LIGHT CLASSIFICATION =================
String getLightStatus(int value) {
  if (value > lightLimit) {
    return "DARK";
  }

  if (value > lightLimit / 2) {
    return "DIM";
  }

  return "BRIGHT";
}

// ================= READ SENSOR VALUES =================
void readSensors() {
  temp = dht.readTemperature();
  hum = dht.readHumidity();

  mq = digitalRead(MQ_PIN);
  lightVal = analogRead(LDR_PIN);

  mqStatus = mq == LOW ? "GAS" : "NORMAL";
  lightStatus = getLightStatus(lightVal);

  status = "NORMAL";

  if (!isnan(temp) && temp >= tempLimit) {
    status = "WARNING";
  }

  if (mq <= airLimit) {
    status = "CRITICAL";
  }

  if (status == "NORMAL") {
    alarmAck = false;
  } else {
    if (!alarmAck) {
      alarmLatched = true;
    }
  }
}

// ================= BUZZER CONTROL =================
void buzzOn() {
  tone(BUZZER_PIN, 4000);
  buzzerStatus = "ON";
}

void buzzOff() {
  noTone(BUZZER_PIN);
  buzzerStatus = "OFF";
}

void updateBuzzer() {
  if (!alertEnabled || outputMode == "OFF") {
    buzzOff();
    alarmLatched = false;
    return;
  }

  if (outputMode == "ON") {
    buzzOn();
    return;
  }

  if (alarmLatched) {
    unsigned long now = millis();
    static bool state = false;

    int interval = status == "CRITICAL" ? 150 : 500;

    if (now - tBuzz >= interval) {
      tBuzz = now;
      state = !state;

      if (state) {
        buzzOn();
      } else {
        noTone(BUZZER_PIN);
        buzzerStatus = "ON";
      }
    }
  } else {
    buzzOff();

    if (alarmAck && status != "NORMAL") {
      buzzerStatus = "MUTED";
    }
  }
}

// ================= GET SETTINGS FROM DASHBOARD =================
void fetchSettings() {
  String res;
  String url = String(SERVER_BASE) + "/api_get_command.php?device_id=" + DEVICE_ID;

  if (!httpGET(url, res)) {
    Serial.println("Failed to fetch settings.");
    return;
  }

  StaticJsonDocument<512> doc;

  if (deserializeJson(doc, res)) {
    Serial.println("Settings JSON parse failed.");
    return;
  }

  tempLimit = doc["temp_threshold"] | 32.0;
  airLimit = doc["air_threshold"] | 0;
  lightLimit = doc["light_threshold"] | 3000;
  uploadSec = doc["upload_interval"] | 10;
  alertEnabled = doc["alert_enabled"] | 1;

  // Optional setting. If your backend does not have clap_enabled, it will stay enabled.
  clapEnabled = doc["clap_enabled"] | 1;

  outputMode = doc["output_mode"].as<String>();

  if (outputMode == "") {
    outputMode = "AUTO";
  }

  outputMode.toUpperCase();

  if (uploadSec < 5) {
    uploadSec = 5;
  }

  Serial.println("Settings updated from dashboard.");
}

// ================= UPLOAD SENSOR DATA TO BACKEND =================
void uploadData() {
  String res;
  String url = String(SERVER_BASE) + "/api_add_data.php";

  url += "?device_id=" + String(DEVICE_ID);
  url += "&temperature=" + String(isnan(temp) ? 0 : temp, 2);
  url += "&humidity=" + String(isnan(hum) ? 0 : hum, 2);
  url += "&air_quality=" + String(mq);
  url += "&light_level=" + String(lightVal);
  url += "&system_status=" + status;
  url += "&output_status=" + buzzerStatus;

  if (httpGET(url, res)) {
    Serial.println("Upload OK");
  } else {
    Serial.println("Upload failed");
  }
}

// ================= OLED DISPLAY PAGES =================
void showOLED() {
  if (!oledOK) return;

  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 0);
  display.setTextSize(1);

  if (page == 1) {
    display.println("[ MAIN DASHBOARD ]");
    display.drawLine(0, 9, 128, 9, SSD1306_WHITE);
    display.setCursor(0, 13);

    display.print("Temp : ");
    display.print(temp, 1);
    display.println(" C");

    display.print("Humid: ");
    display.print(hum, 0);
    display.println(" %");

    display.print("Gas  : ");
    display.println(mq == LOW ? "DETECTED" : "CLEAR");

    display.print("Light: ");
    display.println(lightStatus);

    display.drawLine(0, 48, 128, 48, SSD1306_WHITE);
    display.setCursor(0, 52);
    display.print("SYS STATE: ");
    display.println(status);
  }

  else if (page == 2) {
    display.println("[ CLIMATE CENTER ]");
    display.drawLine(0, 9, 128, 9, SSD1306_WHITE);
    display.setCursor(0, 15);

    display.setTextSize(2);

    display.print(temp, 1);
    display.println(" C");

    display.print(hum, 0);
    display.println(" %");

    display.setTextSize(1);
    display.drawLine(0, 48, 128, 48, SSD1306_WHITE);
    display.setCursor(0, 52);

    display.print("Limit: ");
    display.print(tempLimit, 1);
    display.println(" C");
  }

  else if (page == 3) {
    display.println("[ SAFETY & ALARM ]");
    display.drawLine(0, 9, 128, 9, SSD1306_WHITE);
    display.setCursor(0, 15);

    display.print("Air Quality: ");
    display.println(mq == LOW ? "DANGER" : "SAFE");

    display.print("Raw MQ Val : ");
    display.println(mq);

    display.println();

    display.print("Alarm Lock : ");
    display.println(alarmLatched ? "ENGAGED" : "CLEAR");

    display.print("User Mute  : ");
    display.println(alarmAck ? "ACTIVE" : "OFF");
  }

  else if (page == 4) {
    display.println("[ NETWORK & SPECS ]");
    display.drawLine(0, 9, 128, 9, SSD1306_WHITE);
    display.setCursor(0, 15);

    display.print("WLAN: ");
    display.println(WiFi.status() == WL_CONNECTED ? "CONNECTED" : "OFFLINE");

    display.print("Sync: ");
    display.print(uploadSec);
    display.println(" Seconds");

    display.print("Clap: ");
    display.println(clapEnabled ? "ENABLED" : "DISABLED");

    display.print("Light Raw: ");
    display.println(lightVal);

    display.print("Light: ");
    display.println(lightStatus);
  }

  display.display();
}

// ================= SETUP =================
void setup() {
  Serial.begin(115200);

  pinMode(MQ_PIN, INPUT);
  pinMode(LDR_PIN, INPUT);
  pinMode(SOUND_PIN, INPUT);
  attachInterrupt(digitalPinToInterrupt(SOUND_PIN), soundSensorISR, RISING);
  pinMode(BUZZER_PIN, OUTPUT);

  pinMode(MUTE_BTN_PIN, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(MUTE_BTN_PIN), muteButtonISR, FALLING);

  dht.begin();
  setupOLED();

  connectWiFi();
  fetchSettings();

  readSensors();
  showOLED();
}

// ================= MAIN LOOP =================
void loop() {
  unsigned long now = millis();

  checkClapPageControl();

  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  if (now - tRead >= 1000) {
    tRead = now;
    readSensors();
  }

  if (now - tSettings >= 1000) {
    tSettings = now;
    fetchSettings();
  }

  updateBuzzer();

  if (now - tOLED >= 300) {
    tOLED = now;
    showOLED();
  }

  if (now - tUpload >= (unsigned long)uploadSec * 1000UL) {
    tUpload = now;
    uploadData();
  }
}