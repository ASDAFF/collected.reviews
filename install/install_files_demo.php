<?
//функция перекодировки файла
function my_readDir($path='./'){   
	global $APPLICATION;
	$d=dir($path);   
	while(false!==($entry=$d->read())){ 
		if(($entry== '.')||($entry=='..'))continue;  
		if(is_dir($path.'/'.$entry)){
			my_readDir($path.'/'.$entry); 
		}   
		
		$file = $APPLICATION->ConvertCharset(file_get_contents($path.'/'.$entry), "WINDOWS-1251", "UTF-8");
		if ($file)
		{
			if ($f = fopen($path.'/'.$entry, "w+"))
			{
				@fwrite($f, $file);
				@fclose($f);
			}
		}	
	}   
	$d->close();   
}

if(is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/public'))
{
	if ($dir = opendir($p))
	{
		while (false !== $item = readdir($dir))
		{
			if ($item == '..' || $item == '.')
				continue;

			CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/'.$item, $ReWrite = True, $Recursive = True);
			
			//перекодировка
//			if (defined('BX_UTF') && BX_UTF)
//				my_readDir($_SERVER['DOCUMENT_ROOT'].'/'.$item);
			
		}
		closedir($dir);
	}
}

CopyDirFiles(
	$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/templates',
	$_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/components/bitrix/',
	true, 
	true
	);
?>