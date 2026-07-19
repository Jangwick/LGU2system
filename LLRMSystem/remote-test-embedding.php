<?php
require '/home/llrm.spvalenzuela.com/public_html/LLRMSystem/modules/core/config/config.php';
require '/home/llrm.spvalenzuela.com/public_html/LLRMSystem/modules/search/services/EmbeddingService.php';
$e = new EmbeddingService();
$r = $e->generateEmbedding('test');
echo json_encode(array('ok' => is_array($r), 'error' => $e->getLastError(), 'http' => $e->getLastHttpCode(), 'model' => GEMINI_EMBEDDING_MODEL));
