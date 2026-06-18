<?php
/**
 * DNHS Hub - Index Page
 * 
 * Root redirect to login or dashboard
 */

require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
} else {
    redirect(APP_URL . '/login.php');
}
