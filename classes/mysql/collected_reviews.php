<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

IncludeModuleLangFile(__FILE__);

class COLLECTEDReviews {

	const MODULE_ID = 'collected.reviews';
	var $last_error = '';

	function generate_password($number, $symbol = true)
	{
		$arr = array('a','b','c','d','e','f',
					 'g','h','i','j','k','l',
					 'm','n','o','p','r','s',
					 't','u','v','x','y','z',
					 'A','B','C','D','E','F',
					 'G','H','I','J','K','L',
					 'M','N','O','P','R','S',
					 'T','U','V','X','Y','Z',
					 '1','2','3','4','5','6',
					 '7','8','9','0');

		$arrS = array('.','-','(',')','[',']', '.','-','(',')','[',']');
		
		$pass = "";
		for($i = 0; $i < $number; $i++)
		{
			$index = rand(0, count($arr) - 1);
			$pass .= $arr[$index];
		}

		if($symbol == true)
		{
			for($i = 0; $i < 2; $i++)
			{
				$index1 = rand(0, count($arrS) - 1);
				$index2 = rand(1, strlen($pass) - 1);
				$pass[$index2] = $arrS[$index1];
			}
		}
		return $pass;
		}
	
	function GetSiteList($arFilter = array())
	{
		$arr = array();
		
		if(!count($arFilter))
			$arFilter['ACTIVE'] = 'Y';

		$dbSites = CSite::GetList(($b = ""), ($o = ""), $arFilter);
		while ($site = $dbSites->Fetch())
		{
			$arr[$site['ID']] = $site;
		}

		return $arr;
	}

	function GetUser ()
	{
		GLOBAL $USER, $COLLECTEDReviewsUser;
		
		if ($USER->IsAuthorized())
			$user_id = $USER->GetID();
		else
			$user_id = $COLLECTEDReviewsUser;

		return $user_id;
	}
	

	function UpdateIBlockRating ($element_id, $site_id)
	{
		if($element_id == 0 || $site_id == '')
			return false;
		
		$iblock_prop_rating = COption::GetOptionString(self::MODULE_ID, 'iblock_prop_rating',  '', $site_id);
		
		if(CModule::IncludeModule("iblock"))
		{
			$rsElement = CIBlockElement::GetByID($element_id);
			if($arElement = $rsElement->GetNext())
			{
				$rating = COLLECTEDReviewsMessages::GetRating($arElement['ID']);
				CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $rating, $iblock_prop_rating);
			}
		}
	}
	
	function SendSubscribe($review_id)
	{	
		$review_id = intval($review_id);

		if($review_id == 0)
			return false;

		//get reviev
		$CReview = new COLLECTEDReviewsMessages();
		$rsReview = $CReview->GetByID($review_id);
		if($arReview = $rsReview->GetNext())
		{
			$arEventFields = array(
				'ID' 			=> $arReview['ID'],
				'DIGNITY' 		=> $arReview['DIGNITY'],
				'LIMITATIONS'	=> $arReview['LIMITATIONS'],
				'COMMENTS' 		=> $arReview['COMMENTS'],
				'RATING' 		=> $arReview['RATING'],
				);

			$subscribe = COption::GetOptionString(self::MODULE_ID, 'subscribe',  '', $arReview["SITE_ID"]);

			if($subscribe != 'Y')
				return false;
				
			if(CModule::IncludeModule("iblock"))
			{
				$arFilter = Array(
					'ID' => $arReview['ELEMENT_ID'],
					);

				$arSelect = array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL');

				$rsProduct = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
				if (intval($rsProduct->SelectedRowsCount()) == 0)
				{
					$err[] = GetMessage("COLLECTED_REIEWS_SUBSCR_ADD_ELEMENT_ID_NF");
				}
				else
				{
					//if element found
					if($arProduct = $rsProduct->GetNext())
					{
						$arEventFields['ELEMENT_NAME'] = $arProduct['NAME'];
						$arEventFields['DETAIL_PAGE'] = $arProduct['DETAIL_PAGE_URL'];					
						
						$CSubscribe = new COLLECTEDReviewsSubscribe();
						$rs = $CSubscribe->GetList(array(),array('ELEMENT_ID'=>$arProduct['ID'], 'CONFIRMED_' => 'Y'));
						while($arSubscr = $rs->GetNext())
						{
							$url = COption::GetOptionString(self::MODULE_ID, 'subscribe_edit_page',  '', $arSubscr["SITE_ID"]);
							$url .='?ID='.$arSubscr['ID'].'&action=del&CODE='.$arSubscr['CODE'];

							$arEventFields['EMAIL'] = $arSubscr['EMAIL'];
							$arEventFields['SUBSCRIBE_REMOVE_URL'] = $url;
							
							//send
							CEvent::Send("COLLECTED_REVIEWS_SUBSCRIBE", $arSubscr['SITE_ID'], $arEventFields);
						}
					}
				}
			}
		}
	}

	function InitUser()
	{
		GLOBAL $APPLICATION, $COLLECTEDReviewsUser;

		$COLLECTEDReviewsUser = $APPLICATION->get_cookie("COLLECTEDReviewsUser");

		if(strlen($COLLECTEDReviewsUser) < 5)
		{
			$COLLECTEDReviewsUser = md5(date('d.m.Y').rand(1000,999999999));
			$APPLICATION->set_cookie('COLLECTEDReviewsUser', $COLLECTEDReviewsUser, time()+60*60*24*365);
		}
	}

	function MyOnAdminTabControlBegin(&$form)
	{
		$ID = intval ($_REQUEST['ID']);
		$IBLOCK_ID = intval ($_REQUEST['IBLOCK_ID']);
		
		if($ID <= 0 )
			return false;
			
		
		
		if($GLOBALS["APPLICATION"]->GetCurPage() == "/bitrix/admin/iblock_element_edit.php" && $IBLOCK_ID > 0)
		{
			$Messages = new COLLECTEDReviewsMessages();

			//select by IBLOCK_ID
			$arReviewsFilter = array(
				'IBLOCK_ID' => $IBLOCK_ID,
				);

			$rsMessages = $Messages->GetList(array(), $arReviewsFilter);
			$IBLOCK_COUNT = $rsMessages->SelectedRowsCount();

			if($IBLOCK_COUNT <= 0)
				return false;

			//select all rewiews
			$arReviewsFilter = array(
				'IBLOCK_ID' => $IBLOCK_ID,
				'ELEMENT_ID' => $ID,
				);

			$rsMessages = $Messages->GetList(array(), $arReviewsFilter);
			$COUNT = $rsMessages->SelectedRowsCount();

			//select new rewiews
			$arReviewsFilter['STATUS'] = 'N';
			$rsMessages = $Messages->GetList(array(), $arReviewsFilter);
			$COUNT_NEW = $rsMessages->SelectedRowsCount();

			$rating = COLLECTEDReviewsMessages::GetRating($ID);
			
			$listUrl = '/bitrix/admin/collected_reviews_messages.php?IBLOCK_ID='.$IBLOCK_ID.'&ELEMENT_ID='.$ID.'&lang='.LANG.'&set_filter=Y';
			$addUrl = '/bitrix/admin/collected_reviews_messages_edit.php?ELEMENT_ID='.$ID.'&lang='.LANG;

			$content  = '<tr><td width="40%">'.GetMessage("COLLECTED_REVIEWS_COUNT_ALL").':</td><td> '.$COUNT.' ';
			$content .= '<small><a target="_blank" href="'.$listUrl.'"> '.GetMessage("COLLECTED_REVIEWS_READ_ALL").'</a></small> ';
			$content .= '</td></tr>';
			$content .= '<tr><td>'.GetMessage("COLLECTED_REVIEWS_COUNT_NEW").':</td><td>'.$COUNT_NEW.'</td></tr>';
			$content .= '<tr><td>'.GetMessage("COLLECTED_REVIEWS_COUNT_ACTIVE").':</td><td><b>'.$rating['USER_COUNT'].'</b></td></tr>';
			
			$content .= '<tr><td>'.GetMessage("COLLECTED_REVIEWS_RATING").':</td>';
			$content .= '<td><b>'.$rating['RATING'].'</b> '.GetMessage("COLLECTED_REVIEWS_FROM").' 5';

			
			$content .= '<tr>';
			$content .= '<td></td><td>';
			
			$content .= '</td></tr>';

			
			$CSubscribe = new COLLECTEDReviewsSubscribe();
			
			//select subscribes
			$arSubscrFilter = array(
				'CONFIRMED' => 'Y',
				'ELEMENT_ID' => $ID
				);
			$rsSubscr = $CSubscribe->GetList(array(), $arSubscrFilter);
			$COUNT_SUBSCR = $rsSubscr->SelectedRowsCount();
			
			$content .= '<tr><td>'.GetMessage("COLLECTED_REVIEWS_COUNT_SUBSCRIBES").':</td><td>'.$COUNT_SUBSCR.'</td></tr>';
			
			$form->tabs[] = array(
				"DIV" => "collected_reviews", 
				"TAB" => GetMessage("COLLECTED_REVIEWS_TAB_DESC"),
				"ICON"=>"main_user_edit", 
				"TITLE"=> GetMessage("COLLECTED_REVIEWS_TAB_DESC"),
				"CONTENT"=>$content,
			);
		}
	
	}
	
	function ClearNotConfirmed()
	{
		$subscribe = new COLLECTEDReviewsSubscribe();
		$subscribe->RemoveNotConfirmed();
		
		return "COLLECTEDReviews::ClearNotConfirmed();";
	}
}
?>