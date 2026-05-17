<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Chunk Read Filter untuk membatasi memory usage saat membaca Excel besar.
 * 
 * Filter ini memungkinkan PhpSpreadsheet hanya membaca baris yang diperlukan,
 * bukan seluruh sheet sekaligus.
 */
class ChunkReadFilter implements IReadFilter
{
    private int $startRow = 0;
    private int $endRow = 0;

    /**
     * Set range baris yang akan dibaca.
     */
    public function setRows(int $startRow, int $chunkSize): void
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize - 1;
    }

    /**
     * Tentukan apakah cell harus dibaca atau tidak.
     */
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        // Jika tidak ada filter (startRow = 0), baca semua
        if ($this->startRow === 0 && $this->endRow === 0) {
            return true;
        }

        // Hanya baca baris dalam range yang ditentukan
        return ($row >= $this->startRow && $row <= $this->endRow);
    }
}
