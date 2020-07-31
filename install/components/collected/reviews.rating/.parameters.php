<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	"GROUPS" => array(

	),
	"PARAMETERS" => array(
		"ELEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_REVIEWS_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
	),
);
?>
