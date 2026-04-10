<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$target = 'admin/products.php';

if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
    $target .= '?' . $_SERVER['QUERY_STRING'];
}

redirect($target);
