<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION, $USER, $DB;

$module_id = 'collected.reviews';

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

$POST_RIGHT = $APPLICATION->GetGroupRight('collected.reviews');
$arResult["EDIT_ACCESS"] = $POST_RIGHT >= "W" ? true : false;

//$arResult["IBLOCK_ID"] = COption::GetOptionInt($module_id, 'iblock', '', SITE_ID);

$arResult["ITEMS"] = array();

$arParams['ELEMENT_ID'] = intval($arParams['ELEMENT_ID']);

$arParams['REVIEWS_COUNT'] = intval($arParams['REVIEWS_COUNT']);
if($arParams['REVIEWS_COUNT'] < 1)
	$arParams['REVIEWS_COUNT'] = 5;
	
$arParams['LOADED_NUMBER'] = intval($arParams['LOADED_NUMBER']);
if($arParams['LOADED_NUMBER'] < 1)
	$arParams['LOADED_NUMBER'] = 3;

$arParams["DISPLAY_TOP_PAGER"] = $arParams["DISPLAY_TOP_PAGER"]=="Y";
$arParams["DISPLAY_BOTTOM_PAGER"] = $arParams["DISPLAY_BOTTOM_PAGER"]!="N";
$arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);
$arParams["PAGER_SHOW_ALWAYS"] = $arParams["PAGER_SHOW_ALWAYS"]!="N";
$arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"]);
$arParams["PAGER_DESC_NUMBERING"] = $arParams["PAGER_DESC_NUMBERING"]=="Y";
$arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] = intval($arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]);
$arParams["PAGER_SHOW_ALL"] = $arParams["PAGER_SHOW_ALL"]!=="Y";


if(strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
{
    $arrFilter = array();
}
else
{
    $arrFilter = $GLOBALS[$arParams["FILTER_NAME"]];
    if(!is_array($arrFilter))
        $arrFilter = array();
}

$arParams["CACHE_FILTER"] = $arParams["CACHE_FILTER"]=="Y";
if(!$arParams["CACHE_FILTER"] && count($arrFilter)>0)
    $arParams["CACHE_TIME"] = 0;

if($arParams["DISPLAY_TOP_PAGER"] != 'Y' && $arParams["DISPLAY_BOTTOM_PAGER"] != 'Y')
	$APPLICATION->AddHeadScript('/bitrix/js/collected.reviews/reviews_loader.js');
	
//reviews_manager
$APPLICATION->AddHeadScript('/bitrix/js/collected.reviews/reviews_manager.js');


if($arParams["DISPLAY_TOP_PAGER"] || $arParams["DISPLAY_BOTTOM_PAGER"])
{
	$arNavParams = array(
		"nPageSize" => $arParams["REVIEWS_COUNT"],
		"bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"],
		"bShowAll" => $arParams["PAGER_SHOW_ALL"],
	);
	$arNavigation = CDBResult::GetNavParams($arNavParams);
	if($arNavigation["PAGEN"]==0 && $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]>0)
		$arParams["CACHE_TIME"] = $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"];
}
else
{
	$arNavParams = array(
		"nTopCount" => $arParams["REVIEWS_COUNT"],
		"bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"],
	);
	$arNavigation = false;
}

if(CModule::IncludeModule("iblock"))
{
    if($arParams['ELEMENT_ID'] > 0)
    {
        $res = CIBlockElement::GetByID($arParams['ELEMENT_ID']);
        if(!($ar_res = $res->GetNext()))
        {
        	ShowError(GetMessage("ELEMENT_NOT_FOUND", array('#ID#' => $arParams['ELEMENT_ID'])));
        	return;
        }
    }
}
else
	return;


$BX_COLLECTEDRVW = $APPLICATION->get_cookie("BX_COLLECTEDRVW");
$arReviewsID = unserialize($BX_COLLECTEDRVW);
if(!is_array($arReviewsID))
	$arReviewsID = array();


if($this->StartResultCache(false, array($arParams['ELEMENT_ID'], $USER->GetGroups(), $arNavigation, $arrFilter)))
{
	$ExpUsingList = COLLECTEDReviewsMessages::GetUsePeriodList(); // ???? ?????????????
	
	$res = COLLECTEDReviewsMessages::GetRating($arParams['ELEMENT_ID']);
	$arResult["RATING"] = $res['RATING'];
	$arResult["USER_COUNT"] = $res['USER_COUNT'];
	$arResult["WIDTH"] = round(($res['RATING'] / 5 * 100));

	$arResult["TITLE"] = $arResult["RATING"] > 0 ? $arResult["RATING"] : $MESS["EMPTY_REVIEW"] ;

	$arSort = array('DATE_CREATE' => 'DESC');
	$arFilter = array(
		'ELEMENT_ID' => $arParams['ELEMENT_ID'],	//element of iblock
		'STATUS' => 'A',								                                                            //status active
		'SITE_ID' => SITE_ID,
		);

	if(intval($arParams['IBLOCKI_ID']) > 0)
		$arFilter['IBLOCKI_ID'] = intval($arParams['IBLOCKI_ID']);

    $Messages = new COLLECTEDReviewsMessages();
	$rsMessages = $Messages->GetList($arSort, array_merge($arFilter, $arrFilter));
	$rsMessages->NavStart($arParams['REVIEWS_COUNT']);
	
	
	$arResult["COUNT"] = $rsMessages->SelectedRowsCount();
	
	while($arMessages = $rsMessages->GetNext() )
	{
		if(strlen($arMessages["DATE_CREATE"])>0)
			$arMessages["DATE_CREATE"] = CDatabase::FormatDate($arMessages["DATE_CREATE"], CSite::GetDateFormat("FULL"), CSite::GetDateFormat("SHORT"));

		if(trim($arMessages['USER_NAME']) == '')
			$arMessages['USER_NAME'] = '';
			
		$arMessages["WIDTH"] = round(($arMessages['RATING'] / 5 * 100));
		
		$arMessages["DIGNITY"] = TxtToHTML($arMessages["DIGNITY"]);
		$arMessages["LIMITATIONS"] = TxtToHTML($arMessages["LIMITATIONS"]);
		$arMessages["COMMENTS"] = TxtToHTML($arMessages["COMMENTS"]);
		
		$arMessages["EXP_USING"] = $ExpUsingList[$arMessages["EXP_USING"]];
		
		$arMessages["URL_REMOVE"] = $APPLICATION->GetCurPageParam("action=remove&ID=".$arMessages['ID'], array("ID", "action"), $get_index_page=false);
		$arMessages["URL_DEACTIVATE"] = $APPLICATION->GetCurPageParam("action=reject&ID=".$arMessages['ID'], array("ID", "action"), $get_index_page=false);
		
		$arMessages["VOTED"] = in_array($arMessages['ID'], $arReviewsID);
		
		$arResult["ITEMS"][] = $arMessages;
	}
	
	$arResult["LIST_ID"] = rand(10000, 999999999);
	
	$arResult["NAV_STRING"] = $rsMessages->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
	$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
	
	$arResult["NAV_RESULT"] = $rsMessages;
	$this->SetResultCacheKeys(array(
		"ID",
		"LIST_ID",
		"NAV_CACHED_DATA",
	));
	
	//CJSCore::Init(array("jquery"));
	
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/collected.reviews_stars.css");
	
	$this->IncludeComponentTemplate();
}
?>