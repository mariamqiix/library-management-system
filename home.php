<?php
// Logic to determine which content to display
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>library management system</title>
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
  </style>
</head>

<body>
  <div class="navbar">
    <!-- Navigation bar links -->
    <a href="?page=home" class="<?= $page === 'home' ? 'active' : '' ?>">Home</a>
    <a href="?page=All Books" class="<?= $page === 'All Books' ? 'active' : '' ?>">All Books</a>
    <a href="?page=genres" class="<?= $page === 'genres' ? 'active' : '' ?>">genres</a>
    <a href="?page=my library" class="<?= $page === 'my library' ? 'active' : '' ?>">my library</a>
    <a href="?page=login" class="<?= $page === 'login' ? 'active' : '' ?>">login</a>

  </div>

  <div class="content">
    <?php
    include 'db_connect.php';
    include 'databaseFunctions.php';
    include 'auth.php';

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
      echo "<ul>";
    } elseif ($page === 'All Books') {
      echo "<h1>All Books</h1>";
      echo "<p>Learn more about us on this page.</p>";
    } elseif ($page === 'genres') {
      echo "<h1>genres</h1>";
      echo "<p>Learn more about us on this page.</p>";
    } elseif ($page === 'my library') {
      echo "<h1>my library</h1>";
      echo "<p>Learn more about us on this page.</p>";
    } elseif ($page === 'login') {
      login();
    } elseif ($page === 'register') {
      register();
    } else {
      echo "<h1>Page Not Found</h1>";
      echo "<p>The page you are looking for does not exist.</p>";
    }


    ?>
  </div>

  
</body>

</html>