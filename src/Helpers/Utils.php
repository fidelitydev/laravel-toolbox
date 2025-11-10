<?php

namespace Knighttower\Toolbox\Helpers;

use SplTempFileObject;

class Utils
{
    /**
     * Parse CSV data from a string and return as an array of associative arrays.
     *
     * @usage Utils::parseCsvFromString($csvString);
     */
    public static function parseCsvFromString(string $csvString): array
    {
        $tempFile = new SplTempFileObject;
        $tempFile->fwrite($csvString);
        $tempFile->rewind();

        $rows = [];
        $headers = null;
        $isFirstRow = true;

        while (! $tempFile->eof()) {
            $row = $tempFile->fgetcsv();
            if ($row !== false && $row !== [null]) {
                if ($isFirstRow) {
                    $headers = $row;
                    $isFirstRow = false;

                    continue;
                }

                // Map row to associative array using headers
                $associativeRow = [];
                foreach ($headers as $index => $header) {
                    $associativeRow[$header] = $row[$index] ?? null;
                }

                // Skip empty rows (all values are null or empty)
                if (! empty(array_filter($associativeRow, fn($value) => ! empty($value)))) {
                    $rows[] = $associativeRow;
                }
            }
        }

        return $rows;
    }

    /**
     * Map data from pointers based on a given mapping.
     *
     * @usage Utils::mapDataFromPointers($mapping, $sourceData);
     *
     * @example
     * $mapping = [
     *     'name' => 'user.name',
     *     'email' => 'user.contact.email',
     * ];
     * $sourceData = [
     *     'user' => [
     *         'name' => 'John Doe',
     *         'contact' => [
     *             'email' => 'john.doe@example.com',
     *         ],
     *     ],
     * ];
     */
    public static function mapDataFromPointers(array $mapping, array $sourceData): array
    {
        $result = [];

        foreach ($mapping as $field => $pointer) {
            $parts = explode('.', $pointer);
            $value = $sourceData;

            foreach ($parts as $key) {
                $value = $value[$key] ?? null;

                if ($value === null) {
                    break;
                }
            }

            $result[$field] = $value;
        }

        return $result;
    }


    /**
     * Convert various data types to an array.
     *
     * @usage Utils::toArray($data);
     */
    public static function toArray($data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->toArray();
        }

        if (is_object($data)) {
            return (array) $data;
        }

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return [$data];
        }

        return [];
    }
}