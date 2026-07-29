<?php
require_once __DIR__ . '/modules/core/config/config.php';
echo 'HOME: ' . var_export(getenv('HOME'), true) . PHP_EOL;
echo 'PATH: ' . var_export(getenv('PATH'), true) . PHP_EOL;
putenv('LD_LIBRARY_PATH=/home/llrm.spvalenzuela.com/tesseract/usr/lib/x86_64-linux-gnu:/home/llrm.spvalenzuela.com/lib');
putenv('TESSDATA_PREFIX=/home/llrm.spvalenzuela.com/tesseract/usr/share/tesseract-ocr/4.00/tessdata');
exec('/home/llrm.spvalenzuela.com/bin/tesseract --version 2>&1', $out, $rc);
echo 'RC: ' . $rc . PHP_EOL;
echo implode(PHP_EOL, $out) . PHP_EOL;
