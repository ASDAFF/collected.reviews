<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<?
if(!CModule::IncludeModule("iblock"))
	return false;
	
if(!CModule::IncludeModule("collected.reviews"))
	return false;

	
$StatusList = COLLECTEDReviewsMessages::GetStatusList();
$RatingList = COLLECTEDReviewsMessages::GetRatingList();
$ExpUsingList = COLLECTEDReviewsMessages::GetUsePeriodList();

if (!function_exists('__GD_AIE_ConvertDateTime'))
{
	function __GD_AIE_ConvertDateTime(&$item, $key)
	{
		$item = ToLower(FormatDate("j F Y", MakeTimeStamp($item)));
	}
}

if (
	intval($arGadgetParams["ITEMS_COUNT"]) < 1
	|| intval($arGadgetParams["ITEMS_COUNT"]) > 20
)
	$arGadgetParams["ITEMS_COUNT"] = 5;

if (
	intval($arGadgetParams["TEXT_CUT"]) < 50
	|| intval($arGadgetParams["TEXT_CUT"]) > 5000
)
	$arGadgetParams["TEXT_CUT"] = 200;

if (!is_array($arGadgetParams["FIELDS"]) || count($arGadgetParams["FIELDS"]) <= 0)
	$arGadgetParams["FIELDS"] = array();

array_unshift($arGadgetParams["FIELDS"], 'STATUS');



if (
	strlen($arGadgetParams["IBLOCK_TYPE"]) >= 0
	&& intval($arGadgetParams["IBLOCK_ID"]) > 0
)
{
	$dbIBlock = CIBlock::GetList(
		Array(),
		Array(
			"TYPE" => $arGadgetParams["IBLOCK_TYPE"],
			"ID" => $arGadgetParams["IBLOCK_ID"],
			"CHECK_PERMISSIONS" => "Y",
			"MIN_PERMISSION" => (IsModuleInstalled("workflow")?"U":"W")
		)
	);
	if($arIBlock = $dbIBlock->GetNext())
	{
		if (strlen($arGadgetParams["TITLE_STD"]) <= 0)
			$arGadget["TITLE"] = GetMessage("GD_COLLECTED_REVIEW_TITILE").': '.$arIBlock["NAME"];

		$Messages = new COLLECTEDReviewsMessages();
		
		$arSort = array('DATE_CREATE' => 'DESC');
		$arFilter = array(
			"IBLOCK_ID" => $arGadgetParams["IBLOCK_ID"],
			//'STATUS' => 'A',									
			);

		$rsMessages = $Messages->GetList($arSort, $arFilter, $arGadgetParams["ITEMS_COUNT"]);
		//$rsMessages->NavStart($arGadgetParams["ITEMS_COUNT"]);
		?>
		<div class="bx-gadgets-text" style="clear: both; padding: 0 0 10px 0;">
		<table class="data-table" width="100%">
		<?
		$arElements = array();
		while($arMessages = $rsMessages->GetNext())
		{
			
			
			
			if($arMessages['ELEMENT_ID'] > 0)
			{
				if(array_key_exists($arMessages['ELEMENT_ID'], $arElements))
				{
					$arMessages['ELEMENT_ID_ARR'] = $arElements[$arMessages['ELEMENT_ID']];
				}
				else
				{
					$arSelect = Array("ID", "NAME", "IBLOCK_ID", "IBLOCK_TYPE_ID", "IBLOCK_SECTION_ID", "DETAIL_PAGE_URL");
					$arFilter = Array("ID"=>$arMessages['ELEMENT_ID']);
					$rsElement = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
					if($arEl = $rsElement->GetNext())	
					{
						$arEl['URL'] = '/bitrix/admin/iblock_element_edit.php?ID='.$arEl['ID'].'&type='.$arEl['IBLOCK_TYPE_ID'].'&lang=ru&IBLOCK_ID='.$arEl['IBLOCK_ID'].'&find_section_section='.$arEl['IBLOCK_SECTION_ID'];
						
						$arElements[$arEl['ID']] = $arEl;
						$arMessages['ELEMENT_ID_ARR'] = $arEl;
					}
				}
			}
			?>
			
			<tr>
				<td colspan=2>
					<span class="bx-gadget-gray">
					<a href="/bitrix/admin/collected_reviews_messages_edit.php?ID=<?=$arMessages["ID"]?>&lang=<?=LANGUAGE_ID?>"><?=GetMessage("GD_COLLECTED_REVIEW_T")?> <?=$arMessages['ID']?></a> <?=GetMessage("GD_COLLECTED_REVIEW_OT")?> <?=$arMessages['DATE_CREATE']?>
					</span>
				</td>
			</tr>
			<tr>
				<td width="25%"><?=GetMessage("GD_COLLECTED_REVIEW_ELEMENT")?>:</td>
				<td><a target="_blank" href="<?=$arMessages['ELEMENT_ID_ARR']['URL'];?>"><?=$arMessages['ELEMENT_ID_ARR']['NAME'];?></a></td>
			</tr>
		
			<?foreach($arGadgetParams["FIELDS"] as $field):?>
			<tr>
				<td><?=GetMessage("GD_COLLECTED_REVIEW_".$field)?>:</td>
				<td>
					<?switch($field)
					{
						case 'STATUS':
							echo '<b>'.$StatusList[$arMessages[$field]].'</b>';
						break;
						
						case 'RATING':
							echo $RatingList[$arMessages[$field]];
						break;
						
						case 'EXP_USING':
							echo $ExpUsingList[$arMessages[$field]];
						break;
						
						default:
							if (strlen($arMessages[$field]) > 0)
								echo substr(htmlspecialcharsbx($arMessages[$field]), 0, $arGadgetParams["TEXT_CUT"]);
						break;
					}
					?>
				</td>
			</tr>
			<?endforeach;?>
			</tr>
			<td colspan="2">
			<div style="margin: 10px 1px 10px 1px; border-bottom: 1px solid #D7E0E8;"></div>
			</td>
			</tr>
			
			<?
		}
		?>
		</table>
		</div>
		<?

		$urlReviewsPage = '/bitrix/admin/collected_reviews_messages.php?IBLOCK_ID='.$arIBlock["ID"].'&lang='.LANGUAGE_ID;
		?>
		<div><a href="<?=$urlReviewsPage?>"><?=GetMessage("GD_COLLECTED_REVIEW_ALL_REVIEWS")?></a></div>
		<?
	}
}
?>