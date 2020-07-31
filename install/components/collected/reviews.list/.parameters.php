<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;


$arIBlocks=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = $arRes["NAME"];

	
$arComponentParameters = array(
	"GROUPS" => array(

	),
	"PARAMETERS" => array(

		"ELEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_REVIEWS_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
			"REFRESH" => "Y",
		),

		"REVIEWS_COUNT" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_REVIEWS_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "5",
		),

		"LOADED_NUMBER" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_LOADED_NUMBER"),
			"TYPE" => "STRING",
			"DEFAULT" => "5",
		),

        "FILTER_NAME" => Array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("COLLECTED_REVIEWS_FILTER"),
            "TYPE" => "STRING",
            "DEFAULT" => "arrReviewsFilter",
        ),

		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
        "CACHE_FILTER" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => GetMessage("COLLECTED_REVIEWS_CACHE_FILTER"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
	)
);

CIBlockParameters::AddPagerSettings($arComponentParameters, GetMessage("T_IBLOCK_DESC_PAGER_PAGE"), false, true);

$arComponentParameters["PARAMETERS"]["DISPLAY_TOP_PAGER"]["REFRESH"] = "Y";
$arComponentParameters["PARAMETERS"]["DISPLAY_BOTTOM_PAGER"]["REFRESH"] = "Y";

if($arCurrentValues["DISPLAY_TOP_PAGER"] == "Y" || $arCurrentValues["DISPLAY_BOTTOM_PAGER"] == "Y")
	unset($arComponentParameters["PARAMETERS"]["LOADED_NUMBER"]);

?>
