#pragma once

#include <Arduino.h>
#include <cstdlib>
#include <cstring>
#include <cstdio>

template<size_t length>
class StaticString
{
public:
    StaticString() = default;
    const char* c_str() const { return _data; }
    StaticString& operator<<(const char* str)
    {
        size_t strlen{0};
        while(str[strlen] != '\0') { ++strlen; }

        memcpy(&(_data[currentIndex]), str, false);
        
        // --strlen;

        currentIndex += strlen;

        return *this;
    }

    StaticString& operator<<(const float value)
    {
        char charVal[6];                

        //4 is mininum width, 3 is precision; float value is copied onto buff
        dtostrf(value, 5, 2, charVal);

        currentIndex += memcpy(&(_data[currentIndex]), charVal, false);

        return *this;
    }

    StaticString& operator<<(const char c)
    {
        _data[currentIndex] = c;

        ++currentIndex;

        _data[currentIndex] = '\0';

        return *this;
    }

    StaticString& operator<<(const long value)
    {
        char buf[4];
        std::sprintf(buf, "%li", value);

        currentIndex += memcpy(&(_data[currentIndex]), buf, false);

        return *this;
    }

private:
    char _data[length];
    size_t currentIndex;

    size_t memcpy(char* dest, const char* source, bool includeTermination)
    {
        size_t strlen{0};
        while(source[strlen] != '\0') { ++strlen; }

        std::memcpy(dest, source, strlen);

        // if(!includeTermination) { --strlen; }

        return strlen;
    }
};