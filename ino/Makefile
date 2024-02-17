CXX = g++

CXXFLAGS = -Wall -Wpedantic -Werror -Wconversion -fPIC -std=c++23
CXXFLAGS_temp_reader = $(CXXFLAGS) -I /usr/include/ -I temp_reader/
CXXFLAGS_Hub = $(CXXFLAGS) -isystem HubFakeInclude/ -I /usr/include/ -include ESP8266WebServer.h

BIN = test 

test_temp_reader: CXXFLAGS_temp_reader += -g -O0
test_Hub: CXXFLAGS_Hub += -g -O0

all : $(BIN)

test_temp_reader : tests_temp_reader.cpp temp_reader/helper.h
	$(CXX) $(CXXFLAGS_temp_reader) $^ -o test_temp_reader
	./test_temp_reader

test_Hub : tests_Hub.cpp Hub/*.cpp HubFakeInclude/ArduStatics.cpp
	$(CXX) $(CXXFLAGS_Hub) $^ -o test_Hub
	./test_Hub

.PHONY: all