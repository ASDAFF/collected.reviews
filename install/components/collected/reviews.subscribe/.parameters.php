<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

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

		"MESS_OK" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("COLLECTED_RATING_MESS_OK"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
	),
);

?>
