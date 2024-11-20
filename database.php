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
        Password VARCHAR(255) NOT NULL,
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
    // UserSession table
    "CREATE TABLE IF NOT EXISTS user_session (
        UserId INT,
        Session VARCHAR(50),
        Expire_date DATE,
        FOREIGN KEY (UserId) REFERENCES user(UserId)
    )",
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
        FOREIGN KEY (GenreId) REFERENCES genres(GenreId)
    )" , 
    "   CREATE TABLE IF NOT EXISTS borrowed_books (
        BookId INT,
        UserId INT,
        expire_date DATETIME DEFAULT (CURRENT_TIMESTAMP + INTERVAL 3 DAY),
        FOREIGN KEY (BookId) REFERENCES book(BookId),
        FOREIGN KEY (UserId) REFERENCES user(UserId)
    )"
];

foreach ($tables as $table_sql) {
    if ($conn->query($table_sql) === TRUE) {
        echo "Table created successfully<br>";
    } else {
        die("Error creating table: " . $conn->error);
    }
}


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
    $sql = "INSERT INTO book (Title, Author, Publish_year, Available_books) VALUES ('{$book['Title']}', '{$book['Author']}', '{$book['Publish_year']}', {$book['Available_books']})";
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
    } else {
        echo "Error adding book: " . $conn->error . "<br>";
    }
}
?>
