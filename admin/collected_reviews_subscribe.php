<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

$module_id = 'collected.reviews';

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/include.php");

$Site = COLLECTEDReviews::GetSiteList();

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
	
$sTableID = "tbl_collected_reviews_subscribe";

$oSort = new CAdminSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
	"find_ID",
	"find_IBLOCK_ID",
	"find_ELEMENT_ID",
	"find_EMAIL",
	"find_CONFIRMED",
	"find_SITE_ID",
);

$lAdmin->InitFilter($arFilterFields);


if(intval($_REQUEST['IBLOCK_ID']) > 0)
	$find_IBLOCK_ID = intval($_REQUEST['IBLOCK_ID']);
	
if(intval($_REQUEST['ELEMENT_ID']) > 0)
	$find_ELEMENT_ID = intval($_REQUEST['ELEMENT_ID']);

$arFilter = array(
	'ID' => $find_id,
	'IBLOCK_ID' => $find_IBLOCK_ID,
	'ELEMENT_ID' => $find_ELEMENT_ID,
	'EMAIL' => $find_EMAIL,
	'CONFIRMED' => $find_CONFIRMED,
	'SITE_ID' => $find_site_id,
	);

//Проверка фильтра - введено название
if(intval($find_ELEMENT_ID) == 0 && strlen($find_ELEMENT_ID) > 0)
{
	$arSelect = Array("ID", "NAME", "IBLOCK_ID");
	$arFilter["NAME"] = '%'.$find_ELEMENT_ID.'%';
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
	
		$CSubscribe = new COLLECTEDReviewsSubscribe();
	
		if(($rsData = $CSubscribe->GetByID($ID)) && ($arData = $rsData->Fetch()) && !count($errors))
		{
			foreach($arFields as $key=>$value)
				$arData[$key]=$value;

			if(!$CSubscribe->Update($ID, $arData))
			{
				$lAdmin->AddGroupError($CSubscribe->last_error, $ID);
			}
		}
		elseif(!count($errors))
		{
			$lAdmin->AddGroupError('Ошибка', $ID);
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

		$CMmessages = new COLLECTEDReviewsSubscribe();
		
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
						$lAdmin->AddGroupError(GetMessage("REVIEW_SUBSCRIBE_DELETE_ERROR")." [".$ex->GetString()."]", $ID);
					else
						$lAdmin->AddGroupError(GetMessage("REVIEW_SUBSCRIBE_DELETE_ERROR"), $ID);
				}

			break;
		}
		
		if(isset($return_url) && strlen($return_url)>0)
			LocalRedirect($return_url);
	}
	
}

$Messages = new COLLECTEDReviewsSubscribe();
$rsData = $Messages->GetList(array($by => $order), $arFilter);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SEARCH_PHL_PHRASES")));

$aContext=array();
$arElements = array();

$lAdmin->AddAdminContextMenu($aContext);

$arHeaders=array(
	array("id"=>"ID", "content"=>GetMessage("COLLECTED_REVIEW_SUBSCR_ID"), "sort"=>"ID", "default"=>false, "align"=>"right"),
	array("id"=>"DATE_CREATE", "content"=>GetMessage("COLLECTED_REVIEW_SUBSCR_DATE_CREATE"),"sort"=>"DATE_CREATE", "default"=>true),
	array("id"=>"EMAIL", "content"=>GetMessage("COLLECTED_REVIEW_SUBSCR_EMAIL"), "sort"=>"EMAIL", "default"=>true),
	array("id"=>"CONFIRMED", "content"=>GetMessage("COLLECTED_REVIEW_SUBSCR_CONFIRMED"), "sort"=>"CONFIRMED", "default"=>true),
	
	array("id"=>"IBLOCK_ID", "content"=>GetMessage("COLLECTED_REVIEW_SUBSCR_IBLOCK_ID"), "sort"=>"IBLOCK_ID", "default"=>false),
	array("id"=>"ELEMENT_ID", "content"=>GetMessage("COLLECTED_REVIEW_SUBSCR_ELEMENT_ID"), "sort"=>"ELEMENT_ID", "default"=>true),
	
	array("id"=>"SITE_ID", "content"=>GetMessage("COLLECTED_REVIEW_SUBSCR_SITE"), "sort"=>"SITE_ID", "default"=>false),
);

$lAdmin->AddHeaders($arHeaders);

while($arRes = $rsData->NavNext(true, "f_"))
{
	//заменяем данные
	$iblock  = '[<a target="_blank" href="/bitrix/admin/iblock_section_admin.php?IBLOCK_ID='.$f_IBLOCK_ID.'&type='.$arrIBlock[$f_IBLOCK_ID]['IBLOCK_TYPE_ID'].'&lang='.LANG.'&find_section_section=0">'.$f_IBLOCK_ID.'</a>] ';
	$iblock .= $arrIBlock[$f_IBLOCK_ID]['NAME'];
	
	if(!array_key_exists($f_ELEMENT_ID, $arElements))
	{
		//element search
		$arSelect = Array("ID", "NAME", "IBLOCK_ID", "IBLOCK_TYPE_ID");
		$arFilter = Array("ID"=>$f_ELEMENT_ID);
		$rsElement = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);

		if($arEl = $rsElement->GetNext())	
		{
			$arElements[$arEl['ID']] = $arEl;
		}
	}
		
	$f_ELEMENT_ID_text = '[<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$arElements[$f_ELEMENT_ID]['IBLOCK_ID'].'&type='.$arElements[$f_ELEMENT_ID]['IBLOCK_TYPE_ID'].'&ID='.$f_ELEMENT_ID.'&lang='.LANG.'&find_section_section=-1">'.$f_ELEMENT_ID.'</a>] '.TruncateText($arElements[$f_ELEMENT_ID]['NAME'],50);
	$f_DATE_CREATE = CDatabase::FormatDate($f_DATE_CREATE, "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("SHORT"));
	$f_CONFIRMED_text = $f_CONFIRMED == 'Y' ? 'Y' : '' ;
	
	//**************************************************************
	//строка
	//**************************************************************
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if($_REQUEST["mode"] != "excel")
		$row->AddViewField("DATE_CREATE", str_replace(" ", "&nbsp;", $f_DATE_CREATE));

	$row->AddViewField("IBLOCK_ID", $iblock);
	$row->AddViewField("ELEMENT_ID", $f_ELEMENT_ID_text);
	
	$row->AddInputField('EMAIL', array("size"=>25));
	$row->AddViewField("EMAIL", $f_EMAIL);
	
	$row->AddInputField('CONFIRMED', array("size"=>2));
	$row->AddViewField("CONFIRMED", $f_CONFIRMED_text);
	
	// сформируем контекстное меню
	$arActions = Array();

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

$lAdmin->AddFooter(array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	)
);

$arGroupActions = array();

$arGroupActions["delete"] = GetMessage("MAIN_ADMIN_LIST_DELETE");

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
		"find_ID" => GetMessage("COLLECTED_REVIEW_SUBSCR_ID"),
		"find_IBLOCK_ID" => GetMessage("COLLECTED_REVIEW_SUBSCR_IBLOCK_ID"),
		"find_ELEMENT_ID" => GetMessage("COLLECTED_REVIEW_SUBSCR_ELEMENT_ID"),
		"find_EMAIL" => GetMessage("COLLECTED_REVIEW_SUBSCR_EMAIL"),
		"find_CONFIRMED" => GetMessage("COLLECTED_REVIEW_SUBSCR_CONFIRMED"),
		"find_SITE_ID" => GetMessage("COLLECTED_REVIEW_SUBSCR_SITE"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SUBSCR_ID")?>:</b></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialchars($find_id)?>"></td>
</tr>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SUBSCR_IBLOCK_ID")?>:</b></td>
	<td>
	<select name="find_site_id" class="adm-select">
	<option value="">...</option>
	<?foreach($arrIBlock as $id => $iblock):?>
		<option value="<?=$id?>" <?if($id == $find_iblock_id):?>selected<?endif;?>>[<?=$id?>] <?=$iblock['NAME']?></option>
	<?endforeach;?>
	</select>
</tr>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SUBSCR_ELEMENT_ID")?>:</b></td>
	<td><input type="text" name="find_element_id" size="47" value="<?echo htmlspecialchars($find_element_id)?>"></td>
</tr>

<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SUBSCR_EMAIL")?>:</b></td>
	<td><input type="text" name="find_EMAIL" size="47" value="<?echo htmlspecialchars($find_EMAIL)?>"></td>
</tr>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SUBSCR_CONFIRMED")?>:</b></td>
	<td><input type="text" name="find_CONFIRMED" size="47" value="<?echo htmlspecialchars($find_CONFIRMED)?>"></td>
</tr>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SUBSCR_SITE")?>:</b></td>
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