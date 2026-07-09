<?php

declare(strict_types=1);

/**
 * Copy Nova published assets, the workbench SQLite database, and the
 * storage folder from the testbench-core skeleton into the testbench-dusk
 * skeleton so Dusk browser tests can find them. Recreates the public/storage
 * symlink in the Dusk skeleton.
 *
 * Run after `testbench workbench:build`.
 */
$root = dirname(__DIR__);
$core = $root.'/vendor/orchestra/testbench-core/laravel';
$dusk = $root.'/vendor/orchestra/testbench-dusk/laravel';

if (! is_dir($core) || ! is_dir($dusk)) {
    fwrite(STDERR, "Testbench skeletons not found — run `composer install` first.\n");
    exit(1);
}

$copyDir = static function (string $from, string $to) use (&$copyDir): void {
    if (! is_dir($from)) {
        return;
    }

    @mkdir($to, 0755, true);

    foreach (scandir($from) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $src = $from.DIRECTORY_SEPARATOR.$entry;
        $dst = $to.DIRECTORY_SEPARATOR.$entry;

        if (is_link($src)) {
            continue;
        }

        is_dir($src) ? $copyDir($src, $dst) : @copy($src, $dst);
    }
};

$copyDir("$core/public/vendor", "$dusk/public/vendor");
$copyDir("$core/storage/app", "$dusk/storage/app");

@mkdir("$dusk/database", 0755, true);
if (is_file("$core/database/database.sqlite")) {
    @copy("$core/database/database.sqlite", "$dusk/database/database.sqlite");
}

$link = "$dusk/public/storage";
$target = "$dusk/storage/app/public";
if (! is_dir($target)) {
    @mkdir($target, 0755, true);
}
if (file_exists($link) || is_link($link)) {
    @unlink($link);
}
@symlink($target, $link);

foreach ([$core, $dusk] as $skeleton) {
    $views = $skeleton.'/storage/framework/views';
    if (! is_dir($views)) {
        continue;
    }

    foreach (glob($views.'/*.php') ?: [] as $view) {
        @unlink($view);
    }
}

echo "Dusk skeleton synced from testbench-core.\n";
