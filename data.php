<?php
require_once('library.php');
//$RespData['Auth']=CheckSession();
$Data=new DB();
$Data->Debug=1;
$Query="Select * from `".MySQL_Pre."ACs`";
$RowCount=$Data->do_sel_query($Query);
$TotalCols=$Data->ColCount;
$RespData['RowCount']=$RowCount;
$RespData['ColCount']=$TotalCols;
$i=0;
while ($i<$TotalCols){
	$RespData['ColHeads'][$i]=GetColHead($Data->GetFieldName($i));
	$i++;
}
$r=0;
while ($line = $Data->get_row()){
	$c=0;
	foreach ($line as $col_value){
		$RespData['Data'][$r][$c]=$col_value;
		$c++;
	}
	$r++;
}
//$Data->do_close();
unset($Data);
echo json_encode($RespData);
?>