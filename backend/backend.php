<?php
include "dbconnect.php";
$shop_id=110;
switch ($_POST[code]) {
    case 'get_batch_list':
        get_batch_list ();
        break;
    case 'get_list_item':
        get_list_item ();
        break;
    case 'get_root_item':
        get_root_item ();
        break;
    case 'profile_get':
        profile_get ();
        break;
    case 'wallet_get':
        wallet_get ();
        break;
    case 'history_orders_get':
        history_orders_get ();
        break;
    case 'history_order_get_details':
        history_order_get_details ();
        break;
    case 'search_item':
        search_item ();
        break;
}

function get_batch_list()
{
    $conn   = $GLOBALS['conn'];
    $id     = +$_POST['id'];
    $sql    = "SELECT item_id FROM `folder` WHERE folder_id=$id";
    $result = $conn->query ($sql);
    while ($row = $result->fetch_assoc ()) {
        $sql = "SELECT * FROM `item` WHERE id=" . $row[item_id] . " AND type=2";
        $result2 = $conn->query ($sql);
        while ($row2 = $result2->fetch_assoc ()) {
            $items[] = $row2;
        }
    }
    $json->status = "ok";
    $json->data = $items;
    $json->message = "";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
}

function get_list_item()
{
    $conn = $GLOBALS['conn'];
    $id = +$_POST['id'];
    $sql = "SELECT item_id FROM `folder` WHERE folder_id=$id";
    $result = $conn->query ($sql);
    while ($row = $result->fetch_assoc ()) {
        $sql = "SELECT * FROM `item` WHERE id=" . $row[item_id] . " AND type=1";
        $result2 = $conn->query ($sql);
        while ($row2 = $result2->fetch_assoc ()) {
            $items[] = $row2;
        }
    }
    $json->status = "ok";
    $json->data = $items;
    $json->message = "";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
}

function get_root_item()
{
    $conn = $GLOBALS[conn];
    $shop_id = $GLOBALS[shop_id];

    $sql = "SELECT * FROM `item` WHERE shop_id=$shop_id AND type=3";
    $result = $conn->query ($sql);
    if ($row = $result->fetch_assoc ()) {
        $root = $row;
    }

    $json->status = "ok";
    $json->data = $root;
    $json->message = "";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
}

function profile_get()
{
    $conn = $GLOBALS[conn];
    $user_id = $_POST[user_id];
    $sql = "SELECT * FROM `user` WHERE id=$user_id";
    $result = $conn->query ($sql);
    if ($row = $result->fetch_assoc ()) {
        $user = $row;
    }
    $json->status = "ok";
    $json->data = $user;
    $json->message = "";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
}

function wallet_get()
{
    $conn = $GLOBALS[conn];
    $user_id = $_POST[user_id];
    $data = array();
    $sql = "SELECT amount_wallet FROM `user` WHERE id=$user_id";
    $result = $conn->query ($sql);
    if ($row = $result->fetch_assoc ()) {
        $data = $row;
    }
    $sql = "SELECT * FROM `wallet` WHERE user_id=$user_id";
    $result = $conn->query ($sql);
    while ($row = $result->fetch_assoc ()) {
        $data['wallet'][] = $row;
    }
    $json->status = "ok";
    $json->data = $data;
    $json->message = "";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
}

function history_orders_get()
{
    $conn = $GLOBALS[conn];
    $user_id = $_POST[user_id];
    $sql = "SELECT * FROM `order_tbl` WHERE user_id=$user_id";
    $result = $conn->query ($sql);
    $j = 0;
    while ($row = $result->fetch_assoc ()) {
        $data[$j][order] = $row;
        $sql = "SELECT * FROM `order_item` WHERE order_id=" . $data[$j][order][id];
        $result2 = $conn->query ($sql);
        while ($row2 = $result2->fetch_assoc ()) {
            $data[$j][order_item][] = $row2;
        }
        $sql = "SELECT name FROM `shop` WHERE id=" . $row[shop_id];
        $result2 = $conn->query ($sql);
        if ($row2 = $result2->fetch_assoc ()) {
            $data[$j][shop] = $row2;
        }
        $j++;
    }
    $json->status = "ok";
    $json->data = $data;
    $json->message = "";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
}

function history_order_get_details()
{
    $conn = $GLOBALS[conn];
    $order_id = $_POST[order_id];
    $sql = "SELECT * FROM `order_tbl` WHERE id=$order_id";
    $result = $conn->query ($sql);
    while ($row = $result->fetch_assoc ()) {
        $data[order] = $row;
    }
    $sql = "SELECT * FROM `order_item` WHERE order_id=$order_id";
    $result = $conn->query ($sql);
    while ($row = $result->fetch_assoc ()) {
        $data[order_item][] = $row;
    }


    $json->status = "ok";
    $json->data = $data;
    $json->message = "";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
}

function search_item()
{
    $conn = $GLOBALS[conn];
    $shop_id = $GLOBALS[shop_id];
    $name = $_POST[name];

    $sql = "SELECT * FROM `item` WHERE name LIKE '%$name%' AND shop_id=$shop_id AND type IN(1,2)";
    $result = $conn->query ($sql);
    while ($row = $result->fetch_assoc ()) {
        $data[] = $row;
    }

    $json->status = "ok";
    $json->data = $data;
    $json->message = "";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
}
