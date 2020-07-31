		<?
IncludeModuleLangFile(__FILE__);

class COLLECTEDReviewsMessages extends COLLECTEDReviewsBase {

	const MODULE_ID = 'collected.reviews';
	public $MODULE_ID = 'collected.reviews';
	
	public $table = 'b_collected_reviews_messages';
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
	
	function GetStatusList ()
	{
		return array(
			'N' => GetMessage("COLLECTED_REIEWS_STATUS_NEW"),
			'R' => GetMessage("COLLECTED_REIEWS_STATUS_R"),
			'A' => GetMessage("COLLECTED_REIEWS_STATUS_A"),
			);
	}
	
	function GetRatingList ()
	{
		return array(
			1 => '1',
			2 => '2',
			3 => '3',
			4 => '4',
			5 => '5',
			);
	}
	
	function GetUsePeriodList ()
	{
		return array(
			3 => GetMessage("COLLECTED_REIEWS_EXP_USING_3"),
			7 => GetMessage("COLLECTED_REIEWS_EXP_USING_7"),
			30 => GetMessage("COLLECTED_REIEWS_EXP_USING_30"),
			90 => GetMessage("COLLECTED_REIEWS_EXP_USING_90"),
			180 => GetMessage("COLLECTED_REIEWS_EXP_USING_180"),
			365 => GetMessage("COLLECTED_REIEWS_EXP_USING_365"),
			);
	}
	
	function Add($arFields)
	{
		GLOBAL $DB, $USER;
		
		foreach (GetModuleEvents("collected.reviews", "OnBeforeReviewAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));
		
		//search element by id
		if(intval($arFields['ELEMENT_ID']) <= 0)
			$this->arError[] = GetMessage("COLLECTED_REIEWS_ADD_ELEMENT_ID_N");
		elseif(CModule::IncludeModule("iblock"))
		{
			$arFilter = Array(
				'ID' => $arFields['ELEMENT_ID'],
				);

			$arSelect = array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL');

			$rsProduct = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
			if (intval($rsProduct->SelectedRowsCount()) == 0)
				$this->arError[] = GetMessage("COLLECTED_REIEWS_ADD_ELEMENT_ID_NF");
			else
			{
				if($arProduct = $rsProduct->GetNext())
				{
					$arFields['IBLOCK_ID'] = $arProduct['IBLOCK_ID'];
					$REVIEW_ELEMENT_NAME = $arProduct['NAME'];
					$REVIEW_ELEMENT_DETAIL_PAGE_URL = $arProduct['DETAIL_PAGE_URL'];
				}
			}
		}

		if(array_key_exists('CITY', $arFields))
			$arFields['CITY'] = substr($arFields['CITY'], 0, 127);
		 
		if(array_key_exists('STATUS', $arFields) && !array_key_exists($arFields['STATUS'], self::GetStatusList()))
			$this->arError[] = GetMessage("COLLECTED_REIEWS_ADD_STATUS_NC");
		
		if(array_key_exists('RATING', $arFields) && !array_key_exists($arFields['RATING'], self::GetRatingList()))
			$this->arError[] = GetMessage("COLLECTED_REIEWS_ADD_RATING_NC");
			
		if(array_key_exists('EXP_USING', $arFields) && !array_key_exists($arFields['EXP_USING'], self::GetUsePeriodList()))
			$this->arError[] = GetMessage("COLLECTED_REIEWS_ADD_EXP_USING_NC");

			
		if(intval($arFields['USER_ID']) > 0)
		{
			$rsUser = CUser::GetByID($arFields['USER_ID']);
			$arUser = $rsUser->Fetch();
			if(intval($arUser['ID']) == 0)
				$this->arError[] = 'user not found';
			
			$arFields['USER_EMAIL'] = strlen($arFields['USER_EMAIL']) > 0 ? $arFields['USER_EMAIL'] : $arUser['EMAIL'];
			$arFields['USER_NAME'] = strlen($arFields['USER_NAME']) > 0 ? $arFields['USER_NAME'] : trim($arUser['NAME'].' '.$arUser['LAST_NAME']);
		}
		
		if( strlen($arFields['USER_EMAIL']) > 0 && !check_email($arFields['USER_EMAIL']))
			$this->arError[] = GetMessage("COLLECTED_REIEWS_ADD_EMAIL_NC");

		if($arFields['SITE_ID'] == '')
			$this->arError[] = GetMessage("COLLECTED_REIEWS_ADD_SITE_N");
		else
		{
			$rsSite = CSite::GetByID($arFields['SITE_ID']);
			if(!($arSite = $rsSite->Fetch()))
				$this->arError[] = GetMessage("COLLECTED_REIEWS_ADD_SITE_NF");
		}
		
		if($this->SetError())
			return false;
	
		$moderation = COption::GetOptionString('collected.reviews', 'moderation', '', $arFields['SITE_ID']);

		
		if($moderation == 'Y')
			$arFields['STATUS'] = 'N';
		else
			$arFields['STATUS'] = 'A';
		
		
		extract($arFields, EXTR_PREFIX_ALL, "mess");
		$DB->PrepareFields("b_collected_reviews_messages", "mess_");
		
		$arAddFields = array(
			'DATE_CREATE'		=> $DB->GetNowFunction(),
			'DATE_CREATE_REAL'	=> $DB->GetNowFunction(),
			'STATUS'			=> "'".$mess_STATUS."'",
			'IBLOCK_ID'			=> intval($mess_IBLOCK_ID),
			'ELEMENT_ID'		=> intval($mess_ELEMENT_ID),	
			'DIGNITY'	 		=> "'".htmlspecialcharsEx($mess_DIGNITY)."'",
			'LIMITATIONS'		=> "'".htmlspecialcharsEx($mess_LIMITATIONS)."'",
			'COMMENTS'	 		=> "'".htmlspecialcharsEx($mess_COMMENTS)."'",
			'RATING'	 		=> intval($mess_RATING),
			'EXP_USING'	 		=> intval($mess_EXP_USING), 
			'IP_ADDRESS'		=> "'".$_SERVER['REMOTE_ADDR']."'",
			'CITY'				=> "'".htmlspecialcharsEx($mess_CITY)."'",
			'USER_ID'	 		=> intval($mess_USER_ID),
			'USER_NAME'	 		=> "'".$mess_USER_NAME."'",
			'USER_EMAIL'		=> "'".$mess_USER_EMAIL."'",
			'SITE_ID'	 		=> "'".$mess_SITE_ID."'",
			);
		
		$DB->StartTransaction();
		
		$ID = $DB->Insert("b_collected_reviews_messages", $arAddFields, $err_mess.__LINE__);
		$ID = intval($ID);
		
		if($ID > 0)
		{
			$DB->Commit();
			
			$arFields['ID'] = $ID;
			
			foreach (GetModuleEvents("collected.reviews", "OnAfterReviewAdd", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array(&$arFields));
			
			//notify fields
			
			//get reviev
			$arEventFields = array();
			$CReview = new COLLECTEDReviewsMessages();
			$rsReview = $CReview->GetByID($ID);
			if($arReview = $rsReview->GetNext())
			{
				$arEventFields['ID'] = $arReview['ID'];
				$arEventFields['DATE_CREATE'] = $arReview['DATE_CREATE'];
				$arEventFields['ELEMENT_NAME'] = $REVIEW_ELEMENT_NAME;
				$arEventFields['DETAIL_PAGE']	= $REVIEW_ELEMENT_DETAIL_PAGE_URL;
				
				$arEventFields['DIGNITY'] = $arReview['DIGNITY'];
				$arEventFields['LIMITATIONS'] = $arReview['LIMITATIONS'];
				$arEventFields['COMMENTS'] = $arReview['COMMENTS'];
				$arEventFields['RATING'] = $arReview['RATING'];
				$arEventFields['EXP_USING'] = $arReview['EXP_USING'];
				
				$arEventFields['USER_NAME'] = $arReview['USER_NAME'];
				$arEventFields['USER_EMAIL'] = $arReview['USER_EMAIL'];
				
				//notise sendig
				$emails = COption::GetOptionString(self::MODULE_ID, 'notice_email', '', $arReview['SITE_ID']);
				
				$arEmail = explode(',',$emails);
				
				if(is_array($arEmail))
				{
					foreach($arEmail as $email)
					{
						$arEventFields['MODERATOR_EMAIL'] = trim($email);
						CEvent::Send("COLLECTED_REVIEWS_CREATE", $arReview['SITE_ID'], $arEventFields);
					}
				}
				
				//if free comment(noot moderation)
				if($moderation != 'Y')
				{
					//subscribe to users
					COLLECTEDReviews::SendSubscribe($review_id);
					
					//update iblock property rating
					COLLECTEDReviews::UpdateIBlockRating($arReview['ELEMENT_ID'], $arReview['SITE_ID']);
				}
			}
			
			

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

		$review_id = intval($id);
		
		foreach (GetModuleEvents("collected.reviews", "OnBeforeReviewUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($review_id, &$arFields));
				
		unset($arFields['ID']);
		unset($arFields['DATE_CREATE_REAL']);
		unset($arFields['IP_ADDRESS']);
		
		if(array_key_exists('HELPFUL', $arFields))
			$arFields['HELPFUL'] = intval($arFields['HELPFUL']) > 0 ? intval($arFields['HELPFUL']) : 0 ;
			
		if(array_key_exists('USELESS', $arFields))
			$arFields['USELESS'] = intval($arFields['USELESS']) > 0 ? intval($arFields['USELESS']) : 0 ;
		
		if(array_key_exists('DATE_CREATE', $arFields) && strlen($arFields['DATE_CREATE']) == 0)
		{
			$err[] = GetMessage("COLLECTED_REIEWS_ADD_DATE_CREATE_N");
		}
		elseif(array_key_exists('DATE_CREATE', $arFields) && !$DB->IsDate($arFields['DATE_CREATE'], CSite::GetDateFormat("FULL")))
		{
			$err[] = GetMessage("COLLECTED_REIEWS_ADD_DATE_CREATE_NC");
		}
		
		//search element by id
		if(array_key_exists('ELEMENT_ID', $arFields) && intval($arFields['ELEMENT_ID']) > 0 && CModule::IncludeModule("iblock"))
		{
			$arFilter = Array(
				'ID' => $arFields['ELEMENT_ID'],
				);

			$arSelect = array('ID', 'IBLOCK_ID', 'NAME');

			$rsProduct = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
			if (intval($rsProduct->SelectedRowsCount()) == 0)
				$err[] = GetMessage("COLLECTED_REIEWS_ADD_ELEMENT_ID_NF");
			else
			{
				if($arProduct = $rsProduct->GetNext())
				{
					$REVIEW_IBLOCK_ID = $arProduct['IBLOCK_ID'];
				}
			}
		}
		
		if(array_key_exists('STATUS', $arFields) && !array_key_exists($arFields['STATUS'], self::GetStatusList()))
			$err[] = GetMessage("COLLECTED_REIEWS_ADD_STATUS_NC");
		
		if(array_key_exists('RATING', $arFields) && !array_key_exists($arFields['RATING'], self::GetRatingList()))
			$err[] = GetMessage("COLLECTED_REIEWS_ADD_RATING_NC");
			
		if(array_key_exists('EXP_USING', $arFields) && !array_key_exists($arFields['EXP_USING'], self::GetUsePeriodList()))
			$err[] = GetMessage("COLLECTED_REIEWS_ADD_EXP_USING_NC");
			
		if(array_key_exists('CITY', $arFields))
			$arFields['CITY'] = substr($arFields['CITY'], 0, 127);

		//USER
		if(array_key_exists('USER_ID', $arFields) && $arFields['USER_ID'] > 0)
		{
			$rsUser = CUser::GetByID($arFields['USER_ID']);
			if($arUser = $rsUser->Fetch())
			{
				$arFields['USER_NAME'] = ($arUser['NAME'] || $arUser['LAST_NAME']) ? trim($arUser['LAST_NAME'].' '.$arUser['NAME']): $arUser['LOGIN'];
				$arFields['USER_EMAIL'] = $arUser['EMAIL'];
			}
		}
		elseif(intval($arFields['USER_ID']) == 0)
		{
			unset($arFields['USER_ID']);
		}
		
		if( array_key_exists('USER_EMAIL', $arFields) && strlen($arFields['USER_EMAIL']) > 0 && !check_email($arFields['USER_EMAIL']))
			$err[] = GetMessage("COLLECTED_REIEWS_ADD_EMAIL_NC");

		if(array_key_exists('SITE_ID', $arFields))
		{
			$rsSite = CSite::GetByID($arFields['SITE_ID']);
			$arSite = $rsSite->GetNext();
			
			if(!$arSite['ID'])
				$err[] = GetMessage("COLLECTED_REIEWS_ADD_SITE_NF");
		}
		
		if(count($err) > 0)
		{
			$this->SetError($err);
			return false;
		}
		
		$where = self::qFilter(array(
			'ID' => $review_id
			));

		$arrFields = self::GetArray($arFields);	

		if(count($err))
		{
			$this->SetError($err, '<br/>');
			return false;
		}
		
		//check chenge status
		if(array_key_exists('STATUS', $arFields))
		{	
			//check last status
			$strSql_s = 'SELECT STATUS FROM b_collected_reviews_messages WHERE ID='.$review_id;
			$rs = $DB->Query($strSql_s, true, $err_mess.__LINE__);
			if($lastData = $rs->GetNext())
			{	
				$element_id = (array_key_exists('ELEMENT_ID', $arFields)) ? $arFields['ELEMENT_ID'] : $lastData['ELEMENT_ID'] ;
				
				if($arFields['STATUS'] == 'A' && $lastData['STATUS'] != 'A')
				{
					COLLECTEDReviews::SendSubscribe($review_id);
				}
			}
		}
		
		$DB->StartTransaction();	
		
		$strUpdate = $DB->PrepareUpdate("b_collected_reviews_messages", $arFields);

		$strSql = "UPDATE b_collected_reviews_messages SET ".$strUpdate." WHERE ID=".$review_id;
		$res = $DB->Query($strSql, true, $err_mess.__LINE__);
		
		//$res = $DB->Update('b_collected_reviews_messages', $arrFields, $where, $err_mess.__LINE__, $this->debug);
        $DB->Commit();
		
		foreach (GetModuleEvents("collected.reviews", "OnAfterReviewUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($review_id, &$arFields));
			
		//check chenge status
		if(array_key_exists('RATING', $arFields) || array_key_exists('STATUS', $arFields))
		{	
			//check last status
			$strSql_s = 'SELECT ELEMENT_ID, RATING, STATUS, SITE_ID FROM b_collected_reviews_messages WHERE ID='.$review_id;
			$rs = $DB->Query($strSql_s, true, $err_mess.__LINE__);
			if($arData = $rs->GetNext())
			{	
				//update iblock property rating
				COLLECTEDReviews::UpdateIBlockRating($arData['ELEMENT_ID'], $arData['SITE_ID']);
			}
		}

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
		$strSql  = 'DELETE FROM b_collected_reviews_messages '.$where;
	
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
	
	function reviewReject($ID) {
		
		global $DB;
		
		if(intval($ID) == 0)
		{	
			self::SetError(array(GetMessage("COLLECTED_REIEWS_ELEMENT_ID_NA")));
			return false;
		}	
		$where = self::qFilter(array(
			'ID' => intval($ID)
			));

		$arrFields = self::GetArray(array('STATUS'=>'R'));	

		$DB->StartTransaction();	
		$res = $DB->Update('b_collected_reviews_messages', $arrFields, $where, $err_mess.__LINE__, $this->debug);

        $DB->Commit();

		return true;
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
			$DB->DateToCharFunction("DATE_CREATE_REAL").' DATE_CREATE_REAL,
			STATUS,
			IBLOCK_ID,
			ELEMENT_ID,
			DIGNITY,
			LIMITATIONS,
			COMMENTS,
			RATING,
			EXP_USING,
			HELPFUL,
			USELESS,
			IP_ADDRESS,
			CITY,
			USER_ID,
			USER_NAME,
			USER_EMAIL,
			SITE_ID
		FROM b_collected_reviews_messages 
			'.$where.$sort.$q_limit;
		
		//echo $strSql;
		
		$rs = $DB->Query($strSql, true, $err_mess.__LINE__);
		return $rs;
	}

	function GetByID($ID) {

		GLOBAL $DB;
		
		$strSql  = 'SELECT 
			ID,'.
			$DB->DateToCharFunction("DATE_CREATE").' DATE_CREATE, '.
			$DB->DateToCharFunction("DATE_CREATE_REAL").' DATE_CREATE_REAL,
			STATUS,
			IBLOCK_ID,
			ELEMENT_ID,
			DIGNITY,
			LIMITATIONS,
			COMMENTS,
			RATING,
			EXP_USING,
			HELPFUL,
			USELESS,
			IP_ADDRESS,
			CITY,
			USER_ID,
			USER_NAME,
			USER_EMAIL,
			SITE_ID
		FROM b_collected_reviews_messages WHERE ID = '.intval($ID);
		
		$rs = $DB->Query($strSql, true, $err_mess.__LINE__);
		return $rs;
	}
	
	function GetRating($ELEMENT_ID) {

		GLOBAL $DB;

		if(intval($ELEMENT_ID) == 0)
			return false;
		
		$Rating = false;
		$sum = 0;

		if(intval($ELEMENT_ID) > 0)
			$where = " WHERE ELEMENT_ID = ".intval($ELEMENT_ID)." AND STATUS = 'A'";

		$strSql  = 'SELECT RATING FROM b_collected_reviews_messages'.$where;

		$res = $DB->Query($strSql, true, $err_mess.__LINE__);

		$count = $res->SelectedRowsCount();

		while($el = $res->GetNext())
			$sum += $el['RATING'];
		
		if($sum > 0 )
			$Rating = round(($sum / $count), 1);
		else
			$Rating = 0;

		return array(
			'RATING' => $Rating,
			'USER_COUNT' => $count,
			);
	}
	
	function OnIBlockElementDelete($PRODUCT_ID)
	{
		//del reviews by ELEMENT_ID
		global $DB;

		if(intval($PRODUCT_ID)==0)
			return false;
		
		$strSql  = 'DELETE FROM b_collected_reviews_messages where ELEMENT_ID='.intval($PRODUCT_ID);
	
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
		
		$strSql  = 'DELETE FROM b_collected_reviews_messages where IBLOCK_ID='.intval($ID);
	
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

}
?>