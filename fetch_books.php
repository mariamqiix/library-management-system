<?php

header('Content-Type: application/json'); // Ensure the response is JSON

// Enable error reporting during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    if (isset($_GET['genreId'])) {
        $genreId = intval($_GET['genreId']); // Sanitize input
        if ($genreId <= 0) {
            throw new Exception('Invalid genre ID.');
        }
        $books = fetchBooksByGenre($genreId);

        if (!is_array($books)) {
            throw new Exception('Failed to fetch books.');
        }

        // Ensure valid JSON response
        echo json_encode($books);
    } else {
        echo json_encode(['error' => 'genreId parameter is missing.']);
    }
} catch (Exception $e) {
    // Return error as JSON
    echo json_encode(['error' => $e->getMessage()]);
}

// Prevent additional output
exit;