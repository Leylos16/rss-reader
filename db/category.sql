BEGIN TRANSACTION;
DROP TABLE IF EXISTS "category";
CREATE TABLE "category" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , "label" VARCHAR NOT NULL , "id_user" INTEGER);
INSERT INTO "category" VALUES(1,'Dév',1);
INSERT INTO "category" VALUES(2,'fdféé',1);
INSERT INTO "category" VALUES(3,'lorés',1);
INSERT INTO "category" VALUES(4,'loréés',1);
COMMIT;