<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Book Shop</title>
</head>
<body>
    <?php
    ini_set('display_errors', 0);
    session_start();
    $conn = pg_connect("host= port= dbname= user= password=");
    ?>

    <header>
        <nav>
            <div class="logo">
                <img src="/images/Just read.png" alt="logo">
            </div>
            <div class="links">
                <a href="/index.php?books"><div class="link" id="b1" onclick="changeTab(document.getElementById('books'))">
                    Books
                </div></a>
                <a href="/index.php?contact"><div class="link" id="b2">
                    Contact
                </div></a>
                <a href="/index.php?account"><div class="link" id="b3">
                    Account
                </div></a>
            </div>
            <div class="login">
                <a href="index.php?order"><div class="order">
                    <img src="/images/shopping_cart.png" onclick="changeTab(document.getElementById('order_tab'))">
                </div></a>
            <?php    
                if(!isset($_SESSION['login_username']))
                {
                echo '<a href="/index.php?login"><div class="button_login">
                    Login
                </div></a>';
                echo '<a href="/index.php?signup"><div class="button_login">
                    Sign Up
                    </div></a>';
                }
                else{
                    clearstatcache();
                    $username = $_SESSION['login_username'];
                        $sql_get_name = "SELECT first_name from users where username like '$username'";
                        $result_get_name = pg_query($sql_get_name);
                        $getname = pg_fetch_row($result_get_name);
                        $profile_pic1 = "/uploads/$username.jpg";
                        $profile_pic2 = "/uploads/$username.png";
                        $profile_pic3 = "/uploads/$username.jpeg";
                        $unknown = "/images/unknown.png";
                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$profile_pic1)) $profile = $profile_pic1;
                        else if(file_exists($_SERVER['DOCUMENT_ROOT'].$profile_pic2)) $profile = $profile_pic2;
                        else if(file_exists($_SERVER['DOCUMENT_ROOT'].$profile_pic3)) $profile = $profile_pic3;
                        else $profile = "/images/unknown.png";
                    echo '
                <a href="/index.php?account"><div class="login_info">
                    <div class="pic" style="background-image: url('."$profile".')">
                    </div>
                    <div class="login_name">';
                        echo $getname[0];
                 echo'   </div>
                </div></a>';
                }     
            ?>    
            </div>
        </nav>
    </header>

    <div id="contact">
        <h1>Details</h1>
        <div>
            <h3>Address: 26-28 George Barițiu Street, Cluj-Napoca</h3>
        </div>
        <div>
            <a href="tel: 0754-123-412"><h3>Telephone: 0754 123 412</h3></a>
        </div>
        <div>
            <a href="mailto: bookshop@libris.com"><h3>E-mail: bookshop@libris.com</h3></a>
        </div>
    </div>
    
    <!--
    <div id="books">
        <div class="card">
            <div class="card_pic">
            </div>
            <div class="card_title">
                Title of the book
            </div>
            <div class="card_author">
                Description of the book
            </div>
        </div>
    </div>
-->
  

    <?php
    //$servername = "localhost";
    //$username = "postgres";
    //$port = "5432";
    //$password = "Blaj?123";
    //$dbname = "bookshop";
    
    // Create connection
    
    function setName(&$bookTitle, $row) 
    {
            $array = explode(" ", $row);
            $arrayLength = count($array);
            $space = '_';
            $i = 0;
            while($i<$arrayLength)
            {
                $str = substr($array[$i], -1);
                if($str == ':') $array[$i] = rtrim($array[$i], ":");
                if($str == ',') $array[$i] = rtrim($array[$i], ",");
                $array[$i]=ucfirst($array[$i]);
                if($bookTitle!='"/images/')$bookTitle = $bookTitle.$space.$array[$i];
                else $bookTitle = $bookTitle.$array[$i];
                $i++;
            }
            $bookTitle = $bookTitle.'.jpg"';
    }

    function setNameCover(&$bookTitle, $row) 
    {
            $array = explode(" ", $row);
            $arrayLength = count($array);
            $space = '_';
            $i = 0;
            while($i<$arrayLength)
            {
                $str = substr($array[$i], -1);
                if($str == ':') $array[$i] = rtrim($array[$i], ":");
                if($str == ',') $array[$i] = rtrim($array[$i], ",");
                $array[$i]=ucfirst($array[$i]);
                if($bookTitle!='')$bookTitle = $bookTitle.$space.$array[$i];
                else $bookTitle = $bookTitle.$array[$i];
                $i++;
            }
    }
    // Check connection
      if($conn)
      {
        $ok = 1;
        if(isset($_SESSION['login_username']))
        {
            $username=$_SESSION['login_username'];
            $password=$_SESSION['login_password'];
            $sql_login_remember = "SELECT user_id, authority from users where username like '$username' and password like '$password'";
            $result_login_remember = pg_query($sql_login_remember);
            $row_login = pg_fetch_row($result_login_remember);
            $userid = $row_login[0];
            $authority=$row_login[1];
            if($authority==1) {
                $stock_value = 0;
            }
            else {
                $stock_value = 1;
            }
            $connected=true;
        }
        else {
            $userid =0;
            $username="";
            $connected=false;
            $authority=0;
            $stock_value = 1;
        }
            if(isset($_GET['submit'])){
                $search = $_GET['search_bar'];
                $author = $_GET['author'];
                $price = $_GET['price_filter'];
                if(isset($_GET['type']))
                {
                    if($_GET['type']!="default"){
                        $type = strtolower($_GET['type']);
                        //echo 'no';
                    }
                    else $type = "%";
                }
                else $type = "%";
                if($_GET['author']!='default') {
                    $sql = "SELECT title, author_name, b.book_price, b.book_id, b.book_description from author join book b on author.author_id = b.author_id where author_name like '$author' and b.book_price <= '{$price}' and b.stock_value >= '$stock_value' and LOWER(title) LIKE LOWER('%$search%') group by (title, author_name, b.book_price, b.book_id, b.book_description) ORDER BY b.book_id ASC";
                }
                else {
                    $sql = "SELECT title, author_name, b.book_price, b.book_id, b.book_description from author join book b on author.author_id = b.author_id where b.book_price <= '{$price}' and b.stock_value >= '$stock_value' and (LOWER(title) LIKE LOWER('%$search%') or LOWER(author_name) like LOWER('%$search%')) group by (title, author_name, b.book_price, b.book_id, b.book_description) ORDER BY b.book_id ASC";
    
                }
            }
            else {
                $type="%";
                $search="%";
                $price = 25;
                $sql = "SELECT title, author_name, b.book_price, b.book_id, b.book_description from author join book b on author.author_id = b.author_id and b.stock_value >= '$stock_value' and (title LIKE '%$search%' or author_name like '%$search%') group by (title, author_name, b.book_price, b.book_id, b.book_description) ORDER BY b.book_id ASC";
            }
            
            $result = pg_query($sql);
            echo '<div id="books">';
                echo '<h1>Shop</h1>';
                echo '<div class="filters">';
                    echo "<form action='/index.php' method='GET'>";
                    echo "<div>";
                    echo '<label for="authors">Authors:&nbsp</label>';
                    echo '<select name="author" id="author">';
                    echo "<option value='default'>--Authors--</option>";
                    $sql_authors_filter = "SELECT author_name from author";
                    $result_authors_filter = pg_query($sql_authors_filter);
                    while($row_authors = pg_fetch_row($result_authors_filter)) {
                        $array = explode(" ", $row_authors[0]);
                        echo "<option value='$row_authors[0]'>$row_authors[0]</option>";
                    }
                    echo '</select>';
                    echo '</div>';
                    echo '<div class="slider">';
                    echo '<p>Price: </p>';
                    echo "<p>1$</p>";
                    echo "<input type='range' min='1' max='25' value='$price' name='price_filter' id='myRange' onchange='showValue(this.value)'>";
                    echo '<p>25$</p>';
                    echo '<input id="show_value" style="width: 25px; text-align: center; display: flex; justify-content: center; align-items: center;" value="25"><p style="text-align: center; display: flex; justify-content: center; align-items: center; margin: 0;">$</input>';
                    echo '</div>';
                    echo '<div class="types_filter">';
                    echo '<label for="type">Type:&nbsp</label>';
                    echo '<select name="type" class="select_type">';
                    echo '<option class="type" value="default">--All types--</option>';
                    $sql_get_type="SELECT book_type, color from type";
                    $result_get_type = pg_query($sql_get_type);
                    while($row_get_type=pg_fetch_row($result_get_type))
                    {
                        strtoupper($row_get_type[0][0]);
                        echo "<option class='type' style='background-color: $row_get_type[1]; color: rgb(155,155,155);'>$row_get_type[0]</option>";
                    }
                    echo '</select>';
                    echo '</div>';
                    echo '<div>';
                    echo '<input type="text" name="search_bar" placeholder="Search..."/ style="margin: 0px 10px;">';
                    echo '</div>';
                    echo '<button type="submit" name="submit"  class="filter_button">Filter</button>';
                    echo '</form>';
                echo "</div>";
                echo '<div class="cards">';
              while($row = pg_fetch_row($result)) {
                $sql_type = "SELECT book_type from book join types t on book.book_id = t.book_id join type t2 on t2.type_id = t.type_id where book.book_id = '$row[3]' group by (book_type)";
                $result_type = pg_query($sql_type);
                $ok=0;
                while($row_type = pg_fetch_row($result_type)){
                    if($type!="%")
                    {
                        if($row_type[0]==$type)
                        {
                            $ok=1;
                        }
                    }
                    else $ok=1;
                }
                if($ok==1)
                {
                $bookTitle = '"/images/';
                setName($bookTitle,$row[0]);
                echo "<form action='/index.php' method='POST'>";
                echo "<div class='card' id='card'>";
                echo '<div class="types">';
                $sql_type = "SELECT book_type, color from book b join types t on b.book_id = t.book_id join type t2 on t2.type_id = t.type_id where b.book_id='$row[3]' group by (b.book_id, book_type, color)";
                $result_type = pg_query($sql_type);
                while($row_type = pg_fetch_row($result_type)) {
                    $color = $row_type[1];
                    echo "<span style='background-color: $color; margin-top: 10px;' class='type'></span>";
                }
                echo '</div>';
                echo "<a href='/index.php?books?c$row[3]'><div class='card_pic' style='background-image: url($bookTitle)'>";
                echo "</div></a>";
                echo '<div class="card_title">';
                //echo '<input name="add_title" type="submit" value="'.$row[0].'" name="submit1" class=card_title title="Add to cart"';
                echo "$row[0]";
                echo "</div>";
                echo '<div class="card_author">';
                echo "$row[1]";
                echo "</div>";
                //echo '<div class="card_price" title="Add to cart" onclick="add.submit();">';
                //echo '<input name="add" type="submit" value="'.$row[2].'$" name="submit1" class="card_price" title="Add to cart">';
                echo '<button name="add" type="submit" value="'.$row[3].'" class="card_price">';
                echo "$row[2]$";
                echo '</button>';
                //echo '</div>';
                //echo "id: " . $row[0]. " - Name: " . $row[1]. " - Author: " . $row[2]. "<br>";
                echo "</div>";
                echo '</form>';
            }
              }
              echo '</div>';
            echo "</div>";

            $result=pg_query($sql);
            while($row = pg_fetch_row($result)) {
                $bookTitle = '"/images/';
                setName($bookTitle,$row[0]);
                echo "<div id='c$row[3]' class='book_info'>";
                echo "<h2 style='padding-bottom: 50px; text-align: center;'>$row[0] by $row[1]</h2>";
                echo '<div class="book_info2">';
                echo "<div class='book_pic' style='background-image: url($bookTitle)'></div>";
                echo "<div class='book_description'>";
                echo "$row[4]";
                echo '<div class="info">';
                echo '<div class="types_info">';
                echo "Type(s): ";
                $sql_type = "SELECT book_type, color from type join types t on type.type_id = t.type_id join book b on b.book_id = t.book_id where b.book_id = '$row[3]' group by (book_type, color)";
                $result_type = pg_query($sql_type);
                $first = 0;
                while($row_type = pg_fetch_row($result_type))
                {
                    if($first==0)
                    {
                        $first = 1;
                    }
                    else echo ", ";
                    $color=$row_type[1];
                    $row_type[0][0] = strtoupper($row_type[0][0]);
                    echo "$row_type[0]";
                    echo "<span style='background-color: $color;' class='type_info'></span>";
                }
                echo '</div>';
                echo '<div class="character_info">';
                $sql_char = "SELECT main_character from book where book_id = '$row[3]'";
                $result_char = pg_query($sql_char);
                $row_char = pg_fetch_row($result_char);
                echo "Main character: $row_char[0]";
                echo '</div>';
                echo '<div class="publisher_info">';
                $sql_publisher = "SELECT publisher_name from publisher join book b on publisher.publisher_id = b.publisher_id where book_id = '$row[3]'";
                $result_publisher = pg_query($sql_publisher);
                $row_publisher = pg_fetch_row($result_publisher);
                echo "Publisher: $row_publisher[0]";
                echo '</div>';
                echo '</div>';
                echo '<form method="POST">';
                echo '<button name="add" type="submit" value="'.$row[3].'" class="card_price" style="margin-top: 30px;">';
                echo "$row[2]$";
                echo '</button>';
                echo '</form>';
                echo "</div>";
                echo "</div>";
                if($authority==1)
                {
                    $sql_change="SELECT * from book where book_id = '$row[3]'";
                    $result_change = pg_query($sql_change);
                    $row_change=pg_fetch_row($result_change);
                    echo "<form action='' method='POST' class='form_change'>";
                        echo '<label for="title">Title: </label>';
                        echo '<input type="text" name="title" value="'.$row_change[1].'">';
                        echo '<label for="author">Author: </label>';
                        echo '<select name="author" id="author">';
                            $sql_get_authors="SELECT author_name, author_id from author";
                            $result_get_authors = pg_query($sql_get_authors);
                            while($row_get_authors = pg_fetch_row($result_get_authors))
                            {
                                if($row_change[3]==$row_get_authors[1]){
                                    echo "<option value='$row_get_authors[0]' selected>$row_get_authors[0]</option>";
                                }
                                else echo "<option value='$row_get_authors[0]'>$row_get_authors[0]</option>";
                            }
                        echo '</select>';
                        echo '<label for="publisher" name="publisher">Publisher: </label>';
                        echo '<select name="publisher" id="publisher">';
                            $sql_get_publishers="SELECT publisher_name, publisher_id from publisher";
                            $result_get_publishers = pg_query($sql_get_publishers);
                            while($row_get_publishers = pg_fetch_row($result_get_publishers))
                            {
                                if($row_change[2]==$row_get_publishers[1]) {
                                    echo "<option value='$row_get_publishers[0]' selected>$row_get_publishers[0]</option>";
                                }
                                else echo "<option value='$row_get_publishers[0]'>$row_get_publishers[0]</option>";
                            }
                        echo '</select>';
                        echo '<label for="description">Description: </label>';
                        echo '<input type="text" name="description" value="'.$row_change[4].'">';
                        echo '<label for="type">Types: </label>';
                        echo '<select name="type[]" id="type" multiple="multiple">';
                        $sql_get_types_change = "SELECT book_type from type";
                        $result_get_types_change = pg_query($sql_get_types_change);
                        $sql_get_type = "SELECT book_type from type t JOIN types ts on t.type_id=ts.type_id WHERE book_id = '$row[3]'";
                        $result_get_type = pg_query($sql_get_type);
                        $p=0;
                        while($row_get_type=pg_fetch_row($result_get_type)) {
                            $t[$p]=$row_get_type[0];
                            $p++;
                        }
                        while($row_get_types_change=pg_fetch_row($result_get_types_change)) {
                            $okk=0;
                            for($n=0;$n<$p;$n++) {
                                if($t[$n]==$row_get_types_change[0]) {
                                    $okk=1;
                                }
                            }
                            if($okk==1) {
                                echo "<option value='$row_get_types_change[0]' selected>$row_get_types_change[0]</option>";
                            }
                            else {
                                echo "<option value='$row_get_types_change[0]'>$row_get_types_change[0]</option>";
                            }
                        }
                        echo '</select>';
                        echo '<label for="main_character">Main character: </label>';
                        echo '<input type="text" name="main_character" value="'.$row_change[5].'">';
                        echo '<label for="price">Price: </label>';
                        echo '<input type="text" name="price" value="'.$row_change[6].'">';
                        echo '<label for="stock">Stock value: </label>';
                        echo '<input type="text" name="stock" value="'.$row_change[7].'">';
                        echo '<button type="submit" name="submit_change_book" value="'.$row[3].'" class="button_add">Change</button>';
                        echo '<button type="submit" name="submit_delete_book" value="'.$row[3].'" class="button_add">Delete</button>';
                    echo '</form>'; 
                }
                echo "</div>";
            }

        
            $sum=0;
            if($userid==0){
                echo '<div id="order_tab">';
                echo '<p style="text-align:center;">You need to log in to be able to order!</p>';
                echo '</div>';
              }
              else
              {
                echo '<div id="order_tab">';
                echo '<h1>Cart</h1>';
                $sql_cart='SELECT title, amount, book_price, o.product_id from cart o join book book on book.book_id = o.product_id where o.user_id = '.$userid.' group by (title, amount, book.book_price, o.product_id) order by o.product_id asc';
                $result_cart = pg_query($sql_cart);
                //$sql_cart_pic = 'SELECT title, amount from book join orders o on book.book_id = o.product_id where o.user_id = "'.$user.'" group by (title, amount)';
                //$result_cart_pic = pg_query($sql_cart_pic);
                
                $i=0;
                while($row_cart=pg_fetch_row($result_cart)) {
                    $i++;
                    echo '<div class="order_product">';
                    //$result_cart_pic_row = pg_fetch_row($result_cart_pic);
                    $bookTitle = '"/images/';
                    setName($bookTitle, $row_cart[0]);
                    echo '<div class="order_pic_details">';
                    echo "<div class='order_pic' style='background-image: url($bookTitle)'>";
                    echo '</div>';
                    echo '<div class="order_details">';
                    echo "$row_cart[0]";
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="order_amount">';
                    echo '<form method="POST">';
                    echo "<button type='submit' name='sub_value$i' onlick='addAmount(-1)'>-</button><input type='text' id= 'button_amount' value='$row_cart[1]' style='width:20px; text-align:center'></input><button type='submit' name='add_value$i' onlick='addAmount(1)'>+</button>";
                    echo '</form>';
                    echo "<div>&nbspx&nbsp$row_cart[2]$</div>";
                    echo '</div>';
                    echo '</div>';
                    $sum = $sum + $row_cart[2] * $row_cart[1];
                }
                if($sum>0)
                {
                    echo '<div class="order_total">';
                    echo "<div>Total amount: $sum$</div>";
                    echo '</div>';
                    echo '<form action="/index.php?finalize" method="POST">';
                    echo '<button type="submit" name="finalize" class="button_empty">Finalize order</button>';
                    echo '</form>';
                    echo '<form action="/index.php?order" method="POST">';
                    echo '<button type="submit" name="refresh" class="button_empty" value="Refresh" onclick="refresh()">Empty cart</button>';
                    echo '</form>';
                }
                else 
                {
                    echo '<p style="text-align: center;">Your shopping cart is empty. Add products to be able to order!</p>';
                }
                echo '</div>';
            }
          }

          if(isset($_POST['submit_change_book'])){
            $title_change = $_POST['title'];
            $author_change = $_POST['author'];
            $sql_get_author = "SELECT author_id from author where author_name like '$author_change'";
            $result_get_author = pg_query($sql_get_author);
            $author_id_change = pg_fetch_row($result_get_author);
            $publisher_change = $_POST['publisher'];
            $sql_get_publisher = "SELECT publisher_id from publisher where publisher_name like '$publisher_change'";
            $result_get_publisher = pg_query($sql_get_publisher);
            $publisher_id_change = pg_fetch_row($result_get_publisher); 
            $description_change = $_POST['description'];
            $description_change = str_replace('\'','`',$description_change);
            $description_change = str_replace('"','`',$description_change);
            $type = [];
            $j=0;
            foreach ($_POST['type'] as $typearr)
            {
                $type[$j]=$typearr;
                //echo $type[$j];
                $j++;
            }
            $main_character_change = $_POST['main_character'];
            $price_change = $_POST['price'];
            $stock_change = $_POST['stock'];
            $book_id_change = 0;
            $sql_get_book_id = "SELECT book_id from book";
            $result_get_book_id = pg_query($sql_get_book_id);
            while($row_get_book_id = pg_fetch_row($result_get_book_id)){
                if($row_get_book_id[0]==$_POST['submit_change_book']) {
                    $book_id_change = $row_get_book_id[0];
                }
            }
            $sql_change_book = "UPDATE book SET title = '$title_change', publisher_id = '$publisher_id_change[0]', author_id = '$author_id_change[0]', book_description = '$description_change', main_character = '$main_character_change', book_price = '$price_change', stock_value = '$stock_change' WHERE book_id = $book_id_change";
            if($authority==1) {
                pg_query($sql_change_book);
                $sql_insert_change = "DELETE from types WHERE book_id = '$book_id_change'";
                pg_query($sql_insert_change);
                for($i=0;$i<$j;$i++) {
                    $sql_get_id_type = "SELECT type_id from type WHERE book_type like '$type[$i]'";
                    $result_get_id_type = pg_query($sql_get_id_type);
                    $type_id = pg_fetch_row($result_get_id_type);
                    $sql_insert_change = "INSERT INTO types (type_id, book_id) VALUES ('$type_id[0]', '$book_id_change')";
                    pg_query($sql_insert_change);
                }
                echo '<p style="text-align:center;">Success!</p>';
                echo '<META HTTP-EQUIV="refresh" content="3">';
            }
            else {
                echo '<p style="text-align:center;">You are not an admin!</p>';
            }
        }

        if(isset($_POST['submit_delete_book'])) {
            $sql_get_book_id = "SELECT book_id from book";
            $result_get_book_id = pg_query($sql_get_book_id);
            while($row_get_book_id = pg_fetch_row($result_get_book_id)){
                if($row_get_book_id[0]==$_POST['submit_delete_book']) {
                    $book_id_delete = $row_get_book_id[0];
                }
            }
            $sql_delete_book = "DELETE FROM book where book_id = '$book_id_delete'";
            if($authority==1) {
                $sql_delete_types = "DELETE FROM types where book_id = '$book_id_delete'";
                pg_query($sql_delete_types);
                pg_query($sql_delete_book);
                echo '<META HTTP-EQUIV="refresh" content="0;URL=/index.php?books">';
            }
            else {
                echo '<p style="text-align:center;">You are not an admin!</p>';
            }
        }

          $sql_amounts= "SELECT count(*) from cart where user_id = '$userid'";
          $result_amounts = pg_query($sql_amounts);
          $row_amounts = pg_fetch_row($result_amounts);
          for($i=1;$i<=$row_amounts[0];$i++)
          {
            if(isset($_POST["sub_value$i"]))
            {
                $sql_get_amount = "SELECT amount, product_id FROM cart where user_id = '$userid' order by product_id asc";
                $result_get_amount = pg_query($sql_get_amount);
                $j=0;
                while($j<$i)
                {
                    $row_get_amount = pg_fetch_row($result_get_amount);
                    $j++;
                }
                if($row_get_amount[0]>1)
                {
                    $amount = $row_get_amount[0] - 1;
                    echo "$row_get_amount[1]";
                    $sql_change_amount="UPDATE cart SET amount='$amount' where user_id = '$userid' AND product_id = '$row_get_amount[1]'";
                    pg_query($sql_change_amount);
                }
                else 
                {
                    $sql_delete_product = "DELETE FROM cart WHERE user_id = '$userid' and product_id = '$row_get_amount[1]'";
                    pg_query($sql_delete_product);
                }
                echo '<META HTTP-EQUIV="refresh" content="0;URL=/index.php?order">';
            }
            else if(isset($_POST["add_value$i"]))
            {
                $sql_get_amount = "SELECT amount, product_id FROM cart where user_id = '$userid' order by product_id asc";
                $result_get_amount = pg_query($sql_get_amount);
                $j=0;
                while($j<$i)
                {
                    $row_get_amount = pg_fetch_row($result_get_amount);
                    $j++;
                }
                $sql_get_stock = "SELECT stock_value from book where book_id = '$row_get_amount[1]' order by book_id";
                $result_get_stock = pg_query($sql_get_stock);
                $row_get_stock = pg_fetch_row($result_get_stock);
                $amount = $row_get_amount[0] + 1;
                if($amount <= $row_get_stock[0])
                {
                    $sql_change_amount="UPDATE cart SET amount='$amount' where user_id = '$userid' AND product_id = '$row_get_amount[1]'";
                    pg_query($sql_change_amount);
                    echo '<META HTTP-EQUIV="refresh" content="0;URL=/index.php?order">';
                }
            }
          }
    

          //if(isset($_POST['confirm']))
          //{
            //echo '<div id="confirm_order">
            //<h2>Your oder has been confirmed and will be processed shortly! Thank you for buying from us!</h2>
            //</div>';
          //}

          if(isset($_POST['add'])){
            if($userid!=0)
            {
                $sql_add_in_cart="INSERT INTO cart (product_id, user_id, amount) values ('{$_POST['add']}','{$userid}', '1')";
                pg_query($sql_add_in_cart);
            }
            else echo '<META HTTP-EQUIV="refresh" content="0;URL=/index.php?order">';
            //echo '<META HTTP-EQUIV="refresh" content="0;URL:/index.php?books">';
          }
          if(isset($_POST['refresh']))
          {
            $sql_refresh_cart="DELETE FROM cart where user_id = $userid";
            pg_query($sql_refresh_cart);
            echo '<META HTTP-EQUIV="refresh" content="0;URL=/index.php?order">';
          }
        
          
            echo '<div id="login" class="login_form">';
            echo '<h1>Login</h1>';
            echo '<form method="POST" name="login">';
            echo '<label for="username">Username: </label>';
            echo '<input type="text" name="username" id="username_input"><br>';
            echo '<label for="password">Password: </label>';
            echo '<input type="password" name="password" id="password_input"><br>';
            echo '<button type="submit" name="submit_login" class="submit_button_login">Login</button>';
            echo '</form>';
            if(isset($_POST['submit_login']))
            {
                $username = $_POST['username'];
                $password = $_POST['password'];
                $sql_login = "SELECT username, password, user_id from users";
                $result_login = pg_query($sql_login);
                $ok=0;
                while($row_login = pg_fetch_row($result_login)) {
                    if(($username==$row_login[0])&&($password==$row_login[1])) {
                        $ok=1;
                        $userid=$row_login[2];
                        $_SESSION['login_username']=$username;
                        $_SESSION['login_password']=$password;
                    }
                }
                if($ok==1) {
                    echo '<META HTTP-EQUIV="refresh" content="0;URL=/index.php?books">';
                    //echo "Logged in as $username with id: $userid!";
                }
                else {
                    echo "<br>Incorrect credentials!";
                }
            }
            echo '</div>';

            echo '<div id="signup" class="login_form">';
            echo '<h1>Sign Up</h1>';
            echo '<form method="POST" name="signup">';
            echo '<label for="username_signup">*Username: </label>';
            echo '<input type="text" name="username_signup" id="username_input"><br>';
            echo '<label for="first_name_signup">*First name: </label>';
            echo '<input type="text" name="first_name_signup" id="firstname_input" oninput="checkInput(this)"><br>';
            echo '<label for="last_name_signup">Last name: </label>';
            echo '<input type="text" name="last_name_signup" id="lastname_input" oninput="checkInput(this)"><br>';
            echo '<label for="email">*Email: </label>';
            echo '<input type="text" name="email_signup" id="email_input" oninput="checkEmail(this)"><br>';
            echo '<label for="password">*Password: </label>';
            echo '<input type="password" name="password_signup" id="password_input"><br>';
            echo '<button type="submit" name="submit_signup" class="submit_button_login" style="margin-top: 20px">Sign Up</button>';
            echo '<p style="text-align:center;">* fields are mendatory</p>';
            echo '</form>';
            echo '</div>';

            $sql_authority = "SELECT authority from users where user_id = '$userid'";
            $result_authority = pg_query($sql_authority);
            $row_authority = pg_fetch_row($result_authority);
            $authority = $row_authority[0];

          if($connected==true)
          {
            $sql_account_info="SELECT first_name, last_name, username, email from users where username like '$username'";
            $result_account_info = pg_query($sql_account_info);
            $row_account_info = pg_fetch_row($result_account_info);
            echo '<div id="my_account">';
            echo '<h1>My account</h1>';
            //echo '<form action="/index.php?myorders" method="POST">';
            echo '<a href="/index.php?myorders"><div>';
            echo '<button type="submit" name="submit_my_orders" class="submit_button_login" style="width: 200px;">My orders</button>';
            echo '</div></a>';
            //echo '</form>';
            if($authority == 1)
            {
                echo '<div class="admin_buttons">';
                echo '<a href="/index.php?users"><div class="submit_button_login" style="width: 200px; background-color: rgb(239, 159, 84); text-align:center; display:flex; align-items:center; justify-content:center">Users</div></a>';
                echo '<a href="/index.php?products"><div class="submit_button_login" style="width: 200px; background-color: rgb(239, 159, 84); text-align:center; display:flex; align-items:center; justify-content:center">Products</div></a>';
                echo '</div>';
            }
            echo '<form method="POST">';
            echo '<div>';
            echo "<label for='first_name'>First name: </label>";
            echo "<input type='text' name='first_name' value='$row_account_info[0]'>";
            echo '</div>';
            echo '<div>';
            echo "<label for='last_name'>Last name: </label>";
            if($row_account_info[1]){
                echo "<input type='text' name='last_name' value='$row_account_info[1]'>";
            }
            else {
                echo "<input type='text' name='last_name' value=''>";
            }
            echo '</div>';
            echo '<div>';
            echo "<label for='username'>Username: </label>";
            echo "<input type='text' name='username' value='$row_account_info[2]'>";
            echo '</div>';

            echo '<div style="display:flex;justify-content: center; align-items:center;">';
            echo "<label for='email'>Email:&nbsp;</label>";
            echo "<input type='text' name='email' value='$row_account_info[3]'>";
            echo '</div>';
            
            echo '<button type="submit" name="submit_info" class="submit_button_login">Confirm</button>';
            echo '<form method="POST">';
            
            echo '<button type="submit" name="submit_logout" class="submit_button_login" style="margin-top: 10px;">Logout</button>';
            
            echo '</form>';
            echo '</form>';
            echo '<br>';
            echo '<form action="" method="POST" enctype="multipart/form-data" style="justify-content:center; align-items: center">
                    <label for="image" style="text-align: center">Choose profile picture: </label>
                    <input type="file" name="image" class="image_input"/><br>
                    <input type="submit" name="submit_image" class="submit_button_login" value="Upload" style="width: 100%"/>
                    </form>';
                    if(isset($_POST['submit_image'])){
                        $errors= array();
                        $file_name = $_FILES['image']['name'];
                        $file_size =$_FILES['image']['size'];
                        $file_tmp =$_FILES['image']['tmp_name'];
                        $file_type=$_FILES['image']['type'];
                        $expl = explode('.',$file_name);
                        $file_ext = strtolower(end($expl));
                        
                        $extensions= array("jpeg","jpg","png");
                        
                        if(in_array($file_ext,$extensions)=== false){
                           $errors[]="extension not allowed, please choose a JPEG or PNG file.";
                        }
                        
                        if($file_size > 2097152){
                           $errors[]='File size must be excately 2 MB';
                        }
                        
                        if(empty($errors)==true){
                           if(isset($_SESSION['login_username']))
                           {
                               $file_name=$_SESSION['login_username'].".".$file_ext;
                           }
                           $dest = __DIR__."/uploads/".$file_name;
                           move_uploaded_file($file_tmp,$dest);
                           echo "<br>Uploaded!";
                        }else{
                           print_r($errors);
                        }
                     }
            echo '</div>';   

          }
          else {
            echo '<div id="my_account">';
            echo "<p style='text-align:center;'>Not connected! Login or create account</p>";
            echo "</div>";
          }

          if(isset($_POST['submit_signup'])){
            $usernamesignup=$_POST['username_signup'];
            $passwordsignup=$_POST['password_signup'];
            $firstnamesignup=$_POST['first_name_signup'];
            $emailsignup=$_POST['email_signup'];
            if($_POST['last_name_signup'])$lastnamesignup=$_POST['last_name_signup'];
            else $lastnamesignup='-';
            $sql_check_username="SELECT username from users where username like '$usernamesignup'";
            $result_check_username = pg_query($sql_check_username);
            if(pg_num_rows($result_check_username)==0)
            {
                $sql_signup = "INSERT INTO users (username, password, first_name, last_name, email) values ('$usernamesignup','$passwordsignup','$firstnamesignup','$lastnamesignup', '$emailsignup')";
                $result_signup = pg_query($sql_signup);
                pg_fetch_row($result_signup);
                echo '<META HTTP-EQUIV="refresh" content="0;URL=/index.php?books">';
            }
            else {
                echo '<br><p style="text-align:center;">Username already exists!</p>';
            }
          }

          if(isset($_POST['submit_info']))
          {
            $firstname = $_POST['first_name'];
            $lastname = $_POST['last_name'];
            $usernameinfo = $_POST['username'];
            $email = $_POST['email'];
            $sql_change_credentials = "UPDATE users SET first_name = '$firstname', last_name = '$lastname', username = '$usernameinfo', email = '$email' where user_id = '$userid'";
            $result_change_credentials = pg_query($sql_change_credentials);
            pg_fetch_row($result_change_credentials);
            $_SESSION['login_username'] = $usernameinfo;
            echo '<META HTTP-EQUIV="refresh" content="0;URL=/index.php?account">';
          }

          if(isset($_POST['submit_logout']))
          {
            unset($_SESSION['login_username']);
            unset($_SESSION['login_password']);
            echo '<META HTTP-EQUIV="refresh" content="0;URL=/index.php?books">';
          }

          echo '<div id="finalize_order">';
            echo '<h1>Order details</h1>';
          if(isset($_POST['finalize']))
          {
            $sql_account_info="SELECT first_name, last_name, email from users where username like '$username'";
            $result_account_info = pg_query($sql_account_info);
            $row_account_info = pg_fetch_row($result_account_info);
            echo '<form action="/index.php?confirm" method="POST">';
            echo '<div>';
            echo "<label for='first_name'>First name: </label>";
            echo "<input type='text' name='first_name' value='$row_account_info[0]'>";
            echo '</div>';
            echo '<div>';
            echo "<label for='last_name'>Last name: </label>";
            if($row_account_info[1]){
                echo "<input type='text' name='last_name' value='$row_account_info[1]'>";
            }
            else {
                echo "<input type='text' name='last_name' value=''>";
            }
            echo '</div>';
            echo '<div>';
            echo '<label for="email">Email: </label>';
            echo "<input type='text' name='email' value='$row_account_info[2]'>";
            echo '</div>';
            echo '<div class="address_info">';
            echo '<label for="address">Delivery address: </label>';
            echo '<input type="text" name="address" id="address">';
            echo '</div>';
            echo '<button type="submit" name="confirm" class="submit_button_login">Confirm order</button>';
            echo '</form>';
          }
          echo '</div>';

          echo '<div id="confirm_order">';
          echo '<h2>Your order has been confirmed and will be processed shortly! Thank you for buying from us!<br>
          You will be redirected to the main page...</h2>';
          echo '</div>';
         

         if(isset($_POST['confirm'])){
            $date = date("Y-m-d");
            $delivery_address = $_POST["address"];
            $name = $_POST['first_name']." ".$_POST['last_name'];
            $email = $_POST['email'];
            $sql_confirm = "INSERT INTO orders (user_id, order_date, delivery_address, client_name, email) values ('{$userid}','{$date}','{$delivery_address}','{$name}','{$email}')";  
            pg_query($sql_confirm);
            $sql_getorderid = "SELECT order_id from orders where user_id = '{$userid}' and delivery_address like '{$delivery_address}' AND client_name like '{$name}' order by order_id desc";
            $result_getorderid = pg_query($sql_getorderid);
            $row_getorderid = pg_fetch_row($result_getorderid);
            $sql_products = "SELECT product_id, amount from cart where user_id = '{$userid}'";
            $result_products = pg_query($sql_products);
            $i=0;
            while($row_products = pg_fetch_row($result_products))
            {
                $sql_save_order = "INSERT INTO ordered_products (order_id, product_id, product_amount) values ('{$row_getorderid[0]}', '{$row_products[0]}', '{$row_products[1]}')";
                pg_query($sql_save_order);
                $book[$i][0] = $row_products[0];
                $book[$i][1] = $row_products[1];
                $i++;
            }
            for($j=0;$j<$i;$j++)
            {
                //echo $book[$j][0];
                //echo $book[$j][1];
                $sql_old_stock = "SELECT stock_value from book where book_id = '{$book[$j][0]}'";
                $result_old_stock = pg_query($sql_old_stock);
                $old_stock = pg_fetch_row($result_old_stock);
                $newamount = $old_stock[0] - $book[$j][1];
                $sql_change_stock = "UPDATE book set stock_value = '$newamount' where book_id = '{$book[$j][0]}'";
                pg_query($sql_change_stock);
            }
            $sql_delete_cart = "DELETE FROM cart WHERE user_id = '{$userid}'";
            pg_query($sql_delete_cart);
            echo '<META HTTP-EQUIV="refresh" content="5;URL=/index.php?books">';
          }

        echo'<div id="my_orders">';
        $sql_get_orders = "SELECT order_id, order_date, delivery_address, email from orders where user_id='$userid'";
        $result_get_orders = pg_query($sql_get_orders);
        $j=0;
        if(pg_num_rows($result_get_orders) > 0)
        {
            echo '<h1>My orders</h1>';
            echo '<div class="one_order">';
                echo '<div class="order_id">';
                echo 'ID';
                echo '</div>';
                echo '<div class="order_date">';
                echo 'Date';
                echo '</div>';
                echo '<div class="order_address">';
                echo 'Address';
                echo '</div>';
            echo '</div>';
            
            $j=0;
            while($row_get_orders = pg_fetch_row($result_get_orders))
            {
                $j++;
                $oids[$j]=$row_get_orders[0];
                echo "<a href='/index.php?myorders?o$j'>";
                echo '<div class="one_order">';
                echo '<div class="order_id">';
                echo "$row_get_orders[0]";
                echo '</div>';
                echo '<div class="order_date">';
                echo "$row_get_orders[1]";
                echo '</div>';
                echo '<div class="order_address">';
                echo "$row_get_orders[2]";
                echo '</div>';
                echo '</div>';
                echo '</a>';
            }
        }
        else {
            echo "<p style='text-align:center;'>No order placed yet!</p>";
        }
        echo '</div>';

    for($z=1;$z<=$j;$z++)
    {
        $oid = $oids[$z];
        echo "<div id='o$z' class='order_info'>";
        echo "<h1>Order #$oid</h1>";
                $sql_cart="SELECT title, op.product_amount, book_price from book join ordered_products op on book.book_id = op.product_id where op.order_id = '$oid' group by (title, op.product_amount, book_price)";
                $result_cart = pg_query($sql_cart);
                //$sql_cart_pic = 'SELECT title, amount from book join orders o on book.book_id = o.product_id where o.user_id = "'.$user.'" group by (title, amount)';
                //$result_cart_pic = pg_query($sql_cart_pic);
                
                $i=0;
                $sum=0;
                while($row_cart=pg_fetch_row($result_cart)) {
                    $i++;
                    echo '<div class="order_product">';
                    //$result_cart_pic_row = pg_fetch_row($result_cart_pic);
                    $bookTitle = '"/images/';
                    setName($bookTitle, $row_cart[0]);
                    echo '<div class="order_pic_details">';
                    echo "<div class='order_pic' style='background-image: url($bookTitle)'>";
                    echo '</div>';
                    echo '<div class="order_details">';
                    echo "$row_cart[0]";
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="order_amount">';
                    echo "$row_cart[1]";
                    echo "<div>&nbspx&nbsp$row_cart[2]$</div>";
                    echo '</div>';
                    echo '</div>';
                    $sum = $sum + $row_cart[2] * $row_cart[1];
                }
                    echo '<div class="order_total">';
                    echo "<div>Total amount: $sum$</div>";
                    echo '</div>';
            echo '</div>';
    }

    echo '<div id="users">';
    echo '<h1>Users</h1>';
    $sql_get_users = "SELECT user_id, username, email from users where authority != '$authority' and authority != 3 and user_id != '$userid'";
    $result_get_users = pg_query($sql_get_users);
    $k=0;
    echo '<div class="one_order">';
            echo '<div class="order_date">';
            echo 'Username';
            echo '</div>';
            echo '<div class="order_address">';
            echo 'Address';
            echo '</div>';
    echo '</div>';
    while($row_get_users = pg_fetch_row($result_get_users))
    {
        $k++;
        $user[$k][0]=$row_get_users[0];
        $user[$k][1]=$row_get_users[1];
        $user[$k][2]=$row_get_users[2];
            echo '<div class="one_order">';
            echo '<div class="order_username">';
            echo "$row_get_users[1]";
            echo '</div>';
            echo '<div class="order_address">';
            echo "$row_get_users[2]";
            echo '</div>';
            echo "<form method='POST' name='delete$k'>";
            echo "<button type='submit' name='delete$k' class='button_delete'>Delete user</button>";
            echo '</form>';
            echo '</div>';
        
    }
    echo '</div>';

    for($z=1;$z<=$k;$z++)
    {
        //$buton_set = $_POST["delete$z"];
        if(isset($_POST["delete$z"]))
        {
            if($authority==1)
            {
                $id=$user[$z][0];
                $sql_change_id = "UPDATE orders set user_id = '0' where user_id = '$id'";
                pg_query($sql_change_id);
                $sql_delete_user = "DELETE FROM users where user_id = '$id'";
                pg_query($sql_delete_user);
                echo '<META HTTP-EQUIV="refresh" content="0;URL=/index.php?users">';
            }
            else {
                echo '<p style="text-align:center">You are not an admin!</p>';
            }
        }
    }

    echo '<div id="products">';
        echo '<h1>Products</h1>';
        echo '<a href="/index.php?addbook"><div class="submit_button_login" style="width: 200px; background-color: rgb(239, 159, 84); text-align:center; display:flex; align-items:center; justify-content:center">Add product</div></a>';
        //echo '<a href="/index.php?change"><div class="submit_button_login" style="width: 200px; background-color: rgb(239, 159, 84); text-align:center; display:flex; align-items:center; justify-content:center">Change products</div></a>';
    echo '</div>';

    echo '<div id="addbook">';
        echo '<h1>Add a book</h1>';
        echo '<form action="/index.php?addbook" method="POST" class="form_addbook" enctype="multipart/form-data">';
            echo '<label for="title">Title: </label>';
            echo '<input type="text" name="title">';
            echo '<label for="author">Author: </label>';
            echo '<select name="author" id="author">';
                    $sql_get_authors="SELECT author_name from author";
                    $result_get_authors = pg_query($sql_get_authors);
                    while($row_get_authors = pg_fetch_row($result_get_authors))
                    {
                        echo "<option value='$row_get_authors[0]'>$row_get_authors[0]</option>";
                    }
                echo '</select>';
            echo '<a href="/index.php?addauthor"><div class="button_add">Add author</div></a>';
            echo '<label for="publisher" name="publisher">Publisher: </label>';
            echo '<select name="publisher" id="publisher">';
                    $sql_get_publishers="SELECT publisher_name from publisher";
                    $result_get_publishers = pg_query($sql_get_publishers);
                    while($row_get_publishers = pg_fetch_row($result_get_publishers))
                    {
                        echo "<option value='$row_get_publishers[0]'>$row_get_publishers[0]</option>";
                    }
                echo '</select>';
            echo '<a href="/index.php?addpublisher"><div class="button_add">Add publisher</div></a>';
            echo '<label for="description">Description: </label>';
            echo '<input type="text" name="description">';
            echo '<label for="main_character">Main character: </label>';
            echo '<input type="text" name="main_character">';
            echo '<label for="price">Price: </label>';
            echo '<input type="text" name="price">';
            echo '<label for="stock">Stock value: </label>';
            echo '<input type="text" name="stock">';
            echo '<label for="type">Type: </label>';
            echo '<select name="type[]" id="type" multiple="multiple">';
                $sql_get_types = "SELECT book_type from type";
                $result_get_types = pg_query($sql_get_types);
                while($row_get_types=pg_fetch_row($result_get_types)) {
                    echo "<option value='$row_get_types[0]'>$row_get_types[0]</option>";
                }
            echo '</select>';
            echo '<a href="/index.php?addtype"><div class="button_add">Add type</div></a>';
            echo '<label for="cover" style="text-align: center">Choose cover picture: </label>';
            echo '<input type="file" name="cover" class="image_input"/><br>';
            echo '<button type="submit" class="submit_button_login" style="background-color: rgb(239, 159, 84);" name="submit_addbook">Add product</button>';
        echo '</form>';
        if(isset($_POST['submit_addbook'])){
            if($authority==1)
            {
                $errors= array();
                $file_name = $_FILES['cover']['name'];
                $file_size =$_FILES['cover']['size'];
                $file_tmp =$_FILES['cover']['tmp_name'];
                $file_type=$_FILES['cover']['type'];
                $expl = explode('.',$file_name);
                $file_ext = strtolower(end($expl));
                
                $extensions= array("jpeg","jpg","png");
                
                if(in_array($file_ext,$extensions)=== false){
                   $errors[]="extension not allowed, please choose a JPEG or PNG file.";
                }
                
                if($file_size > 2097152){
                   $errors[]='File size must be excately 2 MB';
                }
                
                if(empty($errors)==true){
                    $bookTitle='';
                    setNameCover($bookTitle,$_POST['title']);
                    $file_name=$bookTitle.".".$file_ext;
                    $dest = __DIR__."/images/".$file_name;
                    move_uploaded_file($file_tmp,$dest);
                    echo "<br>Uploaded!";
                }else{
                   print_r($errors);
                }
                
                $title = $_POST['title'];
                $author = $_POST['author'];
                $publisher = $_POST['publisher'];
                $description = $_POST['description'];
                $character = $_POST['main_character'];
                $price = $_POST['price'];
                $stock = $_POST['stock'];
                $j=0;
                $type=[];
                foreach ($_POST['type'] as $typearr)
                {
                    $type[$j]=$typearr;
                    //echo $type[$j];
                    $j++;
                }
                
                $sql_author = "SELECT author_id from author where author_name like '$author'";
                $result_author = pg_query($sql_author);
                $row_author=pg_fetch_row($result_author);
                $author_id = $row_author[0];
    
                $sql_publisher = "SELECT publisher_id from publisher where publisher_name like '$publisher'";
                $result_publisher = pg_query($sql_publisher);
                $row_publisher=pg_fetch_row($result_publisher);
                $publisher_id = $row_publisher[0];
    
                $sql_addbook = "INSERT INTO book (title, publisher_id, author_id, book_description, main_character, book_price, stock_value) values ('$title','$publisher_id','$author_id','$description','$character','$price','$stock')";
                pg_query($sql_addbook);
    
                $sql_book="SELECT book_id from book where title like '$title'";
                $result_book = pg_query($sql_book);
                $row_book = pg_fetch_row($result_book);
                $book_id = $row_book[0];
        
                for($z=0;$z<$j;$z++)
                {
                    //echo $type[$z];
                    $sql_type = "SELECT type_id from type where book_type like '$type[$z]'";
                    $result_type = pg_query($sql_type);
                    $row_type = pg_fetch_row($result_type);
                    $type_id = $row_type[0];
                    $sql_put_type = "INSERT INTO types (book_id, type_id) values ('$book_id', '$type_id')";
                    pg_query($sql_put_type);
                }
            }
            else {
                echo '<p style="text-align:center">You are not an admin!</p>';
            }
         }
    echo '</div>';

    echo '<div id="addauthor">';
    echo '<h1>Add an author</h1>';
    echo '<form action="/index.php?addbook" method="GET" class="form_addbook">';
    echo '<div class="form_addbook_div">';
    echo '<label for="author_name">Author name: </label>';
    echo '<input name="author_name">';
    echo '</div>';
    echo '<button type="submit" class="submit_button_login" style="background-color: rgb(239, 159, 84); margin-top: 10px;" name="submit_addauthor">Add author</button>';
    echo '</form>';
    echo '</div>';

    echo '<div id="addpublisher">';
    echo '<h1>Add a publisher</h1>';
    echo '<form action="/index.php?addbook" method="GET" class="form_addbook">';
    echo '<div class="form_addbook_div">';
    echo '<label for="publisher_name">Publisher name: </label>';
    echo '<input name="publisher_name">';
    echo '</div>';
    echo '<button type="submit" class="submit_button_login" style="background-color: rgb(239, 159, 84); margin-top: 10px;" name="submit_addpublisher">Add publisher</button>';
    echo '</form>';
    echo '</div>';

    echo '<div id="addtype">';
    echo '<h1>Add a type</h1>';
    echo '<form action="/index.php?addbook" method="GET" class="form_addbook">';
    echo '<div class="form_addbook_div">';
    echo '<label for="type_name">Type name: </label>';
    echo '<input name="type_name">';
    echo '</div>';
    echo '<div class="form_addbook_div">';
    echo '<label for="color">Type color: </label>';
    echo '<input name="color">';
    echo '</div>';
    echo '<button type="submit" class="submit_button_login" style="background-color: rgb(239, 159, 84); margin-top: 10px;" name="submit_addtype">Add type</button>';
    echo '</form>';
    echo '</div>';

    if(isset($_GET["submit_addauthor"])){
        $author = $_GET['author_name'];
        if($authority==1)
        {
            $sql_add = "INSERT INTO author (author_name) VALUES('$author')";
            pg_query($sql_add);
        }
        else {
            echo '<p style="text-align:center">You are not an admin!</p>';
        }
    }

    if(isset($_GET["submit_addpublisher"])){
        $publisher = $_GET['publisher_name'];
        $sql_get_publishers="SELECT publisher_name from publisher";
        $result_get_publishers = pg_query($sql_get_publishers);
        $pid = pg_num_rows($result_get_publishers) + 1;
        if($authority==1)
        {
            $sql_add = "INSERT INTO publisher (publisher_id, publisher_name) VALUES('$pid', '$publisher')";
            pg_query($sql_add);
        }
        else {
            echo '<p style="text-align:center">You are not an admin!</p>';
        }
    }

    if(isset($_GET["submit_addtype"])){
        $type = $_GET['type_name'];
        $color = $_GET['color'];
        if($authority==1)
        {
            $sql_add = "INSERT INTO type (book_type, color) VALUES('$type','$color')";
            pg_query($sql_add);
        }
        else {
            echo '<p style="text-align:center">You are not an admin!</p>';
        }
    }

      pg_close($conn);

    ?>

    <script>
        let b = document.getElementById("books");
        let o = document.getElementById("order_tab");
        let a = document.getElementById("my_account");
        let c = document.getElementById("contact");
        let l = document.getElementById("login");
        let s = document.getElementById("signup");
        let f = document.getElementById("finalize_order");
        let cf = document.getElementById("confirm_order");
        let mo = document.getElementById("my_orders");
        let u = document.getElementById("users");
        let add = document.getElementById("addbook");
        //let ch = document.getElementById("change");
        let p =document.getElementById("products");
        let aa=document.getElementById("addauthor");
        let ap=document.getElementById("addpublisher");
        let at=document.getElementById("addtype");

        function noneDisplay(){
            a.style.display="none";
            b.style.display="none";
            o.style.display="none";
            c.style.display="none";
            s.style.display="none";
            l.style.display="none";
            f.style.display="none";
            cf.style.display="none";
            mo.style.display="none";
            u.style.display="none";
            add.style.display="none";
            p.style.display="none";
            //ch.style.display="none";
            aa.style.display="none";
            ap.style.display="none";
            at.style.display="none";
        }
        /*
        function changeTab(x)
        {   
            a.style.display="none";
            b.style.display="none";
            o.style.display="none";
            c.style.display="none";
            l.style.display="none";
            s.style.display="none";
            x.style.display="flex";
        }*/

        let url = window.location.href;
        //console.log(url);
        let parsedUrl = url.split("?");
        let length = parsedUrl.length;
        let ok=0;
        if(length > 1)
        {
            if(parsedUrl[1]=="login")
            {
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="flex";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="books")
            {
                if(parsedUrl[2])
                {
                    console.log(parsedUrl[0]);
                    console.log(parsedUrl[1]);
                    console.log(parsedUrl[2]);
                    let indexOfBook = url.split('c');
                    let index = indexOfBook[2];
                    
                    /*for(var i=1;i<=10;i++)
                    {
                        if(i!=index)document.getElementById("c" + i).style.display = "none";
                    }*/
                    console.log(index);
                    let bid = document.getElementById(""+parsedUrl[2]);
                    if(bid) {
                        bid.style.display="flex";
                        b.style.display="none";
                    }
                    else {
                        b.style.display="flex";
                    }

                    //document.getElementById("card").style.display="none";
                    a.style.display="none";
                    //b.style.display="none";
                    o.style.display="none";
                    c.style.display="none";
                    s.style.display="none";
                    l.style.display="none";
                    f.style.display="none";
                    cf.style.display="none";
                    mo.style.display="none";
                    u.style.display="none";
                    add.style.display="none";
                    p.style.display="none";
                    //ch.style.display="none";
                    aa.style.display="none";
                    ap.style.display="none";
                    at.style.display="none";
                }
                else 
                {
                    a.style.display="none";
                    o.style.display="none";
                    c.style.display="none";
                    s.style.display="none";
                    l.style.display="none";
                    f.style.display="none";
                    cf.style.display="none";
                    mo.style.display="none";
                    b.style.display="flex";
                    u.style.display="none";
                    add.style.display="none";
                    p.style.display="none";
                    //ch.style.display="none";
                    aa.style.display="none";
                    ap.style.display="none";
                    at.style.display="none";
                }
            }
            else if(parsedUrl[1]=="account")
            {
                a.style.display="flex";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="order")
            {
                a.style.display="none";
                b.style.display="none";
                o.style.display="flex";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="contact")
            {
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="flex";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="signup"){
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="flex";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="finalize"){
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="flex";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="confirm"){
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="flex";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="myorders"){
                if(parsedUrl[2])
                {
                    //console.log(parsedUrl[0]);
                    //console.log(parsedUrl[1]);
                    console.log(parsedUrl[2]);
                    let indexOfBook = url.split('o');
                    //let index = indexOfBook[4];
                    
                    /*for(var i=1;i<=10;i++)
                    {
                        if(i!=index)document.getElementById("c" + i).style.display = "none";
                    }*/
                    //console.log(index);
                    document.getElementById(""+parsedUrl[2]).style.display="flex";
                    //document.getElementById("card").style.display="none";
                    a.style.display="none";
                    b.style.display="none";
                    o.style.display="none";
                    c.style.display="none";
                    s.style.display="none";
                    l.style.display="none";
                    f.style.display="none";
                    cf.style.display="none";
                    mo.style.display="none";
                    u.style.display="none";
                    add.style.display="none";
                    p.style.display="none";
                    //ch.style.display="none";
                    aa.style.display="none";
                    ap.style.display="none";
                    at.style.display="none";
                }
                else 
                {
                    a.style.display="none";
                    b.style.display="none";
                    o.style.display="none";
                    c.style.display="none";
                    s.style.display="none";
                    l.style.display="none";
                    f.style.display="none";
                    cf.style.display="none";
                    mo.style.display="flex";
                    u.style.display="none";
                    add.style.display="none";
                    p.style.display="none";
                    //ch.style.display="none";
                    aa.style.display="none";
                    ap.style.display="none";
                    at.style.display="none";
                }
            }
            else if(parsedUrl[1]=="users"){
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="flex";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="change") {
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="flex";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="addbook") {
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="flex";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="products") {
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="flex";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="addauthor") {
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="flex";
                ap.style.display="none";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="addpublisher") {
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="flex";
                at.style.display="none";
            }
            else if(parsedUrl[1]=="addtype") {
                a.style.display="none";
                b.style.display="none";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                p.style.display="none";
                //ch.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="flex";
            }
            else 
            {
                a.style.display="none";
                b.style.display="flex";
                o.style.display="none";
                c.style.display="none";
                s.style.display="none";
                l.style.display="none";
                f.style.display="none";
                cf.style.display="none";
                mo.style.display="none";
                u.style.display="none";
                add.style.display="none";
                //ch.style.display="none";
                p.style.display="none";
                aa.style.display="none";
                ap.style.display="none";
                at.style.display="none";
            }
        }
        else {
            a.style.display="none";
            b.style.display="flex";
            o.style.display="none";
            c.style.display="none";
            s.style.display="none";
            l.style.display="none";
            f.style.display="none";
            cf.style.display="none";
            mo.style.display="none";
            u.style.display="none";
            add.style.display="none";
            //ch.style.display="none";
            p.style.display="none";
            aa.style.display="none";
            ap.style.display="none";
            at.style.display="none";
        }

        function addAmount(x)
        {
            document.getElementById("button_amount").innerHTML=document.getElementById("button_amount").innerHTML+1;
        }

        function checkInput(x) {
            let text = x.value;
            let ok = 0;
            let characters = "/'`1234567890?!\"";
            for(let i=0;i<characters.length;i++){
                if(text.indexOf(characters[i])!=-1) {
                    ok=1;
                }
            }
            if(ok==1) {
                x.style.border="2px solid red";
            }
            else {
                x.style.border="2px solid rgb(239, 159, 84)";
            }
        }

        function checkEmail(x) {
            let text = x.value;
            let ok = 0;
            let characters = "@.";
            for(let i=0;i<characters.length;i++){
                if(text.indexOf(characters[i])!=-1) {
                    ok++;
                }
            }
            if(text.indexOf(".")+2<=text.length) ok++;
            if(ok<=2) {
                x.style.border="2px solid red";
            }
            else {
                x.style.border="2px solid rgb(239, 159, 84)";
            }
        }
        
        function showValue(val) {
            document.getElementById("show_value").value=val;
        }
    </script>    
</body>
</html>