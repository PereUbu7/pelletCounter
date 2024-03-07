#pragma once

#include "SensorStatistics.hpp"
#include "Utils.hpp"

#include <OneWire.h>
#include <DallasTemperature.h>
#include <dhtnew.h>
#include <Arduino.h>

#include <cstdlib>

template <size_t numberOfDS18B20s>
struct SensorReaderConfiguration
{
    const uint8_t (&DS18B20Ids)[numberOfDS18B20s][8];
};

template <size_t numberOfDS18B20s, size_t numberOfSamples, unsigned long secondsBetweenReads>
class SensorReader final
{
public:
    SensorReader(
        DHTNEW *dhtnew, 
        OneWire *onewire, 
        DallasTemperature *dallasT, 
        const SensorReaderConfiguration<numberOfDS18B20s> &conf) : 
            _config(conf),
            _DS18B20_samples{},
            _AM2301_samplesTemp{},
            _AM2301_samplesHumid{},
            _millisAtLastRead{},
            _DS18B20_indicies{},
            _AM2301Index{},
            _readCounter{0}
    {
        _am2301 = dhtnew;
        _oneWire = onewire;
        _dallasTemp = dallasT;
        _dallasTemp->begin();
    }

    bool TryRead()
    {
        const auto currentTime = millis();
        if (Observation(_millisAtLastRead)
            .IsFurtherAwayFrom(currentTime)
            .Than(secondsBetweenReads * 1000ul) &&
            _readCounter < numberOfSamples)
        {
            _millisAtLastRead = currentTime;
            ++_readCounter;

            DoRead();

            Serial.println("Read sensors");
        }
        return _readCounter < numberOfSamples;
    }

    void ClearBuffer()
    {
        for(size_t sensorIndex{}; sensorIndex < numberOfDS18B20s; ++sensorIndex)
        {
            for(size_t sampleIndex{}; sampleIndex < numberOfSamples; ++sampleIndex)
            {
                _DS18B20_samples[sensorIndex][sampleIndex] = 0.f;
            }
        }
        
        for(size_t sampleIndex{}; sampleIndex < numberOfSamples; ++sampleIndex)
        {
            _AM2301_samplesTemp[sampleIndex] = 0.f;
        }

        for(size_t sampleIndex{}; sampleIndex < numberOfSamples; ++sampleIndex)
        {
            _AM2301_samplesHumid[sampleIndex] = 0.f;
        }

        for(size_t sensorIndex{}; sensorIndex < numberOfDS18B20s; ++sensorIndex)
        {
            _DS18B20_indicies[sensorIndex] = 0ul;
        }

        _AM2301Index = 0ul;
        _readCounter = 0ul;
    }

    SensorStatistics<numberOfDS18B20s> GetData()
    {
        SensorStatistics<numberOfDS18B20s> stat{};

        /* For each DS18B20: */
        for (size_t sensorIndex{0ul}; sensorIndex < numberOfDS18B20s; ++sensorIndex)
        {
            /* Sort samples */
            sortArray(_DS18B20_samples[sensorIndex], _DS18B20_indicies[sensorIndex]);

            /* Pick percentiles */
            const auto p5Index = getScaledP5Index(_DS18B20_indicies[sensorIndex]);
            const auto p50Index = getScaledP50Index(_DS18B20_indicies[sensorIndex]);
            const auto p95Index = getScaledP95Index(_DS18B20_indicies[sensorIndex]);

            /* Save to stat object */
            stat.DS18B20_P5[sensorIndex] = _DS18B20_samples[sensorIndex][p5Index];
            stat.DS18B20_P50[sensorIndex] = _DS18B20_samples[sensorIndex][p50Index];
            stat.DS18B20_P95[sensorIndex] = _DS18B20_samples[sensorIndex][p95Index];

            /* Number of errors is total number of samples - number of successes */
            stat.DS18B20_Errors[sensorIndex] = numberOfSamples - _DS18B20_indicies[sensorIndex];
        }

        /* Same thing for AM2301 Temperature and humidity samples */
        sortArray(_AM2301_samplesTemp, _AM2301Index);
        sortArray(_AM2301_samplesHumid, _AM2301Index);

        const auto p5Index = getScaledP5Index(_AM2301Index);
        const auto p50Index = getScaledP50Index(_AM2301Index);
        const auto p95Index = getScaledP95Index(_AM2301Index);

        stat.AM2301Temp_P5 = _AM2301_samplesTemp[p5Index];
        stat.AM2301Temp_P50 = _AM2301_samplesTemp[p50Index];
        stat.AM2301Temp_P95 = _AM2301_samplesTemp[p95Index];

        stat.AM2301Humid_P5 = _AM2301_samplesHumid[p5Index];
        stat.AM2301Humid_P50 = _AM2301_samplesHumid[p50Index];
        stat.AM2301Humid_P95 = _AM2301_samplesHumid[p95Index];

        stat.AM2301_Errors = numberOfSamples - _AM2301Index;

        return stat;
    }

private:
    SensorReaderConfiguration<numberOfDS18B20s> _config;
    DHTNEW *_am2301;
    OneWire *_oneWire;
    DallasTemperature *_dallasTemp;
    float _DS18B20_samples[numberOfDS18B20s][numberOfSamples];

    float _AM2301_samplesTemp[numberOfSamples];
    float _AM2301_samplesHumid[numberOfSamples];

    unsigned long _millisAtLastRead;

    size_t _DS18B20_indicies[numberOfDS18B20s];
    size_t _AM2301Index;
    size_t _readCounter;

    unsigned int DoRead()
    {
        uint numberOfReads{0u};

        /* Collect DS18B20 temperature sample */
        _dallasTemp->requestTemperatures();
        for (size_t sensorIndex{0ul}; sensorIndex < numberOfDS18B20s; ++sensorIndex)
        {
            const uint8_t(&id)[8] = _config.DS18B20Ids[sensorIndex];
            float temp = _dallasTemp->getTempC(id);

            if (tempInRange(temp) &&
                _DS18B20_indicies[sensorIndex] < numberOfSamples)
            {
                _DS18B20_samples[sensorIndex][_DS18B20_indicies[sensorIndex]] = temp;
                ++_DS18B20_indicies[sensorIndex];
                ++numberOfReads;
            }
        }

        auto am2301ErrorCode = _am2301->read();

        if (!am2301ErrorCode)
        {
            _AM2301_samplesTemp[_AM2301Index] = _am2301->getTemperature();
            _AM2301_samplesHumid[_AM2301Index] = _am2301->getHumidity();

            ++_AM2301Index;
            ++numberOfReads;
        }

        return numberOfReads;
    }
};