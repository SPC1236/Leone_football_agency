<?php
// Helper functions for the admin panel

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function getDBConnection() {
    require_once '../config/database.php';
    return getConnection();
}
?>