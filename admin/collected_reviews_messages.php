<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

$module_id = 'collected.reviews';

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/include.php");
//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/prolog.php");

$Site = COLLECTEDReviews::GetSiteList();

$StatusList = COLLECTEDReviewsMessages::GetStatusList();	// статусы
$RatingList = COLLECTEDReviewsMessages::GetRatingList();  //рейтинг
$ExpUsingList = COLLECTEDReviewsMessages::GetUsePeriodList(); // опыт использования


CModule::IncludeModule('iblock');

$arrIBlock = array();
$rsIBlock = CIBlock::GetList(
	Array('ID', 'IBLOCK_TYPE'), 
	Array(
		'ACTIVE'=>'Y'
	), true
);
while($arIBlock = $rsIBlock->GetNext())
{
	$arrIBlock[$arIBlock['ID']] = $arIBlock;
}

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
$sTableID = "tbl_collected_reviews_messages";
$oSort = new CAdminSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
	"find_id",
	"find_iblock_id",
	"find_element_id",
	"find_STATUS",
	"find_RATING",
	"find_EXP_USING",
	"find_SUBSCRIBE",
	"find_user_id",
	"find_site_id",
);

$lAdmin->InitFilter($arFilterFields);


//if(!array_key_exists($find_site_id, $Site) )
	//$find_site_id = $Site[0]['ID'];

if(intval($_REQUEST['IBLOCK_ID']) > 0)
	$find_iblock_id = intval($_REQUEST['IBLOCK_ID']);
	
if(intval($_REQUEST['ELEMENT_ID']) > 0)
	$find_element_id = intval($_REQUEST['ELEMENT_ID']);

$arFilter = array(
	'ID' => $find_id,
	'IBLOCK_ID' => $find_iblock_id,
	'ELEMENT_ID' => $find_element_id,
	'STATUS' => $find_STATUS,
	'RATING' => $find_RATING,
	'EXP_USING' => $find_EXP_USING,
	'SUBSCRIBE' => $find_SUBSCRIBE,
	'USER_ID' => $find_user_id,
	'SITE_ID' => $find_site_id,
	);

//Проверка фильтра - введено название
if(intval($find_element_id) == 0 && strlen($find_element_id) > 0)
{
	$arSelect = Array("ID", "NAME", "IBLOCK_ID");
	$arFilter["NAME"] = '%'.$find_element_id.'%';
	$elements = array();
	$rsElement = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
	
	if (intval($rsElement->SelectedRowsCount())>0)
	{
		if($arElement = $rsElement->GetNext())
			$element = $arElement['ID'];
	}
	else
	{
		$element = 0;
	}

	$arFilter['ELEMENT_ID'] = $element;	
}

foreach($arFilter as $key => $value)
	if(!strlen($value))
		unset($arFilter[$key]);


		

// ******************************************************************** //
//                ОБРАБОТКА ДЕЙСТВИЙ НАД ЭЛЕМЕНТАМИ СПИСКА              //
// ******************************************************************** //

// сохранение отредактированных элементов
if($lAdmin->EditAction() && $POST_RIGHT=="W")
{
	// пройдем по списку переданных элементов
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
		continue;
    
		// сохраним изменения каждого элемента
		$DB->StartTransaction();
		$ID = IntVal($ID);
	
		$CMmessages = new COLLECTEDReviewsMessages();
	
		if(($rsData = $CMmessages->GetByID($ID)) && ($arData = $rsData->Fetch()) && !count($errors))
		{
			foreach($arFields as $key=>$value)
				$arData[$key]=$value;

			if(!$CMmessages->Update($ID, $arData))
			{
				$lAdmin->AddGroupError($CMmessages->last_error, $ID);
			}
		}
		elseif(!count($errors))
		{
			$lAdmin->AddGroupError('Error ', $ID);
		}
		
	}
}

// ******************************************************************** //
// обработка одиночных и групповых действий
// ******************************************************************** //
if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CIBlockElement::GetList(Array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;

		$CMmessages = new COLLECTEDReviewsMessages();
		
		$ID = IntVal($ID);
		$arRes = $CMmessages->GetByID($ID);
		$arRes = $arRes->Fetch();
		if(!$arRes)
			continue;
		
		
		switch($_REQUEST['action'])
		{
			case "delete":

					$APPLICATION->ResetException();
					if(!$CMmessages->Delete($ID))
					{
						if($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError(GetMessage("REVIEW_DELETE_ERROR")." [".$ex->GetString()."]", $ID);
						else
							$lAdmin->AddGroupError(GetMessage("REVIEW_DELETE_ERROR"), $ID);
					}

				break;
			case "status":
			
				$arFields = Array(
					"STATUS"=>($_REQUEST['change_status_to'])
					);
				if(!$CMmessages->Update($ID, $arFields))
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR").$ob->LAST_ERROR, $ID);
				
			break;
		}
		
		if(isset($return_url) && strlen($return_url)>0)
			LocalRedirect($return_url);
	}
	
}

$Messages = new COLLECTEDReviewsMessages();
$rsData = $Messages->GetList(array($by => $order), $arFilter);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SEARCH_PHL_PHRASES")));

$aContext=array();

$lAdmin->AddAdminContextMenu($aContext);

$arHeaders=array(
	array("id"=>"ID", "content"=>GetMessage("COLLECTED_REVIEW_ID"), "sort"=>"ID", "default"=>false, "align"=>"right"),
	array("id"=>"DATE_CREATE", "content"=>GetMessage("COLLECTED_REVIEW_DATE_CREATE"),"sort"=>"DATE_CREATE", "default"=>true),
	array("id"=>"STATUS", "content"=>GetMessage("COLLECTED_REVIEW_STATUS"), "sort"=>"STATUS", "default"=>true),
	array("id"=>"IBLOCK_ID", "content"=>GetMessage("COLLECTED_REVIEW_IBLOCK_ID"), "sort"=>"IBLOCK_ID", "default"=>false),
	array("id"=>"ELEMENT_ID", "content"=>GetMessage("COLLECTED_REVIEW_ELEMENT_ID"), "sort"=>"ELEMENT_ID", "default"=>true),
	array("id"=>"DIGNITY", "content"=>GetMessage("COLLECTED_REVIEW_DIGNITY"), "default"=>false),
	array("id"=>"LIMITATIONS", "content"=>GetMessage("COLLECTED_REVIEW_LIMITATIONS"), "default"=>false),
	array("id"=>"COMMENTS", "content"=>GetMessage("COLLECTED_REVIEW_COMMENTS"), "default"=>false),
	array("id"=>"RATING", "content"=>GetMessage("COLLECTED_REVIEW_RATING"), "sort"=>"RATING", "default"=>true),
	array("id"=>"EXP_USING", "content"=>GetMessage("COLLECTED_REVIEW_EXP_USING"), "sort"=>"EXP_USING", "default"=>true),
	
	array("id"=>"SUBSCRIBE", "content"=>GetMessage("COLLECTED_REVIEW_SUBSCRIBE"), "sort"=>"SUBSCRIBE", "default"=>false),
	array("id"=>"USER_ID", "content"=>GetMessage("COLLECTED_REVIEW_USER"), "sort"=>"USER_ID", "default"=>true),
	array("id"=>"USER_NAME", "content"=>GetMessage("COLLECTED_REVIEW_USER_NAME"), "sort"=>"USER_NAME", "default"=>false),
	array("id"=>"USER_EMAIL", "content"=>GetMessage("COLLECTED_REVIEW_USER_EMAIL"), "sort"=>"USER_EMAIL", "default"=>false),
	array("id"=>"SITE_ID", "content"=>GetMessage("COLLECTED_REVIEW_SITE"), "sort"=>"SITE_ID", "default"=>false),
);

$lAdmin->AddHeaders($arHeaders);

$i=0;
$PRICE_TOTAL = 0;

$arUsers = array();
$arElements = array();

while($arRes = $rsData->NavNext(true, "f_"))
{
	if(!array_key_exists($f_USER_ID, $arUsers))
	{
		$rsUser = CUser::GetByID($f_USER_ID);
		$arUsers[$f_USER_ID] = $rsUser->Fetch();
	}

	//заменяем данные
	$iblock  = '[<a target="_blank" href="/bitrix/admin/iblock_section_admin.php?IBLOCK_ID='.$f_IBLOCK_ID.'&type='.$arrIBlock[$f_IBLOCK_ID]['IBLOCK_TYPE_ID'].'&lang='.LANG.'&find_section_section=0">'.$f_IBLOCK_ID.'</a>] ';
	$iblock .= $arrIBlock[$f_IBLOCK_ID]['NAME'];
	
	if(!array_key_exists($f_ELEMENT_ID, $arElements))
	{
		//поиск элемента
		$arSelect = Array("ID", "NAME", "IBLOCK_ID", "IBLOCK_TYPE_ID");
		$arFilter = Array("ID"=>$f_ELEMENT_ID);
		$rsElement = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
		
		if($arEl = $rsElement->GetNext())	
		{
			$arElements[$arEl['ID']] = $arEl;
		}
	}
		
	$f_ELEMENT_ID_text = '[<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$arElements[$f_ELEMENT_ID]['IBLOCK_ID'].'&type='.$arElements[$f_ELEMENT_ID]['IBLOCK_TYPE_ID'].'&ID='.$f_ELEMENT_ID.'&lang='.LANG.'&find_section_section=-1">'.$f_ELEMENT_ID.'</a>] '.$arElements[$f_ELEMENT_ID]['NAME'];
	$f_DATE_CREATE = CDatabase::FormatDate($f_DATE_CREATE, CLang::GetDateFormat("FULL"), CLang::GetDateFormat("SHORT"));
	
	if(intval($f_USER_ID) > 0)
		$f_USER_ID_text = '[<a target="_blank" href="/bitrix/admin/user_edit.php?ID='.$f_USER_ID.'">'.$f_USER_ID.'</a>] '.$arUsers[$f_USER_ID]['LOGIN'].'';
	else
		$f_USER_ID_text = '';

	$f_STATUS_text = '<div class="status '.$f_STATUS.'">'.$StatusList[$f_STATUS].'</div>';
	
	$c_width = round(($f_RATING/5 * 90), 1);
	$f_RATING_text  = '<div class="rating_counter">';
	$f_RATING_text .= '<span style="width:'.$c_width.'px;"></span>';
	$f_RATING_text .= '</div>';
	
	$f_EXP_USING_text = array_key_exists($f_EXP_USING, $ExpUsingList) ? $ExpUsingList[$f_EXP_USING] : $f_EXP_USING.' '.GetMessage("COLLECTED_REVIEW_EXP_USING_DAY");
	
	$f_SUBSCRIBE_text = $f_SUBSCRIBE == 'Y' ? 'Да' : 'Нет' ;
	
	//**************************************************************
	//строка
	//**************************************************************
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if($_REQUEST["mode"] != "excel")
		$row->AddViewField("DATE_CREATE", str_replace(" ", "&nbsp;", $f_DATE_CREATE));

	$row->AddViewField("IBLOCK_ID", $iblock);
	$row->AddViewField("ELEMENT_ID", $f_ELEMENT_ID_text);
	//$row->AddInputField('ELEMENT_ID', array("size"=>4));
	
	$row->AddSelectField("STATUS", $StatusList);
	$row->AddViewField("STATUS", $f_STATUS_text);
	
	$row->AddSelectField("RATING", $RatingList);
	$row->AddViewField("RATING", $f_RATING_text);
	
	$row->AddSelectField("EXP_USING", $ExpUsingList);
	$row->AddViewField("EXP_USING", $f_EXP_USING_text);
	
	$row->AddSelectField("SUBSCRIBE", array('N'=>'Нет', 'Y'=>'Да'));
	$row->AddViewField("SUBSCRIBE", $f_SUBSCRIBE_text);
	
	$row->AddViewField("USER_ID", $f_USER_ID_text);
	
	// сформируем контекстное меню
	$arActions = Array();

	// редактирование элемента
	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("REVIEW_ACTION_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("collected_reviews_messages_edit.php?ID=".$f_ID)
	);
	  
	// удаление элемента
	if ($POST_RIGHT>="W")
	{
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("REVIEW_ACTION_DEL"),
			"ACTION"=>"if(confirm('".GetMessage('REVIEW_DEL_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);
	}
	
	// применим контекстное меню к строке
	$row->AddActions($arActions);
}


$aContext[] = array(
	"TEXT"		 =>	GetMessage("COLLECTED_REVEWS_ADD"),
	"TITLE"		 =>	GetMessage("COLLECTED_REVEWS_ADD_TITLE"),
	"LINK"		 =>	"collected_reviews_messages_edit.php?lang=".LANG,
	"ICON"		 =>	"btn_new",
	"LINK_PARAM" =>	"",
);


//$lAdmin->ShowChain($chain);

$lAdmin->AddAdminContextMenu($aContext, false, true);

$lAdmin->AddFooter(array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	)
);


$arGroupActions = array();

$arGroupActions["delete"] = GetMessage("MAIN_ADMIN_LIST_DELETE");
$arGroupActions["status"] = GetMessage("MAIN_ADMIN_LIST_CHANGE_STATUS");

$statusselect = '<div id="status_list" style="display:none">
<select name="change_status_to" size="1">';
foreach($StatusList as $id => $status)
{
	$statusselect .= '<option value="'.$id.'">'.$status.'</option>';
}
$statusselect .= '</select></div>';

$arParams["select_onchange"] = "BX('status_list').style.display = (this.value == 'status' ? 'block':'none');";
$arGroupActions["section_chooser"] = array("type" => "html", "value" => $statusselect);

$lAdmin->AddGroupActionTable($arGroupActions, $arParams);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("COLLECTED_REVIEW_MESSAGES_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"find_id" => GetMessage("COLLECTED_REVIEW_ID"),
		"find_iblock_id" => GetMessage("COLLECTED_REVIEW_IBLOCK_ID"),
		"find_element_id" => GetMessage("COLLECTED_REVIEW_ELEMENT_ID"),
		"find_STATUS" => GetMessage("COLLECTED_REVIEW_STATUS"),
		"find_RATING" => GetMessage("COLLECTED_REVIEW_RATING"),
		"find_EXP_USING" => GetMessage("COLLECTED_REVIEW_EXP_USING"),
		"find_SUBSCRIBE" => GetMessage("COLLECTED_REVIEW_SUBSCRIBE"),
		"find_user_id" => GetMessage("COLLECTED_REVIEW_USER"),
		"find_site_id" => GetMessage("COLLECTED_REVIEW_SITE"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_ID")?>:</b></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialchars($find_id)?>"></td>
</tr>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_IBLOCK_ID")?>:</b></td>
	<td>
	<select name="find_site_id" class="adm-select">
	<option value="">...</option>
	<?foreach($arrIBlock as $id => $iblock):?>
		<option value="<?=$id?>" <?if($id == $find_iblock_id):?>selected<?endif;?>>[<?=$id?>] <?=$iblock['NAME']?></option>
	<?endforeach;?>
	</select>
</tr>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_ELEMENT_ID")?>:</b></td>
	<td><input type="text" name="find_element_id" size="47" value="<?echo htmlspecialchars($find_element_id)?>"></td>
</tr>

<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_STATUS")?>:</b></td>
	<td>
	<select name="find_STATUS" class="adm-select">
	<option value="" <?if('' == $find_STATUS):?>selected<?endif;?>>...</option>
	<?foreach($StatusList as $id => $status):?>
		<option value="<?=$id?>" <?if($id == $find_STATUS):?>selected<?endif;?>><?=$status?></option>
	<?endforeach;?>
	</select>
	</td>
</tr>


<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_RATING")?>:</b></td>
	<td>
	<select name="find_RATING" class="adm-select">
	<option value="" <?if('' == $find_RATING):?>selected<?endif;?>>...</option>
	<?foreach($RatingList as $id => $rating):?>
		<option value="<?=$id?>" <?if($id == $find_RATING):?>selected<?endif;?>><?=$rating?></option>
	<?endforeach;?>
	</select>
	</td>
</tr>

<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_EXP_USING")?>:</b></td>
	<td>
	<select name="find_EXP_USING" class="adm-select">
	<option value="" <?if('' == $find_RATING):?>selected<?endif;?>>...</option>
	<?foreach($ExpUsingList as $id => $rating):?>
		<option value="<?=$id?>" <?if($id == $find_EXP_USING):?>selected<?endif;?>><?=$rating?></option>
	<?endforeach;?>
	</select>
	</td>
</tr>

<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SUBSCRIBE")?>:</b></td>
	<td>
	<input type="checkbox" name="find_SUBSCRIBE" <?if('Y' == $find_SUBSCRIBE):?>checked<?endif;?>/><label>Да</label>
	</td>
</tr>

<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_USER")?>:</b></td>
	<td><input type="text" name="find_user_id" size="47" value="<?echo htmlspecialchars($find_user_id)?>"></td>
</tr>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SITE")?>:</b></td>
	<td>
	<select name="find_site_id" class="adm-select">
	<?foreach($Site as $id => $Site):?>
		<option value="<?=$id?>" <?if($id == $find_site_id):?>selected<?endif;?>><?=$Site['NAME']?></option>
	<?endforeach;?>
	</select>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()));
$oFilter->End();
?>
</form>

<?

$lAdmin->DisplayList();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>