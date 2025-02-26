<?php

//jika bellum login

if(isset($_SESSION['log'])){
     
} else {
    header('location:login.php');
}

?>