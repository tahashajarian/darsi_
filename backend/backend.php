<?php
include "dbconnect.php";
include "session.php";
include 'Mobile_Detect.php';

$shop_pass='13286301546233571032';

session_set('user_agent',$_SERVER['HTTP_USER_AGENT']);
session_set('shop_pass',$shop_pass);

$sql = "SELECT id,phone FROM `shop`  WHERE pass='$shop_pass'";
$result = $conn->query( $sql ) ;

if ( $row = $result->fetch_assoc() ) {
	$shop_id= $row['id'];
	$phone= $row['phone'];

}

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
    case 'get_order_complete':
        get_order_complete();
        break;
    case 'user_get':
        user_get();
        break;
    case 'user_login_send_code_to_user':
        user_login_send_code_to_user();
        break;
    case 'user_login_verify_code':
        user_login_verify_code();
        break;
		case 'add_to_cart':
        add_to_cart();
        break;
		case 'get_around_pharm':
        get_around_pharm();
        break;
		case 'check_discount_code':
        check_discount_code();
        break;
		case 'update_user_data':
	      update_user_data();
	      break;
		case 'bascket_get':
	      bascket_get();
	      break;
		case 'use_wallet':
	      use_wallet();
	      break;
		case 'get_shop_phone':
				get_shop_phone();
				break;
		case 'get_order_repeat':
				get_order_repeat();
				break;
		case 'get_session':
				get_session();
				break;
		case 'set_session':
				set_session();
				break;
		case 'get_user':
				get_user();
				break;
		case 'update_user_name':
				update_user_name();
				break;
		case 'exit_user':
				exit_user();
				break;
		case 'get_order':
				get_order();
				break;
}
function get_order(){

	$conn=$GLOBALS['conn'];
	$order_id=+session_get('order_id');
	$user_id=+session_get('user_id');
	$shop_id=$GLOBALS['shop_id'];
	$type = 3;

	if ( $order_id>0 ) {
		$stmt = $conn->prepare( "SELECT * FROM `order_tbl` WHERE id=?" );
		$stmt->bind_param( "i", $order_id );
		$stmt->execute();
		$result = $stmt->get_result();
		if ( $row = $result->fetch_assoc() ) {
			$order_Array = $row;
		}
	} else if ( $user_id>0 ) {
		$stmt = $conn->prepare( "SELECT * FROM `order_tbl` WHERE shop_id=? AND user_id=?  ORDER BY id desc LIMIT 1" );
		$stmt->bind_param( "ii", $shop_id, $user_id );
		$stmt->execute();
		$result = $stmt->get_result();
		if ( $row = $result->fetch_assoc() ) {
			$order_Array = $row;
		}
	}
	if(($order_Array[status]!=2&&$order_Array[status]!=1)||!$order_Array){
		if($order_Array[status]!=3||($order_Array[status]==3&&$order_Array[fact_status]==0)||!$order_Array){
			$order_Array=insert_order($user_id,$type,$shop_id);
		}
	}

	$sql="UPDATE `order_tbl` SET type=$type WHERE id=".$order_Array["id"]." AND user_id=$user_id";
	$conn->query($sql);

	$stmt = $conn->prepare( "SELECT order_item.option_price,order_item.item_id,order_item.comment,order_item.item_count,item.name,item.picture,item.status,item.price,item.unit,item.weight FROM `order_item` INNER JOIN `item` ON order_item.item_id=item.id AND order_item.order_id=?" );
	$stmt->bind_param( "i", $order_Array[ 'id' ] );
	$stmt->execute();
	$result = $stmt->get_result();
	while ( $row = $result->fetch_assoc() ) {
		$item_Array[] = $row;
	}

	$stmt = $conn->prepare( "SELECT * FROM `sep` WHERE order_id=?" );
	$stmt->bind_param( "i", $order_Array[ 'id' ] );
	$stmt->execute();
	$result = $stmt->get_result();
	if ( $row = $result->fetch_assoc() ) {
		$sep_Array = $row;
	}
	$json->order = $order_Array;
	$json->item = $item_Array;
	$json->sep = $sep_Array;
	header( 'Content-Type: application/json' );
	echo json_encode( $json, JSON_NUMERIC_CHECK );
	$result->close();
}

function insert_order($user_id,$type,$shop_id){
	$conn=$GLOBALS['conn'];
	$flag=true;
	while($flag==true){
	$order_code=rand(1000000000,9999999999).rand(1000000000,9999999999);
	$sql="SELECT COUNT(order_code) AS count FROM `order_tbl` WHERE shop_id=$shop_id AND order_code='$order_code'";
	$result = $conn->query( $sql );
	if ( $row = $result->fetch_assoc() ) {
		if($row['count']>0)
			$flag=true;
		else
			$flag=false;
	}
	}
	$stmt = $conn->prepare( "INSERT INTO `order_tbl`( `user_id`, `shop_id`, `status`,`type`,order_code,time_created) VALUES (?,?,1,?,?,?)");
	$stmt->bind_param( "iiiii",$user_id,$shop_id,$type,$order_code,$_SERVER[REQUEST_TIME]);
	$stmt->execute();
	$order_id=$conn->insert_id;
	$sql="SELECT * FROM `order_tbl` WHERE id=$order_id";
	$result = $conn->query( $sql );
	if ( $row = $result->fetch_assoc() ) {
		$order = $row;
	}
	session_set("order_id",$order_id);
	return $order;
}

function exit_user(){
	session_clear();
	$json->status = "ok";
	$json->message = "";
	header ('Content-Type: application/json');
	echo json_encode ($json, JSON_NUMERIC_CHECK);
}
function update_user_name(){
	$conn=$GLOBALS['conn'];
	$user_id=+session_get("user_id");
	$name=$_POST[name];
	$stmt = $conn->prepare( "UPDATE user SET name=? WHERE id=?" );
	$stmt->bind_param( "si", $name,$user_id );
	if($stmt->execute()){
		$json->status = "ok";
		$json->message = "نام بروز شد";
	}else{
		$json->status = "fail";
		$json->message = "مشکل در بروزرسانی نام";
	}
	header ('Content-Type: application/json');
	echo json_encode ($json, JSON_NUMERIC_CHECK);
}
function get_user(){
	$conn=$GLOBALS['conn'];
	$user_id=+session_get("user_id");
	if($user_id>0){
	$stmt = $conn->prepare( "SELECT * FROM `user` WHERE id=?" );
	$stmt->bind_param( "i", $user_id );
	$stmt->execute();
	$result = $stmt->get_result();
	if ( $row = $result->fetch_assoc() ) {
		$user=$row;
	}
	$json->status = "ok";
	$json->message = $user[name]."، خوش آمدید";
	$json->data= $user;

}else{
	$json->status = "fail";
	$json->message = "هیچ کاربری یافت نشد";
}
header ('Content-Type: application/json');
echo json_encode ($json, JSON_NUMERIC_CHECK);
}
function get_session(){
	$json->status = "ok";
	$json->message = "";
	$json->data=session_get($_POST[object_name]);
	header ('Content-Type: application/json');
	echo json_encode ($json, JSON_NUMERIC_CHECK);
}
function set_session(){
	session_set($_POST[object_name],$_POST[object_val]);
	$json->status = "ok";
	$json->message = "سشن ست شد";
	header ('Content-Type: application/json');
	echo json_encode ($json, JSON_NUMERIC_CHECK);
}
function get_order_repeat(){
	$order_id = $_POST[ 'order_id' ];
		$conn=$GLOBALS['conn'];

	if($GLOBALS['shop_id']>0){
	$shop_id=$GLOBALS['shop_id'];
	}
	$stmt = $conn->prepare( "SELECT * FROM `order_tbl` WHERE id=?" );
	$stmt->bind_param( "i", $order_id );
	$stmt->execute();
	$result = $stmt->get_result();
	if ( $row = $result->fetch_assoc() ) {
	$flag = true;
	while ( $flag == true ) {
		$order_code = rand( 1000000000, 9999999999 ) . rand( 1000000000, 9999999999 );
		$sql = "SELECT COUNT(order_code) AS count FROM `order_tbl` WHERE shop_id=$shop_id AND order_code='$order_code'";
		$result2 = $conn->query( $sql );
		if ( $row2 = $result2->fetch_assoc() ) {
			if ( $row2[ 'count' ] > 0 )
				$flag = true;
			else
				$flag = false;
		}
	}
	$comment="".$row[ 'comment' ];
	$stmt = $conn->prepare( "INSERT INTO `order_tbl`(`time_created`, `user_id`, `shop_id`, `type`, `lat`, `lng`, `address`, `google_addr`, `dist`, `comment`, `price`,`order_code`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)" );
	$stmt->bind_param( "iiiiddssisii", time(), $row[ 'user_id' ], $row[ 'shop_id' ], $row[ 'type' ], $row[ 'lat' ], $row[ 'lng' ], $row[ 'address' ], $row[ 'google_addr' ], $row[ 'dist' ],$comment, $row[ 'price' ],$order_code );
	$stmt->execute();
	$last_id = $conn->insert_id;
	}
	$stmt = $conn->prepare( "SELECT * FROM `order_item` WHERE order_id=?" );
	$stmt->bind_param( "i", $order_id );
	$stmt->execute();
	$result = $stmt->get_result();
	while ( $row = $result->fetch_assoc() ) {
	$order_item[] = $row;
	$stmt = $conn->prepare( "INSERT INTO `order_item`(`order_id`, `item_id`, `item_count`, `comment`) VALUES (?,?,?,?)" );
	$stmt->bind_param( "iiis",$last_id,$row['item_id'],$row['item_count'],$row['comment']);
	$stmt->execute();
	}
	$json->status = "ok";
	$json->data = $last_id;
	$json->message = "";
	header ('Content-Type: application/json');
	echo json_encode ($json, JSON_NUMERIC_CHECK);
}
function get_root_item(){
	$conn = $GLOBALS['conn'];
	$shop_id = $_POST['shop_id'];
	$sql = "SELECT id FROM `item` WHERE shop_id=$shop_id AND type=3";
	$result = $conn->query ($sql);
	if ($row = $result->fetch_assoc ()) {
			$root = $row[id];
	}
	if(!$root){
		$shop_id = $GLOBALS['shop_id'];
		$sql = "SELECT id FROM `item` WHERE shop_id=$shop_id AND type=3";
		$result = $conn->query ($sql);
		if ($row = $result->fetch_assoc ()) {
				$root = $row[id];
		}


	}


	$sql = "SELECT item_id FROM `folder` WHERE folder_id=$root";
	$result = $conn->query ($sql);
	while ($row = $result->fetch_assoc ()) {
			$sql = "SELECT * FROM `item` WHERE  status>-1 AND id=" . $row[item_id];
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
function get_shop_phone(){
	echo $GLOBALS[phone];
}
function use_wallet(){
	$conn=$GLOBALS['conn'];
	$order_id=+$_POST['order_id'];
	$operation=$_POST['operation'];
	$delta=0;

	$sql = "SELECT * FROM `order_tbl`  WHERE id=$order_id";

	$result = $conn->query( $sql ) ;
	if ( $row = $result->fetch_assoc() ) {
		$order= $row;
	}
	$result->close();

	//$main_price=get_main_price();//اینجا نیاز به تابع محاسبه قیمت میباشد.


	$sql = "SELECT * FROM `user`  WHERE id=".$order['user_id'];
	$result = $conn->query( $sql ) ;
	if ( $row = $result->fetch_assoc() ) {
		$user= $row;
	}

	$result->close();
	if($operation=="use_wallet"){
	$delta=$main_price['price']-$user['amount_wallet'];

	if($delta<=0){
		$money=0;
		$used_wallet=$main_price['price'];
	} else {

		$used_wallet=$user['amount_wallet']+$order['used_wallet'];
		$money=$delta;
	}
}else if($operation=="dont_use_wallet"){
		$money=$main_price['price']+$order['used_wallet'];
		$used_wallet=0;
	}
	$sql = "UPDATE `order_tbl` SET `used_wallet`=$used_wallet WHERE id=$order_id";
	$conn->query( $sql );
	echo $used_wallet;
	if($used_wallet>0){
	$json=new stdClass();
	$json->message="عملیات با موفقیت انجام شد";
	$json->status="ok";
	$json->data=$used_wallet;
	header( 'Content-Type: application/json' );
	echo json_encode($json);
}else{
	$json=new stdClass();
	$json->message="کیف پول شما خالی میباشد";
	$json->status="fail";
	$json->data=$used_wallet;
	header( 'Content-Type: application/json' );
	echo json_encode($json);
}}
function shamsi_to_unixtime($date){
	$date = convertNumbers($date);
	$jy=explode("/",$date)[0];
	$jm=explode("/",$date)[1];
	$jd=explode("/",$date)[2];
	$gregorian=jalali_to_gregorian($jy,$jm,$jd,'/');
	return strtotime($gregorian);;
}
function update_user_data(){
	$conn=$GLOBALS['conn'];
	$name = $_POST[ 'name' ];
	$gender = $_POST[ 'gender' ];
	$birthday = shamsi_to_unixtime($_POST[ 'birthday' ]);
	$user_id = session_get('user_id');
	if(!$name){
		$json->message="لطفا نام و نام خانوادگی را وارد کنید";
		$json->status="fail";
		header( 'Content-Type: application/json' );
		echo json_encode($json);
		exit;

	}else if(!$birthday){

		$json->message="لطفا تاریخ تولد را وارد کنید";
		$json->status="fail";
		header( 'Content-Type: application/json' );
		echo json_encode($json);
		exit;
	}

	$stmt=$conn->prepare("UPDATE `user` SET `name`=?,`birthday`=?,`gender`=? WHERE `id`=?");
	$stmt->bind_param( "sisi", $name,$birthday,$gender,$user_id );
	$res=$stmt->execute();
	$stmt->close();

	$json->message="success";
	$json->status="ok";
	header( 'Content-Type: application/json' );
	echo json_encode($json);

}
function check_discount_code() {
	date_default_timezone_set( 'Asia/Tehran' );
	$start_today=strtotime('today midnight');
	$now=$_SERVER['REQUEST_TIME']-$start_today;
	$shop_id=+$GLOBALS['shop_id'];
	$conn=$GLOBALS['conn'];
	$order_id=$_POST['order_id'];
	$code=$_POST['discount_code'];

	$sql = "SELECT * FROM `shop`  WHERE id=$shop_id";
	$result = $conn->query( $sql );
	if ( $row = $result->fetch_assoc() ) {
		$shop = $row;
	}
		if ( $shop[ master_id ] > 0 ) {
		$shop_id=$shop[ master_id ];
		$sql = "SELECT * FROM `shop`  WHERE id=" . $shop[ 'master_id' ] . " OR master_id=" . $shop[ 'master_id' ];

		$result = $conn->query( $sql );
		while ( $row = $result->fetch_assoc() ) {
			$shop_array[] = $row;
		}

	} else {
		$shop_array[ 0 ] = $shop;
	}
	$order_Array = array();
	$stmt = $conn->prepare( "SELECT * FROM `order_tbl` WHERE id=?" );
	$stmt->bind_param( "i", $order_id );
	$stmt->execute();
	$result = $stmt->get_result();
	if ( $row = $result->fetch_assoc() ) {
		$order_Array = $row;
	}
	$price=0;
	$sql = "SELECT item.price,order_item.item_count FROM item INNER JOIN order_item ON item.id =order_item.item_id AND order_item.order_id=$order_id";
	$result = $conn->query( $sql );
	while ( $row = $result->fetch_assoc() ) {
		$price += ($row[ 'price' ]+$row['option_price']) * $row[ 'item_count' ];

	}
	$result->close();
	if ( !$price ) return 0;
	$stmt = $conn->prepare( "SELECT * FROM `discount` WHERE active=1 AND code=? AND shop_id=? AND `end`>? AND `start`<? AND min_buy<=? AND del=0 AND ((start_time=0 AND end_time=0)||(start_time<$now AND end_time>$now)) " );
	$stmt->bind_param( "siiii", $code,$shop_id, $order_Array[ 'fact_create_time' ],$order_Array[ 'fact_create_time'], $price);
	$stmt->execute();
	$result = $stmt->get_result();

	if ( $row = $result->fetch_assoc() ) {
		$discount = $row;
	}

	$used=0;
	$amount = 0;
	if ( $discount[ 'type' ] ==1) {
	for($i=0;$i<count($shop_array);$i++){
		$stmt = $conn->prepare( "SELECT COUNT(*) AS used_count FROM `order_tbl` WHERE discount_code=? AND shop_id=? AND user_id=? AND status NOT IN (-1,5)" );
		$stmt->bind_param( "sii", $code, $shop_array[$i]['id'],$order_Array[ 'user_id' ] );
		$stmt->execute();
		$result = $stmt->get_result();
		if ( $row = $result->fetch_assoc() ) {
			$used += $row[ 'used_count' ];

		}

		}

		if ( ($used <$discount['used_max']+1)||($discount['used_max']==-1))
			$amount = $discount[ 'fixed' ] + $price * $discount[ 'percent' ] / 100;
		else
			$amount = 0;
	}else if ( $discount[ 'type' ] ==2||$discount[ 'type' ] ==4) {

		if($discount['used']<$discount['used_max']){

			$amount = $discount[ 'fixed' ] + $price * $discount[ 'percent' ] / 100;
		}else
			$amount = 0;


	} else if($discount[ 'type' ] == 3){
			if($order_Array['user_id']==$discount['user_id']&&$discount['used']<$discount['used_max']){
				$amount = $discount[ 'fixed' ] + $price * $discount[ 'percent' ] / 100;
			}
		else {
			$amount = 0;
		}
	}else if($discount[ 'type' ] == 5){

		if($discount['max_total']>0){
				$amount = $discount[ 'fixed' ] + $price * $discount[ 'percent' ] / 100;
				if($amount>$discount['max_total']){
						$amount = $discount['max_total'];
				}
			}
		else {
			$amount = 0;
		}
	}
	else if($discount[ 'type' ] == 6){

		$stmt = $conn->prepare( "SELECT * FROM `discount` WHERE  id=? AND del=0" );
		$stmt->bind_param( "i", $discount['master_dis_id']);
		$stmt->execute();

		$result = $stmt->get_result();
		if ( $row = $result->fetch_assoc() ) {
			$mother = $row;
		}


		if($mother['max_total']>0&&$discount['used']<$discount['used_max']){

				$amount = $discount[ 'fixed' ] + $price * $discount[ 'percent' ] / 100;
				if($amount>$mother['max_total']){
						$amount = $mother['max_total'];
				}
				if ( $discount[ 'max_discount' ] <$amount && $discount[ 'max_discount' ] > 0 ) {
						$amount = $discount[ 'max_discount' ];
				}
			}
		else {
			$amount = 0;
		}
	}
	if($discount[ 'type' ] != 6){
		if ( $discount[ 'max_discount' ] <$amount && $discount[ 'max_discount' ] > 0 ) {
			$amount = $discount[ 'max_discount' ];
		}
	}
	if($amount>0){
		$json->data=$amount;
		$json->status="ok";
		$json->message="success";
		header( 'Content-Type: application/json' );
		echo json_encode( $json, JSON_NUMERIC_CHECK );
}else{
	$json->data=$amount;
	$json->status="fail";
	$json->message="کد تخفیف نا معتبر است";
	header( 'Content-Type: application/json' );
	echo json_encode( $json, JSON_NUMERIC_CHECK );
}
}
function get_around_pharm(){
	$conn = $GLOBALS[ 'conn' ];
	$shop_id=+$GLOBALS['shop_id'];
	$user_lat = $_POST['lat'];
	$user_lng = $_POST['lng'];
	$branch_list=array();
	$sql="SELECT id,lat,lng,name,address,transport_fixed,transport_rate,sqrt( pow( ( lat - $user_lat ) * 111, 2 ) + pow( ( lng - $user_lng ) * 90, 2 ) ) AS far FROM `shop` WHERE master_id=$shop_id AND type=2 AND sqrt( pow( ( lat - $user_lat ) * 111, 2 ) + pow( ( lng - $user_lng ) * 90, 2 ) )<20  LIMIT 10";
	$result = $conn->query($sql);
	while( $row = $result->fetch_assoc() ) {
		$branch_list[]=$row;
		}
	header( 'Content-Type: application/json' );
	echo json_encode( $branch_list, JSON_NUMERIC_CHECK );
}
function get_option_force($item_id){
	$conn=$GLOBALS['conn'];
	$sql = "SELECT folder_id FROM folder WHERE item_id=$item_id";
	$result = $conn->query( $sql );
	if( $row = $result->fetch_assoc() ) {
		$folder_id = $row['folder_id'];
	}
	$sql = "SELECT * FROM options WHERE item_id IN($item_id,$folder_id) AND active=1 AND force_option=1";
	$result = $conn->query( $sql );
	while( $row = $result->fetch_assoc() ) {
		$options[] = $row;
	}
	return $options;
}
function add_to_cart(){
	$conn=$GLOBALS['conn'];
	$item_id = +$_POST['item_id'];
	$order_id = +session_get('order_id');
	$func = $_POST[ 'func' ];
	$comment=$_POST[ 'comment' ];

	if(!$item_id){
		exit;
	}
	$comment = isset($comment)?$comment:"";
	$comment_array=json_decode($comment,true);
	$option_force=get_option_force($item_id);

	$j=0;
	$start=count($comment_array);
	$end=count($comment_array)+count($option_force);

	for($i=$start;$i<$end;$i++){
		$flag=false;
		for($k=0;$k<$start;$k++){
			if($option_force[$j]['id']==$comment_array[$k]['id'])
			$flag=true;
		}
		if($flag==false){
			$comment_array[$i]['id']=$option_force[$j]['id'];
			$comment_array[$i]['name']=	$option_force[$j]['name'];
			$comment_array[$i]['price']=$option_force[$j]['price'];
		}
		$j++;
	}

	$comment_price=0;

	if(count($comment_array)==0){

		$comment="";
	}

	else{
		$comment=json_encode( $comment_array,JSON_NUMERIC_CHECK );
		for($i=0;$i<count($comment_array);$i++){
			$comment_price=$comment_price+$comment_array[$i]['price'];
		}
	}


	$sql = "SELECT weight,unit,status,order_min,order_max,name,price FROM `item`  WHERE id=$item_id AND status>-1";
	$result = $conn->query( $sql );
	if ( $row = $result->fetch_assoc() ) {
		$weight = $row[ 'weight' ];

		$data['name']=$row[ 'name' ];
		$data['price']=$row[ 'price' ];

		$unit = $row[ 'unit' ];
		$status = $row[ 'status' ];
		$order_min = $row[ 'order_min' ];
		$order_max = $row[ 'order_max' ];

	}
	$result->close();

if(($status==2||$status==0)&&$func!="remove"){
	$json->data="";
	$json->status="fail";
	$json->message="این آیتم تمام شده است";
	header( 'Content-Type: application/json' );
	echo json_encode( $json, JSON_NUMERIC_CHECK );
	exit;
}
	$item_Array = array();
	$count=0;
	$sql = "SELECT COUNT(*) AS count FROM `order_tbl` WHERE id=$order_id AND status=1 LIMIT 1";
	$result = $conn->query( $sql );
	if ( $row = $result->fetch_assoc() ) {
	$count= $row[ 'count' ];
	}

	$result->close();
	if($count>0){
	$stmt =$conn->prepare( "SELECT * FROM `order_item` WHERE item_id=? AND order_id=? AND comment=? LIMIT 1");
		$stmt->bind_param( "iis", $item_id, $order_id,$comment);
		$stmt->execute();
		$result = $stmt->get_result();
	if ( $row = $result->fetch_assoc() ) {
		if ( $func == "plus" ){
			$row[ 'item_count' ] = $row[ 'item_count' ] + $weight;
			if($row[ 'item_count' ]>$order_max){
			echo data_handler("","حداکثر مقدار این محصول ".unit_string($order_max,$unit)." می باشد","fail");
			exit;
			}
		}
		else if ( $func == "mines" ) {
			$row[ 'item_count' ] = $row[ 'item_count' ] - $weight;
			if($row[ 'item_count' ] < $order_min){
			$data['code']="modal_min_order_show" ;
			$data['id']=$item_id ;
			$status="ok";
			$message=" حداقل مقدار این محصول ".unit_string($order_min,$unit)." می باشد.آیا میخواهید محصول حذف شود؟";
			header( 'Content-Type: application/json' );
			echo data_handler($data,$message,$status);
			exit;

		}
		}


		if ( $row[ 'item_count' ]==0 || $func == "remove" ) {

			$sql = "DELETE FROM `order_item`  WHERE id=" . $row[ 'id' ];
			$conn->query( $sql );
		} else {
			$sql = "UPDATE `order_item` SET `item_count`=" . $row[ 'item_count' ] . " WHERE id=" . $row[ 'id' ];
			$conn->query( $sql );
		}
	} else if($func =="plus"){
		if($order_min==0)
			$first_count=$weight;
		else
			$first_count=$order_min;
		$stmt = $conn->prepare("INSERT INTO `order_item`(`order_id`, `item_id`, `item_count`,`comment`,`option_price`,`item_data`) VALUES (?,?,?,?,?,?)");
		$stmt->bind_param( "iidsis", $order_id, $item_id,$first_count,$comment,$comment_price,json_encode($data,JSON_NUMERIC_CHECK));
		$stmt->execute();
	}
	$result->close();
	$sql = "SELECT item.name,item.price,order_item.item_id,order_item.item_count,order_item.comment,order_item.option_price,item.unit,item.weight FROM item INNER JOIN order_item ON item.id =order_item.item_id AND order_item.order_id=$order_id";
	if ( $result = $conn->query( $sql ) ) {
		while ( $row = $result->fetch_assoc() ) {
			$item_Array[] = $row;
		}

		$json->status="ok";
		$json->message="success";
		header( 'Content-Type: application/json' );
		echo json_encode( $json, JSON_NUMERIC_CHECK );


		$result->close();
	} else {
		$json->status="fail";
		$json->message="";
		header( 'Content-Type: application/json' );
		echo json_encode( $json, JSON_NUMERIC_CHECK );
	}
	}
}
///////////////////////////////////////////////////////////////////////
function user_login_verify_code(){
  $conn=$GLOBALS['conn'];
  $pin = isset($_POST[ 'pin' ])?$_POST[ 'pin' ]:0;
  $pin = convertNumbers( $_POST[ 'pin' ], $toPersian = false );
	$user_id = session_get('user_id');
  $user_Array = array();
  $domain = $_SERVER[ 'HTTP_HOST' ];
  $shop_id=$GLOBALS['shop_id'];
  if($pin>0){
  $stmt = $conn->prepare( "SELECT * FROM `user` WHERE   id=? AND pin=? AND shop_id=? AND (pin_time+86400)>? AND type=1" );
  $stmt->bind_param( "iiii", $user_id,$pin,$shop_id,time() );
  $stmt->execute();
  $result = $stmt->get_result();
  if ( $row = $result->fetch_assoc() ) {
    $user_Array = $row;
  }
  $result->close();
  $stmt->close();
  if ($user_Array ) {
    $json->status = "ok";
    $json->data = $user_Array;
    $json->message =  "کد صحیح است";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
  session_set("user_id",$user_Array['id']);
  } else {
    $json->status = "fail";
    $json->data = "";
    $json->message = "کد نا معتبر است";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
  }
}else{
  $json->status = "fail";
  $json->data = "";
  $json->message = "کد نا معتبر است";
  header ('Content-Type: application/json');
  echo json_encode ($json, JSON_NUMERIC_CHECK);
}
}
function user_login_send_code_to_user(){

	$conn=$GLOBALS['conn'];
	$phone = +convertNumbers( $_POST[ 'phone' ]);
	$location=$_POST['location'];
	$shop_id=$GLOBALS['shop_id'];
	$stmt = $conn->prepare( "SELECT id FROM `user` WHERE phone=? AND shop_id=? AND type=1" );
	$stmt->bind_param( "si", $phone, $shop_id );
	$stmt->execute();
	$result = $stmt->get_result();
	if ( $row = $result->fetch_assoc() ) {
		session_set("user_id",$row['id']);
		get_pin($phone);
	} else {

		register($location,$phone);
		get_pin($phone);
	}
	$result->close();
	$stmt->close();
}
function register($location,$phone){
	$conn=$GLOBALS['conn'];
	$shop_id=$GLOBALS['shop_id'];

	$flag = true;
	while ( $flag ) {
	$pass=rand(1000000000,9999999999).rand(1000000000,9999999999);
	$sql = "SELECT COUNT(pass) AS count FROM `user` WHERE pass='$pass' AND type=1";

	$result = $conn->query( $sql );
	if ( $row = $result->fetch_assoc() ) {
		$count = $row[ 'count' ];
		if ( $count > 0 )
			$flag = true;
		else {
			$loc_array = array();
			$loc_array = json_decode( $location, true );

			$google_addr = file_get_contents( "https://shop.partapp.ir/location/get_address.php?lat=" . $loc_array[ 'latitude' ] . "&lng=" . $loc_array[ 'longitude' ] );
			$time = $_SERVER[REQUEST_TIME];
			$stmt = $conn->prepare( "INSERT INTO `user`( `pass`, `phone`, `shop_id`,google_addr,time_stamp,location) VALUES (?,?,?,?,?,?)" );
			$stmt->bind_param( "siisis", $pass, $phone,  $shop_id, $google_addr, $time,$location);
			try {
				$stmt->execute();
				$user_id = $stmt->insert_id;
				session_set("user_id",$user_id);

				$flag = false;

			} catch ( Exception $e ) {
				echo $e->getMessage();
			}
		}
	}
	}
	return $user_id;
}
function get_pin($phone){
  $conn=$GLOBALS['conn'];
  $sql = "SELECT * FROM `shop`  WHERE id=".$GLOBALS['shop_id'];
  $result = $conn->query( $sql );
  if ( $row = $result->fetch_assoc() ) {
  $shop_array = $row;
  }
  $stmt = $conn->prepare( "SELECT * FROM `user` WHERE phone=? AND shop_id=? AND type=1" );
  $stmt->bind_param( "ii", $phone, $GLOBALS['shop_id']);
  $stmt->execute();
  $result = $stmt->get_result();
  if ( $row = $result->fetch_assoc() ) {
  $pin = rand( 1000, 9999 );
  $stmt = $conn->prepare( "UPDATE  `user` SET pin=?,pin_time=? WHERE   phone=? AND id=?  AND app_user_id=0" );
  $stmt->bind_param( "iisi", $pin, time(), $phone, $row[ 'id' ] );
  $res = $stmt->execute();
    $url = "https://api.kavenegar.com/v1/672F656E30536E4335364B4735474C44557149542F673D3D/verify/lookup.json?receptor=$phone&token=" . convertNumbers( $pin ) . "&token10=".urlencode($shop_array['name'])."&template=Verify";
  $res=file_get_contents( $url );
file_put_contents( __FILE__ . ".log",$res . "\r\n", FILE_APPEND );
  $json->status = "ok";
  $json->data = "";
  $json->message ="کد به شماره تلفن شما ارسال شد";
  header ('Content-Type: application/json');
  echo json_encode ($json, JSON_NUMERIC_CHECK);
  } else {
    $json->status = "fail";
    $json->data = "";
    $json->message = "مشکل در ارسال کد تایید";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
  }
  $result->close();
  $stmt->close();
}
function user_get(){
  $conn   = $GLOBALS['conn'];
  $user_id    = +session_get("user_id");
  if($user_id){
    $sql    = "SELECT * FROM `user` WHERE id=$user_id";
    $result = $conn->query ($sql);
    if ($row = $result->fetch_assoc ()) {
      $user=$row;
    }
    $json->status = "ok";
    $json->data = $user;
    $json->message = "";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
  }else{
    $json->status = "fail";
    $json->data = "";
    $json->message = "شما هنوز ثبت نام نکرده اید";
    header ('Content-Type: application/json');
    echo json_encode ($json, JSON_NUMERIC_CHECK);
  }
}
$formatter= new IntlDateFormatter('en_IR@calendar=persian', 0, 0, 'Asia/Tehran', 0,  "yyyy/MM/dd"  );
echo $formatter->format($shop_array['expire_date']);

function bascket_get(){
	$conn   = $GLOBALS['conn'];
	$user_id    = +$_POST['user_id'];
	$sql    = "SELECT * FROM `order_tbl` WHERE user_id=$user_id ORDER BY id DESC LIMIT 1";
	$result = $conn->query ($sql);
	if ($row = $result->fetch_assoc ()) {
		$sql    = "SELECT order_item.item_id,order_item.item_count,item.name,item.price FROM `order_item` INNER JOIN item ON order_item.order_id=".$row[id]." AND order_item.item_id=item.id";
		$result = $conn->query ($sql);
		while ($row = $result->fetch_assoc ()) {
			$items[]=$row;
		}
	}
	if($items){
	$json->status = "ok";
	$json->data = $items;
	$json->message = "";
	header ('Content-Type: application/json');
	echo json_encode ($json, JSON_NUMERIC_CHECK);
}else{
	$json->status = "fail";
	$json->data = "";
	$json->message = "مشکل در دریافت سفارش";
	header ('Content-Type: application/json');
	echo json_encode ($json, JSON_NUMERIC_CHECK);
}
}
function get_order_complete(){
  $conn   = $GLOBALS['conn'];
  $user_id    = +$_POST['user_id'];
  $sql    = "SELECT * FROM `order_tbl` WHERE user_id=$user_id ORDER BY id DESC LIMIT 1";
  $result = $conn->query ($sql);
  if ($row = $result->fetch_assoc ()) {
    $order=$row;
    $sql    = "SELECT order_item.item_id,order_item.item_count,item.name,item.price FROM `order_item` INNER JOIN item ON order_item.order_id=".$row[id]." AND order_item.item_id=item.id";
    $result = $conn->query ($sql);
    while ($row = $result->fetch_assoc ()) {
      $order['items'][]=$row;
    }
  }
  if($order){
  $json->status = "ok";
  $json->data = $order;
  $json->message = "";
  header ('Content-Type: application/json');
  echo json_encode ($json, JSON_NUMERIC_CHECK);
}else{
  $json->status = "fail";
  $json->data = "";
  $json->message = "مشکل در دریافت سفارش";
  header ('Content-Type: application/json');
  echo json_encode ($json, JSON_NUMERIC_CHECK);
}
}
function get_batch_list()
{
    $conn   = $GLOBALS['conn'];
    $id     = +$_POST['id'];
    $sql    = "SELECT item_id FROM `folder` WHERE folder_id=$id";
    $result = $conn->query ($sql);
    while ($row = $result->fetch_assoc ()) {
        $sql = "SELECT * FROM `item` WHERE id=" . $row[item_id] . " AND type=2 AND status>-1";
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
        $sql = "SELECT * FROM `item` WHERE id=" . $row[item_id] . " AND type=1 AND status>-1";
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

    $sql = "SELECT * FROM `item` WHERE name LIKE '%$name%' AND shop_id=$shop_id AND type=1 AND status>-1";
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
function convertNumbers($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $arabic = ['٠', '١', '٢', '٣','٤','٥','٦','٧' ,'٨' ,'٩'];
    $num = range(0, 9);
    $convertedPersianNums = str_replace($persian, $num, $string);
    $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);
    return $englishNumbersOnly;
}
function gregorian_to_jalali($gy,$gm,$gd,$mod=''){
	 $g_d_m=array(0,31,59,90,120,151,181,212,243,273,304,334);
	 if($gy>1600){
		$jy=979;
		$gy-=1600;
	 }else{
		$jy=0;
		$gy-=621;
	 }
	 $gy2=($gm>2)?($gy+1):$gy;
	 $days=(365*$gy) +((int)(($gy2+3)/4)) -((int)(($gy2+99)/100)) +((int)(($gy2+399)/400)) -80 +$gd +$g_d_m[$gm-1];
	 $jy+=33*((int)($days/12053));
	 $days%=12053;
	 $jy+=4*((int)($days/1461));
	 $days%=1461;
	 if($days > 365){
		$jy+=(int)(($days-1)/365);
		$days=($days-1)%365;
	 }
	 $jm=($days < 186)?1+(int)($days/31):7+(int)(($days-186)/30);
	 $jd=1+(($days < 186)?($days%31):(($days-186)%30));
	 return($mod=='')?array($jy,$jm,$jd):$jy.$mod.$jm.$mod.$jd;
}


function jalali_to_gregorian($jy,$jm,$jd,$mod=''){
	 if($jy>979){
		$gy=1600;
		$jy-=979;
	 }else{
		$gy=621;
	 }
	 $days=(365*$jy) +(((int)($jy/33))*8) +((int)((($jy%33)+3)/4)) +78 +$jd +(($jm<7)?($jm-1)*31:(($jm-7)*30)+186);
	 $gy+=400*((int)($days/146097));
	 $days%=146097;
	 if($days > 36524){
		$gy+=100*((int)(--$days/36524));
		$days%=36524;
		if($days >= 365)$days++;
	 }
	 $gy+=4*((int)($days/1461));
	 $days%=1461;
	 if($days > 365){
		$gy+=(int)(($days-1)/365);
		$days=($days-1)%365;
	 }
	 $gd=$days+1;
	 foreach(array(0,31,(($gy%4==0 and $gy%100!=0) or ($gy%400==0))?29:28 ,31,30,31,30,31,31,30,31,30,31) as $gm=>$v){
		if($gd<=$v)break;
		$gd-=$v;
	 }
	 return($mod=='')?array($gy,$gm,$gd):$gy.$mod.$gm.$mod.$gd;
}
