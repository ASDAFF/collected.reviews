<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

define("STOP_STATISTICS", true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
IncludeModuleLangFile(__FILE__);

$MODULE_ID = 'collected.reviews';

global $USER, $APPLICATION;

$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);

$output = array(
	'ELEMENT_ID' => intval($_REQUEST['ID']),
	'ACTION' 	=> trim($_REQUEST['ACTION']),
	'COUNT' => 0,
	'ERRORS' => array(),
	'ERROR' => false
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

		echo CUtil::PhpToJSObject($out);
		//echo json_encode($out);
		die();
	}
}

CUtil::JSPostUnescape();
	
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

//review.vote.useful

$ACTION = explode(".", $output['ACTION']);

foreach (GetModuleEvents("collected.reviews", "OnBeforeAction", true) as $arEvent)
	ExecuteModuleEventEx($arEvent, array(&$output));
	
$obect_action = isset($ACTION[2]) ? $ACTION[2] : false;

switch($ACTION[0].".".$ACTION[1])
{
	case "review.vote":
		
		$res = COLLECTEDReviewsMessages::GetByID($output['ELEMENT_ID']);
		$ar = $res->Fetch();
		
		$BX_COLLECTEDRVW = $APPLICATION->get_cookie("BX_COLLECTEDRVW");
		$arReviewsID = unserialize($BX_COLLECTEDRVW);
		if(!is_array($arReviewsID))
			$arReviewsID = array();
		/*
		if(isset($_COOKIE["BX_COLLECTEDRVW"]))
			$arReviewsID = unserialize($_COOKIE["BX_COLLECTEDRVW"]);
		else
			$arReviewsID = array();
		*/
		
		if(in_array($ar['ID'], $arReviewsID))
			$arResult['ERRORS'][] = GetMessage("COLLECTED_REVIEWS_USER_VOTED");
	
		if($obect_action == 'useful')
			$output['COUNT'] = $ar['HELPFUL'];
		elseif($obect_action == 'reject')
			$output['COUNT'] = $ar['USELESS'];
		
		
		if($ar["ID"] > 0 && $obect_action !== false && count($arResult['ERRORS']) == 0)
		{
			$arUpdate = array();
			
			switch ($obect_action)
			{
				case 'useful':
				case 'useless':
			
			
					if($obect_action == 'useful')
						$arUpdate['HELPFUL'] = $ar['HELPFUL'] + 1;
					elseif($obect_action == 'reject')
						$arUpdate['USELESS'] = $ar['USELESS'] + 1;
						
					
					$CMessages = new COLLECTEDReviewsMessages();
					$CMessages->Update($output['ELEMENT_ID'], $arUpdate);
			
					$arReviewsID[] = $output['ELEMENT_ID'];
			
					$APPLICATION->set_cookie("BX_COLLECTEDRVW", serialize($arReviewsID), time()+60*60*24*30*12*5);
			
					//setcookie("BX_COLLECTEDRVW", serialize($arReviewsID));
					$output['COUNT']++;
					break;
			}
		}
		elseif(!$obect_action)
			$arResult['ERRORS'][] = GetMessage("COLLECTED_REVIEWS_COMAND_NOT_FOUND");
			
		
		// -- review.vote
		break; 
		
		
	case "review.admin":

		//для администратора
		
		
		if($POST_RIGHT < "W")
			$arResult['ERRORS'][] = GetMessage("COLLECTED_REVIEWS_NEED_ADMIN_RIGHT");
	
		
		if($obect_action && !count($arResult['ERRORS']))
		{
			$CMessages = new COLLECTEDReviewsMessages();
				
			switch ($obect_action)
			{
				case 'remove':
					$res = $CMessages->Delete($output['ELEMENT_ID']);	
					
					if($res === false)
						$arResult['ERRORS'][] = $CMessages->last_error;
					
					break;
				
				case 'reject':
					$res = $CMessages->reviewReject($output['ELEMENT_ID']);
					
					if($res === false)
						$arResult['ERRORS'][] = $CMessages->last_error;
					
					break;
			}
		}
		elseif(!$obect_action)
			$arResult['ERRORS'][] = GetMessage("COLLECTED_REVIEWS_COMAND_NOT_FOUND");
		

		break;
	
	default:
		break;
}	
	


	
	
	
foreach (GetModuleEvents("collected.reviews", "OnAfterAction", true) as $arEvent)
	ExecuteModuleEventEx($arEvent, array(&$output));

retJSON($output);
die();
?>