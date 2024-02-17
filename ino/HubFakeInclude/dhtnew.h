#pragma once

class DHTNEW
{
public:
    DHTNEW(uint port) : stepT{}, stepH{}, doStep{false} {};

    int read() const { return 0; }
    float getTemperature()
    { 
        return doStep ? stepT++*1.f : 0.f; 
    }
    float getHumidity()
    { 
        return doStep ? stepH++*1.f : 0.f; 
    }

    void SetFakeStepupPerRead(bool set) { doStep = set; }
private:
    int stepT, stepH;
    bool doStep;
};