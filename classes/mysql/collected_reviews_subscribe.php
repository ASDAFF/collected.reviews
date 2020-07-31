<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

IncludeModuleLangFile(__FILE__);

class COLLECTEDReviewsSubscribe extends COLLECTEDReviewsBase {

	const MODULE_ID = 'collected.reviews';
	
	public $table = 'b_collected_reviews_subscribe';
	public $last_error = '';
	
	protected $debug = false;

	public function getTable()
	{
		return $this->table;
	}
	
	public function SetError($err = array(), $sep = '')
	{
		if(!is_array($err))
			return false;
			
		global $APPLICATION;

		$errors = array();

		foreach($err as $id => $text)
			$errors[] = array (
				"id" => $id,
				"text" => $text,
				);
				
		$e = new CAdminException($errors);
		$APPLICATION->ThrowException($e);
	}
	
	function Add($arFields)
	{
		GLOBAL $DB, $USER;

		$err = array();

		//search element by id
		if(intval($arFields['ELEMENT_ID']) <= 0)
			$err[] = GetMessage("COLLECTED_REIEWS_SUBSCR_ADD_ELEMENT_ID_N");
		elseif(CModule::IncludeModule("iblock"))
		{
			$arFilter = Array(
				'ID' => $arFields['ELEMENT_ID'],
				);

			$arSelect = array('ID', 'IBLOCK_ID', 'NAME');

			$rsProduct = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
			if (intval($rsProduct->SelectedRowsCount()) == 0)
				$err[] = GetMessage("COLLECTED_REIEWS_SUBSCR_ADD_ELEMENT_ID_NF");
			else
			{
				if($arProduct = $rsProduct->GetNext())
				{
					$REVIEW_IBLOCK_ID = $arProduct['IBLOCK_ID'];
				}
			}
		}

		if( strlen($arFields['EMAIL']) == 0)
			$err[] = GetMessage("COLLECTED_REIEWS_SUBSCR_ADD_EMAIL_N");
		elseif( strlen($arFields['EMAIL']) > 0 && !check_email($arFields['EMAIL']))
			$err[] = GetMessage("COLLECTED_REIEWS_SUBSCR_ADD_EMAIL_NC");

		$rsSite = CSite::GetByID($arFields['SITE_ID']);
		$arSite = $rsSite->GetNext();

		if(!$arSite['ID'])
			$err[] = GetMessage("COLLECTED_REIEWS_SUBSCR_ADD_SITE_NF");

		if(count($err) > 0)
		{
			$this->SetError($err);
			return false;
		}

		$arFields = array(
			'DATE_CREATE'	=> ConvertTimeStamp(time(), "FULL"),
			'IBLOCK_ID'		=> $REVIEW_IBLOCK_ID,
			'ELEMENT_ID'	=> $arFields['ELEMENT_ID'],
			'CONFIRMED'		=> $arFields['CONFIRMED'],
			'EMAIL'			=> $arFields['EMAIL'],
			'CODE'			=> $arFields['CODE'],
			'SITE_ID'	 	=> $arFields['SITE_ID'],
			);

		$arrFields = self::GetArray($arFields);	

		$DB->StartTransaction();

		$ID = $DB->Insert('b_collected_reviews_subscribe', $arrFields, $err_mess.__LINE__);	

		if($ID>0)
		{
			$DB->Commit();
			return $ID;
		}
		else
		{
			$DB->Rollback();
			return false;
		}
	}


	//**********************************************************************************

	function Update($id, $arFields)
	{
		global $DB, $USER;

		unset($arFields['ID']);
		unset($arFields['DATE_CREATE']);
		
		//search element by id
		if(array_key_exists('ELEMENT_ID', $arFields) && intval($arFields['ELEMENT_ID']) > 0 && CModule::IncludeModule("iblock"))
		{
			$arFilter = Array(
				'ID' => $arFields['ELEMENT_ID'],
				);

			$arSelect = array('ID', 'IBLOCK_ID', 'NAME');

			$rsProduct = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
			if (intval($rsProduct->SelectedRowsCount()) == 0)
				$err[] = GetMessage("COLLECTED_REIEWS_SUBSCR_ADD_ELEMENT_ID_NF");
			else
			{
				if($arProduct = $rsProduct->GetNext())
				{
					$REVIEW_IBLOCK_ID = $arProduct['IBLOCK_ID'];
				}
			}
		}
		
		if( array_key_exists('EMAIL', $arFields) && strlen($arFields['USER_EMAIL']) > 0 && !check_email($arFields['USER_EMAIL']))
			$err[] = GetMessage("COLLECTED_REIEWS_SUBSCR_ADD_EMAIL_NC");

		if(array_key_exists('SITE_ID', $arFields))
		{
			$rsSite = CSite::GetByID($arFields['SITE_ID']);
			$arSite = $rsSite->GetNext();
			
			if(!$arSite['ID'])
				$err[] = GetMessage("COLLECTED_REIEWS_SUBSCR_ADD_SITE_NF");
		}
		
		if(count($err) > 0)
		{
			$this->SetError($err);
			return false;
		}
		
		$where = self::qFilter(array(
			'ID' => $id
			));

		$arrFields = self::GetArray($arFields);	

		if(count($err))
		{
			$this->SetError($err, '<br/>');
			return false;
		}
		
		$DB->StartTransaction();	
		$res = $DB->Update('b_collected_reviews_subscribe', $arrFields, $where, $err_mess.__LINE__, $this->debug);

        $DB->Commit();

		return true;
	}
	
	//**********************************************************************************
	
	function Delete($ID) {
		
		global $DB, $APPLICATION;

		if(intval($ID) == 0)
		{	
			self::SetError(array(GetMessage("COLLECTED_REIEWS_ELEMENT_ID_NA")));
			return false;
		}
		
		$arFilter = array(
			'ID' => intval($ID),
			);
		
		$where = self::qFilter($arFilter);
		$strSql  = 'DELETE FROM b_collected_reviews_subscribe '.$where;
	
		@set_time_limit(0);
	
		$DB->StartTransaction();
		
		$rs = $DB->Query($strSql, true, $err_mess.__LINE__);
		
		if(!$rs) 
		{
			$DB->Rollback();
			return false;
		}
		else
		{
			$DB->Commit();
			return true;
		}
		
	}
	
	
	function Confirm($id, $status = 'Y')
	{
		global $DB, $USER;
		$id = intval($id);
		if($id == 0)
			$err[] = 'not correct id';
		
		$status = $status == 'Y' ? 'Y' : 'N';
		
		if(count($err) > 0)
		{
			$this->SetError($err);
			return false;
		}
		
		$where = self::qFilter(array(
			'ID' => $id
			));

		$arrFields = self::GetArray(array(
			'CONFIRMED' => $status,
			));	

		$DB->StartTransaction();	
		$res = $DB->Update('b_collected_reviews_subscribe', $arrFields, $where, $err_mess.__LINE__, $this->debug);
        $DB->Commit();

		return true;
	}
	

	function GetList($arSort = array(), $arFilter = array(), $limit = 0) {

		GLOBAL $DB;
		
		$limit = intval($limit);
		$where = self::qFilter($arFilter);
		$sort = self::qOrder($arSort);
		
		$q_limit = $limit > 0 ? ' LIMIT 0 , '.$limit.' ' : '' ;

		$strSql  = 'SELECT * FROM b_collected_reviews_subscribe'.$where.$sort.$q_limit;
		
		$rs = $DB->Query($strSql, true, $err_mess.__LINE__);
		return $rs;
	}
	
	function GetByID($ID) {

		GLOBAL $DB;
		
		$arFilter = array(
			'ID' => $ID,
			);
		
		$where = self::qFilter($arFilter);

		$strSql  = 'SELECT * FROM b_collected_reviews_subscribe'.$where;
		
		$rs = $DB->Query($strSql, true, $err_mess.__LINE__);
		return $rs;
	}
	
	function OnIBlockElementDelete($PRODUCT_ID)
	{
		//del reviews by ELEMENT_ID
		global $DB;

		if(intval($PRODUCT_ID)==0)
			return false;
		
		$strSql  = 'DELETE FROM b_collected_reviews_subscribe where ELEMENT_ID='.intval($PRODUCT_ID);
	
		@set_time_limit(0);
	
		$DB->StartTransaction();
		
		$rs = $DB->Query($strSql, true, $err_mess.__LINE__);
		
		if(!$rs) 
		{
			$DB->Rollback();
			return false;
		}
		else
		{
			$DB->Commit();
			return true;
		}
	}
	
	function OnIBlockDelete($ID)
	{	
		global $DB, $APPLICATION;

		if(intval($ID)==0)
			return false;
		
		$strSql  = 'DELETE FROM b_collected_reviews_subscribe where IBLOCK_ID = '.intval($ID);
	
		@set_time_limit(0);
	
		$DB->StartTransaction();
		
		$rs = $DB->Query($strSql, true, $err_mess.__LINE__);
		
		if(!$rs) 
		{
			$DB->Rollback();
			return false;
		}
		else
		{
			$DB->Commit();
			return true;
		}
		
	}
	
	function RemoveNotConfirmed()
	{
		GLOBAL $DB;
		
		$dbSites = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
		while ($site = $dbSites->Fetch())
		{
		
			$left_time = COption::GetOptionInt(self::MODULE_ID, "subscribe_confirm_save", 7, $site["ID"]);
	
			$DATE_CREATE = date('d.m.Y').' 00:00:01';
			$stmp = MakeTimeStamp($DATE_CREATE, "DD.MM.YYYY HH:MI:SS");
			$stmp = AddToTimeStamp(array("DD"=>-$left_time), $stmp);
			$DATE_CREATE = ConvertTimeStamp($stmp, "FULL");

			$arFilter = array(
				'<DATE_CREATE' => $DATE_CREATE,
				'CONFIRMED' => 'N',
				'SITE_ID' => $site["ID"],
				);
			
			$where = self::qFilter($arFilter);
			$strSql  = "DELETE FROM b_collected_reviews_subscribe".$where;
			$DB->Query($strSql, true, $err_mess.__LINE__);
		}
		//return true;
	}
}
?>