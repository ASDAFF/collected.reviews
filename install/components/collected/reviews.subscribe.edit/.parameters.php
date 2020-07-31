<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;


$arComponentParameters = array(
	"GROUPS" => array(

	),
	"PARAMETERS" => array(
		
		"MESS_SEND_INFO" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("COLLECTED_RATING_MESS_SEND_INFO"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"MESS_REMOVE_OK" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("COLLECTED_RATING_MESS_REMOVE_OK"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"MESS_CONFIRM" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("COLLECTED_RATING_MESS_CONFIRM"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		
		
	),
);

?>
