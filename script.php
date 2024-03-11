<?php

// divide o array em duas partes
function merge($left, $right, $option)
{
    $res = array();
    while (count($left) > 0 && count($right) > 0) {
        if ($option == 2) {
            if ($left[0] < $right[0]) {
                $res[] = $right[0];
                $right = array_slice($right, 1);
            } else {
                $res[] = $left[0];
                $left = array_slice($left, 1);
            }
        } else {
            if ($left[0] > $right[0]) {
                $res[] = $right[0];
                $right = array_slice($right, 1);
            } else {
                $res[] = $left[0];
                $left = array_slice($left, 1);
            }
        }

    }
    while (count($left) > 0) {
        $res[] = $left[0];
        $left = array_slice($left, 1);
    }
    while (count($right) > 0) {
        $res[] = $right[0];
        $right = array_slice($right, 1);
    }
    return $res;
}

// ordena o array usando o merge sort
function mergeSort($array, $option)
{
    if (count($array) <= 1)
        return $array;

    $mid = count($array) / 2;
    $left = array_slice($array, 0, $mid);
    $right = array_slice($array, $mid);
    $left = mergeSort($left, $option);
    $right = mergeSort($right, $option);
    return merge($left, $right, $option);
}

// divide o arquivo em blocos, ordena os blocos e salva em arquivos temporários
function sortBlocks($inputFile, $blockSize, $option)
{
    $blockNumber = 0;
    $blocks = [];
    $file = fopen($inputFile, 'r');
    while (!feof($file)) {
        $tempFile = "block_$blockNumber.tmp";
        $tempHandle = fopen($tempFile, 'w');
        $lines = [];
        for ($i = 0; $i < $blockSize && !feof($file); $i++) {
            $line = fgets($file);
            if ($line !== false) {
                $lines[] = intval(trim($line));
            }
        }
        $lines = mergeSort($lines, $option);
        fwrite($tempHandle, implode(PHP_EOL, $lines));
        fclose($tempHandle);
        $blocks[] = $tempFile;
        $blockNumber++;
    }
    fclose($file);
    return $blocks;
}

// mescla os blocos ordenados em um único arquivo
function mergeBlocks($blocks, $outputFile, $option)
{
    $blockHandles = [];
    $blockData = [];
    // Abrir os arquivos de bloco e inicializar os dados
    foreach ($blocks as $block) {
        $blockHandles[$block] = fopen($block, 'r');
        $blockData[$block] = null;
    }
    $outputHandle = fopen($outputFile, 'w');

    while (true) {
        $selectedData = null;
        $selectedBlock = null;
        // Encontrar o menor ou maior
        foreach ($blocks as $block) {
            if ($blockData[$block] === null) {
                $line = fgets($blockHandles[$block]);
                if ($line !== false) {
                    $blockData[$block] = intval(trim($line));
                } else {
                    $blockData[$block] = false;
                }
            }
            if ($blockData[$block] !== false) {
                if ($option == 1 && ($selectedData === null || $blockData[$block] < $selectedData)) {
                    $selectedData = $blockData[$block];
                    $selectedBlock = $block;
                } elseif ($option != 1 && ($selectedData === null || $blockData[$block] > $selectedData)) {
                    $selectedData = $blockData[$block];
                    $selectedBlock = $block;
                }
            }
        }
        if ($selectedData === null) {
            break;
        }

        fwrite($outputHandle, $selectedData . PHP_EOL);
        $blockData[$selectedBlock] = null;
    }
    fclose($outputHandle);
    foreach ($blockHandles as $handle) {
        fclose($handle);
    }
    foreach ($blocks as $block) {
        unlink($block);
    }
}

// Mostra o menu de opções
function showMenu()
{
    echo "Escolha a ordem de classificação:\n";
    echo "1. Crescente\n";
    echo "2. Decrescente\n";
    echo "Opção: ";
}

$inputFile = '/home/aquila/Desktop/teste/input.txt';
$outputFile = 'output.txt';
$blockSize = 1000;

showMenu();
$option = readline();

if ($option == 1) {
    $blocks = sortBlocks($inputFile, $blockSize, 1);
    mergeBlocks($blocks, $outputFile, 1);
} elseif ($option == 2) {
    $blocks = sortBlocks($inputFile, $blockSize, 2);
    mergeBlocks($blocks, $outputFile, 2);
} else {
    echo "Opção inválida.\n";
}


