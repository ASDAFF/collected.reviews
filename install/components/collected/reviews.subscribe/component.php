<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION, $USER;

$module_id = 'collected.reviews';

if(!CModule::IncludeModule($module_id))
{
	ShowError(GetMessage("COLLECTED_REVIEWS_MODULE_NOT_INSTALLED"));
	return;
}

$arParams['ELEMENT_ID'] = intval($arParams['ELEMENT_ID']);
$arParams["USE_CAPTCHA"] = $arParams["USE_CAPTCHA"] == 'Y' ? 'Y' : 'N' ;
$arParams["NEED_AUTH"] = $arParams["NEED_AUTH"] == 'N' ? 'N' : 'Y' ;

$arParams['MESS_OK'] = HTMLToTxt($arParams['MESS_OK']);
$arParams['MESS_OK'] = trim($arParams['MESS_OK']);

$arParams["NEED_CONFIRM"] = COption::GetOptionString($module_id, 'subscribe_confirm', '', SITE_ID);

$err = array();

if (check_bitrix_sessid() && !empty($_REQUEST["review_subscribe"]))
{
	//check fields
	if(intval($arParams['ELEMENT_ID']) == 0)
		$arResult["ERRORS"][] = GetMessage("REVIEW_ADD_SUBSCR_ELEMENT_N");

	if(strlen($_REQUEST['SUBSCRIBE_EMAIL']) == 0)
		$arResult["ERRORS"][] = GetMessage("REVIEW_ADD_SUBSCR_EMAIL_N");
	elseif(!check_email($_REQUEST['SUBSCRIBE_EMAIL']))
		$arResult["ERRORS"][] = GetMessage("REVIEW_ADD_SUBSCR_EMAIL_NC");
	
	//search subscribe
	if (count($arResult["ERRORS"]) == 0)
	{	
		$arResult["EMAIL"] = $_REQUEST['SUBSCRIBE_EMAIL'];
		
		$CSubscribe = new COLLECTEDReviewsSubscribe();
		$rsData = $CSubscribe->GetList(
			array(), 
			array(
				"ELEMENT_ID" => $arParams['ELEMENT_ID'],
				"EMAIL" => $arResult["EMAIL"],
				)
			);

		if(intval($rsData->SelectedRowsCount()) > 0)
			$arResult["ERRORS"][] = GetMessage("REVIEW_ADD_SUBSCR_INBASE");
	}
	
	//search element
	if(CModule::IncludeModule('iblock'))
	$arElementFilter = Array(
		'ID' => $arParams['ELEMENT_ID'],
		);

	$arSelect = array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL');

	$rsElement = CIBlockElement::GetList(array(), $arElementFilter, false, array(), $arSelect);
	if (intval($rsElement->SelectedRowsCount()) == 0)
		$err[] = GetMessage("COLLECTED_REIEWS_ADD_ELEMENT_ID_NF");
	else
	{
		$arProduct = $rsElement->GetNext();
	}
	

	if (count($arResult["ERRORS"]) == 0)
	{
		//ошибок нет
		$CSubscribe = new COLLECTEDReviewsSubscribe();
		
		//add new
		$arFields = array(
			'ELEMENT_ID' => $arParams['ELEMENT_ID'],
			'EMAIL' 	 => $arResult["EMAIL"],
			'CONFIRMED'  => $arParams["NEED_CONFIRM"] == 'Y' ? 'N' : 'Y',
			'SITE_ID'	 => SITE_ID,
			'CODE'		 => COLLECTEDReviews::generate_password(10, false),
			);

		$ret = $CSubscribe->Add($arFields);

		if($ret === false)
			$arResult['ERRORS'][] = $CSubscribe->last_error;
			
		if(count($arResult['ERRORS'])==0)
		{
			//need confirm
			if($arParams["NEED_CONFIRM"] == 'Y')
			{
				if($arProduct['ID'] > 0)
				{
					//element fields
					$arEventFields = array(
						'ELEMENT_ID' => $arProduct['ID'],
						'ELEMENT_NAME' => $arProduct['NAME'],
						'ELEMENT_PAGE' => $arProduct['DETAIL_PAGE_URL'],
						);
				}

				//email
				$arEventFields['EMAIL'] = $arResult["EMAIL"];

				//send mail message for confirm
				$actUrl = COption::GetOptionString($module_id, 'subscribe_edit_page', '', SITE_ID);
				$arEventFields['SUBSCRIBE_CONFIRM_PAGE'] = $actUrl.'?action=confirm&ID='.$ret.'&CODE='.$arFields['CODE'];

				//send
				CEvent::Send("COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM", SITE_ID, $arEventFields);
			}
			
			$rUrl = $APPLICATION->GetCurPageParam("review_subscribe=ok", array("review", "edit" ), $get_index_page=false);
			LocalRedirect($rUrl.'#review_subscribe');
			exit();
		}
	}
}

if($_REQUEST['review_subscribe'] == 'ok')
{
	$arResult['MESSAGE'] = strlen($arParams['MESS_OK']) > 0 ? $arParams['MESS_OK'] : GetMessage("REVIEW_SUBSCRIBE_ADDING");
}
	
$this->IncludeComponentTemplate();
?>