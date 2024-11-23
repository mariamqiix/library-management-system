<?php
// Logic to determine which content to display
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
// Example routing logic in index.php
if (preg_match('/fetch_books\.php/', $_SERVER['REQUEST_URI'])) {
  include 'fetch_books.php';
  exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Library Management System</title>
  <style>
    .navbar a {
      margin: 0 10px;
      text-decoration: none;
      color: blue;
    }

    .navbar a.active {
      font-weight: bold;
      color: red;
    }

    .content {
      margin: 20px;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 5px;
      background-color: #f9f9f9;
    }

    .genres-container {
      margin-top: 20px;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
      background-color: #eef;
    }

    .genres-container h1 {
      margin-bottom: 15px;
      font-size: 24px;
      color: #333;
    }

    .genres-container button {
      margin: 5px;
      padding: 10px 15px;
      font-size: 16px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .genres-container button:hover {
      background-color: #0056b3;
    }

    #books-container {
      margin-top: 20px;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      background-color: #fff;
    }
  </style>
</head>

<body>
  <div class="navbar">
    <!-- Navigation bar links -->
    <a href="?page=home" class="<?= $page === 'home' ? 'active' : '' ?>">Home</a>
    <a href="?page=All Books" class="<?= $page === 'All Books' ? 'active' : '' ?>">All Books</a>
    <a href="?page=genres" class="<?= $page === 'genres' ? 'active' : '' ?>">Genres</a>
    <?php
    session_start();
    if ((isset($_SESSION['user_id']) || isset($_COOKIE['username'])) && $_SESSION['role'] == 'admin') {
      echo '<a href="?page=Adminstration" class="' . ($page === 'Adminstration' ? 'active' : '') . '">Adminstration</a>';
      echo '<a href="?page=libraryHistory" class="' . ($page === 'libraryHistory' ? 'active' : '') . '">libraryHistory</a>';

    } 
    if (isset($_SESSION['user_id']) || isset($_COOKIE['username'])) {
      echo '<a href="?page=MyLibrary" class="' . ($page === 'MyLibrary' ? 'active' : '') . '">MyLibrary</a>';
      echo '<a href="?page=logout" class="' . ($page === 'logout' ? 'active' : '') . '">Logout</a>';
    } else {
      echo '<a href="?page=login" class="' . ($page === 'login' ? 'active' : '') . '">Login</a>';
    }

    if ((isset($_SESSION['user_id']) || isset($_COOKIE['username'])) && $_SESSION['role'] == 'admin') {
      echo '<a href="?page=Adminstration" class="' . ($page === 'Adminstration' ? 'active' : '') . '">Adminstration</a>';
      echo '<a href="?page=libraryHistory" class="' . ($page === 'libraryHistory' ? 'active' : '') . '">libraryHistory</a>';

    } 
    ?>
  </div>

  <div class="content">
    <?php
    include 'db_connect.php';
    include 'databaseFunctions.php';
    include 'auth.php';
    include 'books.php';

    // Display content based on the selected page
    if ($page === 'home') {
      echo "<h1>Welcome to the Home Page</h1>";
      $books = GetAllBooks();

      if (!empty($books)) {
        echo "<ul>";
        foreach ($books as $book) {
          echo "<li>";
          echo "<strong>Title:</strong> " . htmlspecialchars($book->Title) . "<br>";
          echo "<strong>Author:</strong> " . htmlspecialchars($book->Author) . "<br>";
          echo "<strong>Publish Year:</strong> " . htmlspecialchars($book->PublishYear) . "<br>";
          echo "<strong>Available Copies:</strong> " . htmlspecialchars($book->AvailableBooks) . "<br>";
          echo "<strong>Genre:</strong> " . htmlspecialchars($book->Genre);
          echo "</li>";
        }
        echo "</ul>";
      } else {
        echo "<p>No books available at the moment.</p>";
      }
    } elseif ($page === 'All Books') {
      $books = GetAllBooks();
      if (!empty($books)) {
        echo "<ul>";
        foreach ($books as $book) {
          echo "<li>";
          echo "<strong>Title:</strong> " . htmlspecialchars($book->Title) . "<br>";
          echo "<strong>Author:</strong> " . htmlspecialchars($book->Author) . "<br>";
          echo "<strong>Publish Year:</strong> " . htmlspecialchars($book->PublishYear) . "<br>";
          echo "<strong>Available Copies:</strong> " . htmlspecialchars($book->AvailableBooks) . "<br>";
          echo "<strong>Genre:</strong> " . htmlspecialchars($book->Genre);
          echo "</li>";
        }
        echo "</ul>";
      } else {
        echo "<p>No books available at the moment.</p>";
      }
    } elseif ($page === 'genres') {
      echo '<div class="genres-container">';
      echo "<h1>Genres</h1>";

      $genres = fetchGenres();
      if (count($genres) > 0) {
        foreach ($genres as $genre) {
          echo "<button onclick='fetchBooks({$genre['GenreId']})'>{$genre['Genre']}</button>";
        }
      } else {
        echo "<p>No genres available.</p>";
      }

      echo '<div id="books-container">';
      echo "<p>Select a genre to see the books.</p>";
      echo '</div>';
      echo '</div>';
    } elseif ($page === 'MyLibrary') {
      $books = returnUserBooks(); // Fetch the user's borrowed books
    
      if (!empty($books)) {
        echo "<ul>";
        foreach ($books as $book) {
          echo "<li>";
          echo "<strong>Title:</strong> " . htmlspecialchars($book['Title']) . "<br>";
          echo "<strong>Author:</strong> " . htmlspecialchars($book['Author']) . "<br>";
          echo "<strong>Publish Year:</strong> " . htmlspecialchars($book['Publish_year']) . "<br>";
          echo "<strong>Valid Until:</strong> " . htmlspecialchars($book['expire_date']) . "<br>";
          echo "</li>";
        }
        echo "</ul>";
      } else {
        echo "<p>No books available at the moment.</p>";
      }

    } elseif ($page === 'login') {
      login();
    } elseif ($page === 'register') {
      register();
    } elseif ($page === 'logout') {
      logout();
    }elseif ($page === 'Adminstration') {
    }elseif ($page === 'libraryHistory') {
    } else {
      echo "<h1>Page Not Found</h1>";
      echo "<p>The page you are looking for does not exist.</p>";
    }
    ?>
  </div>
</body>

<script>
  function fetchBooks(genreId) {
    const booksContainer = document.getElementById('books-container');
    booksContainer.innerHTML = 'Loading...';

    fetch(`fetch_books.php?genreId=${genreId}`)
      .then(response => {
        console.log('Raw response:', response); // Log raw response
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
          console.error('Invalid response type:', contentType);
          return response.text().then(text => { throw new Error(`Response was not JSON: ${text}`); });
        }
        return response.json();
      })
      .then(data => {
        if (data.error) {
          throw new Error(data.error);
        }

        if (data.length > 0) {
          const booksList = data.map(book => `
                    <li>
                        <strong>Title:</strong> ${book.Title}<br>
                        <strong>Author:</strong> ${book.Author}<br>
                        <strong>Publish Year:</strong> ${book.Publish_year}<br>
                        <strong>Available Copies:</strong> ${book.Available_books}
                    </li>
                `).join('');
          booksContainer.innerHTML = `<ul>${booksList}</ul>`;
        } else {
          booksContainer.innerHTML = '<p>No books available in this genre.</p>';
        }
      })
      .catch(err => {
        console.error('Fetch error:', err);
        booksContainer.innerHTML = '<p>Error fetching books. Please try again later.</p>';
      });
  }



</script>


</html>