<?php
// Start the session if it's not already started
// This is crucial to access session variables.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
// This will delete the session file on the server.
session_destroy();

// Clear the session cookie
// This ensures the browser no longer has a session ID.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to the home page or login page
// Assuming your main home page is at the root of your application, e.g., index.html
// Adjust the path as needed based on your actual home page location.
// For example, if your home page is 'home.html' in a 'home_dashboard' folder, it might be '../../home_dashboard/home.html'
header("Location: ../index.html"); // Adjust this path if your main home page is elsewhere
exit(); // Always exit after a header redirect
?>
