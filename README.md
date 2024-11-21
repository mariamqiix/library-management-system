# Library Management System

This project is a simple library management system built with PHP and MySQL.

## Prerequisites

- [XAMPP](https://www.apachefriends.org/index.html) (includes Apache, MySQL, PHP)
- A web browser

## Setup Instructions

### Step 1: Install XAMPP

Download and install XAMPP from the [official website](https://www.apachefriends.org/index.html).

### Step 2: Start Apache and MySQL

Open the XAMPP Control Panel and start the Apache and MySQL services.

### Step 3: Create the Database

1. Open your terminal.
2. Navigate to your project directory:
   ```sh
   cd /path/to/your/project
   ```
3. Run the `database.php` script to create the database and tables:
   ```sh
   php database.php
   ```

### Step 4: Configure the Database Connection

1. Open the `db_connect.php` file in your project directory.
2. Update the database connection details as needed:
   ```php
   <?php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "library_management";

   // Create connection
   $conn = new mysqli($servername, $username, $password, $dbname);

   // Check connection
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }
   ?>
   ```

### Step 5: Run the PHP Scripts

#### Using the Terminal

1. Open your terminal.
2. Navigate to your project directory:
   ```sh
   cd /path/to/your/project
   ```
3. Run the PHP server:
   ```sh
    php -S localhost:8000 home.php
   ```

#### Using XAMPP

1. Place your project directory inside the `htdocs` folder of your XAMPP installation (e.g., `C:\xampp\htdocs\library_management`).
2. Open your web browser and go to `http://localhost/library_management`.

### Step 6: Access the Application

Open your web browser and go to `http://localhost/library_management` to access the library management system.

## Usage

- Add, update, and delete books.
- Borrow and return books.
- View book details and genres.

## Troubleshooting

- Ensure that Apache and MySQL services are running in the XAMPP Control Panel.
- Check the database connection details in `db_connect.php`.
- Verify that the database schema was created correctly by running `database.php`.

## License

This project is licensed under the MIT License.

# library-management-system
