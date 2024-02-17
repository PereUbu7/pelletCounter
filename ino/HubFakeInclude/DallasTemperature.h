#pragma once

#include <OneWire.h>

#include <cstdint>

class DallasTemperature
{
public:
    DallasTemperature(const OneWire* wire) : step{}, doStep{false} {};

    void begin() const {}
    void requestTemperatures() {}
    float getTempC(const uint8_t (&id)[8]) 
    {
        return doStep ? step++*1.f : 0.f;
    }
    
    void SetFakeStepupPerRead(bool set) { doStep = set; }
private:
    int step;
    bool doStep;
};