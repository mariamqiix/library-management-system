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
  $action = $_POST['action'] ?? '';
  if ($action === 'borrowBook') {
    $bookId = $_POST['bookId'] ?? '';
    $userId = $_POST['userId'] ?? '';
    print ($bookId . PHP_EOL);
    print ($userId . PHP_EOL);
    if (borrowBook($bookId, $userId)) {
      echo "Book borrowed successfully.";
    }
  }
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
  $genre = $data['addGenr'];
  $imageFile = $_FILES['bookImage']; // The uploaded file
  // Check if the file is null or not uploaded
  if (empty($imageFile['name']) || $imageFile['error'] == UPLOAD_ERR_NO_FILE) {
    // No file uploaded, return the default ImageId = 1
    $imageID = 1;  // Default ImageId when no file is uploaded
  } else {
    $imageID = uploadImageToDatabase2($imageFile);
  }
  createBook($title, $author, $publishYear, $availableBooks, $genre, $imageID);
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
    $imageFile = $_FILES['userImage']; // The uploaded file

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
    // Check if the file is null or not uploaded
    if (empty($imageFile['name']) || $imageFile['error'] == UPLOAD_ERR_NO_FILE) {
      // No file uploaded, return the default ImageId = 1
      $imageID = 2;  // Default ImageId when no file is uploaded
    } else {
      $imageID = uploadImageToDatabase2($imageFile);
    }
    // Include the database functions file
    try {
      // Call the CreatetUser function to save the user
      CreatetUser($username, $firstName, $lastName, $hashedPassword, $email, $rule, $imageID);
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
<link rel="stylesheet" href="style.css">

<head>
  <meta charset="UTF-8">
  <title>Library Management System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>

<body>
  <!-- Top Navigation Bar -->
  <div class="top-navbar">
    <div class="logo">
    </div>
    <div class="custom-search-container">
      <form action="?page=search" method="POST" id="customSearch">
        <input type="text" name="query" id="customSearchInput" placeholder="Search for books, authors..." required>
        <button type="submit" id="customSearchButton">Search</button>
      </form>
    </div>
  </div>

  <div class="navbar">
    <!-- Navigation bar links -->
    <a href="?page=home" class="<?= $page === 'home' ? 'active' : '' ?>">
      <i class="fa fa-home"></i> <span>Home</span>
    </a>
    <a href="?page=All Books" class="<?= $page === 'All Books' ? 'active' : '' ?>">
      <i class="fa fa-book"></i> <span>All Books</span>
    </a>
    <a href="?page=genres" class="<?= $page === 'genres' ? 'active' : '' ?>">
      <i class="fa fa-tags"></i> <span>Genres</span>
    </a>
    <?php
    session_start();
    if ((isset($_SESSION['user_id']) || isset($_COOKIE['username'])) && $_SESSION['rule'] == 'Admin') {
      echo '<a href="?page=Adminstration" class="' . ($page === 'Adminstration' ? 'active' : '') . '"><i class="fa fa-cogs"></i> <span>Adminstration</span></a>';
      echo '<a href="?page=libraryHistory" class="' . ($page === 'libraryHistory' ? 'active' : '') . '"><i class="fa fa-history"></i> <span>Library History</span></a>';
    }
    if (isset($_SESSION['user_id']) || isset($_COOKIE['username'])) {
      echo '<a href="?page=MyLibrary" class="' . ($page === 'MyLibrary' ? 'active' : '') . '"><i class="fa fa-bookmark"></i> <span>My Library</span></a>';
      echo '<a href="?page=logout" class="' . ($page === 'logout' ? 'active' : '') . '"><i class="fa fa-sign-out"></i> <span>Logout</span></a>';
    } else {
      echo '<a href="?page=login" class="' . ($page === 'login' ? 'active' : '') . '"><i class="fa fa-sign-in"></i> <span>Login</span></a>';
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
      $books = GetAllBooks();

      if (!empty($books)) {
        if (!empty($books)) {
          echo '<div class="book-list-container">';
          echo '<h2>Recommended</h2>';
          echo '<a href="?page=All Books" class="' . ($page === 'All Books' ? 'active' : '') . '">See All &rsaquo;</a>';
          echo '<div class="book-list">';

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
          echo '</div>';


        } else {
          echo "<p>No books available at the moment.</p>";
        }

        echo '<div class="book-list-container" id="genre-1-books-container">';
        echo '<h2>Fiction Books</h2>';
        echo '<a href="?page=genres" class="' . ($page === 'genres' ? 'active' : '') . '">See All Geners &rsaquo;</a>';
        echo '<div class="book-list" id="genre-1-books">';
        echo '</div>';
        echo '</div>';

      } else {
        echo "<p>No books available at the moment.</p>";
      }
    } elseif ($page === 'All Books') {
      $books = GetAllBooks();
      if (!empty($books)) {
        echo '<div class="all-books-container">';
        echo '<h2>All Books</h2>';
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
        echo '</div>';
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
    } elseif ($page === 'search') {
      include 'search.php'; //
    } elseif ($page === 'register') {
      register();
    } elseif ($page === 'logout') {
      logout();
    } elseif ($page === 'Adminstration') {
      echo '<div class="admin-options">';
      echo '<ul class="options-list">';
      echo '<li onclick="showPopUp(\'addBook\')">ADD BOOK</li>';
      echo '<li onclick="showPopUp(\'updateBook\')">UPDATE BOOK</li>';
      echo '<li onclick="showPopUp(\'registerUser\')">REGISTER USER</li>';
      echo '</ul>';
      echo '</div>';

    } elseif ($page === 'libraryHistory') {
      $history = borrowHistory();
      if (!empty($history)) {
        echo '<table border="1" cellpadding="10">';
        echo '<tr>
                  <th>User ID</th>
                  <th>Username</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Email</th>
                  <th>Book Title</th>
                  <th>Book Author</th>
                  <th>Genre</th>
                  <th>Publish Year</th>
                  <th>Available Books</th>
                  <th>Borrow Date</th>
                  <th>Return Date</th>
                </tr>';
        foreach ($history as $entry) {
          echo '<tr>';
          echo '<td>' . $entry->userDetails->userId . '</td>';
          echo '<td>' . $entry->userDetails->username . '</td>';
          echo '<td>' . $entry->userDetails->firstName . '</td>';
          echo '<td>' . $entry->userDetails->lastName . '</td>';
          echo '<td>' . $entry->userDetails->email . '</td>';
          echo '<td>' . $entry->bookDetails->bookTitle . '</td>';
          echo '<td>' . $entry->bookDetails->bookAuthor . '</td>';
          echo '<td>' . $entry->bookDetails->bookGenre . '</td>';
          echo '<td>' . $entry->bookDetails->publishYear . '</td>';
          echo '<td>' . $entry->bookDetails->availableBooks . '</td>';
          echo '<td>' . $entry->bookDetails->borrowDate . '</td>';
          echo '<td>' . $entry->bookDetails->returnDate . '</td>';
          echo '</tr>';
        }
        echo '</table>';
      } else {
        echo "<h1>No history available</h1>";
      }
    } else {
      echo "<h1>Page Not Found</h1>";
      echo "<p>The page you are looking for does not exist.</p>";
    }
    ?>
  </div>
  <div id="bookDetailsSidebar" class="sidebar">
    <button class="close-btn" onclick="closeSidebar()">×</button>
    <img id="bookImage" src="" alt="Book Cover" class="book-image">
    <h2 id="bookTitle" class="book-title"></h2>
    <p id="bookAuthor" class="book-author"></p> <!-- Added author field -->
    <div class="book-info">
      <p><strong>Date of Publish:</strong> <span id="bookPublishYear"></span></p>
      <p><strong>Available Copies:</strong> <span id="bookCopies"></span></p>
      <p><strong>Genre:</strong> <span id="bookGenre"></span></p>
      <p><strong>Description:</strong> <span id="bookDescription"></span></p>
    </div>
    <button id="borrowButton" onclick="borrowSelectedBook()">Borrow</button>
  </div>

  <!-- ADD BOOK Popup -->
  <div id="addBook" style="display: none;">
    <h2>Add a New Book</h2>
    <form id="addBookForm">
      <input type="hidden" name="formType" value="addBookForm">

      <label for="addBookTitle">Book Title:</label>
      <input type="text" id="addBookTitle" name="addBookTitle" required><br><br>

      <label for="addAuthor">Author:</label>
      <input type="text" id="addAuthor" name="addAuthor" required><br><br>

      <label for="addGenr">Genr:</label>
      <input type="text" id="addGenr" name="addGenr" required><br><br>

      <label for="addPublishYear">Publish Year:</label>
      <input type="date" id="addPublishYear" name="addPublishYear" required><br><br>

      <label for="addAvailableBooks">Number of Copies:</label>
      <input type="number" id="addAvailableBooks" name="addAvailableBooks" required><br><br>
      <label for="bookImage">Book Image (Optional):</label>
      <input type="file" name="bookImage"><br>
      <button type="submit">Add Book</button>
      <button type="button" onclick="closePopUp('addBook')">Cancel</button>
    </form>
  </div>


  <!-- Similarly, UPDATE BOOK and REGISTER USER forms will have unique formType values like "updateBook" and "registerUser". -->

  <!-- UPDATE BOOK Popup -->
  <!-- UPDATE BOOK Popup -->
  <div id="updateBook" style="display: none;">
    <h2>Update Existing Book</h2>
    <form id="updateBookForm">
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
      <label for="userImage">Profile Image (Optional):</label>
      <input type="file" name="userImage"><br>
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
        console.log(data);
        if (data.length > 0) {
          const booksList = data.map(book => `
        <li class="book-item" onclick='showBookDetails(${JSON.stringify(book)})'>
            <strong>Title:</strong> ${book.Title}<br>
            <strong>Author:</strong> ${book.Author}<br>
            <strong>Publish Year:</strong> ${book.PublishYear}<br>
            <strong>Available Copies:</strong> ${book.AvailableBooks}<br>
            <strong>Genre:</strong> ${book.Genre}
        </li>
              `).join('');

          // Insert the list of books into the container
          booksContainer.innerHTML = `<ul id="bookList">${booksList}</ul>`;
        } else {
          // If no books, display a message
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
      document.getElementById('updateGenr').value = selectedBook.Genre;
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
  let selectedBook = null;
  function showBookDetails(book) {
    selectedBook = book.BookId;

    // Set image source (handling Base64 image encoding)
    let imageUrl = 'data:image/jpeg;base64,' + book.Image;
    document.getElementById('bookImage').src = imageUrl;

    // Set book title, author, and other details
    document.getElementById('bookTitle').innerText = book.Title;
    document.getElementById('bookAuthor').innerText = book.Author; // Populate author

    // Set publish year
    document.getElementById('bookPublishYear').innerText = book.PublishYear || book.Publish_year;

    // Set available copies
    document.getElementById('bookCopies').innerText = book.AvailableBooks || book.Available_books;

    // Set genre
    document.getElementById('bookGenre').innerText = book.Genre;

    // Set description
    document.getElementById('bookDescription').innerText = book.Description || "No description available.";

    // Enable or disable borrow button
    const borrowButton = document.getElementById('borrowButton');
    if (book.AvailableBooks > 0 || book.Available_books > 0) {
      borrowButton.disabled = false;
    } else {
      borrowButton.disabled = true;
    }

    // Show the sidebar
    document.getElementById('bookDetailsSidebar').classList.add('active');
  }

  function closeSidebar() {
    document.getElementById('bookDetailsSidebar').classList.remove('active');
    selectedBook = null;
  }


  function borrowSelectedBook() {
    const userId = '<?php echo isset($_COOKIE['username']) ? $_COOKIE['username'] : null; ?>';
    if (userId) {
      borrowBook(selectedBook, userId);
    } else {
      alert('User not logged in!');
    }
  }
  function borrowBook(bookId, userId) {
    // Perform AJAX request to borrow the book
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo $_SERVER['PHP_SELF']; ?>', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          alert('Book borrowed successfully!');
          closeSidebar();
          location.reload(); // Refresh to update available copies
        } else {
          console.error('Error:', xhr.responseText);
          alert('Failed to borrow the book. Please try again.');
        }
      }
    };
    const params = `action=borrowBook&bookId=${encodeURIComponent(bookId)}&userId=${encodeURIComponent(userId)}`;
    xhr.send(params);
  }



  fetch('fetch_books.php?genreId=1')
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

      // Get the genre 1 books container
      const container = document.getElementById('genre-1-books');
      console.log('Raw response:', data); // Log raw response

      // Check if books exist
      if (data && data.length > 0) {
        data.forEach(book => {
          // Create a book item and insert it into the container
          const bookItem = document.createElement('div');
          bookItem.classList.add('book-item');
          bookItem.onclick = () => showBookDetails(book); // Assuming showBookDetails is a JS function

          const bookImage = document.createElement('img');
          bookImage.src = 'data:image/jpeg;base64,' + book.Image;
          bookImage.alt = 'Book Cover';

          const bookDetails = document.createElement('div');
          bookDetails.classList.add('book-details');

          const bookTitle = document.createElement('div');
          bookTitle.classList.add('book-title');
          bookTitle.textContent = book.Title;

          const bookAuthor = document.createElement('div');
          bookAuthor.classList.add('book-author');
          bookAuthor.textContent = book.Author;

          bookDetails.appendChild(bookTitle);
          bookDetails.appendChild(bookAuthor);
          bookItem.appendChild(bookImage);
          bookItem.appendChild(bookDetails);

          container.appendChild(bookItem);
        });
      } else {
        container.innerHTML = '<p>No books available for this genre.</p>';
      }
    })
    .catch(error => {
      console.error('Error fetching books:', error);
    });
</script>


</html>