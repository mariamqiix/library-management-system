<?php


function login()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $loginPassword = $_POST['password'];
        $user = GetUser("Username", $username);
        if ($user) {
            $password = GetPassword("Username", $username);
            if (password_verify($loginPassword, $password)) {
                echo "<h1>Welcome, $username!</h1>";
                echo "<p>You have successfully logged in.</p>";
            } else {
                echo "<h1>Invalid Credentials</h1>";
                echo "<p>The password you entered is incorrect.</p>";
            }
        } else if (GetUser("Email", $username)) {
            $password = GetPassword("Email", $username);
            if (password_verify($loginPassword, $password)) {
                echo "<h1>Welcome, $username!</h1>";
                echo "<p>You have successfully logged in.</p>";
            } else {
                echo "<h1>Invalid Credentials</h1>";
                echo "<p>The password you entered is incorrect.</p>";
            }

        } else {
            echo "<h1>Invalid Credentials</h1>";
            echo "<p>The username or password you entered is incorrect.</p>";
        }
    } else {
        echo "<h1>Login</h1>";
        echo "<form method='post'>";
        echo "<label for='username'>Username:</label><br>";
        echo "<input type='text' id='username' name='username'><br>";
        echo "<label for='password'>Password:</label><br>";
        echo "<input type='password' id='password' name='password'><br>";
        echo "<input type='submit' value='Login'>";
        echo "</form>";
        echo '<p><a href="home.php?page=register">Register</a></p>';
    }
}



function createUserSession($userId, $username)
{
    // Store user information in session
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;

    // Set a cookie with the username (expires in 1 hour)
    setcookie("username", $username, time() + 3600, "/"); // "/" means available across the entire site
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle login
} elseif (isset($_GET['action']) && $_GET['action'] === 'register') {
    register();
}function register()
{
    global $conn; // Ensure access to the database connection


    // Render the form
    echo "<h1>Register</h1>";


    echo "<form method='post' id='register-form'>";
    echo "<label for='username'>Username:</label><br>";
    echo "<input type='text' id='username' name='username'><br>";
    echo "<label for='first_name'>First Name:</label><br>";
    echo "<input type='text' id='first_name' name='first_name'><br>";
    echo "<label for='last_name'>Last Name:</label><br>";
    echo "<input type='text' id='last_name' name='last_name'><br>";
    echo "<label for='email'>Email:</label><br>";
    echo "<input type='email' id='email' name='email'><br>";
    echo "<label for='password'>Password:</label><br>";
    echo "<input type='password' id='password' name='password'><br>";
    echo "<label for='confirm_password'>Confirm Password:</label><br>";
    echo "<input type='password' id='confirm_password' name='confirm_password'><br>";
    echo "<input type='submit' value='Register'>";
    echo "</form>";
    echo '<p><a href="home.php?page=login">Login</a></p>';

    echo "<p id='error-message' style='color:red;'></p>";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Collect form inputs
        $username = trim($_POST['username'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // Backend validation (server-side)
        $error = '';
        if (empty($username) || empty($firstName) || empty($lastName) || empty($password) || empty($confirmPassword) || empty($email)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif (!validatePassword($password)) {
            $error = "Password must be 6-12 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character.";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        }

        if ($error) {
            $safeError = htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
            echo "<script>document.getElementById('error-message').innerHTML = '$safeError';</script>";
                } else {
            // Call the CreateUser function to handle registration
            CreatetUser($username, $firstName, $lastName, $password, $email);
            header('Location: home.php?page=home');

        }
    }
}

function validatePassword($password)
{
    $pattern = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{6,12}$/";
    return preg_match($pattern, $password);
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