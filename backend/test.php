<?php
/**
 * Created by PhpStorm.
 * User: Taha
 * Date: 23/01/2019
 * Time: 01:37 PM
 */
include "dbconnect.php";
$sql = "SELECT id,name,location,transport_fixed,transport_rate FROM shop LIMIT 10";
$result = $conn->query ($sql);
while ($row = $result->fetch_assoc()) {
    $shop[] = $row;
}
echo "<pre>";
print_r(json_encode($shop));