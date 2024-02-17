#pragma once

#include <cstdlib>

unsigned long secs();

template <typename T>
struct source
{
    T observation;
    T value;
    bool Than(const T threshold) const
    {
        auto upper = observation + threshold;
        auto lower = observation - threshold;
        return upper < value ||
            lower > value;
    }
};

template <>
struct source<unsigned long>
{
    unsigned long observation;
    unsigned long value;
    bool Than(const unsigned long threshold) const
    {
        signed long upper = observation + static_cast<signed long>(threshold);
        signed long lower = observation - static_cast<signed long>(threshold);
        signed long signValue = static_cast<signed long>(value);
        return upper < signValue ||
            lower > signValue;
    }
};

template <typename T>
struct Observation
{
    const T value;
    Observation(const T v) : value{v} {}
    source<T> IsFurtherAwayFrom(const T s) 
    { 
        return source<T> 
        { 
            .observation = value, 
            .value = s 
        }; 
    }
};

template<typename AnyType> void insertionSort(AnyType array[], size_t sizeOfArray, bool reverse, bool (*largerThan)(AnyType, AnyType)) {
		for (size_t i = 1; i < sizeOfArray; i++) {
			for (size_t j = i; j > 0 && (largerThan(array[j-1], array[j]) != reverse); j--) {
				AnyType tmp = array[j-1];
				array[j-1] = array[j];
				array[j] = tmp;
			}
		}
	}

template<typename AnyType> bool builtinLargerThan(AnyType first, AnyType second) {
		return first > second;
	}

template<typename AnyType> void sortArray(AnyType array[], size_t sizeOfArray) {
	insertionSort(array, sizeOfArray, false, builtinLargerThan);
}

bool tempInRange(float temp);

size_t getScaledP5Index(size_t numberOfSamples);

size_t getScaledP50Index(size_t numberOfSamples);

size_t getScaledP95Index(size_t numberOfSamples);