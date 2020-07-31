<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

$arTemplateParameters = array(
	"SHOW_RATING" => array(
		"NAME" => GetMessage("T_REVIEWS_SHOW_RATING"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y",
	),
	
	"SHOW_RATING_TOTAL" => array(
		"NAME" => GetMessage("T_REVIEWS_SHOW_RATING_TOTAL"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	
	"RATING_SHORT_TEMPLATE" => array(
		"NAME" => GetMessage("T_REVIEWS_RATING_SHORT_TEMPLATE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	),
	
	"REVIEW_FULL" => array(
		"NAME" => GetMessage("T_REVIEWS_SHOW_DETAIL"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
);


if($arCurrentValues["SHOW_RATING"]!="Y")
{
	unset($arTemplateParameters["SHOW_RATING_TOTAL"]);
	unset($arTemplateParameters["RATING_SHORT_TEMPLATE"]);
}	

?>
