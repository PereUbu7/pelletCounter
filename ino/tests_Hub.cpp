#include "Hub/Hub.hpp"
#include "HubFakeInclude/ESP8266WebServer.h"
#include "HubFakeInclude/DallasTemperature.h"
#include "HubFakeInclude/dhtnew.h"
#include "HubFakeInclude/OneWire.h"
#include "HubFakeInclude/WiFiClient.h"

#define DOCTEST_CONFIG_IMPLEMENT_WITH_MAIN
#include "doctest/doctest.h"

#include <iostream>

#define SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS 30u

FakeWiFiClientRequestReceiver fakeReqeuestReceiver;

TEST_CASE("Hub init")
{
    // Start runtime clock at zero
    setCurrentFakedRuntime(0ul);

    HubConfiguration config{
        .ServerSSID{"SecretHub"},
        .ServerPelletEndpoint{"/fakePelletEndpoint/"},
        .ServerPelletSensorIdleTimeoutSeconds{SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS}};

    SensorReaderConfiguration<1> readerConf{
        .DS18B20Ids{
        {0x28, 0xFF, 0x1E, 0xF6, 0xC3, 0x17, 0x04, 0xBA}}};

    DHTNEW am2301(0);
    OneWire oneWire(0);
    DallasTemperature sensors(&oneWire);

    SensorReader<1, 100, 100> reader(&am2301, &oneWire, &sensors, readerConf);

    ESP8266WebServer server(80);
    Hub hub(server, reader, config);

    SUBCASE("Starts at init")
    {
        auto currentState = hub.GetState();
        CHECK(currentState == State::Init);
    }
}

TEST_CASE("Basic hub states")
{
    // Start runtime clock at zero
    setCurrentFakedRuntime(0ul);

    HubConfiguration config{
        .ServerSSID{"SecretHub"},
        .ServerPelletEndpoint{"/fakePelletEndpoint/"},
        .ServerPelletSensorIdleTimeoutSeconds{SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS},
        .ClientMaxNumberOfRetries{1},
        .ClientPelletEndpoint{"pellet_endpoint"},
        .ClientSensorEndpoint{"sensor_endpoint"} 
    };

    SensorReaderConfiguration<1> readerConf{
        .DS18B20Ids{
        {0x28, 0xFF, 0x1E, 0xF6, 0xC3, 0x17, 0x04, 0xBA}}};

    DHTNEW am2301(0);
    OneWire oneWire(0);
    DallasTemperature sensors(&oneWire);

    SensorReader<1, 100, 100> reader(&am2301, &oneWire, &sensors, readerConf);

    ESP8266WebServer server(80);
    Hub hub(server, reader, config);

    // Start up to host mode
    hub.Run();

    SUBCASE("Goes into Host after first call to run")
    {
        auto currentState = hub.GetState();
        CHECK(currentState == State::Host);
    }

    SUBCASE("Switches to Client after idle pellet sensor")
    {
        server.doFakeRequest();

        // Check for pellet activity
        auto currentState = hub.Run();
        CHECK(currentState == State::Host);

        // Fake delay
        setCurrentFakedRuntime(SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS);

        // Check for pellet activity in vain for timeout
        currentState = hub.Run();
        CHECK(currentState == State::Client);
    }

    SUBCASE("Stays in Host mode untill timeout")
    {
        // Check for pellet activity in vain
        auto currentState = hub.Run();

        // Fake short delay
        setCurrentFakedRuntime(SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS - 1u);

        // Check for pellet activity in vain
        currentState = hub.Run();
        CHECK(currentState == State::Host);
    }

    SUBCASE("A pellet request sets a new timeout point 1")
    {
        // Check for pellet activity in vain
        auto currentState = hub.Run();

        // Fake short delay
        setCurrentFakedRuntime(SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS - 1ul);

        // Fake reqest to server
        server.doFakeRequest();

        // Check for pellet activity and handle request
        currentState = hub.Run();

        // Fake another short delay past initial timeout
        setCurrentFakedRuntime(SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS + 1ul);

        // Check for pellet activity and stay in host
        currentState = hub.Run();
        CHECK(currentState == State::Host);
    }

    SUBCASE("A pellet request sets a new timeout point 2")
    {
        // Check for pellet activity in vain
        auto currentState = hub.Run();

        // Fake short delay
        setCurrentFakedRuntime(SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS - 1ul);

        // Fake reqest to server
        server.doFakeRequest();

        // Check for pellet activity and handle request
        currentState = hub.Run();

        // Fake another delay past initial timeout and new timeout
        setCurrentFakedRuntime(2 * SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS - 1ul);

        // Check for pellet activity in vain and switch to Client
        currentState = hub.Run();
        CHECK(currentState == State::Client);
    }

    SUBCASE("Sends pellet data after idle")
    {
        // Check for pellet activity in vain
        auto currentState = hub.Run();

        // Fake short delay
        setCurrentFakedRuntime(SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS - 1ul);

        // Fake reqest to server
        server.doFakeRequest();

        // Check for pellet activity and handle request
        currentState = hub.Run();

        // Fake another delay past initial timeout and new timeout
        setCurrentFakedRuntime(2 * SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS - 1ul);

        // Check for pellet activity in vain and switch to Client
        currentState = hub.Run();
        CHECK(currentState == State::Client);

        // Send pellet data as client - go back to host
        currentState = hub.Run();

        CHECK(fakeReqeuestReceiver.numberOfPelletPulses == 1);
        CHECK(currentState == State::Host);
    }
}

TEST_CASE("Sensor reading and sending")
{
    // Start runtime clock at zero
    setCurrentFakedRuntime(0ul);

    const auto secondsBetweenReads = 3ul;
    const auto numberOfSamples = 3ul;
    const auto numberOfDS18B20s = 2ul;

    HubConfiguration config{
        .ServerSSID{"SecretHub"},
        .ServerPelletEndpoint{"/fakePelletEndpoint/"},
        .ServerPelletSensorIdleTimeoutSeconds{SERVER_PELLET_SENSOR_IDLE_TIMEOUT_SECONDS},
        .ClientMaxNumberOfRetries{1},
        .ClientPelletEndpoint{"pellet_endpoint"},
        .ClientSensorEndpoint{"sensor_endpoint"} 
    };

    SensorReaderConfiguration<numberOfDS18B20s> readerConf{
        .DS18B20Ids{
        {0x28, 0xFF, 0x1E, 0xF6, 0xC3, 0x17, 0x04, 0xBA},
        {0x28, 0xFF, 0x1E, 0xF6, 0xC3, 0x17, 0x04, 0xBA}}};

    DHTNEW am2301(0);
    OneWire oneWire(0);
    DallasTemperature sensors(&oneWire);

    am2301.SetFakeStepupPerRead(true);
    sensors.SetFakeStepupPerRead(true);

    SensorReader<numberOfDS18B20s, numberOfSamples, secondsBetweenReads> reader(&am2301, &oneWire, &sensors, readerConf);

    ESP8266WebServer server(80);
    Hub hub(server, reader, config);

    // Run once to get into Host mode
    hub.Run();
    
    SUBCASE("Does not read within read interval")
    {
        // Fake short delay
        setCurrentFakedRuntime(secondsBetweenReads);

        hub.Run();

        SensorStatistics data = reader.GetData();

        CHECK(data.AM2301Temp_P5 == 0.f);
        CHECK(data.AM2301Temp_P50 == 0.f);
        CHECK(data.AM2301Temp_P95 == 0.f);
        CHECK(data.AM2301Humid_P5 == 0.f);
        CHECK(data.AM2301Humid_P50 == 0.f);
        CHECK(data.AM2301Humid_P95 == 0.f);
        CHECK(data.AM2301_Errors == numberOfSamples);
        CHECK(data.DS18B20_Errors[0] == numberOfSamples);
        CHECK(data.DS18B20_Errors[1] == numberOfSamples);
        CHECK(data.DS18B20_P5[0] == 0.f);
        CHECK(data.DS18B20_P5[1] == 0.f);
        CHECK(data.DS18B20_P50[0] == 0.f);
        CHECK(data.DS18B20_P50[1] == 0.f);
        CHECK(data.DS18B20_P95[0] == 0.f);
        CHECK(data.DS18B20_P95[1] == 0.f);
    }

    SUBCASE("Does read after read interval")
    {
        // Fake delay
        setCurrentFakedRuntime(secondsBetweenReads + 1ul);

        hub.Run();

        SensorStatistics data = reader.GetData();

        CHECK(data.AM2301Temp_P5 == 0.f);
        CHECK(data.AM2301Temp_P50 == 0.f);
        CHECK(data.AM2301Temp_P95 == 0.f);
        CHECK(data.AM2301Humid_P5 == 0.f);
        CHECK(data.AM2301Humid_P50 == 0.f);
        CHECK(data.AM2301Humid_P95 == 0.f);
        CHECK(data.AM2301_Errors == numberOfSamples - 1);
        CHECK(data.DS18B20_Errors[0] == numberOfSamples - 1);
        CHECK(data.DS18B20_Errors[1] == numberOfSamples - 1);
        CHECK(data.DS18B20_P5[0] == 0.f);
        CHECK(data.DS18B20_P5[1] == 1.f);
        CHECK(data.DS18B20_P50[0] == 0.f);
        CHECK(data.DS18B20_P50[1] == 1.f);
        CHECK(data.DS18B20_P95[0] == 0.f);
        CHECK(data.DS18B20_P95[1] == 1.f);
    }

    SUBCASE("Does read again after 2 read intervals")
    {
        // Fake delay
        setCurrentFakedRuntime(secondsBetweenReads + 1ul);
        hub.Run();
        
        // Fake delay
        setCurrentFakedRuntime(2*secondsBetweenReads + 2ul);
        hub.Run();

        SensorStatistics data = reader.GetData();

        CHECK(data.AM2301Temp_P5 == 0.f);
        CHECK(data.AM2301Temp_P50 == 0.f);
        CHECK(data.AM2301Temp_P95 == 1.f);
        CHECK(data.AM2301Humid_P5 == 0.f);
        CHECK(data.AM2301Humid_P50 == 0.f);
        CHECK(data.AM2301Humid_P95 == 1.f);
        CHECK(data.AM2301_Errors == numberOfSamples - 2);
        CHECK(data.DS18B20_Errors[0] == numberOfSamples - 2);
        CHECK(data.DS18B20_Errors[1] == numberOfSamples - 2);
        CHECK(data.DS18B20_P5[0] == 0.f);
        CHECK(data.DS18B20_P5[1] == 1.f);
        CHECK(data.DS18B20_P50[0] == 0.f);
        CHECK(data.DS18B20_P50[1] == 1.f);
        CHECK(data.DS18B20_P95[0] == 2.f);
        CHECK(data.DS18B20_P95[1] == 3.f);
    }

    SUBCASE("Does read again after 3 read intervals")
    {
        // Fake delay
        setCurrentFakedRuntime(secondsBetweenReads + 1ul);
        hub.Run();
        
        // Fake delay
        setCurrentFakedRuntime(2*secondsBetweenReads + 2ul);
        hub.Run();

        // Fake delay
        setCurrentFakedRuntime(3*secondsBetweenReads + 3ul);
        hub.Run();

        SensorStatistics data = reader.GetData();

        CHECK(data.AM2301Temp_P5 == 0.f);
        CHECK(data.AM2301Temp_P50 == 0.f);
        CHECK(data.AM2301Temp_P95 == 2.f);
        CHECK(data.AM2301Humid_P5 == 0.f);
        CHECK(data.AM2301Humid_P50 == 0.f);
        CHECK(data.AM2301Humid_P95 == 2.f);
        CHECK(data.AM2301_Errors == 0);
        CHECK(data.DS18B20_Errors[0] == 0);
        CHECK(data.DS18B20_Errors[1] == 0);
        CHECK(data.DS18B20_P5[0] == 0.f);
        CHECK(data.DS18B20_P5[1] == 1.f);
        CHECK(data.DS18B20_P50[0] == 0.f);
        CHECK(data.DS18B20_P50[1] == 1.f);
        CHECK(data.DS18B20_P95[0] == 4.f);
        CHECK(data.DS18B20_P95[1] == 5.f);
    }

    SUBCASE("Sends data when buffer is full")
    {
        // Fake delay
        setCurrentFakedRuntime(secondsBetweenReads + 1ul);
        hub.Run();
        
        // Fake delay
        setCurrentFakedRuntime(2*secondsBetweenReads + 2ul);
        hub.Run();

        // Fake delay
        setCurrentFakedRuntime(3*secondsBetweenReads + 3ul);
        auto state = hub.Run();

        CHECK(state == State::Client);

        // Send data as client - go back to host mode
        auto currentState = hub.Run();

        CHECK(fakeReqeuestReceiver.numberOfSensorRequests == 1);
        CHECK(currentState == State::Host);
    }
}

TEST_CASE("Fakes")
{
    SUBCASE("millis() fake can be set 1")
    {
        const auto expectedSeconds = 1234ul;
        setCurrentFakedRuntime(expectedSeconds);
        auto actual = millis();

        CHECK(actual == expectedSeconds * 1000ul);
    }

    SUBCASE("millis() fake can be set 2")
    {
        const auto expectedSeconds = 852340ul;
        setCurrentFakedRuntime(expectedSeconds);
        auto actual = millis();

        CHECK(actual == expectedSeconds * 1000ul);
    }
}