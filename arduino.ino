#include <ESP8266WiFi.h>
#include <DHT.h>
#include <ArduinoJson.h>
#include <ESP8266HTTPClient.h>

#define DHTPIN D4
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);
const char* ssid = "jesus";
const char* password = "1234567890j";
const char* serverName = "http://YOUR_SERVER_IP/maize_weevil_php/sensors/insert.php";

void setup() {
  Serial.begin(115200);
  dht.begin();
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("Connected!");
}

void loop() {
  float temp = dht.readTemperature();
  float humid = dht.readHumidity();

  if (isnan(temp) || isnan(humid)) {
    Serial.println("Failed to read from DHT sensor!");
    return;
  }

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/json");

    String postData;
    StaticJsonDocument<100> doc;
    doc["temperature"] = temp;
    doc["humidity"] = humid;
    serializeJson(doc, postData);

    int httpCode = http.POST(postData);
    String payload = http.getString();

    Serial.print("POST code: ");
    Serial.println(httpCode);
    Serial.println("Response: " + payload);

    http.end();
  }

  delay(15000); // Send data every 15 seconds
}
