#include "Utils.hpp"

#include <Arduino.h>

unsigned long secs()
{
    return millis() / 1000ul;
}

bool tempInRange(float temp) {
  float from85 = temp - 85;
  return temp > -50 && !(from85 < 1 && from85 > -1);
}

size_t getScaledP5Index(size_t numberOfSamples)
{
  return numberOfSamples / 25;
}

size_t getScaledP50Index(size_t numberOfSamples)
{
  return numberOfSamples > 1 ? numberOfSamples / 2 - 1 : 0;
}

size_t getScaledP95Index(size_t numberOfSamples)
{
  return numberOfSamples > 0 ? numberOfSamples - numberOfSamples / 20 - 1 : 0;
}