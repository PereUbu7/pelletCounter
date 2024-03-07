#include "helper.h"

#include <OneWire.h>
#include <DallasTemperature.h>
#include <dhtnew.h>

#define ONE_WIRE_BUS 14
#define AM2301_BuS 12

const size_t _numberOfSamples = 3;

uint8_t sensor1[8] = { 0x28, 0xFF, 0x1E, 0xF6, 0xC3, 0x17, 0x04, 0xBA };
uint8_t sensor2[8] = { 0x28, 0xFF, 0x19, 0x16, 0xC4, 0x17, 0x04, 0xD8 };
uint8_t sensor3[8] = { 0x28, 0xAC, 0xAA, 0x43, 0xD4, 0x8E, 0x4D, 0x16 };
uint8_t sensor4[8] = { 0x28, 0x8E, 0x0B, 0x43, 0xD4, 0x1F, 0x38, 0xF0 };
uint8_t sensor5[8] = { 0x28, 0xF1, 0x1C, 0x43, 0xD4, 0x52, 0x38, 0x73 };

DHTNEW am2301(AM2301_BuS);
OneWire oneWire(ONE_WIRE_BUS);	
DallasTemperature sensors(&oneWire);

void setup(void)
{
  sensors.begin();	// Start up the library
  Serial.begin(9600);
}

void loop(void)
{ 
  float samplesAm2301Temp[_numberOfSamples];
  float samplesAm2301Hum[_numberOfSamples];
  float samples1[_numberOfSamples];
  float samples2[_numberOfSamples];

  size_t am2301Index = 0;
  size_t sensor1Index = 0;
  size_t sensor2Index = 0;

  for(uint index = 0; index < _numberOfSamples; ++index)
  {    
    auto initTime = millis();
    sensors.requestTemperatures(); 
    auto temp1 = sensors.getTempC(sensor1);
    auto temp2 = sensors.getTempC(sensor2);

    Serial.print("2:");
    Serial.print(sensors.getTempC(sensor2));
    Serial.print(",3:");
    Serial.print(sensors.getTempC(sensor3));
    Serial.print(",4:");
    Serial.print(sensors.getTempC(sensor4));
    Serial.print(",5:");
    Serial.println(sensors.getTempC(sensor5));
    
    auto am2301ErrorCode = am2301.read();

    if(tempInRange(temp1))
    {
      samples1[sensor1Index] = temp1;
      ++sensor1Index;
    }

    if(tempInRange(temp2))
    {
      samples2[sensor2Index] = temp2;
      ++sensor2Index;
    }

    if(!am2301ErrorCode)
    {
      samplesAm2301Temp[am2301Index] = am2301.getTemperature();
      samplesAm2301Hum[am2301Index] = am2301.getHumidity();

      ++am2301Index;
    }
    
    auto getDuration = (signed long)millis() - (signed long)initTime;

    auto timeLeftToWait = 6000 - getDuration;

    if(timeLeftToWait > 0)
      delay(timeLeftToWait);
  }

  sortArray(samples1, sensor1Index);
  sortArray(samples2, sensor2Index);
  sortArray(samplesAm2301Temp, am2301Index);
  sortArray(samplesAm2301Hum, am2301Index);

  // Scale index in case of error readings
  const size_t p51Index  = getScaledP5Index(sensor1Index);
  const size_t p501Index = getScaledP50Index(sensor1Index);
  const size_t p951Index = getScaledP95Index(sensor1Index);

  const size_t p52Index  = getScaledP5Index(sensor2Index);
  const size_t p502Index = getScaledP50Index(sensor2Index);
  const size_t p952Index = getScaledP95Index(sensor2Index);

  const size_t am2301P5Index  = getScaledP5Index(am2301Index);
  const size_t am2301P50Index = getScaledP50Index(am2301Index);
  const size_t am2301P95Index = getScaledP95Index(am2301Index);

  // Serial.print("1P50:");
  // Serial.print(samples1[p501Index]);
  // Serial.print(",2P50:");
  // Serial.print(samples2[p502Index]);
  // Serial.print(",3P50:");
  // Serial.print(samplesAm2301Temp[am2301P50Index]);
  // Serial.print(",4P50:");
  // Serial.print(samplesAm2301Hum[am2301P50Index]);
  // Serial.print(",1P5:");
  // Serial.print(samples1[p51Index]);
  // Serial.print(",1P95:");
  // Serial.print(samples1[p951Index]);

  // Serial.print(",2P5:");
  // Serial.print(samples2[p52Index]);
  // Serial.print(",2P95:");
  // Serial.print(samples2[p952Index]);

  // Serial.print(",3P5:");
  // Serial.print(samplesAm2301Temp[am2301P5Index]);
  // Serial.print(",3P95:");
  // Serial.print(samplesAm2301Temp[am2301P95Index]);

  // Serial.print(",4P5:");
  // Serial.print(samplesAm2301Hum[am2301P5Index]);
  // Serial.print(",4P95:");
  // Serial.println(samplesAm2301Hum[am2301P95Index]);
}
