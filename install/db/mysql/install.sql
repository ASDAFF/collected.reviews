CREATE TABLE IF NOT EXISTS b_collected_reviews_messages (
	ID int(11) NOT NULL auto_increment,
	DATE_CREATE datetime,
	DATE_CREATE_REAL datetime,
	STATUS char(1) NOT NULL default 'N',
	IBLOCK_ID int(11) NOT NULL, 
	ELEMENT_ID int(11) NOT NULL,
	DIGNITY text,
	LIMITATIONS text,
	COMMENTS text,
	RATING int(11) NOT NULL,
	EXP_USING int(11),
	HELPFUL int(11) NOT NULL,
	USELESS int(11) NOT NULL,
	IP_ADDRESS varchar (128),
	CITY  varchar (128),
	USER_ID int(11) NOT NULL,
	USER_NAME varchar(255),
	USER_EMAIL varchar(255),
	SITE_ID varchar(3) NOT NULL,
	PRIMARY KEY (`ID`),
	KEY `USER_ID` (`USER_ID`)
);

CREATE TABLE IF NOT EXISTS b_collected_reviews_subscribe (
	ID int(11) NOT NULL auto_increment,
	DATE_CREATE datetime,
	IBLOCK_ID int(11) NOT NULL,
	ELEMENT_ID int(11) NOT NULL,
	EMAIL varchar(255),
	CODE  varchar(20) NOT NULL,
	CONFIRMED char(1) NOT NULL default 'N',
	USER_ID int(11) NOT NULL,
	SITE_ID varchar(3) NOT NULL,
	PRIMARY KEY (`ID`),
	KEY `ELEMENT_ID` (`ELEMENT_ID`)
);

CREATE TABLE IF NOT EXISTS b_collected_reviews_stoplist (
	ID int(11) NOT NULL auto_increment,
	DATE_CREATE datetime,
	DATE_ACTIVE_TO datetime,
	IP_ADDRESS varchar (128) NOT NULL,
	USER_ID int(11),
	SITE_ID varchar(3) NOT NULL,
	EXCEP char(1),
	PRIMARY KEY (`ID`),
	KEY `IP_ADDRESS` (`IP_ADDRESS`)
);