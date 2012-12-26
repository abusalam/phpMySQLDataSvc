<?php
require_once('library.php');
$Data=new DB();
$Data->Debug=1;
$UserID=$Data->SqlSafe($_POST['UserID']);
$UserPass=$Data->SqlSafe($_POST['UserPass']);
$SessionID=$Data->SqlSafe($_POST['SessionID']);
$Token=$Data->SqlSafe($_POST['Token']);
$FingerPrint=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$SessionID);
if (($UserID!="") && ($UserPass!="")){
	$AuthResp['Auth']=CheckSession();
	$AuthDB=$Data->do_sel_query("Select `Token`,`AccessTime`,`FingerPrint` from ".MySQL_Pre."Sessions where `SessionID`='{$SessionID}'");
	if($AuthDB>0){	// Verification of Token, FingerPrint and AccessTime is pending
		$AuthRow=$Data->get_row();
		$QueryLogin="Select `PartMapID`,`UserName` from `".MySQL_Pre."Users` where `UserID`='".$UserID."' AND MD5(concat(`UserPass`,MD5('{$Token}')))='".$UserPass."'";
		$rows=$Data->do_sel_query($QueryLogin);
		if($rows>0){
			$Row=$Data->get_row();
			$action="LoggedIn";
			$AuthResp['UserName']=$Row['UserName'];
			$AuthResp['PartMapID']=$Row['PartMapID'];
			$Data->do_ins_query("UPDATE `".MySQL_Pre."Sessions` SET `UserID`='{$UserID}',`Status`='{$action}' where SessionID='{$SessionID}'");
			$Data->do_ins_query("Update ".MySQL_Pre."Users Set LoginCount=LoginCount+1 where `UserID`='".$UserID."' AND MD5(concat(`UserPass`,MD5('".$_POST['LoginToken']."')))='".$UserPass."'");
			$Data->do_ins_query("INSERT INTO ".MySQL_Pre."logs (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserID`,`URL`,`Action`,`Method`,`URI`) values"
					."('".$_SESSION['ID']."','".$_SERVER['REMOTE_ADDR']."','".$Data->SqlSafe($_SERVER['HTTP_REFERER'])."','".$_SERVER['HTTP_USER_AGENT']
					."','".$_SESSION['UserName']."','".$Data->SqlSafe($_SERVER['PHP_SELF'])."','Login: Success','".$Data->SqlSafe($_SERVER['REQUEST_METHOD'])
					."','".$Data->SqlSafe($_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING'])."');");
		}
		else{
			$action="Invalid Credential";
			$Data->do_ins_query("UPDATE `".MySQL_Pre."Sessions` SET `UserID`='{$UserID}',`Status`='{$action}' where SessionID='{$SessionID}'");

		}
	}
	else{
		$action="NoAccess";
		$Data->do_ins_query("INSERT INTO ".MySQL_Pre."logs (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserID`,`URL`,`Action`,`Method`,`URI`) values"
				."('".$_SESSION['ID']."','".$_SERVER['REMOTE_ADDR']."','".$Data->SqlSafe($_SERVER['HTTP_REFERER'])."','".$_SERVER['HTTP_USER_AGENT']
				."','".$UserID."','".$Data->SqlSafe($_SERVER['PHP_SELF'])."','Login: Failed','"
				.$Data->SqlSafe($_SERVER['REQUEST_METHOD'])."','".$Data->SqlSafe($_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING'])."');");
	}
	$AuthResp['Status']=$action;
}
else
{
	$action="Initiated";
	$AuthResp['AuthID']=RandStr(12,md5($_SERVER['REMOTE_ADDR'].time().$ID));
	$FingerPrint=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$AuthResp['AuthID']);
	$AuthResp['Token']="06677d3ceae182f53ab11e3b8dfb096e";//md5($_SERVER['REMOTE_ADDR'].$ID.time());
	$Data->do_ins_query("INSERT INTO `".MySQL_Pre."Sessions` (`SessionID` ,`Token`,`FingerPrint`,`InitTime`) VALUES ('{$AuthResp['AuthID']}' ,'{$AuthResp['Token']}','{$FingerPrint}',CURRENT_TIMESTAMP);");
	$AuthResp['Status']=$action;
}
//$AuthResp['Debug']['SessionID']=$SessionID;
//$AuthResp['Debug']['QueryLogin']=$QueryLogin;
//$AuthResp['POST']=$_POST;
echo json_encode($AuthResp);
?>