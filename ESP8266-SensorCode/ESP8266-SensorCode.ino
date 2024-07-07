// ------------------------------------------------------------------------------------------
// ESP Library Configs
// ------------------------------------------------------------------------------------------
#include <ESP8266WiFi.h>
#include <espnow.h>
#include <Wire.h> // GPIO5 SCL & 4 SDA
#include "DHT.h"

// ------------------------------------------------------------------------------------------
// ESP-NOW SYSTEM CONFIGS
// ------------------------------------------------------------------------------------------
#define WIFI_CHANNEL        1     // Must be 1. (!IMPORTANT)
uint8_t Gateway_Mac[] = {0x00, 0x00, 0x00, 0x00, 0x00, 0x00};

//Array of messages
typedef struct sensor_data_t {    // Sensor data format for sending on ESP-Now to Gateway
  int           board;            // Unit no to identy which sensor is sending
  float         Temp;             // Temperature (C)
  float         Humidity;         // Humidity (%)
  float         Pressure;         // Barometric pressure (hPa)
  float         Light;            // Light sensor data (lux)
  float         Vbat;             // Battery voltage level (V)
} sensor_data_t;

// -----------------------------------------------------------------------------------------
// ESP SENSOR CONFIGS
// -----------------------------------------------------------------------------------------
// Sensor unit ID to identify THIS unit for the receiving gateway. Recommended to use [1 -20]
#define UNIT                1       

#define SLEEP_SECS        1*30    // [sec] Sleep time between wake up and readings. Mine is about 30 second and feel free to change it
#define MAX_WAKETIME_MS   1000      // [ms]  Timeout until forced gotosleep if no sending success 

// -----------------------------------------------------------------------------------------
//  DHT11 sensor Configs
// -----------------------------------------------------------------------------------------
#define DHTPIN 14     // Digital pin connected to the DHT sensor
#define DHTTYPE DHT11   // DHT 11
DHT dht(DHTPIN, DHTTYPE);

// -----------------------------------------------------------------------------------------
// BMP280 sensor Configs
// -----------------------------------------------------------------------------------------
#include <Adafruit_BMP280.h>
Adafruit_BMP280 bmp; // I2C

// -----------------------------------------------------------------------------------------
// BH1750 sensor Configs
// -----------------------------------------------------------------------------------------
#include <BH1750.h>
BH1750 lightMeter;

// -----------------------------------------------------------------------------------------
//  BATTERY LEVEL CALIBRATION (Need Calibration)
// -----------------------------------------------------------------------------------------
#define CALIBRATION         4.15 / 4.2                       // Measured V by multimeter / reported (raw) V 
#define VOLTAGE_DIVIDER     (100+220+100)/100 * CALIBRATION   // D1 Mini Pro voltage divider to A0.

// -----------------------------------------------------------------------------------------
// GLOBALS
// -----------------------------------------------------------------------------------------
sensor_data_t sensorData;
volatile boolean messageSent;     // flag to tell when message is sent out and we can safely goto sleep

// -----------------------------------------------------------------------------------------
void setup()
// -----------------------------------------------------------------------------------------
{
  // Disable WiFi until we shall use it, to save energy ---------------------------
  WiFi.persistent( false );         // Dont save WiFi info to Flash - to save time

  WiFi.mode( WIFI_OFF );            // Wifi OFF - during sensor reading - to save current/power
  WiFi.forceSleepBegin();
  delay( 1 );                       // Necessary for the OFF to work. (!IMPORTANT)
  Wire.begin();                     // Prepare the I2C communication
  Serial.begin(115200);

  //BM280 Reading ---------------------------
  bmp.begin(0x76);
  bmp.setSampling(Adafruit_BMP280::MODE_NORMAL,     /* Operating Mode. */
                  Adafruit_BMP280::SAMPLING_X2,     /* Temp. oversampling */
                  Adafruit_BMP280::SAMPLING_X16,    /* Pressure oversampling */
                  Adafruit_BMP280::FILTER_X16,      /* Filtering. */
                  Adafruit_BMP280::STANDBY_MS_500); /* Standby time. */

  //DHT11 Reading ---------------------------
  dht.begin();

  // Battery Reading ---------------------------
  int raw = analogRead(A0);
  float Vbat = raw * VOLTAGE_DIVIDER / 1023.0;
  //Serial.print("Battery voltage:"); Serial.print(Vbat); Serial.println(" V");   //Un-comment to see calibration is correct.

  //BH1750 Reading ---------------------------
  lightMeter.begin();
  float lux = lightMeter.readLightLevel();

  // compile message to send ---------------------------
   sensorData.board = UNIT;
   sensorData.Pressure = bmp.readPressure()/100.0F;
   sensorData.Humidity = dht.readHumidity();
   sensorData.Temp = bmp.readTemperature();
   sensorData.Light = lux;
   sensorData.Vbat = Vbat;

  // WiFi ON ---------------------------
  WiFi.forceSleepWake();
  delay( 1 );

  // Set up ESP-Now link ---------------------------
  WiFi.mode(WIFI_STA);          // Station mode for esp-now sensor node
  WiFi.disconnect();

  //Serial.printf("My HW mac: %s", WiFi.macAddress().c_str());
  Serial.printf("Sending to MAC: %02x:%02x:%02x:%02x:%02x:%02x", Gateway_Mac[0], Gateway_Mac[1], Gateway_Mac[2], Gateway_Mac[3], Gateway_Mac[4], Gateway_Mac[5]);
  Serial.printf(", on channel: %i\n", WIFI_CHANNEL);

  // Initialize ESP-now ----------------------------
  if (esp_now_init() != 0) {
    Serial.println("*** ESP_Now init failed. Going to sleep");
    delay(100);
    gotoSleep();
  }

  esp_now_set_self_role(ESP_NOW_ROLE_CONTROLLER);
  esp_now_add_peer(Gateway_Mac, ESP_NOW_ROLE_SLAVE, WIFI_CHANNEL, NULL, 0);

  esp_now_register_send_cb([](uint8_t* mac, uint8_t sendStatus) {
    // callback for message sent out
    messageSent = true;         // flag message is sent out - we can now safely go to sleep ...
    Serial.printf("Message sent out, sendStatus = %i\n", sendStatus);
  });

  messageSent = false;

  // Send message -----------------------------------
  Serial.println(", Unit:" + \
                  String(sensorData.board) + ", Temp:" + \
                  String(sensorData.Temp) + "C, Hum: " + \
                  String(sensorData.Humidity) + "C, Pressure: " + \
                  String(sensorData.Pressure) + "%, Lux: " + \
                  String(sensorData.Light) + "lux, Vbat: " + \
                  String(sensorData.Vbat)
                );

  uint8_t sendBuf[sizeof(sensorData)];          // create a send buffer for sending sensor data (safer)
  memcpy(sendBuf, &sensorData, sizeof(sensorData));
  uint16_t result = esp_now_send(NULL, sendBuf, sizeof(sensorData));
  Serial.print("Sending result: "); Serial.println(result);
}

// -----------------------------------------------------------------------------------------
void loop()
// -----------------------------------------------------------------------------------------
{
  // Wait until ESP-Now message is sent, or timeout, then goto sleep
  if (messageSent || (millis() > MAX_WAKETIME_MS)) {
    gotoSleep();
  }
}


// -----------------------------------------------------------------------------------------
void gotoSleep()
// -----------------------------------------------------------------------------------------
{
  int sleepSecs;

  sleepSecs = SLEEP_SECS + ((uint8_t)RANDOM_REG32 / 16);  // add random time to avoid traffic jam collisions
  Serial.printf("Up for %i ms, going to deep sleep for %i secs ...\n", millis(), sleepSecs);

  ESP.deepSleep(sleepSecs * 1000000, RF_NO_CAL);
  delay (10);                                             // good convention with delay after call to deep sleep.

  // Never return here - ESP will be reset after deep sleep
}
