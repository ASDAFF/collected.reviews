<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION, $USER;

if(!CModule::IncludeModule('collected.reviews'))
{
	ShowError(GetMessage("COLLECTED_REVIEWS_MODULE_NOT_INSTALLED"));
	return;
}

$arParams['MESS_REMOVE_OK'] = trim($arParams['MESS_REMOVE_OK']);
$arParams['MESS_SEND_INFO'] = trim($arParams['MESS_SEND_INFO']);
$arParams['MESS_CONFIRM'] = trim($arParams['MESS_CONFIRM']);

$arResult["ERRORS"] = array();

if (check_bitrix_sessid() && $_REQUEST["review_get_mail"])
{
	//send email
	$EMAIL = trim($_REQUEST['SUBSCRIBE_EMAIL']);
	
	$actUrl = COption::GetOptionString('collected.reviews', 'subscribe_edit_page', '', SITE_ID);
	
	if(!check_email($EMAIL))
		$arResult["ERRORS"][] = GetMessage("REVIEW_UNSUBSCR_EMAIL_NC");
	
	if(count($arResult["ERRORS"]) == 0)
	{	
		$CSubscribe = new COLLECTEDReviewsSubscribe();
		$rsSubscribe = $CSubscribe->GetList(array(),array('EMAIL' => $EMAIL));

		if(intval($rsSubscribe->SelectedRowsCount()) == 0)
			$arResult["ERRORS"][] = GetMessage("REVIEW_SUBSCR_NF");
		
		$subscr_info = '';
		
		while($arSubscr = $rsSubscribe->GetNext())
		{
		
			$arEventFields = array(
				'DATE_CREATE' => $arSubscr['DATE_CREATE'],
				'EMAIL' => $arSubscr['EMAIL'],
				);

			if(CModule::IncludeModule('iblock'))
			{
				$arFilterElem = Array(
					'ID' => $arSubscr['ELEMENT_ID'],
					);

				$arSelectElem = array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL');

				$rsProduct = CIBlockElement::GetList(array(), $arFilterElem, false, array(), $arSelectElem);
				if($arProduct = $rsProduct->GetNext())
				{
					$arEventFields['ELEMENT_ID']   = $arProduct['ID'];
					$arEventFields['ELEMENT_NAME'] = $arProduct['NAME'];
					$arEventFields['ELEMENT_PAGE'] = $arProduct['DETAIL_PAGE_URL'];
				}
			}
			$subscr_info .= $arEventFields['ELEMENT_NAME'].chr(13);
			$subscr_info .= 'http://'.$_SERVER['HTTP_HOST'].''.$actUrl.'?action=del&ID='.$arSubscr['ID'].'&CODE='.$arSubscr['CODE'].chr(13).chr(13);
			
			
			$arEventFields['SUBSCRIBE_INFO'] = $subscr_info;
			//$arEventFields['SUBSCRIBE_REMOVE_URL'] = $actUrl.'?action=del'; //&ID='.$arSubscr['ID'];
			
			CEvent::Send("COLLECTED_REVIEWS_SUBSCRIBE_EDIT", $arSubscr['SITE_ID'], $arEventFields);
		}
		
		if(count($arResult['ERRORS'])==0)
		{
			$rUrl = $APPLICATION->GetCurPageParam("subscribe=sending", array("subscribe", "edit" ), $get_index_page=false);
			LocalRedirect($rUrl);
			exit();
		}
	}
}

if (!empty($_REQUEST["action"])) //check_bitrix_sessid() && 
{
	$CSubscribe = new COLLECTEDReviewsSubscribe();
	
	$arFields = array(
		'ID'=> $_REQUEST['ID'],
		'SITE_ID'	=> SITE_ID
		);

	$ret = $CSubscribe->GetList(array(), $arFields);
	if($arSubscr = $ret->GetNext())
	{
		if($arSubscr['CODE'] != $_REQUEST["CODE"])
			$arResult['ERRORS'][] = GetMessage("REVIEW_UNSUBSCR_CODE_NC");

		if(count($arResult['ERRORS'])==0)
		{
			
			switch($_REQUEST["action"])
			{	
				case 'del':
					$ret = $CSubscribe->Delete($arSubscr['ID']);
				break;
				
				case 'confirm':
					$ret = $CSubscribe->Confirm($arSubscr['ID'], 'Y');
					if($ret === false)
						$arResult['ERRORS'][] = 'error';
				break;
				
				default:
					$arResult['ERRORS'][] = 'action not valid';
				break;
			}
		}
	}
	else 
	{
		$arResult['ERRORS'][] = GetMessage("REVIEW_SUBSCR_NOTFOUND");
	}

	if(count($arResult['ERRORS'])==0)
	{
		$rUrl = $APPLICATION->GetCurPageParam("subscribe=".$_REQUEST["action"], array("subscribe", "action" ), $get_index_page=false);
		LocalRedirect($rUrl);
		exit();
	}
}


 
if($_REQUEST['subscribe'] == 'del')
{
	$arResult['MESSAGE'] = strlen($arParams['MESS_REMOVE_OK']) > 0 ? $arParams['MESS_REMOVE_OK'] : GetMessage("REVIEW_SUBSCRIBE_DELETING");
}
elseif($_REQUEST['subscribe'] == 'sending')	
{
	$arResult['MESSAGE'] = strlen($arParams['MESS_SEND_INFO']) > 0 ? $arParams['MESS_SEND_INFO'] : GetMessage("REVIEW_SUBSCR_INFO_SENDING");
}
elseif($_REQUEST['subscribe'] == 'confirm')	
{
	$arResult['MESSAGE'] = strlen($arParams['MESS_CONFIRM']) > 0 ? $arParams['MESS_CONFIRM'] : GetMessage("REVIEW_SUBSCR_CONFIRM");
}

	
$this->IncludeComponentTemplate();
?>