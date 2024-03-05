<?php

class ArrayReduction
{
    public static function Mean($arr, $select)
    {
        return array_reduce(array_map($select, $arr), function ($carry, $val) 
        {
            echo '</br>summing value: ' . $val . '</br>';
            return $carry = $carry + $val;
        }, 0) / count($arr);
    }
}

?>