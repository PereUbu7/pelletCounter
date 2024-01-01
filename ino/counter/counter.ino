#include "secrets.h"

#include <ESP8266WiFi.h>


const char* ssid = STASSID;
const char* password = STAPSK;

const char* host = "http://10.42.0.1/pelletCounter/server/api.php?version=1.0";
const uint16_t port = 80;

void setup() {
  Serial.begin(115200);

  // We start by connecting to a WiFi network

  Serial.println();
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);


  WiFi.setAutoConnect(false);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  uint32_t timeout = millis() + 10000; 
  while ((WiFi.status() != WL_CONNECTED) && (millis()<timeout)) { delay(5); }

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());

  Serial.print("connecting to ");
  Serial.print(host);
  Serial.print(':');
  Serial.println(port);

  // Use WiFiClient class to create TCP connections
  WiFiClient client;

  while (!client.connect(host, port) && (millis()<timeout)) {
    Serial.println("connection failed");
    delay(50);
  }

  // This will send a string to the server
  Serial.println("sending data to server");
  if (client.connected()) { client.println("Hej från pannrummet!"); }

  // Close the connection
  Serial.println();
  Serial.println("closing connection");
  client.stop();
}

void loop() {}