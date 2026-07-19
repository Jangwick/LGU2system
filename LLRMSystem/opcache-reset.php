<?php
if (function_exists('opcache_reset')) opcache_reset();
echo json_encode(['cleared' => true]);
