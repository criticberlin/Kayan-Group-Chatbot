<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;

class ExcelParserService
{
    /**
     * Stream rows from Excel file without loading entire file into memory.
     * 
     * @param string $filePath Absolute path to Excel file
     * @return \Generator
     */
    public function streamRows(string $filePath): \Generator
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Use appropriate reader based on file extension
        $reader = match ($extension) {
            'xlsx' => IOFactory::createReader('Xlsx'),
            'xls' => IOFactory::createReader('Xls'),
            'csv' => IOFactory::createReader('Csv'),
            default => throw new \Exception("Unsupported file format: {$extension}"),
        };

        // Configure reader for memory efficiency
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        // Load spreadsheet
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Get highest row and column
        $highestRow = $worksheet->getHighestDataRow();
        $highestColumn = $worksheet->getHighestDataColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        // Stream rows one at a time
        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];

            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                // PhpSpreadsheet 5.x: use getCell() with column letter + row number
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $cell = $worksheet->getCell($columnLetter . $row);
                $rowData[] = $this->getCellValue($cell);
            }

            yield $row => $rowData;

            // Clear row from memory after processing
            unset($rowData);
        }

        // Clean up
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    /**
     * Get cell value with type handling.
     */
    protected function getCellValue($cell)
    {
        if ($cell === null) {
            return null;
        }

        $value = $cell->getValue();

        // Handle dates
        if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    ->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return $value;
            }
        }

        // Handle formulas
        if ($cell->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA) {
            try {
                return $cell->getCalculatedValue();
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Get header row from Excel file.
     */
    public function getHeaders(string $filePath): array
    {
        $generator = $this->streamRows($filePath);
        $generator->rewind();

        if ($generator->valid()) {
            return $generator->current();
        }

        return [];
    }

    /**
     * Count total rows in Excel file.
     */
    public function countRows(string $filePath): int
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $reader = match ($extension) {
            'xlsx' => IOFactory::createReader('Xlsx'),
            'xls' => IOFactory::createReader('Xls'),
            'csv' => IOFactory::createReader('Csv'),
            default => throw new \Exception("Unsupported file format: {$extension}"),
        };

        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $count = $spreadsheet->getActiveSheet()->getHighestDataRow();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $count;
    }

    /**
     * Parse Excel file to array (use only for small files).
     */
    public function toArray(string $filePath, bool $includeHeader = true): array
    {
        $rows = [];

        foreach ($this->streamRows($filePath) as $index => $row) {
            if (!$includeHeader && $index === 1) {
                continue;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Map row data to associative array using headers.
     */
    public function mapRowToHeaders(array $row, array $headers): array
    {
        $mapped = [];

        foreach ($headers as $index => $header) {
            $mapped[$header] = $row[$index] ?? null;
        }

        return $mapped;
    }

    /**
     * Validate row structure against expected columns.
     */
    public function validateRowStructure(array $row, array $expectedColumns): array
    {
        $errors = [];

        foreach ($expectedColumns as $column => $rules) {
            $value = $row[$column] ?? null;

            // Required check
            if (isset($rules['required']) && $rules['required'] && empty($value)) {
                $errors[] = "Column '{$column}' is required";
            }

            // Type check
            if (isset($rules['type']) && !empty($value)) {
                $valid = match ($rules['type']) {
                    'string' => is_string($value),
                    'numeric' => is_numeric($value),
                    'integer' => is_numeric($value) && (int) $value == $value,
                    'date' => $this->isValidDate($value),
                    'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
                    default => true,
                };

                if (!$valid) {
                    $errors[] = "Column '{$column}' must be of type {$rules['type']}";
                }
            }

            // Max length check
            if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                $errors[] = "Column '{$column}' exceeds maximum length of {$rules['max_length']}";
            }
        }

        return $errors;
    }

    /**
     * Check if value is a valid date.
     */
    protected function isValidDate($value): bool
    {
        if (empty($value)) {
            return false;
        }

        try {
            new \DateTime($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
