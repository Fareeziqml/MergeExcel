<?php
require __DIR__ . '/../vendor/autoload.php';

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

// Allow big Excel files
ini_set('memory_limit', '4096M');
ini_set('max_execution_time', '600');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sheetName  = !empty($_POST['sheetName']) ? $_POST['sheetName'] : 'Merged';
    $customName = !empty($_POST['customName']) ? $_POST['customName'] : 'Merged_File';
    $normalize  = isset($_POST['normalize']);
    $fileOrder  = isset($_POST['fileOrder']) ? explode(',', $_POST['fileOrder']) : [];

    // Create temporary output file
    $outputFile = sys_get_temp_dir() . '/' . uniqid('merged_', true) . '.xlsx';
    $writer = WriterEntityFactory::createXLSXWriter();
    $writer->openToFile($outputFile);

    $headerWritten = false;

    // Reorder uploaded files if order provided
    $filesToMerge = [];
    if (!empty($fileOrder)) {
        foreach ($fileOrder as $index) {
            if (isset($_FILES['files']['tmp_name'][$index])) {
                $filesToMerge[] = [
                    'tmp_name' => $_FILES['files']['tmp_name'][$index],
                    'name' => $_FILES['files']['name'][$index]
                ];
            }
        }
    } else {
        foreach ($_FILES['files']['tmp_name'] as $i => $tmpFile) {
            $filesToMerge[] = [
                'tmp_name' => $tmpFile,
                'name' => $_FILES['files']['name'][$i]
            ];
        }
    }

    foreach ($filesToMerge as $file) {
        $tmpFile = $file['tmp_name'];
        $originalName = $file['name'];

        if (!is_uploaded_file($tmpFile)) continue;

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Create appropriate reader
        if ($ext === 'xlsx' || $ext === 'xls') {
            $reader = ReaderEntityFactory::createXLSXReader();
        } elseif ($ext === 'csv') {
            $reader = ReaderEntityFactory::createCSVReader();
        } else {
            continue; // skip unsupported
        }

        $reader->open($tmpFile);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cells = $row->toArray();

                // ✅ Convert all non-scalar (e.g. DateTime objects) to string
                $cells = array_map(function($cell) {
                    if (is_object($cell)) {
                        if ($cell instanceof DateTimeInterface) {
                            return $cell->format('Y-m-d H:i:s');
                        }
                        return (string)$cell;
                    }
                    if (is_array($cell)) {
                        return json_encode($cell, JSON_UNESCAPED_UNICODE);
                    }
                    return $cell;
                }, $cells);

                // Optional normalization
                if ($normalize && $rowIndex === 1) {
                    $cells = array_map(fn($h) => strtolower(trim((string)$h)), $cells);
                }

                // Write headers once
                if (!$headerWritten) {
                    $writer->addRow(WriterEntityFactory::createRowFromArray($cells));
                    $headerWritten = true;
                } elseif ($rowIndex === 1) {
                    continue; // Skip headers for subsequent files
                } else {
                    $writer->addRow(WriterEntityFactory::createRowFromArray($cells));
                }
            }
        }

        $reader->close();
    }

    $writer->close();

    // Send merged file to browser
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $customName . '.xlsx"');
    header('Content-Length: ' . filesize($outputFile));
    readfile($outputFile);
    unlink($outputFile);
    exit;
}
?>
