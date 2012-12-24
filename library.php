<?php 
require_once('database.php');
function RandStr($length,$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"){	
	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}
	$sess_id=md5(microtime());
	return $str;
}
function GetColHead($ColName)
{
	$Fields=new DB();
	$ColHead=$Fields->do_max_query("Select Description from SRER_FieldNames where FieldName='{$ColName}'");
	$Fields->do_close();
	unset($Fields);
	return (!$ColHead?$ColName:$ColHead);
}
function CheckSession(){
	$Data=new DB();
	$AuthResp['Status']="Valid";
	$AuthResp['Debug']=$_POST;
	$SessionID=$Data->SqlSafe($_POST['SessionID']);
	$LogOut=$Data->SqlSafe($_POST['LogOut']);
	$Token=$Data->SqlSafe($_POST['Token']);
	$RowCount=$Data->do_sel_query("Select `FingerPrint`,`AccessTime`,`Token` from `".MySQL_Pre."Sessions` where `SessionID`='{$SessionID}'");
	if($RowCount>0)
	{
		$RowData=$Data->get_row();
		$FingerPrintDB=$RowData['FingerPrint'];
		$AccessTimeDB=$RowData['AccessTime'];
		$TokenDB=$RowData['Token'];
		$FingerPrint=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$SessionID);
		if($LogOut!=""){
			//$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `Status`='LoggedOut' where `SessionID`='{$LogOut}'");
			$Data->do_close();
			unset($Data);
			$AuthResp['Status']="LogOut".$LogOut;
			exit(json_encode($AuthResp));
		}
		else if($AccessTimeDB<(time()-(LifeTime*60))){
			//$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `Status`='TimedOut' where `SessionID`='{$SessionID}'");
			$Data->do_close();
			unset($Data);
			$AuthResp['Status']="LogOut";
			exit(json_encode($AuthResp));
		}
		else if($FingerPrint!=$FingerPrintDB){
			//$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `Status`='Forged Request' where `SessionID`='{$SessionID}'");
			$Data->do_close();
			unset($Data);
			$AuthResp['Status']="Forged Request";
			exit(json_encode($AuthResp));
		}
		else if($TokenDB!=$Token){
			//$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `Status`='Forged Request' where `SessionID`='{$SessionID}'");
			$Data->do_close();
			unset($Data);
			$AuthResp['Status']="Forged Request";
			exit(json_encode($AuthResp));
		}
	}
	else{
		$Data->do_close();
		unset($Data);
		$AuthResp['Status']="Invalid Session"."Select `FingerPrint`,`AccessTime`,`Token` from `".MySQL_Pre."Sessions` where `SessionID`='{$SessionID}'";
		exit(json_encode($AuthResp));
	}
	$AuthResp['Token']=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$SessionID.md5(microtime()));
	//$Data->do_ins_query("Update `".MySQL_Pre."Sessions` Set `Token`='{$AuthResp['Token']}' where `SessionID`='{$SessionID}'");
	$Data->do_close();
	unset($Data);
	return $AuthResp;
}
?>