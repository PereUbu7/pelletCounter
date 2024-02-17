#include "Hub.hpp"
#include "secrets.hpp"

#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <ESP8266WebServer.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <dhtnew.h>

#define ONE_WIRE_BUS 14
#define AM2301_BuS 12

#define NUMBER_OF_DS18B20S 1
#define NUMBER_OF_SAMPLES 100
#define SECONDS_BETWEEN_READS 1

HubConfiguration config
{
    .APIP{AP_IP},
    .APGateway{AP_GATEWAY},
    .APSubnet{255, 255, 255, 0},
    .ServerSSID{SERVER_SSID},
    .ServerPassword{SERVER_PASSWORD},
    .ServerPelletEndpoint{SERVER_PELLET_ENDPOINT},
    .ServerPelletSensorIdleTimeoutSeconds{30},
    .ClientSSID{CLIENT_SSID},
    .ClientPassword{CLIENT_PASSWORD},
    .ClientConnectToHost{CLIENT_CONNECT_TO_HOST},
    .ClientConnectToPort{80},
    .ClientMaxNumberOfRetries{30},
    .ClientPelletEndpoint{CLIENT_PELLET_ENDPOINT},
    .ClientSensorEndpoint{CLIENT_SENSOR_ENDPOINT} 
};

SensorReaderConfiguration<NUMBER_OF_DS18B20S> readerConf{
        .DS18B20Ids{
        {0x28, 0xFF, 0x1E, 0xF6, 0xC3, 0x17, 0x04, 0xBA}}};

DHTNEW am2301(AM2301_BuS);
OneWire oneWire(ONE_WIRE_BUS);	
DallasTemperature sensors(&oneWire);

SensorReader<NUMBER_OF_DS18B20S, NUMBER_OF_SAMPLES, SECONDS_BETWEEN_READS> reader(&am2301, &oneWire, &sensors, readerConf);

ESP8266WebServer server(80);
Hub hub(server, reader, config);

void setup(void)
{
  Serial.begin(9600);
}

void loop(void)
{ 
  State state = hub.Run();
}