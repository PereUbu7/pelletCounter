#include "Hub.hpp"
#include "Utils.hpp"

#include <WiFiClient.h>
#include <ESP8266HTTPClient.h>

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
State Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::Run()
{
  return _stateMachine.Run();
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
State Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::GetState() const
{
  return _stateMachine.GetState();
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
void Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::RunHost()
{
  _server.handleClient();
  sensorBufferIsFull = !_reader.TryRead();
  if (sensorBufferIsFull)
  {
    Serial.println("Couldn't read sensors - buffer is full");
  }
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
bool Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::StartServer()
{
  WiFi.persistent(false);

  WiFi.softAPConfig(config.APIP, config.APGateway, config.APSubnet);
  WiFi.softAP(config.ServerSSID);

  _server.on(config.ServerPelletEndpoint, [this]()
  {
    Serial.print("Got request on ");
    Serial.println(config.ServerPelletEndpoint);
    
    _server.send(200, "text/html", "<h1>You are connected</h1>");

    secondsAtLastPelletPing = secs();
    ++pelletCounter; 
  });

  _server.begin();

  return true;
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
bool Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::StopServer()
{
  _server.stop();
  return WiFi.softAPdisconnect();
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
bool Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::RunClient()
{
  if(!StartClient()) return false;

  bool sendSensorIsSuccess = !sensorBufferIsFull;
  if(sensorBufferIsFull)
  {
    sendSensorIsSuccess = SendSensorData();
  }

  bool sendPelletsIsSuccess = false;
  sendPelletsIsSuccess = SendPelletsData();

  return sendSensorIsSuccess && sendPelletsIsSuccess;
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
bool Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::StartClient()
{
  WiFi.persistent(false);
  WiFi.mode(WIFI_STA);
  WiFi.begin(config.ClientSSID, config.ClientPassword);
  WiFi.setAutoConnect(false);

  auto timeout = millis() + 30000ul;
  while ((WiFi.status() != WL_CONNECTED) && (millis() < timeout))
  {
    delay(500);// TODO: Remake without delay. Maybe a state: ClientConnecting
    Serial.print("Waiting...");
  }

  if (millis() > timeout)
  {
    Serial.println("Timed out");
    return false;
  }
  return true;
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
bool Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::StopClient()
{
  return WiFi.disconnect();  
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
bool Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::SendPelletsData()
{
  WiFiClient client;

  if (!client.connect(config.ClientConnectToHost, config.ClientConnectToPort))
  {
    Serial.println("pellet client connection failed");
    return false;
  }

  if (!client.connected()) { return false; }

  client.print("GET ");
  client.print(config.ClientPelletEndpoint);
  client.println(pelletCounter);
  client.print("Host: ");
  client.println(config.ClientConnectToHost);
  client.println("Connection: close");
  client.println();

  Serial.print("Sending ");
  Serial.print(config.ClientPelletEndpoint);
  Serial.println(pelletCounter);

  unsigned long timeout = millis();
  while (client.available() == 0) {
    if (millis() - timeout > 30000) {
      Serial.println(">>> Client Timeout !");
      return false;
    }
  }
  
  while(client.available())
  {
    char c = static_cast<char>(client.read());
    Serial.print(c);
  }

  client.stop();

  pelletCounter = 0;
  return true;
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
bool Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::SendSensorData()
{
  WiFiClient client;
  HTTPClient http;

  http.begin(client, config.ClientSensorEndpoint);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  
  auto json = _reader.GetData().toJson();
  
  String httpRequestData = String("version=1.1&type=furnceRoomSensors&json=") + String(json.c_str());  

  Serial.print("POST: ");
  Serial.println(httpRequestData);         

  int httpResponseCode = http.POST(httpRequestData);
  http.writeToStream(&Serial);
  http.end();

  if(httpResponseCode != 200) { return false; }

  _reader.ClearBuffer();
  sensorBufferIsFull = false;
  return true;
}