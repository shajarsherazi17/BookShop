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
    
    // Start - Fetch Data from Database
        
    
    $fetch_sql = "SELECT sale_id, customer_name, customer_mail, product_id, product_name, product_price, sale_date FROM bs_sales";
    // End = Fetch Data from database
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
        <h3>Sales Data</h3>
        <!-- HTML Table to show results -->
        <table>
            <tr><th>Sale Id</th><th>Customer Name</th><th>Customer Email</th><th>Product ID</th><th>Product Name</th><th>Product Price</th><th>Sale Date</th></tr>
            <?php
                $total_price = 0;
                $conn = mysqli_connect($servername, $username, $password, $db);                
                if($fetch_result = $conn->query($fetch_sql)){
                    // for pagination
                    $total_records = mysqli_num_rows($fetch_result);
                    while ($result = $fetch_result->fetch_assoc()){
                        $total_price += $result['product_price'];
                        echo "<tr><td>".$result['sale_id']."</td><td>".$result['customer_name']."</td><td>".$result['customer_mail']."</td><td>".$result['product_id']."</td><td>".$result['product_name']."</td><td>".$result['product_price']."</td><td>".$result['sale_date']."</td></tr>";
                    }
                }
                echo "<tr><th></th><th></th><th></th><th></th><th>Total Price</th><th>".$total_price."</th><th></th></tr>";
                $conn->close();
            ?>
        </table>
    </body>
</html>