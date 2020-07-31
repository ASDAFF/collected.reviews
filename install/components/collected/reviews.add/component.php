<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION, $USER;

$module_id = 'collected.reviews';

$arParams['ELEMENT_ID'] = intval($arParams['ELEMENT_ID']);
$arParams["USE_GEO"] = $arParams["USE_GEO"] == 'N' ? 'N' : 'Y' ;

$arParams["USE_CAPTCHA"] = $arParams["USE_CAPTCHA"] == 'N' ? 'N' : 'Y' ;
$arParams["REVIEW_FULL"] = $arParams["REVIEW_FULL"] == 'N' ? 'N' : 'Y' ;
$arParams["NEED_AUTH"] = $arParams["NEED_AUTH"] == 'Y' ? 'Y' : 'N' ;

$arParams["ONECOMMENT_REQUIRED"] = $arParams["ONECOMMENT_REQUIRED"] == 'N' ? 'N' : 'Y' ;



$arParams['MESS_OK'] = HTMLToTxt($arParams['MESS_OK']);
$arParams['MESS_OK'] = trim($arParams['MESS_OK']);

if($arParams["NEED_AUTH"] == 'Y' && !$USER->IsAuthorized())
	return;

if($arParams['ELEMENT_ID'] <= 0)
{
	ShowError(GetMessage("ELEMENT_ID_NOT_SET"));
	return;
}
elseif(CModule::IncludeModule("iblock"))
{
	$res = CIBlockElement::GetByID($arParams['ELEMENT_ID']);
	if(!($ar_res = $res->GetNext()))
	{
		ShowError(GetMessage("ELEMENT_NOT_FOUND", array('#ID#' => $arParams['ELEMENT_ID'])));
		return;
	}
}
else
{
	return;
}




if($USER->IsAuthorized())
	$arParams["USE_CAPTCHA"] = 'N';

$rsModule = CModule::IncludeModuleEx($module_id);

if($rsModule == MODULE_DEMO_EXPIRED)
{
	echo GetMessage("COLLECTED_REVIEWS_MODULE_DEMO_EXPIRED");
	return;
}
elseif($rsModule == MODULE_NOT_FOUND)
{
	ShowError(GetMessage("COLLECTED_REVIEWS_MODULE_NOT_INSTALLED"));
	return;
}

CUtil::JSPostUnescape();

$arResult['RATING_LIST'] = COLLECTEDReviewsMessages::GetRatingList();
$arResult['EXP_USING_LIST'] = COLLECTEDReviewsMessages::GetUsePeriodList();

$arResult['FIELDS'] = array(
	'USER_NAME' => array(
			'NAME' => GetMessage("USER_NAME_DESC"),
			'TYPE' => 'S',
			'VALUE' => '',
			'REQUIRED' => 'Y',
		),
	'USER_EMAIL' => array(
			'NAME' => GetMessage("USER_EMAIL_DESC"),
			'TYPE' => 'S',
			'VALUE' => '',
			'REQUIRED' => 'Y',
		),
		
	'CITY' => array(
			'NAME' => GetMessage("USER_CITY_DESC"),
			'TYPE' => 'S',
			'VALUE' => ''
		),
		
	'DIGNITY' => array(
			'NAME' => GetMessage("DIGNITY_DESC"),
			'TYPE' => 'T',
			'VALUE' => '',
			'IS_COMMENT' => 'Y',
		),
	'LIMITATIONS' => array(
			'NAME' => GetMessage("LIMITATIONS_DESC"),
			'TYPE' => 'T', 
			'VALUE' => '',
			'IS_COMMENT' => 'Y',
		),
	'COMMENTS' => array(
			'NAME' => GetMessage("COMMENTS_DESC"),
			'TYPE' => 'T',
			'VALUE' => '',
			'IS_COMMENT' => 'Y',
		),
	'EXP_USING' => array(
			'NAME' => GetMessage("EXP_USING_DESC"),
			'TYPE' => 'L',
			'VALUES' => $arResult['EXP_USING_LIST'],
		),
	'RATING' => array(
			'NAME' => GetMessage("RATING_DESC"),
			'TYPE' => 'L',
			'VALUES' => $arResult['RATING_LIST'],
			'REQUIRED' => 'Y',
		),
	
	
	);

if(!is_array($arParams['FIELDS']))
	$arParams['FIELDS'] = array();


//default setting
if(count($arParams['FIELDS']) == 0)
{
	foreach($arResult['FIELDS'] as $i => $field)
	{
		$arParams['FIELDS'][] = $i;
	}
}

if($arParams["USE_GEO"] == 'Y' && in_array('CITY', $arParams['FIELDS']) && (empty($_REQUEST["review_submit"]) && empty($_REQUEST["review_apply"])))
{
	$options = array();

	if(defined('BX_UTF') && BX_UTF === true)
		$options['charset'] = 'utf-8';

	try {
		$Geo = new COLLECTEDReviewsGeo($options);
		$arResult['FIELDS']['CITY']['VALUE'] = $Geo->get_value('city', true);	
		}
	catch (Exception $e){
		echo "Error: ".$e->getMessage();
		}
}


if(!is_array($arParams['FIELDS_REQUIRED']))
	$arParams['FIELDS_REQUIRED'] = array();

if($arParams['REVIEW_FULL'] != 'Y')
{
	unset($arResult['FIELDS']['DIGNITY']);
	unset($arResult['FIELDS']['LIMITATIONS']);
	$arResult['FIELDS']['COMMENTS']['NAME'] = GetMessage("COMMENTS2_DESC");
}

if($USER->IsAuthorized())
{
	unset($arResult['FIELDS']['USER_EMAIL']);
	
	//$arResult['FIELDS']['USER_NAME']['VALUE'] = $arResult['USER_NAME'] = strlen($USER->GetFullName()) > 0 ? $USER->GetFullName() : $USER->GetLogin() ;
	$arResult['FIELDS']['USER_NAME']['VALUE'] = $arResult['USER_NAME'] = strlen($USER->GetFirstName()) > 0 ? $USER->GetFirstName() : $USER->GetLogin() ;
	
	$arResult['FIELDS']['USER_NAME']['ATTR'] = 'disabled';
}

$tmp = array();

foreach($arParams['FIELDS'] as $id)
{
	if(array_key_exists($id, $arResult["FIELDS"]))
		$tmp[$id] = $arResult['FIELDS'][$id];
}
	
$arResult['FIELDS'] = $tmp;

//required fields
foreach($arParams['FIELDS_REQUIRED'] as $id)
{
	if(array_key_exists($id, $arResult["FIELDS"]))
		$arResult['FIELDS'][$id]['REQUIRED'] = 'Y';
}



$bAllowAccess = true;
$arResult["ERRORS"] = array();
$arResult["ERRORS_FIELDS"] = array();

$IS_COMMENT = 0;

if ($bAllowAccess && check_bitrix_sessid() && (!empty($_REQUEST["review_submit"]) || !empty($_REQUEST["review_apply"])))
{
	
		 
	$block_ip = COption::GetOptionString($module_id, 'block_ip', '', SITE_ID);
	$block_ip_maxcount = COption::GetOptionInt($module_id, 'block_ip_maxcount', '', SITE_ID);
	$block_ip_maxtime = COption::GetOptionInt($module_id, 'block_ip_maxtime', 30, SITE_ID);
	$block_ip_blocktime = COption::GetOptionInt($module_id, 'block_ip_blocktime', 0, SITE_ID);
	
	$block_ip_message = COption::GetOptionString($module_id, 'block_ip_message', '', SITE_ID);
	$block_ip_message = str_replace('#TIME#', $block_ip_blocktime, $block_ip_message);
	 
	if($block_ip == "Y")
	{
		$arrAdd = array(
			"MI"	=> -$block_ip_maxtime,
			);
		$stmp = AddToTimeStamp($arrAdd, time());
		
		$arBlockFilter = array(
			'>DATE_CREATE_REAL' 	=> date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), $stmp),
			'IP_ADDRESS'		=> $_SERVER['REMOTE_ADDR'],
			'SITE_ID'			=> SITE_ID,
			);
		
		$Messages = new COLLECTEDReviewsMessages();
		
		$res = $Messages->GetList(array(), $arBlockFilter);
		$count = $res->SelectedRowsCount();
		
		//$count = 50; // !!! check !!!
		
		$check = COLLECTEDReviewsStopList::Check($_SERVER['REMOTE_ADDR'], 0, SITE_ID);
		
		if(!$check)
		{
			$arResult["ERRORS"][] = $block_ip_message."";   
		}
		elseif($count >= $block_ip_maxcount)
		{
			//add to stop list
			$stoplist = new COLLECTEDReviewsStopList();
			$resAdd = $stoplist->Add(array(
				'SITE_ID'	=> SITE_ID,
				));

			if(!$resAdd)
				$arResult["ERRORS"] = $stoplist->arError;
				
			//create message
			$arResult["ERRORS"][] = $block_ip_message."" ;
		}
	
	}
	
	if(count($arResult["ERRORS"]) ==0 )
	{
			
		//check fields	
		foreach($arResult['FIELDS'] as $id => $arField )
		{
			if(!($USER->IsAuthorized() && ($id == 'USER_NAME' || $id == 'USER_EMAIL')))
				$arResult['FIELDS'][$id]['VALUE'] = htmlspecialcharsEx(trim($_REQUEST[$id]));
				
			if( empty($_REQUEST[$id]) && $arField['REQUIRED'] == 'Y')
				$arResult["ERRORS"][$id] = GetMessage("REVIEW_ADD_FIELD_N").' "'.$arField['NAME'].'"';
			
			if(!empty($_REQUEST[$id]) && $arField['IS_COMMENT'] == 'Y')
				$IS_COMMENT++;
			
			
			if(!empty($_REQUEST[$id]) && count($arField['VALUES']) > 0 && !array_key_exists($_REQUEST[$id], $arField['VALUES']) )
				$arResult["ERRORS"][$id] = GetMessage("REVIEW_ADD_FIELD_NC").' "'.$arField['NAME'].'"';

			if(
				$id == 'USER_EMAIL' && 
				$arField['REQUIRED'] == 'Y' &&  
				strlen($arResult['FIELDS'][$id]['VALUE']) > 0 && 
				!check_email($arResult['FIELDS'][$id]['VALUE'])
			)
				$arResult["ERRORS"][$id] = GetMessage("REVIEW_ADD_FIELD_NC").' "'.$arField['NAME'].'"';
		}
	

		if($IS_COMMENT == 0 && $arParams["ONECOMMENT_REQUIRED"] == 'Y')
			$arResult["ERRORS"]['ONECOMMENT_REQUIRED'] = GetMessage("REVIEW_ONECOMMENT_REQUIRED");


        //проверм капчу
		if($arParams["USE_CAPTCHA"] == 'Y' && $_REQUEST['captcha_word']=='')
			$arResult["ERRORS"]["captcha_word"] = GetMessage("REVIEW_ADD_CATCHA_N");
		elseif($arParams["USE_CAPTCHA"] == 'Y' && !$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]))
			$arResult["ERRORS"]["captcha_word"] = GetMessage("REVIEW_ADD_CATCHA_NC");
		
	}
	
	if (count($arResult["ERRORS"]) == 0)
	{
        //ошибок нет
		$CReview = new COLLECTEDReviewsMessages();
		
		if ($arParams["ID"] > 0)
		{
			//edit
		}
		else
		{
			//add new
			$arFields = array(
				'ELEMENT_ID'	=> $arParams['ELEMENT_ID'],
				'SITE_ID'		=> SITE_ID,
				'USER_ID'		=> $USER->IsAuthorized() ? $USER->GetID() : 0 ,
				);
			
			foreach($arResult['FIELDS'] as $id => $arField )
				$arFields[$id] = $arField['VALUE'];
			
			$ret = $CReview->Add($arFields);
			
			if($ret == false)
				$arResult['ERRORS'] = $CReview->arError;
			elseif(!array_key_exists('ajax', $_REQUEST) || !$_REQUEST['ajax']=='Y')
			{
				LocalRedirect($rUrl);
				exit();
			}
		}
	}
}

if($_REQUEST['review'] == 'ok' || ($ret && array_key_exists('ajax', $_REQUEST) && $_REQUEST['ajax']=='Y'))
{
	$arResult['MESSAGE'] = strlen($arParams['MESS_OK']) > 0 ? $arParams['MESS_OK'] : GetMessage("REVIEW_ADDING");
}

if($arParams["USE_CAPTCHA"] == 'Y')
{
	$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
	$APPLICATION->AddHeadScript('/bitrix/js/collected.reviews/reload_capcha.js');
}

$arResult["FORM_ID"] = rand(10000, 999999999);


foreach($arResult['FIELDS'] as $ID => $arField)
{
	if(array_key_exists($ID, $arResult["ERRORS"]))
	{
		$arResult["ERRORS_FIELDS"][$ID] = $arResult["ERRORS"][$ID];
		unset($arResult["ERRORS"][$ID]);
	}
}

if(array_key_exists("captcha_word", $arResult["ERRORS"]))
{
	$arResult["ERRORS_FIELDS"]["captcha_word"] = $arResult["ERRORS"]["captcha_word"];
		unset($arResult["ERRORS"]["captcha_word"]);
}
	
if(array_key_exists('ajax', $_REQUEST) && $_REQUEST['ajax']=='Y')
	$APPLICATION->RestartBuffer();

$APPLICATION->AddHeadScript('/bitrix/js/collected.reviews/rating.js');
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/collected.reviews_stars.css");

if (!$bAllowAccess && !$bHideAuth)
	$APPLICATION->AuthForm("");
else
	$this->IncludeComponentTemplate();

if(array_key_exists('ajax', $_REQUEST) && $_REQUEST['ajax']=='Y')
	die();
?>