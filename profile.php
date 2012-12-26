<?php
require_once('library.php');
$RespData['Auth']=CheckSession();
$RespData['Success']=FALSE;
$Data=new DB();
$Data->Debug=1;
$SessionID=$Data->SqlSafe($_POST['SessionID']);
$UserID=$Data->do_max_query("Select UserID from `".MySQL_Pre."Sessions` where SessionID='{$SessionID}';");
$UserPass=$Data->SqlSafe($_POST['OldUP']);
$NewUserPass=$Data->SqlSafe($_POST['NewUP']);
$Token=$Data->SqlSafe($_POST['Token']);
$QueryChgPwd="Update `".MySQL_Pre."Users` Set `UserPass`='{$NewUserPass}' where `UserID`='{$UserID}' AND MD5(concat(`UserPass`,MD5('{$Token}')))='{$UserPass}'";
$Rows=$Data->do_ins_query($QueryChgPwd);
if($Rows>0)
	$RespData['Success']=TRUE;
$Data->do_close();
unset($Data);
echo json_encode($RespData);
?>