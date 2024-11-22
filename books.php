<?php 

function returnUserBooks() {
    global $conn; // Assuming $conn is the database connection

    // Check if the username cookie exists
    if (!isset($_COOKIE['username'])) {
        die("Error: User is not logged in.");
    }

    // Get the username from the cookie
    $userId = $_COOKIE['username'];
    // SQL query to get the books borrowed by the user
    $sql = "
        SELECT 
            book.Title,
            book.Author,
            book.Publish_year,
            borrowed_books.expire_date
        FROM 
            borrowed_books
        INNER JOIN 
            book ON borrowed_books.BookId = book.BookId
        WHERE 
            borrowed_books.UserId = ?
    ";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the UserId parameter
        $stmt->bind_param("i", $userId);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Fetch all rows into an array
        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        // Close the statement
        $stmt->close();

        return $books;
    } else {
        echo("Error preparing query: " . $conn->error);
    }
}


?>