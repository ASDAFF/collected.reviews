<?
$eventName = array(
	'COLLECTED_REVIEWS_CREATE',
	'COLLECTED_REVIEWS_SUBSCRIBE',
	'COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM',
	'COLLECTED_REVIEWS_SUBSCRIBE_EDIT'
	);

foreach($eventName as $event)
{
	$eventType = new CEventType;
	$eventType->Delete($event);

	$eventM = new CEventMessage;
	$dbEvent = CEventMessage::GetList($b="ID", $order="ASC", Array("EVENT_NAME" => $event));
	while($arEvent = $dbEvent->Fetch())
	{
		$eventM->Delete($arEvent["ID"]);
	}
}
?>