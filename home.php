<?php

include 'db_connect.php';
include 'databaseFunctions.php';

// Logic to determine which content to display
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
// Example routing logic in index.php
if (preg_match('/fetch_books\.php/', $_SERVER['REQUEST_URI'])) {
  include 'fetch_books.php';
  exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['formType'])) {
    switch ($_POST['formType']) {
      case 'addBookForm':
        // Call the addBook function from the included file
        addBook($_POST);
        break;

      case 'updateBookForm':
        // Call the updateBook function from the included file
        updateBookPost($_POST);
        break;

      case 'registerUserForm':
        // Call the registerUser function from the included file
        registerUser($_POST);
        break;
    }
  }
}
// functions.php

// Example function to handle adding a book
function addBook($data)
{

  // Extract form data
  $title = $data['addBookTitle'];
  $author = $data['addAuthor'];
  $publishYear = $data['addPublishYear'];
  $availableBooks = $data['addAvailableBooks'];
  $genr = $data['addGenr'];
  createBook($title, $author, $publishYear, $availableBooks, $genr);
}
function registerUser($data)
{
  // Check if the form is submitted via POST
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract and sanitize user inputs
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim(string: $_POST['password']) : '';
    $firstName = isset($_POST['firstName']) ? trim(string: $_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? trim(string: $_POST['lastName']) : '';
    $rule = isset($_POST['rule']) ? trim(string: $_POST['rule']) : '';

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
      echo "All fields are required.";
      return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      echo "Invalid email format.";
      return;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Include the database functions file
    try {
      // Call the CreatetUser function to save the user
      CreatetUser($username, $firstName, $lastName, $email, $hashedPassword, $rule);
      echo "User registered successfully.";
    } catch (Exception $e) {
      // Handle any errors that occur during user creation
      echo "Error: " . $e->getMessage();
    }
  }
}


function updateBookPost($data)
{
  // Decode the JSON from the 'bookSelect' field
  $bookSelect = json_decode($data['bookSelect'], true);
  // Extract the book ID and form values
  $bookId = $bookSelect['BookId']; // Adjust the key name to match your book object structure
  $title = $data['updateBookTitle'];
  $author = $data['updateAuthor'];
  $publishYear = $data['updatePublishYear'];
  $availableBooks = $data['updateAvailableBooks'];
  $genr = $data['updateGenr'];

  // Call the updateBook function
  updateBook($bookId, $title, $author, $publishYear, $availableBooks, $genr);
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
    if ((isset($_SESSION['user_id']) || isset($_COOKIE['username'])) && $_SESSION['rule'] == 'Admin') {
      echo '<a href="?page=Adminstration" class="' . ($page === 'Adminstration' ? 'active' : '') . '">Adminstration</a>';
      echo '<a href="?page=libraryHistory" class="' . ($page === 'libraryHistory' ? 'active' : '') . '">libraryHistory</a>';

    }
    if (isset($_SESSION['user_id']) || isset($_COOKIE['username'])) {
      echo '<a href="?page=MyLibrary" class="' . ($page === 'MyLibrary' ? 'active' : '') . '">MyLibrary</a>';
      echo '<a href="?page=logout" class="' . ($page === 'logout' ? 'active' : '') . '">Logout</a>';
    } else {
      echo '<a href="?page=login" class="' . ($page === 'login' ? 'active' : '') . '">Login</a>';
    }
    ?>
  </div>

  <div class="content">
    <?php
    include 'db_connect.php';
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
    } elseif ($page === 'Adminstration') {
      echo "<h1 onclick=\"showPopUp('addBook')\">ADD BOOK</h1>";
      echo "<h1 onclick=\"showPopUp('updateBook')\">UPDATE BOOK</h1>";
      echo "<h1 onclick=\"showPopUp('registerUser')\">REGISTER USER</h1>";

    } elseif ($page === 'libraryHistory') {
      $history = borrowHistory();
      if (!empty($history)) {
        echo '<table>';
        foreach ($historyyy as $history) {
          echo '<td>' . htmlspecialchars($historyyy['username']) . '</td>';
        }
        echo '</table>';

      } else {
        echo "<h1>no history </h1>";
      }
    } else {
      echo "<h1>Page Not Found</h1>";
      echo "<p>The page you are looking for does not exist.</p>";
    }
    ?>
  </div>

  <!-- ADD BOOK Popup -->
  <div id="addBook" style="display: none;">
    <h2>Add a New Book</h2>
    <form id="addBookForm" >
    <input type="hidden" name="formType" value="addBookForm">

      <input type="hidden" name="formType" value="addBook">
      <label for="addBookTitle">Book Title:</label>
      <input type="text" id="addBookTitle" name="addBookTitle" required><br><br>

      <label for="addAuthor">Author:</label>
      <input type="text" id="addAuthor" name="addAuthor" required><br><br>

      <label for="addGenr">Author:</label>
      <input type="text" id="addGenr" name="addGenr" required><br><br>

      <label for="addPublishYear">Publish Year:</label>
      <input type="number" id="addPublishYear" name="addPublishYear" required><br><br>

      <label for="addAvailableBooks">Number of Copies:</label>
      <input type="date" id="addAvailableBooks" name="addAvailableBooks" required><br><br>

      <button type="submit">Add Book</button>
      <button type="button" onclick="closePopUp('addBook')">Cancel</button>
    </form>
  </div>


  <!-- Similarly, UPDATE BOOK and REGISTER USER forms will have unique formType values like "updateBook" and "registerUser". -->

  <!-- UPDATE BOOK Popup -->
  <!-- UPDATE BOOK Popup -->
  <div id="updateBook" style="display: none;">
    <h2>Update Existing Book</h2>
    <form id="updateBookForm" >
    <input type="hidden" name="formType" value="updateBookForm">

      <label for="bookSelect">Select Book Title:</label>
      <select id="bookSelect" name="bookSelect" onchange="loadBookDetails(this)">
        <!-- Options will be populated by JavaScript -->
      </select><br><br>

      <label for="updateBookTitle">Book Title:</label>
      <input type="text" id="updateBookTitle" name="updateBookTitle"><br><br>

      <label for="updateAuthor">Author:</label>
      <input type="text" id="updateAuthor" name="updateAuthor"><br><br>

      <label for="updatePublishYear">Publish Year:</label>
      <input type="date" id="updatePublishYear" name="updatePublishYear"><br><br>

      <label for="updateAvailableBooks">Number of Copies:</label>
      <input type="number" id="updateAvailableBooks" name="updateAvailableBooks"><br><br>
      <label for="updateGenr">Genr:</label>
      <input type="text" id="updateGenr" name="updateGenr"><br><br>
      <button type="submit">Update Book</button>
      <button type="button" onclick="closePopUp('updateBook')">Cancel</button>
    </form>
  </div>


  <!-- REGISTER USER Popup -->
  <div id="registerUser" style="display: none;">
    <h2>Register a New User</h2>
    <form id="registerUserForm">
    <input type="hidden" name="formType" value="registerUserForm">

      <label for="username">Username:</label>
      <input type="text" id="username" name="username"><br><br>

      <label for="firstName">First Name:</label>
      <input type="text" id="firstName" name="firstName"><br><br>

      <label for="lastName">Last Name:</label>
      <input type="text" id="lastName" name="lastName"><br><br>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email"><br><br>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password"><br><br>

      <label for="rule">rule:</label>
      <input type="rule" id="rule" name="rule"><br><br>

      <button type="submit">Register User</button>
      <button type="button" onclick="closePopUp('registerUser')">Cancel</button>
    </form>
  </div>

</body>

<script>

  function showPopUp(id) {
    const popup1 = document.getElementById("registerUser");
    popup1.style.display = 'none';
    const popup2 = document.getElementById("updateBook");
    popup2.style.display = 'none';
    const popup3 = document.getElementById("addBook");
    popup3.style.display = 'none';
    const popup = document.getElementById(id);
    popup.style.display = 'block';
  }

  function closePopUp(id) {
    const popup = document.getElementById(id);
    popup.style.display = 'none';
  }



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

  document.getElementById('addBookForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent the form from submitting normally


    // Create a new FormData object to capture the form data
    var formData = new FormData(this);

    // Perform the AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo $_SERVER['PHP_SELF']; ?>', true); // Form action (same page)
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        // Close the popup if the response is successful
        closePopUp('addBook');
        alert('Book added successfully!'); // Optionally, show a success message
      }
    };
    xhr.send(formData); // Send the form data to the server
  });

 

  document.getElementById('registerUserForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent the form from submitting normally


    // Create a new FormData object to capture the form data
    var formData = new FormData(this);

    // Perform the AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo $_SERVER['PHP_SELF']; ?>', true); // Form action (same page)
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        // Close the popup if the response is successful
        closePopUp('registerUser');
        alert('user added successfully!'); // Optionally, show a success message
      }
    };
    xhr.send(formData); // Send the form data to the server
  });
  document.getElementById('updateBookForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent the form from submitting normally

    // Create a new FormData object to capture the form data
    var formData = new FormData(this);

    // Perform the AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo $_SERVER['PHP_SELF']; ?>', true); // Form action (same page)
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Close the popup if the response is successful
            closePopUp('updateBook');
            alert('Book updated successfully!'); // Optionally, show a success message
        }
    };
    xhr.send(formData); // Send the form data to the server
});


  // Function to populate the book select dropdown with book titles
  function populateBookSelect() {
    var select = document.getElementById('bookSelect');
    select.innerHTML = ''; // Clear any existing options

    // Fetch all books using AJAX or preloaded data (adjust based on your implementation)
    fetchBooksFromServer(function (books) {
      books.forEach(function (book) {
        var option = document.createElement('option');
        option.value = JSON.stringify(book); // Store the entire book object as a JSON string
        option.textContent = book.Title; // Display the book title
        select.appendChild(option);
      });
    });
  }

  // Function to load book details into the form when a book is selected
  function loadBookDetails(selectElement) {
    var selectedBook = JSON.parse(selectElement.value); // Parse the JSON string back into an object

    if (selectedBook) {
      // Use the book object to populate the form fields
      document.getElementById('updateBookTitle').value = selectedBook.Title;
      document.getElementById('updateAuthor').value = selectedBook.Author;
      document.getElementById('updatePublishYear').value = selectedBook.PublishYear;
      document.getElementById('updateAvailableBooks').value = selectedBook.AvailableBooks;
      document.getElementById('updateGenr').value = selectedBook.Genre ;
    }
  }

  // Simulate fetching book data from the server (you can replace this with an AJAX call)
  function fetchBooksFromServer(callback) {
    var books = <?php echo json_encode(GetAllBooks()); ?>; // PHP function to get books
    callback(books);
  }

  function fetchBookDetails(bookId) {
    fetch(`get_book.php?id=${bookId}`)
      .then(response => response.json())
      .then(data => {
        if (data && data.Title) {
          // Use the Title property safely
          document.getElementById('book-title').textContent = data.Title;
        } else {
          console.error('Book data is undefined or missing Title.');
        }
      })
      .catch(error => {
        console.error('Error fetching book details:', error);
      });
  }

  // Initialize the select dropdown when the page loads
  window.onload = function () {
    populateBookSelect();
  };


</script>


</html>