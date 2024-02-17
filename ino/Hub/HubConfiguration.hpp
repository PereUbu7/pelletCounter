#pragma once

#include <IPAddress.h>
#include <cstdint>

struct HubConfiguration
{
    const IPAddress APIP;
    const IPAddress APGateway;
    const IPAddress APSubnet;
    const char* ServerSSID;
    const char* ServerPassword;
    const char* ServerPelletEndpoint;
    unsigned int ServerPelletSensorIdleTimeoutSeconds;
    const char* ClientSSID;
    const char* ClientPassword;
    const char* ClientConnectToHost;
    const uint16_t ClientConnectToPort;
    const size_t ClientMaxNumberOfRetries;
    const char* ClientPelletEndpoint;
    const char* ClientSensorEndpoint;
};