#include "Hub.hpp"
#include "Utils.hpp"

#include <WiFiClient.h>

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
  else
  {
    Serial.println("Read sensors");
  }
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
bool Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::StartServer()
{
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
  WiFi.mode(WIFI_STA);
  WiFi.begin(config.ClientSSID, config.ClientPassword);
  WiFi.setAutoConnect(false);

  auto timeout = millis() + 30000ul;
  while ((WiFi.status() != WL_CONNECTED) && (millis() < timeout))
  {
    // delay(50); TODO: Remake without delay. Maybe a state: ClientConnecting
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

  Serial.print("Sending ");
  Serial.print(pelletCounter);
  Serial.println(" number of pellet pulses.");
  
  client.stop();

  pelletCounter = 0;
  return true;
}

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
bool Hub<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>::SendSensorData()
{
  WiFiClient client;

  if (!client.connect(config.ClientConnectToHost, config.ClientConnectToPort))
  {
    Serial.println("sensor client connection failed");
    return false;
  }

  if (!client.connected()) { return false; }

  client.print("GET ");
  client.print(config.ClientSensorEndpoint);
  client.stop();

  Serial.println("Sending sensor data");

  _reader.ClearBuffer();
  sensorBufferIsFull = false;
  return true;
}