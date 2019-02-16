<?php

// $conn=new MySQLi("79.137.121.221","root","HezLY8QFKzvX","partapp_shop");
$conn=new MySQLi("localhost","partapp","M4Ezqw8Q","partapp_shop");
$conn->set_charset("utf8mb4");

if($conn->connect_error){
	
	echo '<div style="height: 100%;width: 100%;display: flex;position: absolute;justify-content: center;align-items: center;color:white;background-color: #444;left:0px;top:0px;direction:rtl"><h1>فروشگاه موقتاً در دسترس نیست. یک دقیقه دیگر دوباره سر بزنید.</h1></div>';
} else {

}
