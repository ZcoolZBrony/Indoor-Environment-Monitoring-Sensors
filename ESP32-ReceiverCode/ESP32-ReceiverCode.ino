// ------------------------------------------------------------------------------------------
// ESP Library Configs
// ------------------------------------------------------------------------------------------
#include <esp_now.h>
#include <WiFi.h>

// ------------------------------------------------------------------------------------------
// ESP-NOW SYSTEM CONFIGS
// ------------------------------------------------------------------------------------------
// Structure example to receive data, Must match the sender structure
typedef struct struct_message {
  int board;
  float Temp;
  float Humdity;
  float Pressure;
  float light;
  float Vbat;
} struct_message;

// Create a struct_message called myData
struct_message myData;

// Callback function that will be executed when data is received
void OnDataRecv(const uint8_t * mac, const uint8_t *incomingData, int len) {
  memcpy(&myData, incomingData, sizeof(myData));
  Serial.print(myData.board);
  Serial.print("#");
  Serial.print(myData.Temp);
  Serial.print("#");
  Serial.print(myData.Humdity);
  Serial.print("#");
  Serial.print(myData.Pressure);
  Serial.print("#");
  Serial.print(myData.light);
  Serial.print("#");
  Serial.print(myData.Vbat);
  Serial.println(" ");
}

void setup() {
  // Initialize Serial Monitor
  Serial.begin(115200);
  
  // Set device as a Wi-Fi Station
  WiFi.mode(WIFI_STA);

  // Init ESP-NOW
  if (esp_now_init() != ESP_OK) {
    Serial.println("Error initializing ESP-NOW");
    return;
  }
  
  // Once ESPNow is successfully Init, we will register for recv CB to get recv packer info
  esp_now_register_recv_cb(OnDataRecv);
}

void loop() {

}