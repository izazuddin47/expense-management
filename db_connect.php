
<?php

$host = "127.0.0.1";
$username = "root";
$password = "";
$db = "expense_management";


$conn = mysqli_connect($host , $username , $password , $db);

if(!$conn){
    die("connection failed" .mysqli_connection_error());
}

?>