<?php
// router.php

include 'db_connect.php';
include 'databaseFunctions.php';

// Define routes and their corresponding handler functions
$routes = [
    '/' => 'homeHandler',
    '/login' => 'LoginHandler',

];

// Get the current path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Check if the path exists in the routes
if (array_key_exists($path, $routes)) {
    call_user_func($routes[$path]);
} else {
    // If the route does not exist, show a 404 error
    http_response_code(404);
    echo "404 Not Found";
}

// Handler functions
// Handler functions
function homeHandler() {
    // Get all books
    $books = GetAllBooks();
    // Set the content type to application/json
    header('Content-Type: application/json');
    // Return the books as JSON
    echo json_encode($books);
}


function LoginHandler() {
    // $email = $_GET['email'] ?? '';
    // $userIds = GetUserIdByEmail($email);
    // echo json_encode($userIds);
}

function getUserByUsernameHandler() {
    $username = $_GET['username'] ?? '';
    $userIds = GetUserIdByUserName($username);
    echo json_encode($userIds);
}
?>