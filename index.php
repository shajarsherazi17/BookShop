<?php    
    require("db_connect.php"); // database connection string
    
    // Start - Import the file to database
    if(isset($_POST["submit"])) {
        // Read the JSON file 
        $json = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
        
        // Decode the JSON file
        $json_data = json_decode($json,true);
        $query= '';
        foreach($json_data as $row){
            //if ($row['sale_id'] > 6)
            $query .= " INSERT INTO bs_sales ( sale_id, customer_name, customer_mail, product_id, product_name, product_price, sale_date) 
                     VALUES ( '". $row['sale_id'] ."', '". $row['customer_name'] ."', '". $row['customer_mail'] ."', '". $row['product_id'] ."', '". mysqli_real_escape_string($conn, $row['product_name']) ."', '". $row['product_price'] ."', '". $row['sale_date']."');";               
        }
        if ($conn->multi_query($query) === TRUE) {
            echo "File has been Uploaded successfully.";
        }
        $conn -> close();
        unset($_POST["submit"]);
    }
    // End - Import the file to database
?>
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="author" content="Shajar Sherazi">
        <title>BookShop - Sales</title>
    </head>
    <body>        
        <form action="" name="sales_upload" method="post" enctype="multipart/form-data">
            Select sales file to import:
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Import" name="submit">
        </form>
    </body>
</html>