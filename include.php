<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

global $DBType;

abstract class COLLECTEDReviewsBase
{
	private static $arAps = array ('!', '=', '>', '<', '><', '!><', '<=', '>=', '><', '%');
	private $fields = array();
	private $table = '';
	
	abstract public function getTable();
	
	//запрос
	public function Query($strSql, $errors = false)
	{
		GLOBAL $DB;
		
		if($errors)
			return $DB->Query($strSql, true, $err_mess.__LINE__);
		else
			return $DB->Query($strSql, $errors);
	}

	//список полей таблицы
	protected function GetFields()
	{
		$table = $this->getTable();
		$rsFields = self::Query('SHOW COLUMNS FROM ' . $table);

		while($arFields = $rsFields->Fetch())
			$fields[$arFields['Field']] = $arFields;

		return $fields;
	}
	
	//получение знача поля
	protected function GetFieldValue($field, $value, $default=false)
	{
		GLOBAL $DB;
		
		//field = Type Null Key Default Extra
		if($default) $value = $field['Default'];
				
		$Type = $field['Type'];
		$Default = $field['Default'];
		
		$p1 = strrpos($Type, '(');
		$p2 = strrpos($Type, ')');
		
		if($p1>0) 
		{
			$stype = trim(substr($Type,0,$p1));
			if($p2>0) 
				$len = intval(substr($Type,$p1+1,($p2-$p1-1)));
			else
				$len = 0;
		}	
		else
		{
			$stype = $Type;
			$len = 0;
		}

		
		switch ($stype)
		{
			case 'char':
			case 'varchar':
			case 'text': 
			{
				$value = addslashes($value);
				$value = $len > 0 ? substr($value, 0, $len) : $value;
				$value = "'".$DB->ForSql($value)."'";
				break;
			}
			case 'timestamp':
			case 'datetime':
			{
				$value = "'".CDatabase::FormatDate($value, CSite::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS")."'";
				//$value = "'".$DB->CharToDateFunction($value)."'";
				break;
			}
			case 'int':
			{
				if($value=='NULL')
					$value = '"'.$value.'"';
				else
					$value = intval($value);
					
				break;
			}
		}
		return $value;
	}
	
	//создание массива
	protected function GetArray($arFields)
	{
		$arAddFields = array();
		$tableFields = self::GetFields();

		foreach($tableFields as $field => $value)
		{	
			if(array_key_exists($field, $arFields)) // && isset($tableFields[$field]) && $value <> ''
			{
				$arAddFields[$field] = self::GetFieldValue($tableFields[$field], $arFields[$field]);
			}
		}
		return $arAddFields;
	}
	
	protected function qFilter($arFilter, $s='')
	{
		GLOBAL $DB;
		
		$tableFields = self::GetFields();
		$tableFieldsCode = array();
		
		//создаем массив полей таблицы
		foreach($tableFields as $code => $field)
		{
			$tableFieldsCode[] = $code;
		}
		
		if(trim($s)) $s = $s.'.';
		//найдем в массиве _from, _to
		//меняем ID_from и ID_to на >=ID и <=ID
		foreach($tableFields as $code => $field)
		{
			if(isset($arFilter[$code.'_from']))
			{
				if(!isset($arFilter['>='.$code]) && $arFilter[$code.'_from']) 
					$arFilter['>='.$code] = $arFilter[$code.'_from'];
					
				unset($arFilter[$code.'_from']);
			}
			if(isset($arFilter[$code.'_to']))
			{
				if(!isset($arFilter['<='.$code]) && $arFilter[$code.'_to'])  
					$arFilter['<='.$code] = $arFilter[$code.'_to'];
					
				unset($arFilter[$code.'_to']);
			}
		}
		
		if($arFilter['ACTIVE_DATE']=='Y')
		{
			$arFilter['<=DATE_ACTIVE_FROM']  = GetTime(mktime(),"FULL");
			$arFilter['>=DATE_ACTIVE_TO']  = GetTime(mktime(),"FULL");
			
			unset($arFilter['!DATE_ACTIVE_FROM']);
			unset($arFilter['!DATE_ACTIVE_TO']);
			
			unset($arFilter['ACTIVE_DATE']);
		}
		
		if(count($arFilter))
		{
			foreach($arFilter as $field => $value)
			{
				$ap1 = substr($field, 0, 1);
				$ap2 = substr($field, 0, 2);
				$ap3 = substr($field, 0, 3);

				$a = self::$arAps;	
				
				$field_new = $field;
				
				$ap = '';
				
				if (in_array($ap1, $a)) { $ap = $ap1; $field_new = substr($field, 1); }
				if (in_array($ap2, $a)) { $ap = $ap2; $field_new = substr($field, 2); }
				if (in_array($ap3, $a)) { $ap = $ap3; $field_new = substr($field, 3); }
				if ($ap=='') $ap = '=';
				
				$field = $field_new;
				
				$strADD = '';
				
				if(in_array($field, $tableFieldsCode)) 
				{	
					if(is_array($value)){
					
						$or_array = array();
						foreach($value as $val)
						{
							$or_array[] = $s.$field.$ap.$this->GetFieldValue($tableFields[$field], $val);
						}
						$strADD = ' ('.implode(' OR ', $or_array).')';
					}
					elseif	(
						!is_array($value) 
						&& 
						(
							(strlen($value) || $value) || $ap=='!'
						)
					)
					{
						if($ap=='=')
							$strADD = $s.$field.$ap.self::GetFieldValue($tableFields[$field], $value); 
						elseif($ap=='%')
							$strADD = $s.$field.' LIKE '.self::GetFieldValue($tableFields[$field], '%'.$value.'%');
						elseif($ap=='!')
							$strADD = $s.$field.' NOT LIKE '.self::GetFieldValue($tableFields[$field], $value);	
						else
							$strADD = $s.$field.$ap.self::GetFieldValue($tableFields[$field], $value);	
					}
					
					if($strWHERE == '' && $strADD !='' && $s=='')
						$strWHERE = ' WHERE '.$strADD;
					elseif($strWHERE == '' && $strADD !='' && $s!='')
						$strWHERE = ''.$strADD;	
					elseif($strWHERE != '' && $strADD != '')
						$strWHERE .= ' AND '.$strADD;
						
				}
			}
		}	
		return $strWHERE;
	}
	
	protected function qOrder($sort=array(), $s='')
	{
		if(!is_array($sort) && !count($sort)) 
			return '';

		$table = $this->getTable();
		$Fields = self::GetFields($table);
		
		//поля таблицы
		foreach ($Fields as $code => $field){
			$fcode[] = $code;
		}
		
		if($s=='') 
			$ap=''; 
		else 
			$ap=$s.'.';
		
		foreach ($sort as $by => $order){
			if(in_array($by, $fcode) && (strtoupper($order) == 'DESC' || strtoupper($order) == 'ASC')){
				$strSql .= ' ORDER BY '.$ap.$by.' '.$order;
			}
		}
		return $strSql;
	}
}

global $APPLICATION;
$APPLICATION->AddHeadScript('/bitrix/js/collected.reviews/reviews_util.js');

IncludeModuleLangFile(__FILE__);

CModule::AddAutoloadClasses(
	"collected.reviews",
	array(
		'COLLECTEDReviews' 			=> 'classes/'.$DBType.'/collected_reviews.php',
		'COLLECTEDReviewsMessages'	=> 'classes/'.$DBType.'/collected_reviews_messages.php',
		'COLLECTEDReviewsSubscribe'	=> 'classes/'.$DBType.'/collected_reviews_subscribe.php',
		'COLLECTEDReviewsStopList'	=> 'classes/'.$DBType.'/collected_reviews_stoplist.php',
		'COLLECTEDReviewsGeo'			=> 'classes/general/geo.php',
	)
);
?>