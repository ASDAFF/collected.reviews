<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("T_COLLECTED_REVIEWS_COUNT_NAME"),
	"DESCRIPTION" => GetMessage("T_COLLECTED_REVIEWS_COUNT_DESC"),
	"ICON" => "/images/reviews.gif",
	"SORT" => 20,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "collected_reviews",
			"NAME" => GetMessage("T_COLLECTED_REVIEWS_NAME"),
			"SORT" => 100,
		),
	),
);

?>