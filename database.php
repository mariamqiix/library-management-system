<?php

include 'db_connect.php';
include 'databaseFunctions.php';
// Create the database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);
// SQL to create tables
$tables = [
    // User table
    "CREATE TABLE IF NOT EXISTS user (
        UserId INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) NOT NULL,
        FirstName VARCHAR(50) NOT NULL,
        LastName VARCHAR(50) NOT NULL,
        HashedPassword CHAR(60) NOT NULL,
        Email VARCHAR(100) NOT NULL
    )",
    // Book table
    "CREATE TABLE IF NOT EXISTS book (
        BookId INT AUTO_INCREMENT PRIMARY KEY,
        Title VARCHAR(50),
        Author VARCHAR(20),
        Publish_year DATE,
        Available_books INT
    )",
    // Rules table
    "CREATE TABLE IF NOT EXISTS rules (
        RuleId INT AUTO_INCREMENT PRIMARY KEY,
        Rule VARCHAR(20) 
    )",
    // Genres table
    "CREATE TABLE IF NOT EXISTS genres (
        GenreId INT AUTO_INCREMENT PRIMARY KEY,
        Genre VARCHAR(20) 
    )",
    // // UserSession table
    // "CREATE TABLE IF NOT EXISTS user_session (
    //     UserId INT,
    //     Session VARCHAR(50),
    //     Expire_date DATE,
    //     FOREIGN KEY (UserId) REFERENCES user(UserId)
    // )",
    // UserRule table
    "CREATE TABLE IF NOT EXISTS user_rule (
        UserId INT,
        RuleId INT,
        FOREIGN KEY (UserId) REFERENCES user(UserId),
        FOREIGN KEY (RuleId) REFERENCES rules(RuleId)
    )",
    // BookGenre table
    "CREATE TABLE IF NOT EXISTS book_genre (
        BookId INT,
        GenreId INT,
        FOREIGN KEY (GenreId) REFERENCES genres(GenreId),
        FOREIGN KEY (BookId) REFERENCES book(BookId)
    )",
    "   CREATE TABLE IF NOT EXISTS borrowed_books (
        BookId INT,
        UserId INT,
        borrow_date DATETIME DEFAULT (CURRENT_TIMESTAMP),
        expire_date DATETIME DEFAULT (CURRENT_TIMESTAMP + INTERVAL 3 DAY),
        FOREIGN KEY (BookId) REFERENCES book(BookId),
        FOREIGN KEY (UserId) REFERENCES user(UserId)
    )",
    "  CREATE TABLE IF NOT EXISTS ImagesTable (
        ImageId INT AUTO_INCREMENT PRIMARY KEY,
        ImageData LONGBLOB
    )",
    "CREATE TABLE IF NOT EXISTS ImagesToUsersAndBooks (
        ImageId INT,
        UserId INT,
        BookId INT,
        FOREIGN KEY (ImageId) REFERENCES ImagesTable(ImageId),
        FOREIGN KEY (UserId) REFERENCES user(UserId),
        FOREIGN KEY (BookId) REFERENCES book(BookId)
    )"

];

foreach ($tables as $table_sql) {
    if ($conn->query($table_sql) === TRUE) {
        echo "Table created successfully<br>";
    } else {
        die("Error creating table: " . $conn->error);
    }
}

// Example usage
$filePath = './book.gif'; // Replace with your file path
uploadFileToDatabase($filePath);
$filePath = 'icons8-user-48.png'; // Replace with your file path
uploadFileToDatabase($filePath);
// Insert rules
$rules = [
    "Admin",
    "User",
];

foreach ($rules as $rule) {
    $sql = "INSERT INTO rules (Rule) VALUES ('$rule')";
    if ($conn->query($sql) === TRUE) {
        echo "Rule '$rule' added successfully<br>";
    } else {
        echo "Error adding rule: " . $conn->error . "<br>";
    }
}

// Insert genres
$genres = [
    "Fiction",
    "Non-Fiction",
    "Science",
    "Fantasy",
    "Biography"
];

foreach ($genres as $genre) {
    $sql = "INSERT INTO genres (Genre) VALUES ('$genre')";
    if ($conn->query($sql) === TRUE) {
        echo "Genre '$genre' added successfully<br>";
    } else {
        echo "Error adding genre: " . $conn->error . "<br>";
    }
}


// Insert books and link them to genres
$books = [
    ["Title" => "The Great Gatsby", "Author" => "F. Scott Fitzgerald", "Publish_year" => "1925-04-10", "Available_books" => 5, "Genre" => "Fiction"],
    ["Title" => "1984", "Author" => "George Orwell", "Publish_year" => "1949-06-08", "Available_books" => 3, "Genre" => "Science"],
    ["Title" => "To Kill a Mockingbird", "Author" => "Harper Lee", "Publish_year" => "1960-07-11", "Available_books" => 4, "Genre" => "Fiction"],
];

foreach ($books as $book) {
    // Insert the book
    $sql = "INSERT INTO book (Title, Author, Publish_year, Available_books) 
            VALUES ('{$book['Title']}', '{$book['Author']}', '{$book['Publish_year']}', {$book['Available_books']})";
    if ($conn->query($sql) === TRUE) {
        $bookId = $conn->insert_id; // Get the new BookId

        // Get or insert the genre and get its ID
        $genreId = getGenreId($book['Genre']);
        if ($genreId !== null) {
            // Link the book to the genre
            $sql = "INSERT INTO book_genre (BookId, GenreId) VALUES ($bookId, $genreId)";
            if ($conn->query($sql) === TRUE) {
                echo "Book '{$book['Title']}' added successfully and linked to genre '{$book['Genre']}'<br>";
            } else {
                echo "Error linking book to genre: " . $conn->error . "<br>";
            }
        }

        // Link the book to the default image
        $defaultImageId = 1; // Assuming default image ID is 1
        $sql = "INSERT INTO ImagesToUsersAndBooks (ImageId, BookId) VALUES ($defaultImageId, $bookId)";
        if ($conn->query($sql) === TRUE) {
            echo "Default image linked for book '{$book['Title']}'<br>";
        } else {
            echo "Error linking default image: " . $conn->error . "<br>";
        }
    } else {
        echo "Error adding book: " . $conn->error . "<br>";
    }
}

// Add users of type Admin
$adminUsers = [
    ["Username" => "mariamabbas", "FirstName" => "Mariam", "LastName" => "Abbas", "HashedPassword" => password_hash("123456789", PASSWORD_BCRYPT), "Email" => "mariam.abbas@example.com"],
    ["Username" => "lamesadel", "FirstName" => "Lames", "LastName" => "Adel", "HashedPassword" => password_hash("123456789", PASSWORD_BCRYPT), "Email" => "lames.ader@example.com"],
    ["Username" => "amaniemad", "FirstName" => "Amani", "LastName" => "Emad", "HashedPassword" => password_hash("123456789", PASSWORD_BCRYPT), "Email" => "amani.emad@example.com"],
    ["Username" => "miramahmood", "FirstName" => "Mira", "LastName" => "Mahmood", "HashedPassword" => password_hash("123456789", PASSWORD_BCRYPT), "Email" => "mira.mahmood@example.com"],
];

// Get the RuleId for "Admin"
$sql = "SELECT RuleId FROM rules WHERE Rule = 'Admin'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $adminRuleId = $result->fetch_assoc()['RuleId'];

    foreach ($adminUsers as $user) {
        // Insert the user into the `user` table
        $sql = "INSERT INTO user (Username, FirstName, LastName, HashedPassword, Email) VALUES 
            ('{$user['Username']}', '{$user['FirstName']}', '{$user['LastName']}', '{$user['HashedPassword']}', '{$user['Email']}')";
        if ($conn->query($sql) === TRUE) {
            $userId = $conn->insert_id; // Get the new UserId

            // Assign the Admin role to the user
            $sql = "INSERT INTO user_rule (UserId, RuleId) VALUES ($userId, $adminRuleId)";
            if ($conn->query($sql) === TRUE) {
                echo "Admin user '{$user['FirstName']} {$user['LastName']}' added successfully<br>";

                // Link the default image to the user in `ImagesToUsersAndBooks`
                $defaultImageId = 2; // Assuming default image ID is 1
                $sql = "INSERT INTO ImagesToUsersAndBooks (ImageId, UserId) VALUES ($defaultImageId, $userId)";
                if ($conn->query($sql) === TRUE) {
                    echo "Default image linked for user '{$user['FirstName']} {$user['LastName']}'<br>";
                } else {
                    echo "Error linking default image: " . $conn->error . "<br>";
                }
            } else {
                echo "Error assigning Admin role: " . $conn->error . "<br>";
            }
        } else {
            echo "Error adding user '{$user['FirstName']} {$user['LastName']}': " . $conn->error . "<br>";
        }
    }
} else {
    echo "Error: Admin rule not found<br>";
}


function updatePassword($userId, $password)
{
    global $conn; // Assuming $conn is the database connection

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the SQL statement
    $sql = "UPDATE user SET password = ? WHERE UserId = ?";

    // Prepare statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("si", $hashedPassword, $userId);

        // Execute statement
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}


?>