<?php
function encryptPassword($plainPassword) {
    return password_hash($plainPassword, PASSWORD_BCRYPT);
}

function comparePasswords($plainPassword, $hashedPassword) {
    return password_verify($plainPassword, $hashedPassword);
}


function createUserSession($userId, $username) {
    // Store user information in session
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;

    // Set a cookie with the username (expires in 1 hour)
    setcookie("username", $username, time() + 3600, "/"); // "/" means available across the entire site
}
?>