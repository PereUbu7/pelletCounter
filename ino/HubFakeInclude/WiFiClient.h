#pragma once

#include <Arduino.h>

#include <iostream>
#include <cstdint>
#include <string.h>

extern FakeWiFiClientRequestReceiver fakeReqeuestReceiver;

class WiFiClient
{
public:
    bool connect(const char* host, uint16_t port) { isConnected = true; return true; }
    bool connected() { return isConnected; }
    void print(const char* text) { internPrintln(text, false); }
    void println(const char* text) { internPrintln(text, true); }
    void println(unsigned long value) {}
    void println(float value) {}
    void stop() { isConnected = false; }
private:
    bool isConnected{false};
    bool connectedToPelletApi{false};

    bool isUnknownRequest{true};

    void internPrintln(const char* text, bool hasNewline)
    {
        std::cout << text;
        if(hasNewline) std::cout << '\n';

        if(isConnected)
        {
            if(strstr(text, "pellet")) 
            { 
                connectedToPelletApi = true;
                isUnknownRequest = false;
            }
            else if(strstr(text, "sensor"))
            { 
                connectedToPelletApi = false; 
                isUnknownRequest = false;
            }

            if(connectedToPelletApi && !isUnknownRequest) 
            { 
                ++fakeReqeuestReceiver.numberOfPelletPulses; 
                isUnknownRequest = true; 
            }
            else if(!isUnknownRequest) 
            { 
                ++fakeReqeuestReceiver.numberOfSensorRequests; 
                isUnknownRequest = true; 
            }

            if(hasNewline) { isUnknownRequest = true; }
        }
    }
};