<?

//events

$MESS["COLLECTED_REVIEWS_EVENT_CREATE_NAME"] = "Добавление нового отзыва";
$MESS["COLLECTED_REVIEWS_EVENT_CREATE_DESC"] = "
#ID# - номер отзыва
#DATE_CREATE# - время создания отзыва
#ELEMENT_NAME# - название элемента
#DIGNITY# - достоинства
#LIMITATIONS# - недостатки
#COMMENTS# - комментарии
#RATING# - рейтинг
#EXP_USING# - период использования
#USER_NAME# -  имя пользователя
#USER_EMAIL# - email пользователя

#MODERATOR_EMAIL# - email модератора
";

$MESS["COLLECTED_REVIEWS_EVENT_CREATE_SUBJECT"] = "#SITE_NAME#: Добавлен новый отзыв №#ID#";
$MESS["COLLECTED_REVIEWS_EVENT_CREATE_MESSAGE"] = "
На сайте #SITE_NAME# был добавлен новый отзыв
для #ELEMENT_NAME#

Отзыву присвоен номер #ID# от #DATE_CREATE#

Достоинства:
#DIGNITY#
Недостатки:
#LIMITATIONS#
Комментарии:
#COMMENTS#


Рейтинг: #RATING#
Имя: #USER_NAME#
Email: #USER_EMAIL#

Страница товара: http://#SERVER_NAME##DETAIL_PAGE#
	
----------------------
сообщение сгенерированно автоматически

";



//subscribe confirm

$MESS["COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM_NAME"] = "Подтверждение подписки на отзывы";
$MESS["COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM_DESC"] = "
#ID# - номер отзыва
#DATE_CREATE# - время создания отзыва
#ELEMENT_NAME# - название элемента
#ELEMENT_PAGE# - страница элемента

#SUBSCRIBE_CONFIRM_PAGE# - страница подтверждения подписки
";

$MESS["COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM_SUBJECT"] = "#SITE_NAME#: Подтверждение подписки на отзывы";
$MESS["COLLECTED_REVIEWS_SUBSCRIBE_CONFIRM_MESSAGE"] = "
#SITE_NAME#

Вы подписались на новые отзывы для 
#ELEMENT_NAME#
Страница товара: http://#SERVER_NAME##ELEMENT_PAGE#

Для подтверждения подписки перейдите по адресу: 
http://#SERVER_NAME##SUBSCRIBE_CONFIRM_PAGE#
	
----------------------
сообщение сгенерированно автоматически

";


//subscribe

$MESS["COLLECTED_REVIEWS_EVENT_SUBSCRIBE_NAME"] = "Рассылка уведомлений о новом отзыве";
$MESS["COLLECTED_REVIEWS_EVENT_SUBSCRIBE_DESC"] = "
#ID# - номер отзыва
#DATE_CREATE# - дата создания
#ELEMENT_NAME# - название элемента
#DIGNITY# - достоинства
#LIMITATIONS# - недостатки
#COMMENTS# - комментарии
#RATING# - рейтинг
#EXP_USING# - период использования
#DETAIL_PAGE# - URL страницы элемента
#SUBSCRIBE_REMOVE_URL# - URL страницы для удаление подписки
";

$MESS["COLLECTED_REVIEWS_EVENT_SUBSCRIBE_SUBJECT"] = "#SITE_NAME#: Добавлен новый отзыв о #ELEMENT_NAME#";
$MESS["COLLECTED_REVIEWS_EVENT_SUBSCRIBE_MESSAGE"] = "
Уважаемый пользователь!

На сайте #SITE_NAME# был добавлен новый отзыв 
для #ELEMENT_NAME#

Прочитать отзыв можно на странице товара: http://#SERVER_NAME##DETAIL_PAGE#
	
----------------------------------------------------
Вы получаете это сообщение т.к. подписаны на обнвления 
отзывов для #ELEMENT_NAME#.

Отписаться от рассылки http://#SERVER_NAME##SUBSCRIBE_REMOVE_URL#

----------------------------------------------------
сообщение сгенерированно автоматически
";



//edit
//***************************************************


$MESS["COLLECTED_REVIEWS_EVENT_SUBSCRIBE_EDIT_NAME"] = "Информация о подписке";
$MESS["COLLECTED_REVIEWS_EVENT_SUBSCRIBE_EDIT_DESC"] = "
#ID# - номер отзыва
#DATE_CREATE# - дата создания
#SUBSCRIBE_INFO# - информация о подписках
";

$MESS["COLLECTED_REVIEWS_EVENT_SUBSCRIBE_EDIT_SUBJECT"] = "#SITE_NAME#: Информация о подписке";
$MESS["COLLECTED_REVIEWS_EVENT_SUBSCRIBE_EDIT_MESSAGE"] = "
Здравствуйте.
Вы запроссили список рассылок обновлений отзывов с сайта #SITE_NAME#.

Для удаления подписки перейдите по указанной под названием товара ссылке:

#SUBSCRIBE_INFO#

----------------------------------------------------
сообщение сгенерированно автоматически
";
?>