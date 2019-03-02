<?php

	// farz bar ine ke pish az session data include shode
	// $GLOBALS['conn']

	// save ro baraye in tahe set naneveshtim ke roozi ke toonestim payane php ro detect konim save ro bezarim oontoo ke faghat yekbar save she

	session_load();

	function session_new(){
		$conn=$GLOBALS['conn'];

		while(!$session['id']){
			$session['pass']=rand(1000000000,9999999999).rand(1000000000,9999999999);
			$sql = "INSERT INTO `session`(`pass`,`time_created`) VALUES ('".$session['pass']."',".$_SERVER['REQUEST_TIME'].")";
			$conn->query($sql);
			$session['id']=$conn->insert_id;
		}

		$GLOBALS['session'] = $session;
	}

	function session_get($object_name){
		return $GLOBALS['session']['data'][$object_name];
	}

	function session_set($object_name,$object){
		if (+$GLOBALS['session']['pass']<1E19) session_new();

		$GLOBALS['session']['data'][$object_name]=$object;

		session_save();
		}

	function session_load(){
		if (+$_COOKIE['session_pass']<1E19) return; // validate PASS

		$conn=$GLOBALS['conn'];
		$sql = "SELECT * FROM `session` WHERE pass='".$_COOKIE['session_pass']."'"; // ! Security -- pass bayad filter she ya prepared stat. estefade konim
		$session=$conn->query($sql)->fetch_assoc();

		// only data is json
		$session['data']=json_decode($session['data'],true);

		$GLOBALS['session'] = $session;

		setcookie("session_pass",$session['pass'], $_SERVER['REQUEST_TIME'] + ( 86400 * 100 ), "/" );
		}

	function session_save(){
		$session = $GLOBALS['session'];

		// chon save hamishe pas az set e pas hamishe session darim chon age nabashe too set new mishe
		// if (!$session['pass']) $session = session_new();

		$conn=$GLOBALS['conn'];
		$sql = "UPDATE `session` SET `time_updated`=".$_SERVER['REQUEST_TIME'].",`data`='".json_encode( $session['data'])."' WHERE pass='".$session['pass']."'";
		$conn->query($sql);

		setcookie("session_pass",$session['pass'], $_SERVER['REQUEST_TIME'] + ( 86400 * 100 ), "/" );
	}
	function session_clear(){
		setcookie("session_pass", "", time() - (86400 * 100), "/");
	}
?>
