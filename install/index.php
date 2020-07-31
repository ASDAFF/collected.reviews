<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

global $MESS;

IncludeModuleLangFile(__FILE__);

if(class_exists("collected_reviews")) return;

Class collected_reviews extends CModule
{
	const MODULE_ID = 'collected.reviews';
	var $MODULE_ID = 'collected.reviews';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";
	var $errors;

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("COLLECTED_REVIEWS_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("COLLECTED_REVIEWS_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("COLLECTED_REVIEWS_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("COLLECTED_REVIEWS_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		
		$this->errors = false;
		$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/'.self::MODULE_ID.'/install/db/'.strtolower($DB->type).'/install.sql');

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		GLOBAL $DB;
		
		if(!array_key_exists("SAVE_OPTIONS", $arParams) || ($arParams["SAVE_OPTIONS"] != "Y"))
			COption::RemoveOption(self::MODULE_ID);

		if(!array_key_exists("SAVE_DATA", $arParams) || ($arParams["SAVE_DATA"] != "Y"))
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/db/'.strtolower($DB->type).'/uninstall.sql');
		
		return true;
	}
	
	function InstallFiles($arParams = array())
	{
		include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/install_files.php');
		
		if(array_key_exists("install_demo", $arParams) && $arParams["install_demo"] == "Y")
		{
			include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/install_files_demo.php');
		}
		
		return true;
	}

	function UnInstallFiles($arParams = array())
	{
		include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/uninstall_files.php');

		if($arParams["SAVE_DEMO"] != "Y")
			include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/uninstall_files_demo.php');

		return true;
	}

	function InstallEvents()
	{
		include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/install_events.php');
		return true;
	}

	function UnInstallEvents()
	{
		include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/uninstall_events.php');
		return true;
	}
	
	function DoInstall($arParams = array())
	{
		global $APPLICATION, $step;
		$step = IntVal($step);

		if($step < 2)
			$APPLICATION->IncludeAdminFile(GetMessage("COLLECTED_OFFICESMAP_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/step1.php");
		elseif($step == 2)
		{	
			$this->InstallFiles(array(
				'install_demo' => $_REQUEST['install_demo'],
				));

			$this->InstallDB();
			$this->InstallEvents();

			//регистрация модуля
			RegisterModule(self::MODULE_ID);

			//подключение модуля
			CModule::IncludeModule(self::MODULE_ID);

			//remove reviews
			RegisterModuleDependences("iblock", 
                          "OnBeforeIBlockDelete", 
                          "collected.reviews",
                          "COLLECTEDReviewsMessages",
                          "OnIBlockDelete");
						  
			RegisterModuleDependences("iblock", 
                          "OnIBlockElementDelete", 
                          "collected.reviews",
                          "COLLECTEDReviewsMessages",
                          "OnIBlockElementDelete");

			//remove subscr
			RegisterModuleDependences("iblock", 
                          "OnBeforeIBlockDelete", 
                          "collected.reviews",
                          "COLLECTEDReviewsSubscribe",
                          "OnIBlockDelete");
						  
			RegisterModuleDependences("iblock", 
                          "OnIBlockElementDelete", 
                          "collected.reviews",
                          "COLLECTEDReviewsSubscribe",
                          "OnIBlockElementDelete");
						  

			RegisterModuleDependences("main", 
                          "OnAdminTabControlBegin", 
                          "collected.reviews",
                          "COLLECTEDReviews",
                          "MyOnAdminTabControlBegin");
						  
			RegisterModuleDependences("main", 
							"OnPageStart", 
							"collected.reviews",
							"COLLECTEDReviews",
							"InitUser");
			
			
			//Агент - очистка не подтвержденных / настройка запуска
			$hour = 0;
			$date = date('d.m.Y').' 04:00:00';
			$stmp = MakeTimeStamp($date, "DD.MM.YYYY HH:MI:SS");

			//необходима дата большая текущей даты
			if($stmp < time())
				$stmp = AddToTimeStamp(array("DD"=>1), $stmp);

			$date_start = ConvertTimeStamp($stmp, "FULL");

			//Agent для отслеживания старых позиций в корзине
			CAgent::AddAgent("COLLECTEDReviews::ClearNotConfirmed();", 'collected.reviews', "N", 86400 , "", "Y", $date_start, 10);
			
			$APPLICATION->IncludeAdminFile(GetMessage("COLLECTED_OFFICESMAP_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/step2.php");
		}	
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = IntVal($step);

		if($step < 2)
			$APPLICATION->IncludeAdminFile(GetMessage("COLLECTED_OFFICESMAP_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/unstep1.php");
		elseif($step == 2)
		{		
			
			$this->UnInstallDB(array(
				"SAVE_OPTIONS" => $_REQUEST["SAVE_OPTIONS"],
				"SAVE_DATA" => $_REQUEST["SAVE_DATA"],
				));

			$this->UnInstallEvents();
			$this->UnInstallFiles(array(
				"SAVE_DEMO" => $_REQUEST["SAVE_DEMO"] == "Y",
			));
			
			UnRegisterModuleDependences("iblock", 
                          "OnIBlockDelete", 
                          "collected.reviews",
                          "COLLECTEDReviewsMessages",
                          "OnIBlockDelete");
			
			UnRegisterModuleDependences("iblock", 
                          "OnIBlockElementDelete", 
                          "collected.reviews",
                          "COLLECTEDReviewsMessages",
                          "OnIBlockElementDelete");
						  
			UnRegisterModuleDependences("iblock", 
                          "OnBeforeIBlockDelete", 
                          "collected.reviews",
                          "COLLECTEDReviewsSubscribe",
                          "OnIBlockDelete");
						  
			UnRegisterModuleDependences("iblock", 
                          "OnIBlockElementDelete", 
                          "collected.reviews",
                          "COLLECTEDReviewsSubscribe",
                          "OnIBlockElementDelete");
						  
			UnRegisterModuleDependences("main", 
                          "OnAdminTabControlBegin", 
                          "collected.reviews",
                          "COLLECTEDReviews",
                          "MyOnAdminTabControlBegin");
						  
			UnRegisterModuleDependences("main", 
							"OnPageStart", 
							"collected.reviews",
							"COLLECTEDReviews",
							"InitUser");
			
			CAgent::RemoveModuleAgents("collected.reviews");
			
			UnRegisterModule(self::MODULE_ID);
			
			$APPLICATION->IncludeAdminFile(GetMessage("COLLECTED_OFFICESMAP_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/unstep2.php");
		}
	}
	
	function GetModuleRightList()
	{ 
		$arr = array( 
			"reference_id" => array("D","R","W"), 
			"reference" => array( 
				GetMessage("COLLECTED_REVIEWS_RIGHT_D"),
				GetMessage("COLLECTED_REVIEWS_RIGHT_R"),
				GetMessage("COLLECTED_REVIEWS_RIGHT_W"),
			),
		); 
		return $arr; 
	}

}
?>