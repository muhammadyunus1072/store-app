<?php

namespace App\Helpers\General;

use Carbon\Carbon;

class NumberGenerator
{
    const COMPANY_CODE = "TOKO";
    const RESET_TYPE_YEARLY = 1;
    const RESET_TYPE_MONTHLY = 2;
    const SEPARATOR = "/";

    public static function generate(
        $className,
        $code,
        $companyCode = self::COMPANY_CODE,
        $zeroPad = 3,
        $resetType = self::RESET_TYPE_YEARLY,
    ) {
        $dateTime = Carbon::now();
        $day_now = $dateTime->day;
        $month_now = $dateTime->month;
        $year_now = $dateTime->year;

        $lastModel = $className::withTrashed()->select('number')
            ->when($resetType == self::RESET_TYPE_YEARLY, function ($query) use ($year_now) {
                $query->whereYear('created_at', '=', $year_now);
            })
            ->when($resetType == self::RESET_TYPE_MONTHLY, function ($query) use ($year_now, $month_now) {
                $query->whereMonth('created_at', '=', $month_now)
                    ->whereYear('created_at', '=', $year_now);
            })
            ->orderBy('id', 'DESC')
            ->first();

        if (!empty($lastModel)) {
            $lastNumber = intval(explode(self::SEPARATOR, $lastModel->number)[0]);
        } else {
            $lastNumber = 0;
        }

        // Get Current Number
        $currentNumber = strval($lastNumber + 1);
        $currentNumber = str_pad($currentNumber, $zeroPad, "0", STR_PAD_LEFT);

        // Roman Day
        $roman_day = RomanConverter::number2Roman($day_now);
        // Roman Month
        $roman_month = RomanConverter::number2Roman($month_now);

        // Generate Format Number
        if ($companyCode == null) {
            $formattedNumber = "$code/$currentNumber/$roman_day/$roman_month/$year_now";
        } else {
            $formattedNumber = "$currentNumber/$code/$companyCode/$roman_month/$year_now";
        }

        return $formattedNumber;
    }


    public static function simpleYearCode(
        $className,
        $code,
        $date,
        $zeroPad = 6,
    ) {
        $dateTime = Carbon::parse($date);
        $year = substr($dateTime->year, 2);

        $lastModel = $className::withTrashed()->select('number')
            ->orderBy('id', 'DESC')
            ->first();

        if (!empty($lastModel)) {
            $lastNumber = intval(substr($lastModel->number, 4));
        } else {
            $lastNumber = 0;
        }

        // Get Current Number
        $currentNumber = strval($lastNumber + 1);
        $currentNumber = str_pad($currentNumber, $zeroPad, "0", STR_PAD_LEFT);

        return "{$code}{$year}{$currentNumber}";
    }
}
