<?php
////////////////////////// Connecting to Database /////////////////////////
global $db;
$db=mysqli_connect('localhost','root','','7learn_shop');
mysqli_query($db,"SET NAMES 'utf8mb4'");
mysqli_query($db,"SET CHARACTER SET utf8mb4");
mysqli_query($db,"SET SESSION collation_connection = 'utf8mb4_unicode_ci'");
//////////////////////////////////////////////////////////////////////////