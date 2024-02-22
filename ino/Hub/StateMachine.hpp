#pragma once

#include "HubConfiguration.hpp"
#include "Utils.hpp"

#include <Arduino.h>
#include <cstdlib>

enum class State
{
  Init,
  Host,
  Client
};

class StateMachineConfiguration
{
public:
  StateMachineConfiguration(const HubConfiguration &hubConf) : 
    clientMaxNumberOfRetries{hubConf.ClientMaxNumberOfRetries},
    ServerPelletSensorIdleTimeoutSeconds{hubConf.ServerPelletSensorIdleTimeoutSeconds}
  {}

  size_t clientMaxNumberOfRetries;
  unsigned int ServerPelletSensorIdleTimeoutSeconds;
};

template <class Hubtype> 
class StateMachine
{
public:
  StateMachine() = delete;
  StateMachine(Hubtype &hub) : _config(hub.config),
                               _state{State::Init},
                               _hub{hub},
                               _clientNumberOfTries{0} {};

  StateMachine operator=(const StateMachine right)
  {
    return StateMachine(this->_hub);
  }

  const State &Run()
  {
    switch (_state)
    {
    case State::Host:
      _hub.RunHost();

      {
        auto sinceLast = secs() - _hub.secondsAtLastPelletPing;

        if ((_config.ServerPelletSensorIdleTimeoutSeconds <= sinceLast &&
          _hub.pelletCounter > 0ul) ||
          _hub.sensorBufferIsFull )
        {
          _state = State::Client;
        }
      }
      break;

    case State::Client:
      if (_clientNumberOfTries < _config.clientMaxNumberOfRetries)
      {
        if (!_hub.RunClient())
        {
          ++_clientNumberOfTries;
          break;
        }
      }
      _clientNumberOfTries = 0;
      _state = State::Init;

      break;

    case State::Init:
      Serial.println("\n--- Run state machine in state Init - Start server ---");
      if (_hub.StartServer())
      {
        _state = State::Host;
      }

      break;

    default:
      _state = State::Init;
      break;
    }

    return _state;
  }

  State GetState() const {
    return _state;
  }

private:
  StateMachineConfiguration _config;
  State _state;
  Hubtype &_hub;
  size_t _clientNumberOfTries;
};