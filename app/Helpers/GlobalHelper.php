<?php

use Livewire\Component;
use App\Helpers\FilePathHelper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Repositories\MasterData\Studio\StudioRepository;

if (!function_exists('getAccessStudio')) {
    function getAccessStudio()
    {
        $studios = auth()->user()->studios;
        if(!$studios->count())
        {
            return StudioRepository::all()->map(function ($studio) {
                return [
                    'id' => Crypt::encrypt($studio->id),
                    'name' => $studio->name,
                    'city' => $studio->city,
                ];
            });
        }
        return $studios->map(function ($studio) {
            return [
                'id' => Crypt::encrypt($studio->id),
                'name' => $studio->name,
                'city' => $studio->city,
            ];
        });
    }
}

if (!function_exists('calculatedDiscount')) {
    function calculatedDiscount($amount, $percentage) {
        return round($amount * ($percentage / 100));
    }
}
if (!function_exists('generateUrl')) {
    function generateUrl($url, $path) {
        return Storage::url($path . $url);
    }
}
if (!function_exists('simple_encrypt')) {
    function simple_encrypt($value) {
        $key = hash('sha256', env('APP_KEY'), true);
        $iv = substr($key, 0, 16); // Fixed IV
        return base64_encode(openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv));
    }
}
if (!function_exists('simple_decrypt')) {
    function simple_decrypt($encryptedValue) {
        $key = hash('sha256', env('APP_KEY'), true);
        $iv = substr($key, 0, 16);
        return openssl_decrypt(base64_decode($encryptedValue), 'aes-256-cbc', $key, 0, $iv);
    }
}

if (!function_exists('consoleLog')) {
    function consoleLog(Component $component, $data)
    {
        $component->dispatch('consoleLog', $data);
    }
}

if (!function_exists('calculatedAdminFee')) {
    function calculatedAdminFee($amount, $percentage)
    {
        return ceil(($amount / (1 - ($percentage / 100))) - $amount);
    }
}

if (!function_exists('imaskToValue')) {
    function imaskToValue($data)
    {
        return str($data)->replace('.', '')->replace(',', '.')->toFloat();
    }
}

if (!function_exists('valueToImask')) {
    function valueToImask($data)
    {
        return str($data)->replace('.', ',')->toString();
    }
}

if (!function_exists('numberFormat')) {
    function numberFormat($number, $decimalPoin = 2)
    {
        $decimalPoin = fmod($number, 1) !== 0.00 ? $decimalPoin : 0;
        return number_format($number, $decimalPoin, ",", ".");
    }
}

if (!function_exists('denominator')) {
    function denominator($data)
    {
        $nilai = abs($data);
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " " . $huruf[$nilai];
        } else if ($nilai < 20) {
            $temp = denominator($nilai - 10) . " Belas";
        } else if ($nilai < 100) {
            $temp = denominator($nilai / 10) . " Puluh" . denominator($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " Seratus" . denominator($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = denominator($nilai / 100) . " Ratus" . denominator($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " Seribu" . denominator($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = denominator($nilai / 1000) . " Ribu" . denominator($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = denominator($nilai / 1000000) . " Juta" . denominator($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = denominator($nilai / 1000000000) . " Milyar" . denominator(fmod($nilai, 1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = denominator($nilai / 1000000000000) . " Trilyun" . denominator(fmod($nilai, 1000000000000));
        }
        return $temp;
    }
}
