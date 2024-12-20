<?php

function login()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $loginPassword = trim($_POST['password'] ?? '');
        
        // Variable to store error messages
        $error = '';

        // Validate form fields
        if (empty($username) || empty($loginPassword)) {
            $error = "Both fields are required.";
        } else {
            // Check if the username is either email or username
            if (checkUser("Username", $username)) {
                $password = GetPassword("Username", $username);
                if (password_verify($loginPassword, $password)) {
                    $user = GetUser("Username", $username);
                    $sessionId = createUserSession($user['Username'], $user['UserId'], $user['Rule']);
                    addUserSession($user['UserId'], $sessionId); // Store session in DB
                    header("Location: home.php?page=home");
                    exit;
                } else {
                    $error = "The password you entered is incorrect.";
                }
            } elseif (checkUser("Email", $username)) {
                $password = GetPassword("Email", $username);
                if (password_verify($loginPassword, $password)) {
                    $user = GetUser("Email", $username);
                    $sessionId = createUserSession($user['Username'], $user['UserId'], $user['Rule']);
                    addUserSession($user['UserId'], $sessionId); // Store session in DB
                    header("Location: home.php?page=home");
                    exit;
                } else {
                    $error = "The password you entered is incorrect.";
                }
            } else {
                $error = "The username or email you entered is incorrect.";
            }
        }

        // Display the error if any
        if ($error) {
            $safeError = htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
            echo "<script>document.getElementById('error-message').innerHTML = '$safeError';</script>";
        }
    }

    // Render the login form
    echo "<form class='form_container' method='post'>";
    echo "<div class='logo_container'></div>";
    echo "<div class='title_container'>";
    echo "<p class='title'>Login to your Account</p>";
    echo "<span class='subtitle'>Get started with our app, just create an account and enjoy the experience.</span>";
    echo "</div>";

    echo "<div class='input_container'>";
    echo "<label class='input_label' for='username'>Username</label>";
    echo "<input placeholder='name@mail.com' title='Input title' name='username' type='text' class='input_field' id='username'>";
    echo "</div>";

    echo "<div class='input_container'>";
    echo "<label class='input_label' for='password'>Password</label>";
    echo "<input placeholder='Password' title='Input title' name='password' type='password' class='input_field' id='password'>";
    echo "</div>";

    // Display error message in the form
    echo "<p id='error-message' style='color:red;'></p><br>";

    echo "<button title='Login' type='submit' class='sign-in_btn'>";
    echo "<span>Sign In</span>";
    echo "</button>";

    echo "<div class='separator'>";
    echo "<hr class='line'>";
    echo "<span>or</span>";
    echo "</div>";

    echo "<p><a href='home.php?page=register' class='register-link'>Register</a></p>";
    echo "</form>";
}



function createUserSession($userId, $username, $rule)
{
    session_start();  // Ensure session is started

    // Store user information in session
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['rule'] = GetUserRule($username);
    $sessionId = session_id();  // Use PHP's built-in session ID generator

    // Set a cookie with the username (expires in 1 hour)
    setcookie("username", $username, time() + 3600, "/"); // "/" means available across the entire site
    setcookie("rule", GetUserRule($username), time() + 3600, "/"); // "/" means available across the entire site
    return $sessionId;

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle login
} elseif (isset($_GET['action']) && $_GET['action'] === 'register') {
    register();
}
function register()
{
    global $conn; // Ensure access to the database connection

    // Render the form
    echo "<form class='form_container' method='post' enctype='multipart/form-data' id='register-form'>";

    echo "<div class='logo_container'></div>";

    echo "<div class='title_container'>";
    echo "<p class='title'>Create Your Account</p>";
    echo "<span class='subtitle'>Sign up to enjoy our features and be part of the community.</span>";
    echo "</div>";

    echo "<div class='input_container'>";
    echo "<label class='input_label' for='username'>Username</label>";
    echo "<input placeholder='Choose a username' title='Input title' name='username' type='text' class='input_field' id='username'>";
    echo "</div>";

    echo "<div class='input_container'>";
    echo "<label class='input_label' for='first_name'>First Name</label>";
    echo "<input placeholder='John' title='Input title' name='first_name' type='text' class='input_field' id='first_name'>";
    echo "</div>";

    echo "<div class='input_container'>";
    echo "<label class='input_label' for='last_name'>Last Name</label>";
    echo "<input placeholder='Doe' title='Input title' name='last_name' type='text' class='input_field' id='last_name'>";
    echo "</div>";

    echo "<div class='input_container'>";
    echo "<label class='input_label' for='email'>Email</label>";
    echo "<input placeholder='name@mail.com' title='Input title' name='email' type='email' class='input_field' id='email'>";
    echo "</div>";

    echo "<div class='input_container'>";
    echo "<label class='input_label' for='password'>Password</label>";
    echo "<input placeholder='Password' title='Input title' name='password' type='password' class='input_field' id='password'>";
    echo "</div>";

    echo "<div class='input_container'>";
    echo "<label class='input_label' for='confirm_password'>Confirm Password</label>";
    echo "<input placeholder='Confirm password' title='Input title' name='confirm_password' type='password' class='input_field' id='confirm_password'>";
    echo "</div>";

    echo "<div class='input_container'>";
    echo "<label class='input_label' for='profile_image'>Profile Image</label>";
    echo "<input type='file' name='profile_image' class='input_field' id='profile_image'>";
    echo "</div>";
    echo "<p id='error-message' style='color:red;'></p><br>";


    echo "<button title='Register' type='submit' class='sign-in_btn'>";
    echo "<span>Register</span>";
    echo "</button>";

    echo "<div class='separator'>";
    echo "<hr class='line'>";
    echo "<span>or</span>";
    echo "</div>";

    echo "<p><a href='home.php?page=login' class='register-link'>Login</a></p>";

    echo "</form>";



    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Collect form inputs
        $username = trim($_POST['username'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // Collect image file (if uploaded)
        $imageFile = $_FILES['profile_image'] ?? null;

        // Backend validation (server-side)
        $error = '';
        if (empty($username) || empty($firstName) || empty($lastName) || empty($password) || empty($confirmPassword) || empty($email)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } 
        if (checkUser("Username", $username)) {
            $error = "Username already exists.";
        } elseif (checkUser("Email", $email)) {
            $error = "Email already exists.";
        }
        // } elseif (!validatePassword($password)) {
        //     $error = "Password must be 6-12 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.";
        // } elseif ($password !== $confirmPassword) {
        //     $error = "Passwords do not match.";
        // }

        if ($error) {
            $safeError = htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
            echo "<script>document.getElementById('error-message').innerHTML = '$safeError';</script>";
        } else {
            // If image is uploaded, pass the image to the CreateUser function
            $imageId = 2; // Default image ID if no image is uploaded
            if ($imageFile && isset($imageFile['tmp_name']) && is_uploaded_file($imageFile['tmp_name'])) {
                // Call a function to handle the image upload and return the image ID
                $imageId = uploadImageToDatabase2($imageFile); // This function should return the ImageId
            }

            // Call the CreateUser function to handle registration with the imageId
            CreatetUser($username, $firstName, $lastName, $password, $email, 'User', $imageId);
            $user = GetUser(type: "Email", user: $email);
            // echo "<h1>Welcome, " . htmlspecialchars(string: $user['Title']) . "</h1>";
            createUserSession($user['Username'], $user['UserId'], $user['Rule']);
            // Redirect after successful registration
            header('Location: home.php?page=home');
            exit; // Ensure the script stops executing after redirect
        }
    }
}



function logout()
{
    // Start the session if it's not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // If there's a session cookie, delete it
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    $sessionId = session_id();
    DeleteUserSession($sessionId);  // Remove session from database
    session_destroy();  // Destroy the session

    // Remove the 'username' cookie if it exists
    if (isset($_COOKIE['username'])) {
        setcookie("username", "", time() - 3600, "/");
        setcookie("rule", "", time() - 3600, "/");

    }

    // Redirect to the home page after logout
    header("Location: home.php?page=home");
    exit();
}

/// if we want to add number in ther future 
// function validateISBN($isbn)
// {
//     $isbn = str_replace(['-', ' '], '', $isbn); // Remove dashes and spaces
//     if (preg_match('/^\d{9}[\dX]$/', $isbn)) {
//         // ISBN-10 Validation
//         $checksum = 0;
//         for ($i = 0; $i < 9; $i++) {
//             $checksum += ($i + 1) * $isbn[$i];
//         }
//         $checksum = ($checksum % 11 === 10) ? 'X' : $checksum % 11;
//         return $checksum == $isbn[9];
//     } elseif (preg_match('/^\d{13}$/', $isbn)) {
//         // ISBN-13 Validation
//         $checksum = 0;
//         for ($i = 0; $i < 12; $i++) {
//             $checksum += $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
//         }
//         $checksum = 10 - ($checksum % 10);
//         $checksum = ($checksum === 10) ? 0 : $checksum;
//         return $checksum == $isbn[12];
//     }
//     return false;
// }

?>