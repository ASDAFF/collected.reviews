<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

define("STOP_STATISTICS", true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
IncludeModuleLangFile(__FILE__);

$MODULE_ID = 'collected.reviews';

$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);

$output = array(
	'ELEMENT_ID' => intval($_REQUEST['ELEMENT_ID']),
	//'REVIEW_ID' => intval($_REQUEST['REVIEW_ID']),
	'COUNT' => intval($_REQUEST['COUNT']),
	'COUNT_LOAD'  => intval($_REQUEST['COUNT_LOAD']),
	'COUNT_TOTAL' => intval($_REQUEST['COUNT_TOTAL']),
	'LOADED_ALL' => false,
	'ERRORS' => array(),
	'ERROR' => false,
	'DATA' => array()
	);

function retJSON($out, $check_error = false)
{
	if(
		(count($out['ERRORS']) && $check_error) 
		|| 
		!$check_error
		)
	{
		if(count($out['ERRORS']) > 0)
			$out['ERROR'] = true;

		//echo CUtil::PhpToJSObject($out);
		echo json_encode($out);
		die();
	}
}

CUtil::JSPostUnescape();

//if($output['COUNT_LOAD'] == 0)
//	$output['COUNT_LOAD'] = 3;
	
if($output['ELEMENT_ID'] <= 0)
{
	$output['ERRORS'][] = GetMessage("COLLECTED_REVIEWS_ELEMENT_ID_N");
	retJSON($output, true);
}

$rsModule = CModule::IncludeModuleEx($MODULE_ID);
if($rsModule == MODULE_DEMO_EXPIRED)
{
	$output['ERRORS'][] = GetMessage("COLLECTED_REVIEWS_MODULE_DEMO_EXPIRED");
	retJSON($output, true);
}
elseif($rsModule == MODULE_NOT_FOUND)
{
	$output['ERRORS'][] = GetMessage("COLLECTED_REVIEWS_MODULE_NOT_INSTALLED");
	retJSON($output, true);
}

$ExpUsingList = COLLECTEDReviewsMessages::GetUsePeriodList();

$arSort = array('DATE_CREATE' => 'DESC');
$arFilter = array(
	'ELEMENT_ID' => $output['ELEMENT_ID'],
	'STATUS' => 'A',
	'SITE_ID' => SITE_ID,
	);
	
$CMessages = new COLLECTEDReviewsMessages();

if($output['COUNT_TOTAL'] == 0)
{
	$rsMessages = $CMessages->GetList($arSort, $arFilter);
	$output['COUNT_TOTAL'] = $rsMessages->SelectedRowsCount();
}


$BX_COLLECTEDRVW = $APPLICATION->get_cookie("BX_COLLECTEDRVW");
$arReviewsID = unserialize($BX_COLLECTEDRVW);
if(!is_array($arReviewsID))
	$arReviewsID = array();

$limit = array($output['COUNT']);

if($output['COUNT_LOAD'])
	$limit[] = $output['COUNT_LOAD'];
	
	
$rsMessages = $CMessages->GetList($arSort, $arFilter, $limit);
$output['COUNT_LOAD'] = $rsMessages->SelectedRowsCount();

while($arMessages = $rsMessages->GetNext())
{
	if(strlen($arMessages["DATE_CREATE"])>0)
		$arMessages["DATE_CREATE"] = CDatabase::FormatDate($arMessages["DATE_CREATE"], CSite::GetDateFormat("FULL"), CSite::GetDateFormat("SHORT"));
			
	$arMessages["WIDTH"] = round(($arMessages['RATING'] / 5 * 100));
	$arMessages["EXP_USING"] = $ExpUsingList[$arMessages["EXP_USING"]];
	
	$arMessages["DIGNITY"] = TxtToHTML($arMessages["DIGNITY"]);
	$arMessages["LIMITATIONS"] = TxtToHTML($arMessages["LIMITATIONS"]);
	$arMessages["COMMENTS"] = TxtToHTML($arMessages["COMMENTS"]);
	
	$arMessages["VOTED"] = in_array($arMessages['ID'], $arReviewsID);
	
	$arMessages["RIGHT"] = $POST_RIGHT;
	
	$output['DATA'][] = $arMessages;
}

//результат - загружены все?
$output['LOADED_ALL'] = ($output['COUNT_TOTAL'] - $output['COUNT'] - $output['COUNT_LOAD']) > 0 ? false : true ;

foreach (GetModuleEvents("collected.reviews", "OnReviewsLoad", true) as $arEvent)
	ExecuteModuleEventEx($arEvent, array(&$output['DATA']));

retJSON($output);
die();
?>