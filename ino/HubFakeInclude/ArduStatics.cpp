#include "Arduino.h"

WifiFake WiFi;
SerialFake Serial;

unsigned long fakedRunTimeSeconds{0ul};
unsigned long millis() { return fakedRunTimeSeconds * 1000ul; }
void setCurrentFakedRuntime(unsigned long seconds) { fakedRunTimeSeconds = seconds; }