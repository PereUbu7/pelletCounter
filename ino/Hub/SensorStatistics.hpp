#pragma once

#include "StaticString.hpp"

#include <cstdlib>

template <size_t numberOfDS18B20s>
struct SensorStatistics
{
    float DS18B20_P5[numberOfDS18B20s];
    float DS18B20_P50[numberOfDS18B20s];
    float DS18B20_P95[numberOfDS18B20s];
    long DS18B20_Errors[numberOfDS18B20s]; //  TODO: Include id
    
    float AM2301Temp_P5;
    float AM2301Temp_P50;
    float AM2301Temp_P95;

    float AM2301Humid_P5;
    float AM2301Humid_P50;
    float AM2301Humid_P95;

    long AM2301_Errors;

    template<size_t jsonLength = 500>
    StaticString<jsonLength> toJson()
    {
        StaticString<jsonLength> json{};
        json << R"({"ATP5":)"
            << AM2301Temp_P5
            << R"(,"ATP50":)"
            << AM2301Temp_P50
            << R"(,"ATP95":)"
            << AM2301Temp_P95
            << R"(,"AHP5":)"
            << AM2301Humid_P5
            << R"(,"AHP50":)"
            << AM2301Humid_P50
            << R"(,"AHP95":)"
            << AM2301Humid_P95
            << R"(,"AE":)"
            << AM2301_Errors
            << R"(,"DS":[)";
        
        for(size_t i{0}; i < numberOfDS18B20s; ++i)
        {
            json << R"({"P5":)"
                << DS18B20_P5[i]
                << R"(,"P50":)"
                << DS18B20_P50[i]
                << R"(,"P95":)"
                << DS18B20_P95[i]
                << R"(,"E":)"
                << DS18B20_Errors[i]
                << '}';

                if(i < (numberOfDS18B20s - 1))
                    json << ',';
        }
        json << "]}";

        return json;
    }
};