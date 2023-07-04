<?php
// These are database connection parametters
    $servername = "localhost";
    $username = "sa";
    $password = "123";
    $db = "BookShop";

    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $db);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }/*else  
        echo "DB connection successful.";
*/
?>