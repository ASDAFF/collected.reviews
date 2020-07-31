<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("iblock"))
	return false;

if(!CModule::IncludeModule("collected.reviews"))
	return false;
	
$StatusList = COLLECTEDReviewsMessages::GetStatusList();	// статусы


$arFields = array(
	'USER_NAME' => GetMessage("GD_COLLECTED_REVIEW_USER_NAME"),
	'USER_EMAIL' => GetMessage("GD_COLLECTED_REVIEW_USER_EMAIL"),
	'CITY' => GetMessage("GD_COLLECTED_REVIEW_CITY"),
	'DIGNITY' => GetMessage("GD_COLLECTED_REVIEW_DIGNITY"),
	'LIMITATIONS' => GetMessage("GD_COLLECTED_REVIEW_LIMITATIONS"),
	'COMMENTS' => GetMessage("GD_COLLECTED_REVIEW_COMMENTS"),
	'RATING' => GetMessage("GD_COLLECTED_REVIEW_RATING"),
	'EXP_USING' => GetMessage("GD_COLLECTED_REVIEW_EXP_USING"),
	);
	
$dbIBlock = CIBlock::GetList(
	array("SORT"=>"ASC", "NAME"=>"ASC"), 
	array(
		"CHECK_PERMISSIONS" => "Y", 
		"MIN_PERMISSION" => (IsModuleInstalled("workflow")?"U":"W")
	)
);
while($arIBlock = $dbIBlock->GetNext())
	$arIBlock_Types[$arIBlock["IBLOCK_TYPE_ID"]] = $arIBlock;

$arTypes = array("" => GetMessage("GD_IBEL_EMPTY"));
$rsTypes = CIBlockType::GetList(Array("SORT"=>"ASC"));
while($arType = $rsTypes->Fetch())
{
	if (is_array($arIBlock_Types) && array_key_exists($arType["ID"], $arIBlock_Types))
	{
		$arType = CIBlockType::GetByIDLang($arType["ID"], LANGUAGE_ID);
		$arTypes[$arType["ID"]] = "[".$arType["ID"]."] ".$arType["NAME"];
	}
}

$arIBlocks = array("" => GetMessage("GD_IBEL_EMPTY"));
if (
	is_array($arAllCurrentValues)
	&& array_key_exists("IBLOCK_TYPE", $arAllCurrentValues)
	&& array_key_exists("VALUE", $arAllCurrentValues["IBLOCK_TYPE"])
	&& strlen($arAllCurrentValues["IBLOCK_TYPE"]["VALUE"]) > 0
)
{
	$dbIBlock = CIBlock::GetList(
		array("SORT" => "ASC"), 
		array(
			"CHECK_PERMISSIONS" => "Y", 
			"MIN_PERMISSION" => (IsModuleInstalled("workflow")?"U":"W"), 
			"TYPE" => $arAllCurrentValues["IBLOCK_TYPE"]["VALUE"]
		)
	);
	while($arIBlock = $dbIBlock->GetNext())
		$arIBlocks[$arIBlock["ID"]] = "[".$arIBlock["ID"]."] ".$arIBlock["NAME"];
}

$arIBlockProperties = array();
if (
	is_array($arAllCurrentValues)
	&& array_key_exists("IBLOCK_ID", $arAllCurrentValues)
	&& array_key_exists("VALUE", $arAllCurrentValues["IBLOCK_ID"])
	&& intval($arAllCurrentValues["IBLOCK_ID"]["VALUE"]) > 0
	&& array_key_exists($arAllCurrentValues["IBLOCK_ID"]["VALUE"], $arIBlocks)
)
{

	$dbIBlockProperties = CIBlockProperty::GetList(
		array("SORT" => "ASC"),
		array(
			"IBLOCK_ID" => $arAllCurrentValues["IBLOCK_ID"]["VALUE"],
			"ACTIVE" => "Y"
		)
	);
	while($arIBlockProperty = $dbIBlockProperties->GetNext())
		$arIBlockProperties["PROPERTY_".$arIBlockProperty["CODE"]] = "[".$arIBlockProperty["CODE"]."] ".$arIBlockProperty["NAME"];
}

$arParameters = Array(
	"PARAMETERS"=> Array(),
	"USER_PARAMETERS"=> Array(
		"IBLOCK_TYPE" => Array(
			"NAME" => GetMessage("GD_IBEL_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"REFRESH" => "Y"
		)
	)
);

if (count($arIBlocks) > 0)
	$arParameters["USER_PARAMETERS"]["IBLOCK_ID"] = Array(
		"NAME" => GetMessage("GD_IBEL_IBLOCK_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arIBlocks,
		"MULTIPLE" => "N",
		"DEFAULT" => "",
		"REFRESH" => "Y"
	);
	

$arParameters["USER_PARAMETERS"]["ITEMS_COUNT"] = Array(
	"NAME" => GetMessage("GD_COLLECTED_REVIEW_COUNT"),
	"TYPE" => "STRING",
	"DEFAULT" => "10"
);

$arParameters["USER_PARAMETERS"]["STATUS"] = Array(
	"NAME" => GetMessage("GD_COLLECTED_REVIEW_STATUS"),
	"TYPE" => "LIST",
	"VALUES" => $StatusList,
	"MULTIPLE" => "Y",
	"DEFAULT" => "N",
	"REFRESH" => "Y"
);

$arParameters["USER_PARAMETERS"]["FIELDS"] = Array(
	"NAME" => GetMessage("GD_COLLECTED_REVIEW_FIELDS"),
	"TYPE" => "LIST",
	"VALUES" => $arFields,
	"MULTIPLE" => "Y",
	"DEFAULT" => array('USER_NAME', 'DIGNITY', 'LIMITATIONS', 'COMMENTS')
);

$arParameters["USER_PARAMETERS"]["TEXT_CUT"] = Array(
	"NAME" => GetMessage("GD_COLLECTED_REVIEW_CUT"),
	"TYPE" => "STRING",
	"DEFAULT" => "200"
	);
?>