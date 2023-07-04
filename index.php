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
    
    // Start - Fetch Data from Database with or without filters
    $fetch_sql = "SELECT sale_id, customer_name, customer_mail, product_id, product_name, product_price, sale_date FROM bs_sales";
    
    // Declare default empty variables for search fields
    $srch_by_customer = '';
    $srch_by_product = '';
    $srch_by_price = '';

    if (isset($_POST['submit_search_form'])){
        $srch_by_customer = $_POST['srch_by_customer'];
        $srch_by_product = $_POST['srch_by_product'];
        $srch_by_price = $_POST['srch_by_price'];
        // if any of search filter is provided then add the where clause
        if($srch_by_price <> '' || $srch_by_customer <> '' || $srch_by_product <> '')
            $fetch_sql .= " WHERE";

        // search by Customer
        if($srch_by_customer <> ''){
            $fetch_sql .= " customer_name like '%".$srch_by_customer."%' ";
        }
        // Search by product and make sure the AND operator is working properly
        if($srch_by_product <> '' && $srch_by_customer <> ''){
            $fetch_sql .= " AND product_name like '%".$srch_by_product."%' ";
        }else if($srch_by_product <> ''){
            $fetch_sql .= " product_name like '%".$srch_by_product."%' ";
        }
        // Search by price and make sure the AND operator is working with other columns too
        if($srch_by_price <> '' && ($srch_by_customer <> '' || $srch_by_product <> '')){
            $fetch_sql .= " AND product_price = '".$srch_by_price."' ";
        }else if($srch_by_price <> '' ){
            $fetch_sql .= " product_price = '".$srch_by_price."' ";
        }
        $org_fetch_sql = $fetch_sql; // to preserve the original query with where clause 
        unset($_POST['submit_search_form']);
    }
    // End - Fetch Data from Database with or without filters

    // Start - Pagination and limit the 15 records per page    
    $conn = mysqli_connect($servername, $username, $password, $db);                
    $fetch_result_count = $conn->query($fetch_sql);
    $total_records = mysqli_num_rows($fetch_result_count);
    
    if(isset($_POST['btn_prev_page'])){ // if Previous button clicked then action 
        $org_fetch_sql = $_POST['fetch_sql']; // to reserve the original query for next iteration        
        //echo 'this is the hidden value'
        $page_no = $_POST['page_no'];
        $fetch_sql = $org_fetch_sql. " LIMIT $page_no, 15"; 
        $page_no -=15;
        if($page_no < 0)
            $page_no = 0;
        unset($_POST['btn_prev_page']);
    }else if(isset($_POST['btn_next_page'])){ // if Next button clicked then action
        $org_fetch_sql = $_POST['fetch_sql']; // to reserve the original query for next iteration
        $page_no = $_POST['page_no'];
        echo "page no in next $page_no <br/>";
        $fetch_sql = $org_fetch_sql. " LIMIT $page_no, 15";
        $page_no += 15;
        unset($_POST['btn_next_page']);
    }else{ // If none of pagination button clicked then load by default first 15 records
        $page_no = 0;
        $org_fetch_sql = $fetch_sql; // to reserve the original query for first page load
        if ($total_records/15 > 1){
            $fetch_sql .= " LIMIT 0, 15"; 
            $page_no = settype($page_no, "integer") + 15;
        }
    }
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
        <div>
            <!-- Start - Search by filters -->
            <form action="" name="form_search_data" method="post">
                Search Data:
                <input type="text" placeholder="Search by Customer" name="srch_by_customer" title="Search By Customer" value="<?php echo $srch_by_customer;?>">
                <input type="text" placeholder="Search by Product"  name="srch_by_product" title="Search By Product" value="<?php echo $srch_by_product;?>">
                <input type="text" placeholder="Search by Price"  name="srch_by_price" title="Search By Price" value="<?php echo $srch_by_price;?>">
                <input type="submit" name="submit_search_form" value="Search" title="Search" />
            </form>
            <!-- End - Search by filters -->
        </div>
        <form action="" name="pagination_form" method="post">
            <input type="hidden" value="<?php echo $page_no;?>" name="page_no">
            <input type="hidden" value="<?php echo $org_fetch_sql;?>" name="fetch_sql">
            <input type="submit" name="btn_prev_page" value="Prev">
            <input type="submit" name="btn_next_page" value="Next">
        </form>
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