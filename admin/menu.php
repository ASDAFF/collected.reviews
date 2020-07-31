<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

IncludeModuleLangFile(__FILE__);

$module_id = 'collected.reviews';


$rsModule = CModule::IncludeModuleEx($module_id);

if($rsModule != MODULE_INSTALLED && $rsModule != MODULE_DEMO)
	return;

if($APPLICATION->GetGroupRight($module_id)!="D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "collected_reviews",
		"module_id"=> "collected.reviews",
		"sort" => 1000,
		"text" => GetMessage("mnu_collected_reviews"),
		"title" => GetMessage("mnu_collected_reviews_title"),
		"icon" => "collected_reviews_menu_icon",
		"page_icon" => "collected_reviews_messages_page_icon",
		"url" => "collected_reviews_index.php?lang=".LANGUAGE_ID,
		
		"items_id" => "menu_collected.reviews",
		"items" => array(
			array(
				"text" => GetMessage("mnu_collected_reviews_list"),
				"title" => GetMessage("mnu_collected_reviews_list_title"),
				"url" => "collected_reviews_messages.php?lang=".LANGUAGE_ID,
				"more_url" => Array("collected_reviews_messages_edit.php"),
				),
			array(
				"text"  => GetMessage("mnu_collected_reviews_suscribe"),
				"title" => GetMessage("mnu_collected_reviews_suscribe_title"),
				"url" => "collected_reviews_subscribe.php?lang=".LANGUAGE_ID,
				"more_url" => Array(),
				),
			array(
				"text" => GetMessage("mnu_collected_stoplist"),
				"url" => "collected_reviews_stoplist.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("mnu_collected_stoplist_title"),
				),
			)
	);
	
	return $aMenu;
}
return false;
?>
