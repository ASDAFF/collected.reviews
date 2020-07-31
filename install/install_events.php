<?
//====================================================
// create review
//====================================================
$dbEvent = CEventMessage::GetList($b="ID", $order="ASC", array("EVENT_NAME" => "COLLECTED_REVIEWS_CREATE"));
if(!($arEvent = $dbEvent->Fetch()))
{
	//if not found - crete mail event
	$langs = CLanguage::GetList(($b=""), ($o=""));

	while($lang = $langs->Fetch())
	{
		IncludeModuleLangFile(__FILE__, $lang["LID"]);

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lang["LID"],
			"EVENT_NAME" => "COLLECTED_REVIEWS_CREATE",
			"NAME" => GetMessage("COLLECTED_REVIEWS_EVENT_CREATE_NAME"),
			"DESCRIPTION" => GetMessage("COLLECTED_REVIEWS_EVENT_CREATE_DESC"),
		));
	}

	//site list
	$arSites = array();
	$sites = CSite::GetList(($b=""), ($o=""), array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site;

	if(count($arSites) > 0)
	{
		foreach($arSites as $site)
		{
			IncludeModuleLangFile(__FILE__, $site["LANGUAGE_ID"]);
			
			//list property iblock
			$emess = new CEventMessage;

			$emess->Add(array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "COLLECTED_REVIEWS_CREATE",
				"LID" => $site["LID"],
				"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
				"EMAIL_TO" => "#MODERATOR_EMAIL#",
				"SUBJECT" => GetMessage("COLLECTED_REVIEWS_EVENT_CREATE_SUBJECT"),
				"MESSAGE" => GetMessage("COLLECTED_REVIEWS_EVENT_CREATE_MESSAGE"),
				"BODY_TYPE" => "text",
			));
		}
	}
}

//====================================================
//confirm review
//====================================================
$dbEvent = CEventMessage::GetList($b="ID", $order="ASC", array("EVENT_NAME" => "COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM"));
if(!($arEvent = $dbEvent->Fetch()))
{
	//if not found - crete mail event
	$langs = CLanguage::GetList(($b=""), ($o=""));

	while($lang = $langs->Fetch())
	{
		IncludeModuleLangFile(__FILE__, $lang["LID"]);

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lang["LID"],
			"EVENT_NAME" => "COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM",
			"NAME" => GetMessage("COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM_NAME"),
			"DESCRIPTION" => GetMessage("COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM_DESC"),
		));
	}

	//site list
	$arSites = array();
	$sites = CSite::GetList(($b=""), ($o=""), array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site;

	if(count($arSites) > 0)
	{
		foreach($arSites as $site)
		{
			IncludeModuleLangFile(__FILE__, $site["LANGUAGE_ID"]);
			
			//list property iblock
			$emess = new CEventMessage;

			$emess->Add(array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM",
				"LID" => $site["LID"],
				"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
				"EMAIL_TO" => "#EMAIL#",
				"SUBJECT" => GetMessage("COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM_SUBJECT"),
				"MESSAGE" => GetMessage("COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM_MESSAGE"),
				"BODY_TYPE" => "text",
			));
		}
	}
}



//====================================================
//subscribe review
//====================================================
$dbEvent = CEventMessage::GetList($b="ID", $order="ASC", array("EVENT_NAME" => "COLLECTED_REVIEWS_SUBSCRIBE"));
if(!($arEvent = $dbEvent->Fetch()))
{
	//if not found - crete mail event
	$langs = CLanguage::GetList(($b=""), ($o=""));

	while($lang = $langs->Fetch())
	{
		IncludeModuleLangFile(__FILE__, $lang["LID"]);

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lang["LID"],
			"EVENT_NAME" => "COLLECTED_REVIEWS_SUBSCRIBE",
			"NAME" => GetMessage("COLLECTED_REVIEWS_EVENT_SUBSCRIBE_NAME"),
			"DESCRIPTION" => GetMessage("COLLECTED_REVIEWS_EVENT_SUBSCRIBE_DESC"),
		));
	}

	//site list
	$arSites = array();
	$sites = CSite::GetList(($b=""), ($o=""), array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site;

	if(count($arSites) > 0)
	{
		foreach($arSites as $site)
		{
			IncludeModuleLangFile(__FILE__, $site["LANGUAGE_ID"]);
			
			//list property iblock
			$emess = new CEventMessage;

			$emess->Add(array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "COLLECTED_REVIEWS_SUBSCRIBE",
				"LID" => $site["LID"],
				"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
				"EMAIL_TO" => "#EMAIL#",
				"SUBJECT" => GetMessage("COLLECTED_REVIEWS_EVENT_SUBSCRIBE_SUBJECT"),
				"MESSAGE" => GetMessage("COLLECTED_REVIEWS_EVENT_SUBSCRIBE_MESSAGE"),
				"BODY_TYPE" => "text",
			));
		}
	}
}

//====================================================
//COLLECTED_REVIEWS_SUBSCRIBE_EDIT
//====================================================
$dbEvent = CEventMessage::GetList($b="ID", $order="ASC", array("EVENT_NAME" => "COLLECTED_REVIEWS_SUBSCRIBE_EDIT"));
if(!($arEvent = $dbEvent->Fetch()))
{
	//if not found - crete mail event
	$langs = CLanguage::GetList(($b=""), ($o=""));

	while($lang = $langs->Fetch())
	{
		IncludeModuleLangFile(__FILE__, $lang["LID"]);

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lang["LID"],
			"EVENT_NAME" => "COLLECTED_REVIEWS_SUBSCRIBE_EDIT",
			"NAME" => GetMessage("COLLECTED_REVIEWS_EVENT_SUBSCRIBE_EDIT_NAME"),
			"DESCRIPTION" => GetMessage("COLLECTED_REVIEWS_EVENT_SUBSCRIBE_EDIT_DESC"),
		));
	}

	//site list
	$arSites = array();
	$sites = CSite::GetList(($b=""), ($o=""), array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site;

	if(count($arSites) > 0)
	{
		foreach($arSites as $site)
		{
			IncludeModuleLangFile(__FILE__, $site["LANGUAGE_ID"]);
			
			//list property iblock
			$emess = new CEventMessage;

			$emess->Add(array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "COLLECTED_REVIEWS_SUBSCRIBE_EDIT",
				"LID" => $site["LID"],
				"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
				"EMAIL_TO" => "#EMAIL#",
				"SUBJECT" => GetMessage("COLLECTED_REVIEWS_EVENT_SUBSCRIBE_EDIT_SUBJECT"),
				"MESSAGE" => GetMessage("COLLECTED_REVIEWS_EVENT_SUBSCRIBE_EDIT_MESSAGE"),
				"BODY_TYPE" => "text",
			));
		}
	}
}
?>