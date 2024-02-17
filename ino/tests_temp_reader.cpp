#include "helper.h"

#define DOCTEST_CONFIG_IMPLEMENT_WITH_MAIN
#include "doctest/doctest.h"

TEST_CASE("tempInRange finds correct error values")
{
    CHECK(tempInRange(0.f) == true);
    CHECK(tempInRange(10.f) == true);
    CHECK(tempInRange(84.f) == true);
    CHECK(tempInRange(86.f) == true);
    CHECK(tempInRange(100.f) == true);
    CHECK(tempInRange(-10.f) == true);

    CHECK(tempInRange(85.f) == false);
    CHECK(tempInRange(-127.f) == false);
}

TEST_CASE("getScaledP5Index returns correct index")
{
    CHECK(getScaledP5Index(200) == 8);
    CHECK(getScaledP5Index(100) == 4);
    CHECK(getScaledP5Index(50) == 2);
    CHECK(getScaledP5Index(25) == 1);
    CHECK(getScaledP5Index(10) == 0);
    CHECK(getScaledP5Index(1) == 0);
    CHECK(getScaledP5Index(0) == 0);
}

TEST_CASE("getScaledP50Index returns correct index")
{
    CHECK(getScaledP50Index(200) == 99);
    CHECK(getScaledP50Index(100) == 49);
    CHECK(getScaledP50Index(50) == 24);
    CHECK(getScaledP50Index(10) == 4);
    CHECK(getScaledP50Index(2) == 0);
    CHECK(getScaledP50Index(1) == 0);
    CHECK(getScaledP50Index(0) == 0);
}

TEST_CASE("getScaledP95Index returns correct index")
{
    CHECK(getScaledP95Index(200) == 189);
    CHECK(getScaledP95Index(100) == 94);
    CHECK(getScaledP95Index(50) == 47);
    CHECK(getScaledP95Index(10) == 9);
    CHECK(getScaledP95Index(2) == 1);
    CHECK(getScaledP95Index(1) == 0);
}

TEST_CASE("sortArray sorts int array")
{
    int arr[5] = {4, 2, 10, 6, -2};
    sortArray(arr, 5);

    CHECK(arr[0] == -2);
    CHECK(arr[1] == 2);
    CHECK(arr[2] == 4);
    CHECK(arr[3] == 6);
    CHECK(arr[4] == 10);
}

TEST_CASE("sortArray sorts float array")
{
    float arr[5] = {4.f, 2.f, 10.f, 6.f, -2.f};
    sortArray(arr, 5);

    CHECK(arr[0] == -2.f);
    CHECK(arr[1] == 2.f);
    CHECK(arr[2] == 4.f);
    CHECK(arr[3] == 6.f);
    CHECK(arr[4] == 10.f);
}

TEST_CASE("sortArray sorts float with length 1")
{
    float arr[1] = {4.f};
    sortArray(arr, 1);

    CHECK(arr[0] == 4.f);
}

TEST_CASE("sortArray only sorts beginning of float array")
{
    float arr[5] = {4.f, 2.f, 10.f, 6.f, -2.f};
    sortArray(arr, 3);

    CHECK(arr[0] == 2.f);
    CHECK(arr[1] == 4.f);
    CHECK(arr[2] == 10.f);
    CHECK(arr[3] == 6.f);
    CHECK(arr[4] == -2.f);
}

TEST_CASE("sortArray only doesn't sort if length is zero")
{
    float arr[5] = {4.f, 2.f, 10.f, 6.f, -2.f};
    sortArray(arr, 0);

    CHECK(arr[0] == 4.f);
    CHECK(arr[1] == 2.f);
    CHECK(arr[2] == 10.f);
    CHECK(arr[3] == 6.f);
    CHECK(arr[4] == -2.f);
}