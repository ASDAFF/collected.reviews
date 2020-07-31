<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

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

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams['RATING_SHORT_TEMPLATE'] = $arParams['RATING_SHORT_TEMPLATE'] == 'Y' ? 'Y' : 'N';

$arParams['ELEMENT_ID'] = intval($arParams['ELEMENT_ID']);

if($arParams['ELEMENT_ID'] <= 0)
{
	ShowError(GetMessage("COLLECTED_REVIEWS_ELEMENT_NF"));
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

if($this->StartResultCache(false, array($USER->GetGroups(), rand(1,11111))))
{
	$res = COLLECTEDReviewsMessages::GetRating($arParams['ELEMENT_ID']);
	$arResult["RATING"] = $res['RATING'];
	$arResult["USER_COUNT"] = $res['USER_COUNT'];
	$arResult["WIDTH"] = round(($res['RATING'] / 5 * 100));

	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/collected.reviews_stars.css");
	
	$this->IncludeComponentTemplate();
}
?>