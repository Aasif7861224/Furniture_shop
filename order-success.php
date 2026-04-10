<?php
require_once __DIR__ . '/includes/bootstrap.php';
redirect('order_success.php' . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : ''));
