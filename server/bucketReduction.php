<?php

require_once('utils.php');

class BucketReduction
{
    public static function Mean($arr, $transform)
    {
        return array_map(function ($key) use ($arr, $transform)
        { 
            echo json_encode($arr[$key]);
           ArrayReduction::Mean($arr[$key], $transform); 
        }, 
        array_keys($arr));
    }
}

?>