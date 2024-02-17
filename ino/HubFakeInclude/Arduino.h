#pragma once

#include <IPAddress.h>

#include <iostream>

enum WIFIMode { WIFI_STA };
enum WIFIStatus { WL_CONNECTED };

struct WifiFake
{
    void softAPConfig(const IPAddress ip, const IPAddress gateway, const IPAddress mask) {}
    void softAP(const char* ssid) {}
    IPAddress softAPIP()
    {
        return IPAddress();
    }
    bool softAPdisconnect() { return true; }
    void mode(int) {}
    void setAutoConnect(bool set) {}
    void begin(const char* host, const char* pass) {}
    WIFIStatus status() { return WL_CONNECTED; }
    bool disconnect() { return true; }
};

extern WifiFake WiFi;

struct SerialFake
{
    void print(const char* line) { std::cout << line; }
    void print(const unsigned long value) { std::cout << value; }
    void println() { std::cout << '\n'; }
    void println(const char* line) { std::cout << line << '\n'; }
    void println(const unsigned long value) { std::cout << value << '\n'; }
    void println(const IPAddress ip) { 
        std::cout << 
        "IP(" << ip.a << ", " << ip.b << ", " << ip.c << ", " << ip.d << ")\n";
    }
};

extern SerialFake Serial;

struct FakeWiFiClientRequestReceiver
{
    int numberOfPelletPulses;
    int numberOfSensorRequests;
};

extern FakeWiFiClientRequestReceiver fakeReqeuestReceiver;