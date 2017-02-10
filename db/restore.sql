DROP TABLE IF EXISTS "user";
CREATE TABLE "user" (
"id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL ,
"username" VARCHAR NOT NULL ,
"password" VARCHAR NOT NULL,
UNIQUE (username)
);
DROP TABLE IF EXISTS "url";
CREATE TABLE "url" (
"id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL ,
"url" VARCHAR NOT NULL ,
"title" VARCHAR NOT NULL,
UNIQUE (url)
);
DROP TABLE IF EXISTS "category";
CREATE TABLE "category" (
"id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL ,
"label" VARCHAR NOT NULL ,
"user_id" INTEGER NOT NULL,
UNIQUE (label, user_id),
FOREIGN KEY(user_id) REFERENCES user(id)
);
DROP TABLE IF EXISTS "feed";
CREATE TABLE "feed" (
"id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL ,
"url_id" INTEGER NOT NULL,
"category_id" INTEGER DEFAULT NULL,
"user_id" INTEGER NOT NULL,
FOREIGN KEY(url_id) REFERENCES url(id),
FOREIGN KEY(category_id) REFERENCES category(id),
FOREIGN KEY(user_id) REFERENCES user(id)
);
CREATE INDEX fki_category_user_fk ON category (user_id ASC);
CREATE INDEX fki_feed_url_fk ON feed (url_id ASC);
CREATE INDEX fki_feed_category_fk ON feed (category_id ASC);
CREATE INDEX fki_feed_user_fk ON feed (user_id ASC);