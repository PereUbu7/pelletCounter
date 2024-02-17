#include <cstdlib>

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