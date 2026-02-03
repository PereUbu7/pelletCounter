<?php

class ArrayReduction
{
    public static function Mean($arr, $select)
    {
        return array_reduce(array_map($select, $arr), function ($carry, $val) 
        {
            return $carry = $carry + $val;
        }, 0) / count($arr);
    }

    public static function MeanSlope($arr, $select)
    {
        $n = count($arr);
        if ($n < 2) {
            return 0; // Not enough data points to calculate slope
        }

        $x_mean = ($n - 1) / 2; // Mean of x values (0, 1, 2, ..., n-1)
        $y_values = array_map($select, $arr);
        $y_mean = array_sum($y_values) / $n;

        $numerator = 0;
        $denominator = 0;

        for ($i = 0; $i < $n; $i++) {
            $numerator += ($i - $x_mean) * ($y_values[$i] - $y_mean);
            $denominator += ($i - $x_mean) ** 2;
        }

        return ($denominator != 0) ? ($numerator / $denominator) : 0;
    }
}

?>