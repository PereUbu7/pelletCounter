#include "secrets.h"

#include <ESP8266WiFi.h>
#include <WiFiClient.h>

void setup()
{
  const char *ssid = STASSID;
  const char *password = STAPSK;

  IPAddress ip(10, 42, 0, 1);
  IPAddress dns(0, 0, 0, 0);
  IPAddress gateway(255, 255, 255, 0);

  const char *host = "10.42.0.1";
  const uint16_t port = 80;
  const uint8_t channel = 11;
  uint8_t bssid[6] = {0, 26, 115, 173, 217, 195};

  uint32_t startupAt = millis();

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  WiFi.setAutoConnect(false);

  uint32_t timeout = millis() + 30000;
  while ((WiFi.status() != WL_CONNECTED) && (millis() < timeout))
  {
    delay(50);
  }

    Serial.begin(115200);

  if(millis() > timeout)
  {
    Serial.println("Timed out");
    return;
  }

  uint32_t connectedAt = millis();

  WiFiClient client;

  if (!client.connect(host, port))
  {
    Serial.println("connection failed");
    return;
  }

  if (client.connected())
  {
    client.println("GET /pelletCounter/server/api.php?version=1.1");
  }

  client.stop();

  uint32_t doneAt = millis();


  Serial.print("Connection time: ");
  Serial.println(connectedAt - startupAt);
  Serial.print("Total time: ");
  Serial.println(doneAt - startupAt);

  Serial.print("At channel: ");
  Serial.print(WiFi.channel());
  Serial.print(" and bssid: ");
  memcpy( bssid, WiFi.BSSID(), 6 );
  Serial.print(bssid[0]);
  Serial.print(' ');
  Serial.print(bssid[1]);
  Serial.print(' ');
  Serial.print(bssid[2]);
  Serial.print(' ');
  Serial.print(bssid[3]);
  Serial.print(' ');
  Serial.print(bssid[4]);
  Serial.print(' ');
  Serial.println(bssid[5]);
}

void loop() {}