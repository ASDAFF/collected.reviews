<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

$module_id = 'collected.reviews';

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/include.php");

IncludeModuleLangFile(__FILE__);

$arSite = COLLECTEDReviews::GetSiteList();

$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
$sTableID = "tbl_collected_reviews_stoplist";
$oSort = new CAdminSorting($sTableID, "ID", "DATE_ACTIVE_TO");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
	"find_ip",
	"find_user_id",
	"find_site_id",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	'IP_ADDRESS' => $find_ip,
	'USER_ID' => $find_user_id,
	'SITE_ID' => $find_site_id,
	);

foreach($arFilter as $key => $value)
	if(!strlen($value))
		unset($arFilter[$key]);
?>

<?

// ******************************************************************** //
// обработка одиночных и групповых действий
// ******************************************************************** //
if(($arID = $lAdmin->GroupAction()))
{
	// дествие для всех
	if($_REQUEST['action_target']=='selected')
	{
		$StopList = new COLLECTEDReviewsStopList();
		$rsData = $StopList->GetList(array($by => $order), $arFilter);

		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}
	
	
	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;

		$StopList = new COLLECTEDReviewsStopList();
		
		$ID = IntVal($ID);
		
		switch($_REQUEST['action'])
		{
			case "block":
			case "unblock":

					$APPLICATION->ResetException();
					$block = $_REQUEST['action'] == "block" ? true : false;

					if(!$StopList->ChangeBlock($ID, $block))
					{
						$mess = $block ? GetMessage("REVIEW_BLOCK_ERROR") : GetMessage("REVIEW_UNBLOCK_ERROR") ;

						if($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError($mess." [".$ex->GetString()."]", $ID);
						else
							$lAdmin->AddGroupError($mess, $ID);
					}
					

				break;

		}
		
		if(isset($return_url) && strlen($return_url)>0)
			LocalRedirect($return_url);
	}
	
}

$StopList = new COLLECTEDReviewsStopList();
$rsData = $StopList->GetList(array($by => $order), $arFilter);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SEARCH_PHL_PHRASES")));

$aContext=array();

$lAdmin->AddAdminContextMenu($aContext);

$arHeaders=array(
	array("id"=>"IP_ADDRESS", "content"=>GetMessage("COLLECTED_REVIEW_SL_IP_ADDRESS"), "sort"=>"IP_ADDRESS", "default"=>true),
	array("id"=>"STATUS", "content"=>GetMessage("COLLECTED_REVIEW_SL_STATUS"), "sort"=>"", "default"=>true),
	array("id"=>"DATE_CREATE", "content"=>GetMessage("COLLECTED_REVIEW_SL_DATE_CREATE"),"sort"=>"DATE_CREATE", "default"=>true),
	array("id"=>"DATE_ACTIVE_TO", "content"=>GetMessage("COLLECTED_REVIEW_SL_DATE_ACTIVE_TO"), "sort"=>"DATE_ACTIVE_TO", "default"=>true),

	array("id"=>"USER_ID", "content"=>GetMessage("COLLECTED_REVIEW_SL_USER"), "sort"=>"USER_ID", "default"=>false),
	array("id"=>"SITE_ID", "content"=>GetMessage("COLLECTED_REVIEW_SL_SITE"), "sort"=>"SITE_ID", "default"=>false),
	array("id"=>"EXCEP", "content"=>GetMessage("COLLECTED_REVIEW_SL_EXCEP"), "sort"=>"EXCEP", "default"=>false),
);

$lAdmin->AddHeaders($arHeaders);

$i=0;
$PRICE_TOTAL = 0;

$arUsers = array();
$arElements = array();

global $DB;
$format = CSite::GetDateFormat("FULL");


while($arRes = $rsData->NavNext(true, "f_"))
{
	$result = $DB->CompareDates(date($DB->DateFormatToPHP($format)), $f_DATE_ACTIVE_TO);
	$BLOCKED = $result == -1 ? true : false ;
	
	$f_STATUS_text = '<div class="status_block">';
	$f_STATUS_text .= '<div></div>';
	$f_STATUS_text .= GetMessage("COLLECTED_REVIEW_SL_STATUS_BLOCK");
	$f_STATUS_text .= '</div>';

	$row =& $lAdmin->AddRow($f_ID, $arRes);
	
	if($BLOCKED)
		$row->AddViewField("STATUS", $f_STATUS_text);

		
	// сформируем контекстное меню
	$arActions = Array();
	/*
	if($BLOCKED)
	{

		// редактирование элемента
		$arActions[] = array(
			"ICON"=>"unblock",
			"DEFAULT"=>true,
			"TEXT"=>GetMessage("REVIEW_ACTION_UNBLOCK"),
			"ACTION"=>$lAdmin->ActionRedirect("collected_reviews_stoplist.php?ID=".$f_ID)
			);

	} else {

		$arActions[] = array(
			"ICON"=>"block",
			"DEFAULT"=>true,
			"TEXT"=>GetMessage("REVIEW_ACTION_BLOCK"),
			"ACTION"=>$lAdmin->ActionRedirect("collected_reviews_stoplist.php?ID=".$f_ID)
			);

	}

	// применим контекстное меню к строке
	$row->AddActions($arActions);
	*/
}


$lAdmin->AddFooter(array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	)
);


$arGroupActions = array();

$arGroupActions["block"] = GetMessage("REVIEW_ACTION_BLOCK");
$arGroupActions["unblock"] = GetMessage("REVIEW_ACTION_UNBLOCK");


$lAdmin->AddGroupActionTable($arGroupActions, $arParams);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("COLLECTED_REVIEW_STOPLIST_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ip" => GetMessage("COLLECTED_REVIEW_SL_IP_ADDRESS"),
		"find_user_id" => GetMessage("COLLECTED_REVIEW_SL_USER"),
		"find_site_id" => GetMessage("COLLECTED_REVIEW_SL_SITE"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SL_IP_ADDRESS")?>:</b></td>
	<td><input type="text" name="find_ip" size="20" value="<?echo htmlspecialchars($find_ip)?>"></td>
</tr>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SL_USER")?>:</b></td>
	<td><input type="text" name="find_user_id" size="12" value="<?echo htmlspecialchars($find_user_id)?>"></td>
</tr>
<tr>
	<td nowrap><b><?echo GetMessage("COLLECTED_REVIEW_SL_SITE")?>:</b></td>
	<td>
	<select name="find_site_id" class="adm-select">
	<?foreach($arSite as $id => $Site):?>
		<option value="<?=$id?>" <?if($id == $find_site_id):?>selected<?endif;?>><?=$Site['NAME']?></option>
	<?endforeach;?>
	</select>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()));
$oFilter->End();
?>
</form>

<style>
.status_block {
	
	}
.status_block div{
	display: block;
	background-color: #f00 !important;
	width: 12px;
	height: 12px;
	float: left;
	margin: 2px 10px 0 0;
	}
</style>
<?

$lAdmin->DisplayList();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>