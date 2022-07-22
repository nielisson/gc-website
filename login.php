<?php
// Include config file
require_once "connect.php";

// Define variables and initialize with empty values
if(isset($_POST["email"])){

    $email = strip_tags($_POST["email"]);
    $password = md5(strip_tags($_POST["password"]));

  
    $sql = "SELECT * FROM users WHERE email = '$email'";

    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
      // output data of each row
      while($row = $result->fetch_assoc()) {
          
        if($row['password'] == $password){
          $row_array['status'] = 1;
          $row_array['username'] = $row['user_name'];
          $row_array['uid'] = $row['user_id'];

          echo json_encode($row_array); 

        }else{
          $row_array['message'] = "Invalid Credentials";
          $row_array['status'] = 0;

          //array_push($return_arr,$row_array);

          echo json_encode($row_array);
        }
      }
    } else {
      $row_array['message'] = "Invalid Credentials";
      $row_array['status'] = 0;

      //array_push($return_arr,$row_array);

      echo json_encode($row_array);
    }
    $conn->close();


}
  ?>

  <html>

<!--  <form id="form1" name="form1" method="post" action="login.php">
<label for="email">email</label><input type="text" name="email" id="email" />
<br class="clear" /> 
<label for="password">password</label><input type="text" name="password" id="password" />
<br class="clear" /> 
<input type ="submit" name = "submit" value="submit">
</form>-->
  </html>