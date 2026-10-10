<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Convert integer/decimal number to Indonesian words (Terbilang).
     */
    public static function terbilang(float|int $number): string
    {
        $number = abs($number);
        $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $result = '';

        if ($number < 12) {
            $result = ' '.$words[(int) $number];
        } elseif ($number < 20) {
            $result = self::terbilang($number - 10).' Belas';
        } elseif ($number < 100) {
            $result = self::terbilang((int) ($number / 10)).' Puluh'.self::terbilang($number % 10);
        } elseif ($number < 200) {
            $result = ' Seratus'.self::terbilang($number - 100);
        } elseif ($number < 1000) {
            $result = self::terbilang((int) ($number / 100)).' Ratus'.self::terbilang($number % 100);
        } elseif ($number < 2000) {
            $result = ' Seribu'.self::terbilang($number - 1000);
        } elseif ($number < 1000000) {
            $result = self::terbilang((int) ($number / 1000)).' Ribu'.self::terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            $result = self::terbilang((int) ($number / 1000000)).' Juta'.self::terbilang($number % 1000000);
        } elseif ($number < 1000000000000) {
            $result = self::terbilang((int) ($number / 1000000000)).' Miliar'.self::terbilang(fmod($number, 1000000000));
        } elseif ($number < 1000000000000000) {
            $result = self::terbilang((int) ($number / 1000000000000)).' Triliun'.self::terbilang(fmod($number, 1000000000000));
        }

        return trim($result);
    }

    /**
     * Format currency to Rupiah.
     */
    public static function formatRupiah(float|int $amount, bool $withSymbol = true): string
    {
        $fmt = number_format($amount, 0, ',', '.');

        return $withSymbol ? 'Rp '.$fmt : $fmt;
    }
}
