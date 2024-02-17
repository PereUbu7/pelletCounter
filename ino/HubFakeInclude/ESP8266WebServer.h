#pragma once

#include <functional>

class ESP8266WebServer
{
public:
    ESP8266WebServer();
    ESP8266WebServer(int port) {}
    ESP8266WebServer(ESP8266WebServer& other);

    // NOTE: This fake can only handle ONE enpoint for now!
    void on(const char* path, std::function<void()> callback) 
    {
        _callback = callback;
    }
    void send(const int code, const char* header, const char* content) {}
    void begin() {}
    void stop() {}
    void handleClient() 
    {
        if(_hasRequest)
        {
            _hasRequest = false;
            _callback();
        }
    }

    void doFakeRequest() { _hasRequest = true; }
private:
    std::function<void()> _callback;
    bool _hasRequest{false};
};

extern unsigned long fakedRunTimeSeconds;
unsigned long millis();
void setCurrentFakedRuntime(unsigned long seconds);