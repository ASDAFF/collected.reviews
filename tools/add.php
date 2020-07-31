<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

define("STOP_STATISTICS", true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');



//добавление отзыва 
$MODULE_ID = 'collected.reviews';

$output = array(
	'ID' => 0,
	'ERROR' => false,
	'MESSAGE' => '',
	);

function retJSON($out, $message, $check_error = false)
{
	if(($out['ERROR'] && $check_error) || !$check_error)
	{
		if($out['ERROR'])
		{ 
			$message = $message == '' ? '' : '<b>'.$message.'</b>:<br/>' ;
			$out["MESSAGE"] = $message . $out["MESSAGE"];
		}
		//echo json_encode($out);
		echo CUtil::PhpToJSObject($out);
	}
}

CUtil::JSPostUnescape();

$REIEW = $_REQUEST['COLLECTED_REVIEWS'];

if(CModule::IncludeModule($MODULE_ID))
{
	$arFields = array(
		'ELEMENT_ID'	=> $REIEW['ELEMENT_ID'],
		'DIGNITY'		=> $REIEW['DIGNITY'],
		'LIMITATIONS' 	=> $REIEW['LIMITATIONS'],
		'COMMENTS' 		=> $REIEW['COMMENTS'],
		'RATING'		=> $REIEW['RATING'],
		'USER_NAME' 	=> $REIEW['USER_NAME'],
		'USER_EMAIL' 	=> $REIEW['USER_EMAIL'],
		'SITE_ID'		=> SITE_ID,
		);
	
	$Message = new COLLECTEDReviewsMessages();

	$ret = $Message->Add($arFields);

	if($ret === false)
	{
		$output['ERROR'] = true;
		$output['MESSAGE'] = $Message->last_error;
		$message = $Message->message;
	}
	else
	{
		$output['MESSAGE'] = $Message->message;
		$output['ID'] = $ret;
		$message = $Message->message;
	}
}

retJSON($output, $message);
?>