<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

//создание страниц административного меню
if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
{
	if ($dir = opendir($p))
	{
		while (false !== $item = readdir($dir))
		{
			if ($item == '..' || $item == '.' || $item == 'menu.php')
				continue;
			file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item,
			'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/admin/'.$item.'");?'.'>');
		}
		closedir($dir);
	}
}

//проверка и создание директории при необходимости
CheckDirPath($_SERVER["DOCUMENT_ROOT"].'/bitrix/tools/'.self::MODULE_ID."/");

//кописрование tools
if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/tools'))
{
	if ($dir = opendir($p))
	{
		while (false !== $item = readdir($dir))
		{
			if ($item == '..' || $item == '.')
				continue;
			
			file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.self::MODULE_ID.'/'.$item,
			'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/tools/'.$item.'");?'.'>');
		}
		closedir($dir);
	}
}

//копирование оформления
CopyDirFiles(
	$_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/themes', 
	$_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", 
	true, 
	true
	);
	
//копирование js
CopyDirFiles(
	$_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/js', 
	$_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::MODULE_ID, 
	true, 
	true
	);

//копирование компонентов
CopyDirFiles(
	$_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/components', 
	$_SERVER["DOCUMENT_ROOT"]."/bitrix/components", 
	true, 
	true
	);

//копирование гаджетов
CopyDirFiles(
	$_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/gadgets', 
	$_SERVER["DOCUMENT_ROOT"].'/bitrix/gadgets/collected', 
	true, 
	true
	);


//копирование tools
/*
CopyDirFiles(
	$_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/tools', 
	$_SERVER["DOCUMENT_ROOT"].'/bitrix/tools/'.self::MODULE_ID, 
	true, 
	true
	);
*/

?>