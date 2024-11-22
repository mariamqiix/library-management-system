<?php


include 'db_connect.php';

function searchBooks($searchString)
{
    global $conn;
    // Sanitize the input to prevent SQL injection
    $searchString = $conn->real_escape_string($searchString);

    // SQL query to search for books by title, author, or genre
    $sql = "
        SELECT b.BookId, b.Title, b.Author, g.Genre
        FROM book b
        LEFT JOIN book_genre bg ON b.BookId = bg.BookId
        LEFT JOIN genres g ON bg.GenreId = g.GenreId
        WHERE b.Title LIKE '%$searchString%' 
        OR b.Author LIKE '%$searchString%' 
        OR g.Genre LIKE '%$searchString%'
    ";

    $result = $conn->query($sql);

    // Check if there are results
    $books = [];
    if ($result->num_rows > 0) {
        // Fetch all results into an array
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }

    // Return the array of books (could be empty if no results)
    return $books;
}


function CreatetUser($username, $firstName, $lastName, $password, $email, $rule = 'User')
{
    global $conn;

    // Sanitize inputs to prevent SQL injection
    $username = $conn->real_escape_string($username);
    $firstName = $conn->real_escape_string($firstName);
    $lastName = $conn->real_escape_string($lastName);
    $password = $conn->real_escape_string($password);
    $email = $conn->real_escape_string($email);
    $rule = $conn->real_escape_string($rule);

    // Hash the password before storing it (better practice than storing plain text passworads)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Step 1: Insert the user into the 'user' table
    $sql = "INSERT INTO user (Username, FirstName, LastName, HashedPassword, Email) 
            VALUES ('$username', '$firstName', '$lastName', '$hashedPassword', '$email')";

    if ($conn->query($sql) === TRUE) {
        // Step 2: Get the last inserted UserId
        $userId = $conn->insert_id;

        // Step 3: Get the RuleId for the given rule
        $ruleSql = "SELECT RuleId FROM rules WHERE Rule = '$rule' LIMIT 1";
        $ruleResult = $conn->query($ruleSql);

        if ($ruleResult->num_rows > 0) {
            $ruleRow = $ruleResult->fetch_assoc();
            $ruleId = $ruleRow['RuleId'];

            // Step 4: Insert a record into the 'user_rule' table
            $userRuleSql = "INSERT INTO user_rule (UserId, RuleId) 
                            VALUES ($userId, $ruleId)";

            if ($conn->query($userRuleSql) === TRUE) {
                echo "User and user rule created successfully.<br>";
            } else {
                echo "Error creating user rule: " . $conn->error . "<br>";
            }
        } else {
            echo "Error: Rule '$rule' not found.<br>";
        }
    } else {
        echo "Error inserting user: " . $conn->error . "<br>";
    }
}

function createBook($title, $author, $publishYear, $availableBooks, $genre)
{
    global $conn;

    // Sanitize inputs to prevent SQL injection
    $title = $conn->real_escape_string($title);
    $author = $conn->real_escape_string($author);
    $publishYear = $conn->real_escape_string($publishYear);
    $availableBooks = $conn->real_escape_string($availableBooks);
    $genre = $conn->real_escape_string($genre);

    // Step 1: Check if the genre exists
    $genreSql = "SELECT GenreId FROM genres WHERE Genre = '$genre' LIMIT 1";
    $genreResult = $conn->query(query: $genreSql);

    if ($genreResult->num_rows > 0) {
        // Genre exists, fetch the GenreId
        $genreRow = $genreResult->fetch_assoc();
        $genreId = $genreRow['GenreId'];
    } else {
        // Genre does not exist, insert it
        $insertGenreSql = "INSERT INTO genres (Genre) VALUES ('$genre')";
        if ($conn->query($insertGenreSql) === TRUE) {
            $genreId = $conn->insert_id; // Get the new GenreId
        } else {
            echo "Error inserting genre: " . $conn->error . "<br>";
            return;
        }
    }

    // Step 2: Insert the book into the 'book' table
    $insertBookSql = "INSERT INTO book (Title, Author, Publish_year, Available_books) 
                      VALUES ('$title', '$author', '$publishYear', '$availableBooks')";

    if ($conn->query($insertBookSql) === TRUE) {
        // Step 3: Get the newly inserted BookId
        $bookId = $conn->insert_id;

        // Step 4: Link the book with the genre in the 'book_genre' table
        $insertBookGenreSql = "INSERT INTO book_genre (BookId, GenreId) 
                               VALUES ($bookId, $genreId)";

        if ($conn->query($insertBookGenreSql) === TRUE) {
            echo "Book and genre linked successfully.<br>";
        } else {
            echo "Error linking book with genre: " . $conn->error . "<br>";
        }
    } else {
        echo "Error inserting book: " . $conn->error . "<br>";
    }
}


function borrowBook($bookId, $userId)
{
    global $conn;

    // Sanitize inputs to prevent SQL injection
    $bookId = $conn->real_escape_string($bookId);
    $userId = $conn->real_escape_string($userId);

    // Step 1: Check if the book is available
    $bookSql = "SELECT Available_books FROM book WHERE BookId = $bookId LIMIT 1";
    $bookResult = $conn->query($bookSql);

    if ($bookResult->num_rows > 0) {
        $bookRow = $bookResult->fetch_assoc();
        $availableBooks = $bookRow['Available_books'];

        if ($availableBooks > 0) {
            // Step 2: Decrement the number of available books
            $updateBookSql = "UPDATE book SET Available_books = Available_books - 1 WHERE BookId = $bookId";

            if ($conn->query($updateBookSql) === TRUE) {
                // Step 3: Insert a record into the 'borrowed_books' table
                $borrowSql = "INSERT INTO borrowed_books (BookId, UserId) VALUES ($bookId, $userId)";

                if ($conn->query($borrowSql) === TRUE) {
                    echo "Book borrowed successfully.<br>";
                } else {
                    echo "Error borrowing book: " . $conn->error . "<br>";
                }
            } else {
                echo "Error updating book: " . $conn->error . "<br>";
            }
        } else {
            echo "Error: Book not available.<br>";
        }
    } else {
        echo "Error: Book not found.<br>";
    }
}


function returnBook($bookId, $userId)
{
    global $conn;

    // Sanitize inputs to prevent SQL injection
    $bookId = $conn->real_escape_string($bookId);
    $userId = $conn->real_escape_string($userId);

    // Step 1: Check if the user has borrowed the book
    $borrowedSql = "SELECT * FROM borrowed_books WHERE BookId = $bookId AND UserId = $userId LIMIT 1";
    $borrowedResult = $conn->query($borrowedSql);

    if ($borrowedResult->num_rows > 0) {
        // Step 2: Increment the number of available books
        $updateBookSql = "UPDATE book SET Available_books = Available_books + 1 WHERE BookId = $bookId";

        if ($conn->query($updateBookSql) === TRUE) {
            // Step 3: Delete the record from the 'borrowed_books' table
            $returnSql = "DELETE FROM borrowed_books WHERE BookId = $bookId AND UserId = $userId";

            if ($conn->query($returnSql) === TRUE) {
                echo "Book returned successfully.<br>";
            } else {
                echo "Error returning book: " . $conn->error . "<br>";
            }
        } else {
            echo "Error updating book: " . $conn->error . "<br>";
        }
    } else {
        echo "Error: Book not borrowed by user.<br>";
    }
}


function updateBook($bookId, $title, $author, $publishYear, $availableBooks, $genre)
{
    global $conn;

    // Sanitize inputs to prevent SQL injection
    $bookId = $conn->real_escape_string($bookId);
    $title = $conn->real_escape_string($title);
    $author = $conn->real_escape_string($author);
    $publishYear = $conn->real_escape_string($publishYear);
    $availableBooks = $conn->real_escape_string($availableBooks);
    $genre = $conn->real_escape_string($genre);

    // Step 1: Check if the book exists
    $bookSql = "SELECT * FROM book WHERE BookId = $bookId LIMIT 1";
    $bookResult = $conn->query($bookSql);

    if ($bookResult->num_rows > 0) {
        // Step 2: Update the book details
        $updateBookSql = "UPDATE book SET 
                          Title = '$title', 
                          Author = '$author', 
                          Publish_year = '$publishYear', 
                          Available_books = '$availableBooks'
                          WHERE BookId = $bookId";

        if ($conn->query($updateBookSql) === TRUE) {
            // Step 3: Check if the genre exists
            $genreSql = "SELECT GenreId FROM genres WHERE Genre = '$genre' LIMIT 1";
            $genreResult = $conn->query($genreSql);

            if ($genreResult->num_rows > 0) {
                // Genre exists, fetch the GenreId
                $genreRow = $genreResult->fetch_assoc();
                $genreId = $genreRow['GenreId'];
            } else {
                // Genre does not exist, insert it
                $insertGenreSql = "INSERT INTO genres (Genre) VALUES ('$genre')";
                if ($conn->query($insertGenreSql) === TRUE) {
                    $genreId = $conn->insert_id; // Get the new GenreId
                } else {
                    echo "Error inserting genre: " . $conn->error . "<br>";
                    return;
                }
            }

            // Step 4: Update the book's genre in the book_genre table
            $updateBookGenreSql = "INSERT INTO book_genre (BookId, GenreId)
                                   VALUES ($bookId, $genreId)
                                   ON DUPLICATE KEY UPDATE GenreId = $genreId";

            if ($conn->query($updateBookGenreSql) === TRUE) {
                echo "Book and genre updated successfully.<br>";
            } else {
                echo "Error linking book with genre: " . $conn->error . "<br>";
            }
        } else {
            echo "Error updating book: " . $conn->error . "<br>";
        }
    } else {
        echo "Error: Book not found.<br>";
    }
}

function borrowHistory()
{
    global $conn;

    // Step 1: SQL to fetch borrow history along with user details
    $sql = "
        SELECT u.UserId, u.Username, u.FirstName, u.LastName, u.Email, 
               b.Title AS bookTitle, b.Author AS bookAuthor, b.Publish_year AS publishYear, 
               b.Available_books AS availableBooks, g.Genre AS bookGenre, bb.BorrowDate, bb.ReturnDate
        FROM borrowed_books bb
        JOIN user u ON bb.UserId = u.UserId
        JOIN book b ON bb.BookId = b.BookId
        LEFT JOIN book_genre bg ON b.BookId = bg.BookId
        LEFT JOIN genres g ON bg.GenreId = g.GenreId
        ORDER BY bb.BorrowDate DESC
    ";

    // Step 2: Execute the query
    $result = $conn->query($sql);

    // Step 3: Initialize an array to hold all borrow history records
    $borrowHistory = [];

    // Step 4: Check if there are results
    if ($result->num_rows > 0) {
        // Step 5: Fetch and populate the borrow history as objects
        while ($row = $result->fetch_assoc()) {
            // Create the userDetails object
            $userDetails = (object) [
                'userId' => $row['UserId'],
                'username' => $row['Username'],
                'firstName' => $row['FirstName'],
                'lastName' => $row['LastName'],
                'email' => $row['Email']
            ];

            // Create the bookDetails object
            $bookDetails = (object) [
                'bookTitle' => $row['bookTitle'],
                'bookAuthor' => $row['bookAuthor'],
                'bookGenre' => $row['bookGenre'],
                'publishYear' => $row['publishYear'],
                'availableBooks' => $row['availableBooks'],
                'borrowDate' => $row['BorrowDate'],
                'returnDate' => $row['ReturnDate']
            ];

            // Add both user and book objects to the borrowHistory array
            $borrowHistory[] = (object) [
                'userDetails' => $userDetails,
                'bookDetails' => $bookDetails
            ];
        }
    }

    // Step 6: Return the array of borrow history records (with userDetails and bookDetails objects)
    return $borrowHistory;
}

function addUserSession($UserId, $Session)
{
    global $conn;
    $sql = 'INSERT INTO user_session (UserId, Session) VALUES (' . $UserId . ', "' . $Session . '")';
    if ($conn->query($sql) === TRUE) {
        echo "User session added successfully.<br>";
    } else {
        echo "Error adding user session: " . $conn->error . "<br>";
    }
}

function getUserSession($Session)
{
    global $conn;

    // SQL to fetch user session details and associated user rule
    $sql = "
        SELECT us.UserId, us.Session, us.Expire_date, ur.RuleId, r.Rule
        FROM user_session us
        LEFT JOIN user_rule ur ON us.UserId = ur.UserId
        LEFT JOIN rules r ON ur.RuleId = r.RuleId
        WHERE us.Session = '$Session'
        LIMIT 1
    ";

    // Execute the query
    $result = $conn->query($sql);

    // Check if a session is found
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Create an object to return the session details along with user rule
        $userSession = (object) [
            'UserId' => $row['UserId'],
            'Session' => $row['Session'],
            'ExpireDate' => $row['Expire_date'],
            'RuleId' => $row['RuleId'],
            'Rule' => $row['Rule']
        ];

        return $userSession;
    } else {
        // If no session is found, return null
        return null;
    }
}


function getBookDetails($BookID)
{
    global $conn;

    // SQL query to fetch book details and the associated genre
    $sql = "
        SELECT b.BookId, b.Title, b.Author, b.Publish_year, b.Available_books, g.Genre
        FROM book b
        LEFT JOIN book_genre bg ON b.BookId = bg.BookId
        LEFT JOIN genres g ON bg.GenreId = g.GenreId
        WHERE b.BookId = $BookID
        LIMIT 1
    ";

    // Execute the query
    $result = $conn->query($sql);

    // Check if a book was found
    if ($result->num_rows > 0) {
        // Fetch the book details and genre
        $row = $result->fetch_assoc();

        // Create an object to return the book and genre details
        $bookDetails = (object) [
            'BookId' => $row['BookId'],
            'Title' => $row['Title'],
            'Author' => $row['Author'],
            'PublishYear' => $row['Publish_year'],
            'AvailableBooks' => $row['Available_books'],
            'Genre' => $row['Genre'] // Genre field
        ];

        return $bookDetails;
    } else {
        // If no book is found, return null
        return null;
    }
}


function GetAllBooks()
{
    global $conn;

    // SQL to select all books along with their genres
    $sql = "
        SELECT b.BookId, b.Title, b.Author, b.Publish_year, b.Available_books, g.Genre
        FROM book b
        LEFT JOIN book_genre bg ON b.BookId = bg.BookId
        LEFT JOIN genres g ON bg.GenreId = g.GenreId
    ";

    $result = $conn->query($sql);

    // Initialize an array to hold all book objects
    $books = [];

    // Check if the query returned any rows
    if ($result->num_rows > 0) {
        // Loop through each row in the result set
        while ($row = $result->fetch_assoc()) {
            // Create a new book object for each row
            $book = (object) [
                'BookId' => $row['BookId'],
                'Title' => $row['Title'],
                'Author' => $row['Author'],
                'PublishYear' => $row['Publish_year'],
                'AvailableBooks' => $row['Available_books'],
                'Genre' => $row['Genre'] // Genre field
            ];

            // Add the book object to the books array
            $books[] = $book;
        }
    }

    // Return the array of book objects
    return $books;
}


function checkUser($type, $username)
{
    global $conn;

    // Define allowed types to prevent SQL injection
    $allowedTypes = ['Username', 'Email']; // Adjust based on your database columns

    if (!in_array($type, $allowedTypes)) {
        return false; // Invalid type
    }

    // Prepare the SQL statement with a placeholder
    $stmt = $conn->prepare("SELECT 1 FROM user WHERE $type = ? LIMIT 1");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    // Bind the username parameter to the placeholder
    $stmt->bind_param("s", $username);

    // Execute the query
    $stmt->execute();

    // Store result to use num_rows
    $stmt->store_result();

    // Check if any rows returned
    return $stmt->num_rows > 0;
}


function GetPassword($type, $username)
{
    global $conn;

    // Prepare the SQL statement to prevent SQL injection
    $sql = "SELECT HashedPassword FROM user WHERE $type = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind the parameter
        $stmt->bind_param("s", $username);

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Fetch the password
        $data = $result->fetch_assoc();

        // Close the statement
        $stmt->close();

        return $data['HashedPassword'] ?? null;
    } else {
        // Handle query preparation error
        die("Error preparing query: " . $conn->error);
    }
}


function DeleteUserSession($Session)
{
    global $conn;
    $sql = "DELETE FROM user_session WHERE Session = '$Session'";
    if ($conn->query($sql) === TRUE) {
        echo "User session deleted successfully.<br>";
    } else {
        echo "Error deleting user session: " . $conn->error . "<br>";
    }
}

function DeleteUserSessionByUserId($UserId)
{
    global $conn;
    $sql = "DELETE FROM user_session WHERE UserId = $UserId";
    if ($conn->query($sql) === TRUE) {
        echo "User session deleted successfully.<br>";
    } else {
        echo "Error deleting user session: " . $conn->error . "<br>";
    }
}

function getUserId($type, $Email)
{
    global $conn;
    $sql = "SELECT UserId FROM user WHERE '$type = '$Email'";
    $result = $conn->query($sql);
    $userIds = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $userIds[] = $row['UserId'];
        }
    }
    return $userIds;
}

function getGenreId($genre)
{
    global $conn;

    // Check if the genre exists
    $sql = "SELECT GenreId FROM genres WHERE Genre = '$genre'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Genre exists, return its ID
        $row = $result->fetch_assoc();
        return $row['GenreId'];
    } else {
        // Genre does not exist, insert it
        $sql = "INSERT INTO genres (Genre) VALUES ('$genre')";
        if ($conn->query($sql) === TRUE) {
            return $conn->insert_id; // Return the new GenreId
        } else {
            echo "Error adding genre: " . $conn->error;
            return null; // Return null if there was an error
        }
    }
}
function GetUser($type, $user)
{
    global $conn;

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM user WHERE $type = ?");
    if ($stmt === false) {
        error_log('SQL Prepare failed: ' . $conn->error); // Log the error for debugging
        return null; // Return null on error
    }

    // Bind parameters and execute
    $stmt->bind_param("s", $user);
    if (!$stmt->execute()) {
        error_log('SQL Execute failed: ' . $stmt->error); // Log the error for debugging
        return null;
    }

    // Get the result
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $userData = $result->fetch_assoc(); // Fetch user data as an associative array
        $stmt->close(); // Close the statement
        return $userData;
    } else {
        $stmt->close(); // Close the statement
        return null; // No user found
    }
}


?>