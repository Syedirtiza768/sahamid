<?php
// session.inc - Session management

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set RootPath if needed
if (!isset($RootPath)) {
    $RootPath = '/sahamid'; // Adjust this to your actual root path
}

// Don't store DB connection in session - we'll create it fresh each time
?>