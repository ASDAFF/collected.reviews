<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

$module_id = 'collected.reviews';

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/include.php");
//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/prolog.php");

IncludeModuleLangFile(__FILE__);

$Site = COLLECTEDReviews::GetSiteList();
$StatusList = COLLECTEDReviewsMessages::GetStatusList();
$UsePeriodList = COLLECTEDReviewsMessages::GetUsePeriodList();
$RatingList = COLLECTEDReviewsMessages::GetRatingList();

$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

GLOBAL $USER, $DB, $APPLICATION;

// сформируем список закладок
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("message_TAB_MAIN"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("message_TAB_MAIN_TITLE"));

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);		// идентификатор редактируемой записи
$message = null;		// сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.
$prop_filter = array();

$arSites = array();

$dbSites = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
while ($site = $dbSites->Fetch())
{
	$arSites[$site['ID']] = $site;
}	

	
//************************************************
// ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ                             		 |
//************************************************

if(
	$REQUEST_METHOD == "POST" // проверка метода вызова страницы
	&&
	($save!="" || $apply!="") // проверка нажатия кнопок "Сохранить" и "Применить"
	&&
	$POST_RIGHT=="W"          // проверка наличия прав на запись для модуля
	&&
	check_bitrix_sessid()     // проверка идентификатора сессии
)
	{
	
	$arFields = Array(
		"DATE_CREATE"		=> $DATE_CREATE,
		"STATUS"  			=> $STATUS,
		"DIGNITY"	  		=> $DIGNITY,
		"LIMITATIONS"    	=> $LIMITATIONS,
		"COMMENTS"    		=> $COMMENTS,
		"RATING"    		=> $RATING,
		"EXP_USING"    		=> $EXP_USING,
		"HELPFUL"			=> $HELPFUL,
		"USELESS"			=> $USELESS,
		"SUBSCRIBE"    		=> $SUBSCRIBE,
		"CITY"				=> $CITY,
		"USER_ID"    		=> intval($USER_ID),
		"USER_NAME"    		=> trim($USER_NAME),
		"USER_EMAIL"    	=> trim($USER_EMAIL),
		"SITE_ID"    		=> $SITE_ID,
	);
	
	if($ID == 0)
		$arFields["ELEMENT_ID"] = intval($ELEMENT_ID);

	$CMessageAdd = new COLLECTEDReviewsMessages();

	// сохранение данных
	if($ID > 0)
	{
		$res = $CMessageAdd->Update($ID, $arFields);
	}
	else
	{
		$ID = $CMessageAdd->Add($arFields);
		$res = ($ID > 0);
	}

	if($res)
	{
		// если сохранение прошло удачно - перенаправим на новую страницу 
		// (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
		if ($apply != "")
		{
			// если была нажата кнопка "Применить" - отправляем обратно на форму.
			LocalRedirect("/bitrix/admin/collected_reviews_messages_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
		}	
		else
		{
			// если была нажата кнопка "Сохранить" - отправляем к списку элементов.
			LocalRedirect("/bitrix/admin/collected_reviews_messages.php?lang=".LANG);
		}	
	}
	else
	{
		// если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("message_SAVE_ERROR"), $e);
			
		$bVarsFromForm = true;
	}
}

//************************************************
// ВЫБОРКА И ПОДГОТОВКА ДАННЫХ ФОРМЫ                     
//************************************************

// значения по умолчанию
$message_STATUS = "N";

$CMessage = new COLLECTEDReviewsMessages();

// выборка данных
if($ID>0)
{
	$rsMessage = $CMessage->GetByID($ID);
	if(!$rsMessage->ExtractFields("message_"))	
		$ID=0;
		
	$rsUser = CUser::GetByID($message_USER_ID);
	$arUser = $rsUser->Fetch();
}
else
{
	if( array_key_exists('ELEMENT_ID',$_REQUEST))
	$message_ELEMENT_ID = intval($_REQUEST['ELEMENT_ID']);
}

// если данные переданы из формы, инициализируем их
if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_collected_reviews_messages", "", "message_");


CModule::IncludeModule('iblock');

//**********************************************************
// поиск элемента
//**********************************************************
$arSelect = Array("ID", "NAME", "IBLOCK_ID", "IBLOCK_TYPE_ID");
$arFilter = Array("ID" => $message_ELEMENT_ID);
$rsElement = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
if($arEl = $rsElement->GetNext())	
	$arElement = $arEl;
else
	$arElement['NAME'] = GetMessage("message_ELEMENT_NF");

	
$element = '[ <a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$arElement['IBLOCK_ID'].'&type='.$arElement['IBLOCK_TYPE_ID'].'&ID='.$arElement['ID'].'&lang='.LANG.'&find_section_section=-1">'.$arElement['ID'].'</a> ] '.$arElement['NAME'];

//**********************************************************
// инфоблок
//**********************************************************
$arIBlock = array();
$arFilter = Array();

if($arElement['IBLOCK_ID'] > 0)
	$arFilter['ID'] = $arElement['IBLOCK_ID'];
else
	$arFilter['ID'] = $message_IBLOCK_ID;


$rsIB = CIBlock::GetList(
	Array(),
	$arFilter, 
	false
	);

if($arIB = $rsIB->GetNext())
{
	$arIBlock = $arIB;
	$iblock  = '[ <a target="_blank" href="/bitrix/admin/iblock_section_admin.php?IBLOCK_ID='.$arIB['ID'].'&type='.$arIB['IBLOCK_TYPE_ID'].'&lang='.LANG.'&find_section_section=0">'.$arIB['ID'].'</a> ] '.$arIB['NAME'];
}

if($arIBlock['ID'] != $message_IBLOCK_ID)
	$iblock .= ' <small>('.GetMessage("message_IBLOCK").'  <b>'.$message_IBLOCK_ID.'</b> '.GetMessage("message_IBLOCK_NF").')</small>';

//===============================================================================
// ВЫВОД ФОРМЫ                                           
//===============================================================================

// установим заголовок страницы
$APPLICATION->SetTitle(($ID>0? GetMessage("message_PAGE_TITLE").' #'.$ID : GetMessage("message_PAGE_TITLE_ADD")));

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// конфигурация административного меню
$aMenu = array(
	array(
		"TEXT"=>GetMessage("message_ACTION_LIST"),
		"TITLE"=>GetMessage("message_ACTION_LIST_TITLE"),
		"LINK"=>"collected_reviews_messages.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);

if($ID > 0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("message_MENU_ADD_NEW"),
		"LINK"=>"collected_reviews_messages_edit.php",
		"ICON"=>"btn_new",
	);

	//if(UserHasRightTo("delete"))
	//{
		$urlDelete  = '/bitrix/admin/collected_reviews_messages.php?action=delete';
		$urlDelete .= '&'.bitrix_sessid_get();
		$urlDelete .= '&ID='.$ID;
		
		$aMenu[] = array(
			"TEXT"=>GetMessage("message_MENU_DEL"),
			"LINK"=>"javascript:if(confirm('".GetMessage("message_MENU_DEL_CONF")."'))window.location='".CUtil::JSEscape($urlDelete)."';",
			"ICON"=>"btn_delete",
		);
	//}
}


// создание экземпляра класса административного меню
$context = new CAdminContextMenu($aMenu);

// вывод административного меню
$context->Show();

// если есть сообщения об ошибках или об успешном сохранении - выведем их.
if($_REQUEST["mess"] == "ok" && $ID>0)
  CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("message_SAVED"), "TYPE"=>"OK"));

if($message)
	echo $message->Show();
elseif($CMessageAdd->last_error)
	CAdminMessage::ShowMessage($CMessageAdd->last_error);
  
//===============================================================================
// выводим форму
//===============================================================================
?>
<style>
.disabled { display: none;}
</style>

<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>?ID=<?=$ID?>&lang=<?=LANG?>" ENCTYPE="multipart/form-data" name="post_form">
	<input type="hidden" name="lang" value="<?=LANG?>"/>
	<?
	// проверка идентификатора сессии 
	echo bitrix_sessid_post();
	
	$tabControl->Begin();
	//************************************************
	// первая закладка - форма редактирования параметров рассылки
	//************************************************
	$tabControl->BeginNextTab();
	?>
	<?if(intval($ID) && !$bCopy):?>
		<tr>
			<td><?echo GetMessage("message_ID")?>:</td>
			<td><?=$message_ID?><input type="hidden" name="ID" value="<?=$message_ID?>"></td>
		</tr>
		<tr>
			<td><?echo GetMessage("message_DATE_CREATE_REAL")?>:</td>
			<td><?=$message_DATE_CREATE_REAL;?></td>
		</tr>
		<tr>
			<td><?echo GetMessage("message_DATE_CREATE")?>:</td>
			<td><?echo CalendarDate("DATE_CREATE", htmlspecialchars($message_DATE_CREATE), "message_DATE_CREATE", 20)?></td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("message_IBLOCK_ID")?>:</td>
			<td><?=$iblock?></td>
		</tr>
	<?endif;?>

	<tr>
		<td><span class="adm-required-field"><?echo GetMessage("message_ELEMENT_ID")?>:</span></td>
		<td>
		<?if(intval($ID) && !$bCopy):?>	
		<?=$element;?>
		<?else:?>
		<input type="text" name="ELEMENT_ID" value="<?=$message_ELEMENT_ID?>" size="50" maxlength="11">
		<?endif;?>
		</td>
	</tr>

	<tr>
		<td><span class="adm-required-field"><?echo GetMessage("message_STATUS")?>:</span></td>
		<td width="70%" class="adm-detail-content-cell-r">
			<select name="STATUS" class="select">
			<?foreach($StatusList as $id => $text):?>
				<option value="<?=$id?>" <?if($id==$message_STATUS):?>selected<?endif;?>><?=$text?></option>
			<?endforeach;?>
			</select>
		</td>
	</tr>
	
	<tr>
		<td><?echo GetMessage("message_DIGNITY")?>:</td>
		<td><textarea class="typearea" wrap="virtual" style="width:100%;height:100px;" name="DIGNITY"><?=$message_DIGNITY?></textarea></td>
	</tr>
	<tr>
		<td><?echo GetMessage("message_LIMITATIONS")?>:</td>
		<td><textarea class="typearea" wrap="virtual" style="width:100%;height:100px;" name="LIMITATIONS"><?=$message_LIMITATIONS?></textarea></td>
	</tr>
	<tr>
		<td><?echo GetMessage("message_COMMENTS")?>:</td>
		<td><textarea class="typearea" wrap="virtual" style="width:100%;height:100px;" name="COMMENTS"><?=$message_COMMENTS?></textarea></td>
	</tr>
	<tr>
		<td><?echo GetMessage("message_RATING")?>:</td>
		<td class="adm-detail-content-cell-r">
			<select name="RATING" class="select">
			<?foreach($RatingList as $id => $text):?>
				<option value="<?=$id?>" <?if($id==$message_RATING):?>selected<?endif;?>><?=$text?></option>
			<?endforeach;?>
			</select>
		</td>
	</tr>
	
	
	<tr>
		<td><?echo GetMessage("message_EXP_USING")?>:</td>
		<td class="adm-detail-content-cell-r">
			<select name="EXP_USING" class="select">
			<?foreach($UsePeriodList as $id => $text):?>
				<option value="<?=$id?>" <?if($id==$message_EXP_USING):?>selected<?endif;?>><?=$text?></option>
			<?endforeach;?>
			</select>
		</td>
	</tr>
	
	<?if(intval($ID) && !$bCopy):?>
	<tr> 
		<td><?=GetMessage("message_HELPFUL")?>:</td>
		<td><input type="text" name="HELPFUL" value="<?=intval($message_HELPFUL);?>" size="5" maxlength="11"> <?=GetMessage("USERS")?></td> 
	</tr>
	<tr>
		<td><?=GetMessage("message_USELESS")?>:</td>
		<td><input type="text" name="USELESS" value="<?=intval($message_USELESS);?>" size="5" maxlength="11"> <?=GetMessage("USERS")?></td> 
	</tr>
	<?endif;?>
	
	<tr class='heading'>
		<td align='center' colspan='2' nowrap><?echo GetMessage("USER_INFO")?></td>
	</tr>
	<tr>
		<td><?=GetMessage("message_USER_ID")?>:</td>
		<td>
			<input type="text" name="USER_ID" value="<?= ($message_USER_ID) ? $message_USER_ID : '';?>" size="10" maxlength="11">
			<?if($message_USER_ID > 0):?>
			<span id="USER_ID_info">
			<a href="/bitrix/admin/user_edit.php?lang=<?=LANG?>&ID=<?=$arUser['ID']?>" target="_blank">
			<?=$arUser['LAST_NAME'] ? $arUser['LAST_NAME'] : $arUser['LOGIN'] ;?>
			</a>
			</span>
			<?endif;?>
		</td>
	</tr>
	
	<tr>
		<td><?=GetMessage("message_USER_NAME")?>:</td>
		<td><input type="text" name="USER_NAME" value="<?=$message_USER_NAME?>" size="50" maxlength="255"></td>
	</tr>
	<tr>
		<td><?=GetMessage("message_USER_EMAIL")?>:</td>
		<td><input type="text" name="USER_EMAIL" value="<?=$message_USER_EMAIL?>" size="50" maxlength="255"></td>
	</tr>
	
	<tr>
		<td><?=GetMessage("message_CITY")?>:</td>
		<td><input type="text" name="CITY" value="<?=$message_CITY?>" size="50" maxlength="255"></td>
	</tr>

	<?if(intval($ID) && !$bCopy):?>
	<tr>
		<td><?echo GetMessage("message_IP_ADDRESS")?>:</td>
		<td><?=$message_IP_ADDRESS?></td>
	</tr>
	<?endif;?>
	
	<tr>
		<td><?=GetMessage("message_SITE_ID")?>:</td>
		<td class="adm-detail-content-cell-r">
			<select name="SITE_ID" class="select">
			<?foreach($arSites as $id => $site):?>
				<option value="<?=$site['ID']?>" <?if($site['ID']==$message_SITE_ID):?>selected<?endif;?>><?=$site['NAME']?></option>
			<?endforeach;?>
			</select>
		</td>
	</tr>

	<?
	//************************************************
	// завершение формы - вывод кнопок сохранения изменений
	//************************************************
	$tabControl->Buttons(
		array(
			"disabled"=>($POST_RIGHT<"W"),
			"back_url"=>"collected_reviews_messages.php?lang=".LANG,
		)
	);
	?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<?if($ID>0 && !$bCopy):?>
		<input type="hidden" name="ID" value="<?=$ID?>">
	<?endif;?>
	<?
	// завершаем интерфейс закладок
	$tabControl->End();
	
	// дополнительное уведомление об ошибках - вывод иконки около поля, в котором возникла ошибка
	$tabControl->ShowWarnings("post_form", $message);?>
</form>
	
<?
// завершение страницы
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>