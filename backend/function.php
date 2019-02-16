<?php
    include("../../../dbconnect.php");
    $pass = "13286301546233571032";
    $shop = $conn->query("SELECT * FROM `shop` WHERE `pass` = $pass")->fetch_assoc();
    $shop_id = $shop[id];
    $root = $conn->query("SELECT id FROM `item` WHERE `shop_id` = $shop_id AND type=3")->fetch_assoc()[id];
    $result = $conn->query("SELECT item_id FROM `folder` WHERE `folder_id` = $root");
    while ($row = $result->fetch_assoc()) {
        $main_batch_ids[] = $row[item_id];
    }

    foreach ($main_batch_ids as $main_batch_id) {
        $main_batchs[] = $conn->query("SELECT * FROM `item` WHERE `id` = $main_batch_id")->fetch_assoc();
    }
    // echo "<pre>";
    // print_r($main_batchs); exit;

