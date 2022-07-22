<?php
// Include config file
require_once "connect.php";

function generate_playerid ($length_of_string) { 
      
    // md5 the timestamps and returns substring 
    // of specified length 
    return substr(md5(time()), 0, $length_of_string); 
} 

function generate_vcode($length_of_string2) 
{ 
  
    // String of all alphanumeric character 
    $str_result2 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
  
    // Shufle the $str_result and returns substring 
    // of specified length 
    return substr(str_shuffle($str_result2),  
                       0, $length_of_string2); 
} 


function generate_rname() 
{ 
  

$names = array("Unslot", "Wibo", "Tenka");
$randNameVAL = rand(0, 2);

return $names[$randNameVAL].rand(111,9999);

} 
 
// Define variables and initialize with empty values
if(isset($_POST["email"])){
    
  $regex =  '/^([a-zA-Z0-9\.]+@+[a-zA-Z]+(\.)+[a-zA-Z]{2,3})$/';

  if(preg_match($regex, $_POST["email"])){

    $email = strip_tags($_POST["email"]);
    $username = strip_tags($_POST["username"]);
    $password = strip_tags($_POST["password"]);
    $conpassword  = strip_tags($_POST["conpassword"]);
    $genre = strip_tags($_POST["genre"]);
    $city = strip_tags($_POST["city"]);
    $country = strip_tags($_POST["country"]);

  
        $sql2 = "SELECT * FROM users WHERE email = '$email' OR user_name = '$username'";
        

        $result = $conn->query($sql2);

        if ($result->num_rows > 0) {

          $row_array['message'] = "User With This Username/Email Already Exists";
          $row_array['status'] = 1;
  //$row_array['col2'] = $row['col2'];

          //array_push($return_arr,$row_array);
          echo json_encode($row_array);

        }else{
          if($password == $conpassword){
            $date = date("Y-m-d");
            $uid = generate_vcode(15);
            $vcode = generate_vcode(10);
            //$username = $username."_".generate_vcode(4);
    
            $password = md5($password);
    
            $sql = "INSERT INTO users (user_id, email, password, membership_date, user_name, fav_game_genre, city, country) VALUES ('$uid', '$email', '$password','$date','$username', $genre, '$city', '$country')";
            
    
            if ($conn->query($sql) === TRUE) {
              //echo "New record created successfully";
    
                $row_array['message'] = "New record created successfully";
                $row_array['status'] = 2;

                echo json_encode($row_array);
    
/*                 $to = "$email";
                $subject = "KICK : Your Verification Code";
                
                $message = "<b>This is Your Verification Code to Complete Your Sign Up Process On Kick.</b>";
                $message .= "<h1>$vcode</h1>";
                
                $header = "From:kick@imaginarium.com \r\n";
                $header .= "Cc:help@imaginarium.com \r\n";
                $header .= "MIME-Version: 1.0\r\n";
                $header .= "Content-type: text/html\r\n";
                
                $retval = mail ($to,$subject,$message,$header);
                
                if( $retval == true ) {
                   //echo "Message sent successfully...";
                }else {
                  // echo "Message could not be sent...";
                } */
    
                //array_push($return_arr,$row_array);
    
            } else {
              //echo "Error: " . $sql . "<br>" . $conn->error;
              $row_array['message'] = "Error: " . $sql . "<br>" . $conn->error;
              $row_array['status'] = 0;
      //$row_array['col2'] = $row['col2'];
    
              //array_push($return_arr,$row_array);
              echo json_encode($row_array);
    
            }
            
       
            $conn->close();
        }
        }
  }else{
    $row_array['message'] = "Please Provide A Valid Email Address";
              $row_array['status'] = 0;
      //$row_array['col2'] = $row['col2'];
    
              //array_push($return_arr,$row_array);
              echo json_encode($row_array);
  }
}
  ?>

  <html>

  <form id="form1" name="form1" method="post" action="register.php">
  <label for="email">Username</label><input type="text" name="username" id="username" />
<br class="clear" /> 
<label for="email">email</label><input type="text" name="email" id="email" />
<br class="clear" /> 
<label for="password">password</label><input type="password" name="password" id="password" />
<br class="clear" /> 
<label for="conpassword">conpassword</label><input type="password" name="conpassword" id="conpassword" />
<br class="clear" /> 
<input type ="submit" name = "submit" value="submit">
</form>
  </html>