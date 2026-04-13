#!/usr/bin/env php
<?php

declare(strict_types=1);

$appDir = realpath(__DIR__.'/..');

if ($appDir === false) {
    fwrite(STDERR, "Unable to resolve application directory.\n");
    exit(1);
}

if (!chdir($appDir)) {
    fwrite(STDERR, "Unable to change working directory to {$appDir}.\n");
    exit(1);
}

$phpBin = trim((string) getenv('PHP_BIN'));
if ($phpBin === '') {
    $phpBin = PHP_BINARY;
}
if ($phpBin === '' || !is_file($phpBin)) {
    $resolvedPhp = trim((string) shell_exec('command -v php 2>/dev/null'));
    if ($resolvedPhp !== '') {
        $phpBin = $resolvedPhp;
    }
}

if ($phpBin === '') {
    fwrite(STDERR, "php binary not found\n");
    exit(1);
}

$artisan = $appDir.'/artisan';
if (!is_file($artisan)) {
    fwrite(STDERR, "artisan file not found at {$artisan}\n");
    exit(1);
}

$logDir = $appDir.'/storage/logs';
if (!is_dir($logDir) && !mkdir($logDir, 0775, true) && !is_dir($logDir)) {
    fwrite(STDERR, "Unable to create log directory at {$logDir}\n");
    exit(1);
}

$commands = [
    [
        'args' => ['schedule:run', '--no-interaction'],
        'log' => $logDir.'/cron-schedule.log',
    ],
    [
        'args' => ['queue:work', '--once', '--queue=default', '--tries=3', '--no-interaction'],
        'log' => $logDir.'/cron-queue.log',
    ],
];

foreach ($commands as $entry) {
    $command = implode(' ', array_map(
        static fn (string $part): string => escapeshellarg($part),
        array_merge([$phpBin, $artisan], $entry['args'])
    ));
    $command .= ' >> '.escapeshellarg($entry['log']).' 2>&1';

    $exitCode = 0;
    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, "Command failed ({$exitCode}): {$command}\n");
        exit($exitCode);
    }
}

exit(0);
