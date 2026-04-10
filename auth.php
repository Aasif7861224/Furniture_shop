<?php
require_once __DIR__ . '/db.php';

function require_login()
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please login to continue.');
        redirect('login.php');
    }
}

function require_admin()
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please login to continue.');
        redirect('login.php');
    }

    if (!is_admin()) {
        set_flash('error', 'You are not authorized to access that page.');
        redirect('index.php');
    }
}
