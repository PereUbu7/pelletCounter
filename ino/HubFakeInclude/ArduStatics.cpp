#include "Arduino.h"

#include <cmath>
#include <cinttypes>
#include <cstring>

WifiFake WiFi;
SerialFake Serial;

unsigned long fakedRunTimeSeconds{0ul};
unsigned long millis() { return fakedRunTimeSeconds * 1000ul; }
void setCurrentFakedRuntime(unsigned long seconds) { fakedRunTimeSeconds = seconds; }

char * dtostrf(double number, signed char width, unsigned char prec, char *s) {
    bool negative = false;

    if (std::isnan(number)) {
        strcpy(s, "nan");
        return s;
    }
    if (std::isinf(number)) {
        strcpy(s, "inf");
        return s;
    }

    char* out = s;

    int fillme = width; // how many cells to fill for the integer part
    if (prec > 0) {
        fillme -= (prec+1);
    }

    // Handle negative numbers
    if (number < 0.0) {
        negative = true;
        fillme--;
        number = -number;
    }

    // Round correctly so that print(1.999, 2) prints as "2.00"
    // I optimized out most of the divisions
    double rounding = 2.0;
    for (uint8_t i = 0; i < prec; ++i)
        rounding *= 10.0;
    rounding = 1.0 / rounding;

    number += rounding;

    // Figure out how big our number really is
    double tenpow = 1.0;
    int digitcount = 1;
    double nextpow;
    while (number >= (nextpow = (10.0 * tenpow))) {
        tenpow = nextpow;
        digitcount++;
    }

    // minimal compensation for possible lack of precision (#7087 addition)
    number *= 1 + std::numeric_limits<decltype(number)>::epsilon();

    number /= tenpow;
    fillme -= digitcount;

    // Pad unused cells with spaces
    while (fillme-- > 0) {
        *out++ = ' ';
    }

    // Handle negative sign
    if (negative) *out++ = '-';

    // Print the digits, and if necessary, the decimal point
    digitcount += prec;
    int8_t digit = 0;
    while (digitcount-- > 0) {
        digit = (int8_t)number;
        if (digit > 9) digit = 9; // insurance
        *out++ = (char)('0' | digit);
        if ((digitcount == prec) && (prec > 0)) {
            *out++ = '.';
        }
        number -= digit;
        number *= 10.0;
    }

    // make sure the string is terminated
    *out = 0;
    return s;
}