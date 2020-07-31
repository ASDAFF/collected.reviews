<?
/**
 * Copyright (c) 31/7/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Модуль \"Отзывы\"");
?> 
<p>Основные возможности модуля:</p>
 
<ul> 	 	
  <li>настроить коментарии к любым элементам, хранящихся в инфоблоках</li>
 	
  <li>для хранения отзывов не использует инфоблоки</li>
 	
  <li>отправка отзыва и подгрузка отзывов без перезагрузки страницы</li>
 	
  <li>подписка на новые отзывы</li>
 	
  <li>настройка отображаемых и обязательных полей ввода, автоматическое определение города в форме добавления отзыва</li>
 	
  <li>уведомление о поступлении нового отзыва для администратора</li>
 	
  <li>возможность премодерации отзывов</li>
 </ul>
 
<br />
 
<p>Модуль содержит 5 компонентов:</p>
 
<ul>	 	
  <li>reviews.add - форма добавления нового отзыва</li>
 	
  <li>reviews.list - список отзывов</li>
 	
  <li>reviews.rating - отображение рейтинга элемента каталога в виде пиктограмы</li>
 	
  <li>reviews.subscribe - подписка на обновление отзывов для элемента каталога</li>
 	
  <li>reviews.subscribe.edit - управление подпиской (потдверждение и отписка)</li>
 </ul>
 
<br />
 
<br />
 <b>Документация и описание подключения компонентов к каталогу
  <br />
на нашем сайте <a target="_blank" href="http://asdaff.github.io/marketplace/reviews/?from=demo" >http://asdaff.github.io/marketplace/reviews/</a> </b>
<br />
 
<br />
 
<br />
 

<?
$ELEMENT_ID = 0;
$ELEMENT_NAME = '';

if(CModule::IncludeModule('iblock'))
{
	$arSelect = Array("ID", "NAME", "IBLOCK_ID");
	$arFilter = Array(
		"ACTIVE_DATE" => "Y", 
		"ACTIVE" => "Y"
		);
	
	$res = CIBlockElement::GetList(Array('ID' => 'ASC'), $arFilter, false, Array("nPageSize"=>1), $arSelect);
	if($arElement = $res->GetNext())
	{
		$ELEMENT_ID = $arElement['ID'];
		$ELEMENT_NAME = $arElement['NAME'];
	}
}
?>

<?if($ELEMENT_ID > 0):?>
<p style="color: red;">Демонстрация работы компонентов модуля для элемента инфоблока "<?=$ELEMENT_NAME?>" (ID = <?=$ELEMENT_ID?>) .</p>
<?else:?>
<p style="color: red;">Для демонстрации работы перейдите в режим редактирования страницы и укажите ID элемента инфоблока в настройках компонентов.</p>
<?endif;?>
<br />
<br />


 <?$APPLICATION->IncludeComponent(
	"collected:reviews.add",
	".default",
	Array(
		"ELEMENT_ID" => $ELEMENT_ID,
		"MESS_OK" => "",
		"FIELDS" => array(0=>"USER_NAME",1=>"USER_EMAIL",2=>"CITY",3=>"DIGNITY",4=>"LIMITATIONS",5=>"COMMENTS",6=>"RATING",7=>"EXP_USING",),
		"FIELDS_REQUIRED" => array(0=>"USER_NAME",1=>"USER_EMAIL",2=>"DIGNITY",3=>"LIMITATIONS",4=>"RATING",5=>"EXP_USING",),
		"USE_GEO" => "Y",
		"REVIEW_FULL" => "Y",
		"NEED_AUTH" => "N",
		"USE_CAPTCHA" => "Y"
	)
);?>   
<br />

<br />
 <?$APPLICATION->IncludeComponent(
	"collected:reviews.rating",
	".default",
	Array(
		"ELEMENT_ID" => $ELEMENT_ID,
		"RATING_SHORT_TEMPLATE" => "N",
		"CACHE_TYPE" => "N", //для демонстрации
		"CACHE_TIME" => "3600"
	)
);?>  
<br />

<br />
 <?$APPLICATION->IncludeComponent(
	"collected:reviews.list",
	".default",
	Array(
		"ELEMENT_ID" => $ELEMENT_ID,
		"REVIEWS_COUNT" => "2",
		"CACHE_TYPE" => "N",  //для демонстрации
		"CACHE_TIME" => "3600",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"PAGER_TITLE" => "Страница",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "",
		"PAGER_SHOW_ALL" => "N",
		"SHOW_RATING" => "Y",
		"SHOW_RATING_TOTAL" => "N",
		"RATING_SHORT_TEMPLATE" => "N",
		"REVIEW_FULL" => "Y"
	)
);?> 
<br />

<br />

<br />
 <b>Документация и описание подключения компонентов к каталогу
  <br />
на нашем сайте <a target="_blank" href="http://asdaff.github.io/marketplace/reviews/?from=demo" >http://asdaff.github.io/marketplace/reviews/</a> </b>
<br />

<br />
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>