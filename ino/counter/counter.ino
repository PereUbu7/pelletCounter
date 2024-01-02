#include "secrets.h"

#include <ESP8266WiFi.h>

void setup()
{
  const char *ssid = STASSID;
  const char *password = STAPSK;

  const char *host = "10.42.0.1";
  const uint16_t port = 80;
  // Serial.begin(115200);

  // We start by connecting to a WiFi network

  // Serial.println();
  // Serial.println();
  // Serial.print("Connecting to ");
  // Serial.println(ssid);

  uint32_t startupAt = millis();

  WiFi.setAutoConnect(false);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  uint32_t timeout = millis() + 10000;
  while ((WiFi.status() != WL_CONNECTED) && (millis() < timeout))
  {
    delay(50);
  }

  uint32_t connectedAt = millis();

  // Serial.println("");
  // Serial.println("WiFi connected");
  // Serial.println("IP address: ");
  // Serial.println(WiFi.localIP());

  // Serial.print("connecting to ");
  // Serial.print(host);
  // Serial.print(':');
  // Serial.println(port);

  // Use WiFiClient class to create TCP connections
  WiFiClient client;

  while (!client.connect(host, port) && (millis() < timeout))
  {
    // Serial.println("connection failed");
    delay(50);
  }

  // Serial.println("sending data to server");
  if (client.connected())
  {
    client.print(String("GET /pelletCounter/server/api.php?version=1.0") + " HTTP/1.1\r\n" +
                 "Host: " + host + "\r\n" +
                 "Connection: close\r\n\r\n");
  }

  client.stop();

  uint32_t doneAt = millis();

  Serial.begin(115200);
  Serial.print("Connection time: ");
  Serial.println(connectedAt - startupAt);
  Serial.print("Total time: ");
  Serial.println(doneAt - startupAt);
}

void loop() {}