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
            b.BookId, 
            b.Title, 
            b.Author, 
            b.Publish_year, 
            bb.expire_date,
            i.ImageData
        FROM 
            borrowed_books bb
        INNER JOIN 
            book b ON bb.BookId = b.BookId
        LEFT JOIN 
            ImagesToUsersAndBooks ibu ON b.BookId = ibu.BookId
        LEFT JOIN 
            ImagesTable i ON ibu.ImageId = i.ImageId
        WHERE 
            bb.UserId = ?
    ";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the UserId parameter
        $stmt->bind_param("i", $userId);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Initialize an array to hold the book objects
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
                    'ExpireDate' => $row['expire_date'],
                    'Image' => isset($row['ImageData']) ? base64_encode($row['ImageData']) : null // Convert binary image data to base64 string if it exists
                ];

                // Add the book object to the books array
                $books[] = $book;
            }
        }

        // Close the statement
        $stmt->close();

        // Return the array of book objects (could be empty if no results)
        return $books;
    } else {
        echo ("Error preparing query: " . $conn->error);
    }
}


?>