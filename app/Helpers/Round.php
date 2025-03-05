<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class Round
{
    public static function roundToNearest($number)
    {
        $lastThreeDigits = $number % 1000;
        return ($lastThreeDigits < 500)
            ? $number - $lastThreeDigits + 500
            : $number - $lastThreeDigits + 1000;
    }

    // $numbers = [6540, 6300, 6700, 6010, 6780];
    // $roundedNumbers = array_map('roundToNearest', $numbers);

    // print_r($roundedNumbers);



}
