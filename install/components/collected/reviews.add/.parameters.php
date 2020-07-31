<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;


$arIBlocks=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = $arRes["NAME"];


$arFields = array(
	'USER_NAME' => GetMessage("T_COLLECTED_REVIEWS_USER_NAME"),
	'USER_EMAIL' => GetMessage("T_COLLECTED_REVIEWS_USER_EMAIL"),
	'CITY' => GetMessage("T_COLLECTED_REVIEWS_CITY"),
	'DIGNITY' => GetMessage("T_COLLECTED_REVIEWS_DIGNITY"),
	'LIMITATIONS' => GetMessage("T_COLLECTED_REVIEWS_LIMITATIONS"),
	'COMMENTS' => GetMessage("T_COLLECTED_REVIEWS_COMMENTS"),
	'RATING' => GetMessage("T_COLLECTED_REVIEWS_RATING"),
	'EXP_USING' => GetMessage("T_COLLECTED_REVIEWS_EXP_USING"),
	//'SUBSCRIBE' => 'SUBSCRIBE',
	);

//fields to select the required
$arFields_R = array();

if(is_array($arCurrentValues['FIELDS']) && count($arCurrentValues['FIELDS']))
{
	foreach($arCurrentValues['FIELDS'] as $fid)
		$arFields_R[$fid] = $arFields[$fid];
}

$arComponentParameters = array(
	"GROUPS" => array(

	),
	"PARAMETERS" => array(

		"ELEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("COLLECTED_RATING_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
		),
		
		"FIELDS" => array(
			"PARENT" => "FIELDS",
			"NAME" => GetMessage("REIEW_FIELDS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arFields,
			"REFRESH" => "Y",
		),

		"FIELDS_REQUIRED" => array(
			"PARENT" => "FIELDS",
			"NAME" => GetMessage("REIEW_FIELDS_REQUIRED"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arFields_R,
		),
		
		"ONECOMMENT_REQUIRED" => array(
			"PARENT" => "FIELDS",
			"NAME" => GetMessage("REIEW_FIELDS_ONECOMMENT_REQUIRED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		

		"USE_GEO" => array(
			"PARENT" => "PARAMS",
			"NAME" => GetMessage("REIEW_USE_GEO"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		
		"REVIEW_FULL" => array(
			"PARENT" => "PARAMS",
			"NAME" => GetMessage("REIEW_REVIEW_FULL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		
		"NEED_AUTH" => array(
			"PARENT" => "PARAMS",
			"NAME" => GetMessage("REIEW_NEED_AUTH"),
			"TYPE" => "CHECKBOX",
			"REFRESH" => "Y",
			"DEFAULT" => "N",
		),
		
		"USE_CAPTCHA" => array(
			"PARENT" => "PARAMS",
			"NAME" => GetMessage("REIEW_USE_CAPTCHA"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		
		"MESS_OK" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("COLLECTED_RATING_MESS_OK"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
	),
);

if($arCurrentValues['NEED_AUTH'] == 'Y')
	unset($arComponentParameters["PARAMETERS"]["USE_CAPTCHA"]);

if(!in_array('CITY', $arCurrentValues["FIELDS"]))
	unset($arComponentParameters["PARAMETERS"]["USE_GEO"]);


?>
