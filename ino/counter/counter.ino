#include "secrets.h"

#include <ESP8266WiFi.h>
#include <WiFiClient.h>

// #define DEBUG 1

uint32_t connectedAt;
uint32_t startupAt;
bool firstMeasure = true;

void setup()
{
  #ifdef DEBUG
  Serial.begin(9600);
  #endif

  // delay(500);
  const char *ssid = STASSID;
  const char *password = STAPSK;



  IPAddress staticIP(192, 168, 0, 108);
  IPAddress gateway(192, 168, 0, 108);
  IPAddress netmask(255, 255, 255, 0);

  startupAt = millis();

  WiFi.persistent(false);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid);
  WiFi.config(staticIP, gateway, netmask);
  WiFi.setAutoConnect(false);

  #ifdef DEBUG
  Serial.print("Connecting to ");
  Serial.println(ssid);
  #endif

  uint32_t timeout = millis() + 30000;
  while ((WiFi.status() != WL_CONNECTED) && (millis() < timeout))
  {
    #ifdef DEBUG
    Serial.println("Waiting...");
    #endif
    delay(50);
  }

  if(millis() > timeout)
  {
    #ifndef DEBUG
    Serial.begin(9600);
    #endif
    Serial.println("Timed out");
    return;
  }

 
}

void loop() 
{
  if(!firstMeasure)
  {
    #ifdef DEBUG
    Serial.print("waiting ");
    Serial.println(connectedAt - startupAt);
    #endif
    delay(connectedAt - startupAt);
  }
  else
  {
    connectedAt = millis();
    firstMeasure = false;
  }

  const char *host = "192.168.0.37";
  const uint16_t port = 80;


  WiFiClient client;

  #ifdef DEBUG
  Serial.print("Connecting to ");
  Serial.println(host);
  #endif

  if (!client.connect(host, port))
  {
    #ifndef DEBUG
    Serial.begin(9600);
    #endif
    Serial.print("connection to ");
    Serial.print(host);
    Serial.print(":");
    Serial.print(port);
    Serial.println(" failed");
    return;
  }

  if (client.connected())
  {
    client.println("GET /pellet_call/ HTTP/1.1");
    client.print("Host: ");
    client.println(host);
    client.println("Connection: close");
    client.println();
  }

  auto timeout = millis();
  while (client.available() == 0) {
    if (millis() - timeout > 10000) {
      #ifndef DEBUG
      Serial.begin(9600);
      #endif
      Serial.println(">>> Client Timeout !");
      return;
    }
  }

  #ifndef DEBUG
  Serial.begin(9600);
  #endif
  while(client.available())
  {
    char c = static_cast<char>(client.read());
    Serial.print(c);
  }

  client.stop();

  uint32_t doneAt = millis();

  #ifndef DEBUG
  Serial.begin(9600);
  #endif

  Serial.print("Connection time: ");
  Serial.println(connectedAt - startupAt);
  Serial.print("Total time: ");
  Serial.println(doneAt - startupAt);

  Serial.print("At channel: ");
  Serial.print(WiFi.channel());
}