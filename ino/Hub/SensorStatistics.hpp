#pragma once

#include <cstdlib>

template <size_t numberOfDS18B20s>
struct SensorStatistics
{
    float DS18B20_P5[numberOfDS18B20s];
    float DS18B20_P50[numberOfDS18B20s];
    float DS18B20_P95[numberOfDS18B20s];
    long DS18B20_Errors[numberOfDS18B20s];
    
    float AM2301Temp_P5;
    float AM2301Temp_P50;
    float AM2301Temp_P95;

    float AM2301Humid_P5;
    float AM2301Humid_P50;
    float AM2301Humid_P95;

    long AM2301_Errors;
};