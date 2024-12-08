<?php
if (isset($_POST['query']) && !empty($_POST['query'])) {
    $searchQuery = $_POST['query']; // Get the search term from the POST data
    $books = searchBooks($searchQuery); // Call the searchBooks function

    echo '<div class="search-results-container">';
    echo '<h2>Search Results for: "' . htmlspecialchars($searchQuery) . '"</h2>';

    if (!empty($books)) {
        echo '<div class="book-grid">';
        foreach ($books as $book) {
            echo '<div class="book-item" onclick=\'showBookDetails(' . json_encode($book) . ')\'>';
            echo '<img src="data:image/jpeg;base64,' . htmlspecialchars($book->Image) . '" alt="Book Cover">';
            echo '<div class="book-details">';
            echo '<div class="book-title">' . htmlspecialchars($book->Title) . '</div>';
            echo '<div class="book-author">' . htmlspecialchars($book->Author) . '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo "<p>No results found for your search.</p>";
    }
    echo '</div>';
} else {
    echo "<p>Please enter a search term.</p>";
}
?>