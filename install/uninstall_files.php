<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

//удаление админ страниц
DeleteDirFiles(
	$_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/admin', 
	$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin"
	);

//удаление css
DeleteDirFiles(
	$_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.self::MODULE_ID.'/install/themes/.default/', 
	$_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");

//удаление ions, images
DeleteDirFilesEx('/bitrix/themes/.default/icons/'.self::MODULE_ID.'/');
DeleteDirFilesEx('/bitrix/themes/.default/images/'.self::MODULE_ID.'/');

//удалние js
DeleteDirFilesEx('/bitrix/js/'.self::MODULE_ID.'/');

//удаление tools
DeleteDirFilesEx('/bitrix/tools/'.self::MODULE_ID.'/');

//удаление компонентов
if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
{
	if ($dir = opendir($p))
	{
		while (false !== $item = readdir($dir))
		{
			if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
				continue;

			$dir0 = opendir($p0);
			while (false !== $item0 = readdir($dir0))
			{
				if ($item0 == '..' || $item0 == '.')
					continue;
				DeleteDirFilesEx('/bitrix/components/'.$item.'/'.$item0);
			}
			closedir($dir0);
		}
		closedir($dir);
	}
}

//удаление гаджетов
if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/gadgets'))
{
	if ($dir = opendir($p))
	{
		while (false !== $item = readdir($dir))
		{
			if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
				continue;

			$dir0 = opendir($p0);
			while (false !== $item0 = readdir($dir0))
			{
				if ($item0 == '..' || $item0 == '.')
					continue;
				DeleteDirFilesEx('/bitrix/gadgets/collected/'.$item);
			}
			closedir($dir0);
		}
		closedir($dir);
	}
}

?>