#pragma once

#include "HubConfiguration.hpp"
#include "StateMachine.hpp"
#include "SensorReader.hpp"

#include <ESP8266WebServer.h>

#include <cstdlib>

template<size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
class Hub
{
public:
    Hub(ESP8266WebServer& server, SensorReader<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>& reader, const HubConfiguration& conf) :
        config{conf},
        secondsAtLastPelletPing{0ul},
        pelletCounter{0ul},
        sensorBufferIsFull{false},
        _stateMachine(*this),
        _server{server},
        _reader{reader}
    {}

    State Run();
    void RunHost();
    bool StartServer();
    bool StopServer();
    bool RunClient();
    State GetState() const;

    const HubConfiguration& config;
    unsigned long secondsAtLastPelletPing;
    unsigned long pelletCounter;
    bool sensorBufferIsFull;

private:
    StateMachine<Hub> _stateMachine;
    ESP8266WebServer& _server;
    SensorReader<numberOfDS18B20s, numberOfSamples, secondsBetweenReads>& _reader;
    
    bool StartClient();
    bool StopClient();
    bool SendSensorData();
    bool SendPelletsData();
};

#include "HubImpl.hpp"