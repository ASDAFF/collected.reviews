		<?
IncludeModuleLangFile(__FILE__);

class COLLECTEDReviewsStopList extends COLLECTEDReviewsBase {

	const MODULE_ID = 'collected.reviews';
	protected $MODULE_ID = 'collected.reviews';
	public $table = 'b_collected_reviews_stoplist';
	public $last_error = '';
	public $arError = array();
	
	protected $debug = false;

	public function getTable()
	{
		return $this->table;
	}
	
	public function SetError($sep = '')
	{
		global $APPLICATION;

		if(!is_array($this->arError))
			return false;
			
		if(count($this->arError) == 0)
			return false;
			
		$exc = array();

		foreach($this->arError as $id => $text)
			$exc[] = array (
				"id" => $id,
				"text" => $text,
				);
			
		$e = new CAdminException($exc);
		$APPLICATION->ThrowException($e);
		
		return true;
	}
	
	function Add($arFields)
	{
		GLOBAL $DB, $USER;
		
		$block_ip_blocktime = COption::GetOptionInt(self::MODULE_ID, 'block_ip_blocktime', 0, $arFields["SITE_ID"]);
		
		//check last status
		$strSql = "SELECT ID, DATE_ACTIVE_TO, IP_ADDRESS, SITE_ID FROM b_collected_reviews_stoplist WHERE IP_ADDRESS='".$_SERVER['REMOTE_ADDR']. "' AND SITE_ID='".$arFields["SITE_ID"]."'";

		if(intval($arFields["USER_ID"]) > 0)
			$strSql	.= " AND USER_ID = ".intval($arFields["USER_ID"]);
			
		$rsSearch = $DB->Query($strSql, true, $err_mess.__LINE__);
		
		
			
		if($rsSearch->SelectedRowsCount() > 0)
		{
			$arID = array();
			
			while($arS = $rsSearch->GetNext())
				$arID[] = $arS['ID'];
			
			$arrAdd = array(
				"MI"	=> $block_ip_blocktime,
				);
				
			$stmp = AddToTimeStamp($arrAdd, time());
		
			
			
			$arUpdFields = array(
				'DATE_ACTIVE_TO' => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), $stmp),
				);

			$DB->StartTransaction();	
			$strUpdate = $DB->PrepareUpdate("b_collected_reviews_stoplist", $arUpdFields);
			$strSql = "UPDATE b_collected_reviews_stoplist SET ".$strUpdate." WHERE ID in (".implode(",",$arID).")";
			
			
			$res = $DB->Query($strSql, true, $err_mess.__LINE__);
			$DB->Commit();
			return true;
		}
			
		if(intval($arFields['USER_ID']) > 0)
		{
			$rsUser = CUser::GetByID($arFields['USER_ID']);
			$arUser = $rsUser->Fetch();

			if(intval($arUser['ID']) == 0)
				$this->arError[] = 'user not found';
		}

		if($arFields['SITE_ID'] == '')
			$this->arError[] = GetMessage("COLLECTED_REIEWS_ADD_SITE_N");

		if($this->SetError())
			return false;

		extract($arFields, EXTR_PREFIX_ALL, "entry");
		$DB->PrepareFields("b_collected_reviews_stoplist", "entry_");
		
		$block_ip_blocktime = COption::GetOptionInt($this->MODULE_ID, 'block_ip_blocktime', 60, $arFields['SITE_ID']);
		
		$arrAdd = array(
			"MI"	=> $block_ip_blocktime,
			);
			
		$stmp = AddToTimeStamp($arrAdd, time());
		
		$arAddFields = array(
			'DATE_CREATE'		=> $DB->GetNowFunction(),
			'DATE_ACTIVE_TO'	=> "'".date("Y-m-d H:i:s", $stmp)."'",
			'IP_ADDRESS'		=> "'".$_SERVER['REMOTE_ADDR']."'",
			'USER_ID'	 		=> intval($USER->GetID()),
			'SITE_ID'	 		=> "'".$entry_SITE_ID."'",
			'EXCEP'	 			=> "'N'",
			);
		
		$DB->StartTransaction();
		
		$ID = $DB->Insert("b_collected_reviews_stoplist", $arAddFields, $err_mess.__LINE__);
		$ID = intval($ID);
		
		if($ID > 0)
		{
			$DB->Commit();
			$arFields['ID'] = $ID;
			return $ID;
		}
		else
		{
			$DB->Rollback();
			return false;
		}
	}

	//**********************************************************************************
	
	function Check($IP, $USER_ID = 0, $SITE_ID) {

		GLOBAL $DB;

		$USER_ID = intval($USER_ID);
		
		$strSql  = "SELECT 
			ID,".
			$DB->DateToCharFunction("DATE_ACTIVE_TO")." DATE_ACTIVE_TO, 
			IP_ADDRESS,
			SITE_ID,
			EXCEP
		FROM 
			b_collected_reviews_stoplist 
		WHERE 
			IP_ADDRESS = '".$IP."' AND SITE_ID = '".$SITE_ID."' AND EXCEP NOT LIKE 'Y' AND DATE_ACTIVE_TO > ".$DB->GetNowFunction();
			
		if($USER_ID > 0)
			$strSql  = ' AND USER_ID = '.$USER_ID.' ';
			
		$rsCheck = $DB->Query($strSql, true, $err_mess.__LINE__);
		
		return $rsCheck->SelectedRowsCount() > 0 ? false : true ;
	}
	
	
	function GetList($arSort = array(), $arFilter = array(), $limit = 0) {

		GLOBAL $DB;
		
		if(is_array($limit))
		{
			$limit = array(
				intval($limit[0]),
				intval($limit[1])
				);

			$q_limit = ($limit[0] > 0 && $limit[1] > 0) ? ' LIMIT '.$limit[0].' , '.$limit[1].' ' : '' ;	
		}
		else 
		{
			$limit = intval($limit);
			$q_limit = $limit > 0 ? ' LIMIT 0 , '.$limit.' ' : '' ;	
		}
		
		$where = self::qFilter($arFilter);
		$sort = self::qOrder($arSort);

		$strSql  = 'SELECT 
			ID,'.
			$DB->DateToCharFunction("DATE_CREATE").' DATE_CREATE, '.
			$DB->DateToCharFunction("DATE_ACTIVE_TO").' DATE_ACTIVE_TO,
			IP_ADDRESS,
			USER_ID,
			SITE_ID,
			EXCEP
		FROM 
			b_collected_reviews_stoplist 
			'.$where.$sort.$q_limit;
		
		//echo $strSql;
		
		$rs = $DB->Query($strSql, true, $err_mess.__LINE__);
		return $rs;
	}
	
	function ChangeBlock($ID, $block) 
	{
		GLOBAL $DB;
		$arrAdd = array();
		$block_ip_blocktime = COption::GetOptionInt(self::MODULE_ID, 'block_ip_blocktime', 0, $arFields["SITE_ID"]);
		
		if($block)
			$arrAdd["MI"] = $block_ip_blocktime;
		else
			$arrAdd["MI"] = -1;
		
		$stmp = AddToTimeStamp($arrAdd, time());

		$arUpdFields = array(
			'DATE_ACTIVE_TO' => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), $stmp),
			);

		$DB->StartTransaction();	
		$strUpdate = $DB->PrepareUpdate("b_collected_reviews_stoplist", $arUpdFields);
		$strSql = "UPDATE b_collected_reviews_stoplist SET ".$strUpdate." WHERE ID = ".$ID." ";
		
		$res = $DB->Query($strSql, true, $err_mess.__LINE__);
		$DB->Commit();
		return true;
	}
	
	
}
?>