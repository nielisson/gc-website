<?php
// Include config file
require_once "connect.php";

// Define variables and initialize with empty values
if(isset($_POST["email"])){

    $email = strip_tags($_POST["email"]);
    $password = md5(strip_tags($_POST["password"]));

  
    $sql2 = "SELECT * FROM users WHERE email = '$email'";
        

    $result = $conn->query($sql2);

    if ($result->num_rows > 0) {

      
            $row = $result->fetch_assoc();
            $row_array['status'] = 1;
            $row_array['username'] = $row['username'];
            $row_array['uid'] = $row['uid'];

            //array_push($return_arr,$row_array);

            echo json_encode($row_array); 
        } else {
            $row_array['message'] = "No Such User Exisit";
            $row_array['status'] = 0;

            //array_push($return_arr,$row_array);

            echo json_encode($row_array);
        }
    }
  ?>

  <html>

  <form id="form1" name="form1" method="post" action="login.php">
<label for="email">email</label><input type="text" name="email" id="email" />
<br class="clear" /> 
<label for="password">password</label><input type="text" name="password" id="password" />
<br class="clear" /> 
<input type ="submit" name = "submit" value="submit">
</form>
  </html>