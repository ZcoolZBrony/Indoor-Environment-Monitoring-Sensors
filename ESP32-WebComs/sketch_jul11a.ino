#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>

String board, Temp, Humdity, Pressure, light, battery;

// Wifi Credential
const char* ssid     = "SSID";
const char* password = "PASS";

// REPLACE with your Domain name and URL path or IP address with path
const char* serverName = "https://yourlinkadrress/post-data.php";

// Keep this API Key value to be compatible with the PHP code provided in the project page. 
String apiKeyValue = "tPmAT5Ab3j7F9";

void setup() {
  Serial.begin(115200);
  Serial2.begin(115200);

  WiFi.begin(ssid, password);
  Serial.println("Connecting");
  while(WiFi.status() != WL_CONNECTED) { 
    delay(500);
    Serial.print(".");
  }
}

void loop() {
  while (Serial2.available() > 0) {
    String datafromarduino = Serial2.readStringUntil('\n');
    int data1 = datafromarduino.indexOf('#');
    int data2 = datafromarduino.indexOf('#', data1 + 1);
    int data3 = datafromarduino.indexOf('#', data2 + 1);
    int data4 = datafromarduino.indexOf('#', data3 + 1);
    int data5 = datafromarduino.indexOf('#', data4 + 1);
    Serial.println(datafromarduino);

    if (data1 >= 0 && data4 >= 0) {
      board = datafromarduino.substring(0, data1);
      Temp = datafromarduino.substring(data1 + 1, data2);
      Humdity = datafromarduino.substring(data2 + 1, data3);
      Pressure = datafromarduino.substring(data3 + 1, data4);
      light = datafromarduino.substring(data4 + 1, data5);
      battery = datafromarduino.substring(data5 + 1);
    }

    if(WiFi.status()== WL_CONNECTED){
    WiFiClientSecure *client = new WiFiClientSecure;
    client->setInsecure(); //don't use SSL certificate
    HTTPClient https;
    
    // Your Domain name with URL path or IP address with path
    https.begin(*client, serverName);
    
    // Specify content-type header
    https.addHeader("Content-Type", "application/x-www-form-urlencoded");
    
    // Prepare your HTTP POST request data

    String httpRequestData = "api_key=" + apiKeyValue + "&value1=" + board
                            + "&value2=" + Temp + "&value3=" + Humdity + "&value4=" + Pressure + "&value5=" + light + "&value6=" + battery + "";
   
    Serial.print("httpRequestData: ");
    Serial.println(httpRequestData);

    // Send HTTP POST request
    int httpResponseCode = https.POST(httpRequestData);
    
      if (httpResponseCode>0) {
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
      }
      else {
        Serial.print("Error code: ");
        Serial.println(httpResponseCode);
      }
      // Free resources
      https.end();
    }
    else {
      Serial.println("WiFi Disconnected");
      ESP.restart();
    }
  }
}
