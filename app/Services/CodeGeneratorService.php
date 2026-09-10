<?php

namespace App\Services;

class CodeGeneratorService
{
    public static function generateBarcode(
        string $modelClass,
        string $column = 'barcode',
        string $prefix = 'A',
        int $padLength = 3
    ): string {
        $lastRecord = $modelClass::whereNotNull($column)
            ->where($column, 'LIKE', $prefix.'%')
            ->latest('id')
            ->first();

        $nextNumber = 1;

        if ($lastRecord) {
            $nextNumber = ((int) preg_replace('/[^0-9]/', '', $lastRecord->{$column})) + 1;
        }

        do {
            $code = $prefix.str_pad($nextNumber, $padLength, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while ($modelClass::where($column, $code)->exists());

        return $code;
    }

    public static function generateMonthBasedCode(string $modelClass, string $column, ?string $date = null, string $dateColumn = 'created_at', int $padLength = 3): string
    {
        $timestamp = $date ? strtotime($date) : time();
        $monthPrefix = strtoupper(date('M', $timestamp));

        $lastRecord = $modelClass::where($column, 'LIKE', $monthPrefix.'%')
            ->whereYear($dateColumn, date('Y', $timestamp))
            ->whereMonth($dateColumn, date('m', $timestamp))
            ->latest('id')
            ->first();

        $nextNumber = 1;

        if ($lastRecord) {
            $nextNumber = ((int) preg_replace('/[^0-9]/', '', $lastRecord->{$column})) + 1;
        }

        do {
            $code = $monthPrefix.str_pad($nextNumber, $padLength, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while ($modelClass::where($column, $code)->exists());

        return $code;
    }
}
