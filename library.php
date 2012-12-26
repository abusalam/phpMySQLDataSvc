<?php 
require_once('database.php');
function RandStr($length,$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"){
	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ){
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}
	$sess_id=md5(microtime());
	return $str;
}
function GetColHead($ColName){
	$Fields=new DB();
	$ColHead=$Fields->do_max_query("Select Description from SRER_FieldNames where FieldName='{$ColName}'");
	$Fields->do_close();
	unset($Fields);
	return (!$ColHead?$ColName:$ColHead);
}
function CheckSession(){
	$Data=new DB();
	$AuthResp['Status']="Valid";
	//$AuthResp['Debug']=$_POST;
	$SessionID=$Data->SqlSafe($_POST['SessionID']);
	$LogOut=$Data->SqlSafe($_POST['LogOut']);
	$Token=$Data->SqlSafe($_POST['Token']);
	$RowCount=$Data->do_sel_query("Select `FingerPrint`,(CURRENT_TIMESTAMP-`InitTime`) as `LoggedInSince`,(CURRENT_TIMESTAMP-`AccessTime`) as `TimeElapsed`,`Token` from `".MySQL_Pre."Sessions` where `SessionID`='{$SessionID}'");
	if($RowCount>0){
		$RowData=$Data->get_row();
		$AuthResp['TimeElapsed']=$RowData['LoggedInSince'];
		$FingerPrint=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$SessionID);
		if(isset($_POST['LogOut'])){
			$AuthResp['Status']="LogOut [{$LogOut}]";
			$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `Status`='{$AuthResp['Status']}' where `SessionID`='{$LogOut}'");
			$Data->do_close();
			unset($Data);
			exit(json_encode($AuthResp));
		}
		else if($RowData['TimeElapsed']>(LifeTime*60)){
			$AuthResp['Status']="TimeOut [{$RowData['TimeElapsed']}]";
			$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `Status`='{$AuthResp['Status']}' where `SessionID`='{$SessionID}'");
			$Data->do_close();
			unset($Data);
			exit(json_encode($AuthResp));
		}
		else if($FingerPrint!=$RowData['FingerPrint']){
			$AuthResp['Status']="Forged Request";
			$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `Status`='{$AuthResp['Status']}' where `SessionID`='{$SessionID}'");
			$Data->do_close();
			unset($Data);
			exit(json_encode($AuthResp));
		}
		else if($RowData['Token']!=$Token){
			$AuthResp['Status']="Token Forged[{$RowData['Token']}]";
			$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `Status`='{$AuthResp['Status']}' where `SessionID`='{$SessionID}'");
			$Data->do_close();
			unset($Data);
			exit(json_encode($AuthResp));
		}
	}
	else{
		$AuthResp['Status']="Invalid Session";
		$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `Status`='{$AuthResp['Status']}' where `SessionID`='{$SessionID}'");
		$Data->do_close();
		unset($Data);
		exit(json_encode($AuthResp));
	}
	$AuthResp['Token']=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$SessionID.md5(microtime()));
	$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `HitCount`=`HitCount`+1, Token`='{$AuthResp['Token']}' where `SessionID`='{$SessionID}'");
	$Data->do_close();
	unset($Data);
	return $AuthResp;
}
?>