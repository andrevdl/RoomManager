CREATE DATABASE persistent;

USE persistent;

-- base tables

CREATE TABLE IF NOT EXISTS users (
  user_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL, #need possible data type change
  salt VARCHAR(255) NOT NULL, #need possible data type change

  #protection part
  verify CHAR(64), -- move
  lease TIMESTAMP -- move
);

CREATE TABLE IF NOT EXISTS auth (
  share CHAR(32) NOT NULL PRIMARY KEY,
  private CHAR(32) NOT NULL
);

-- need edit
CREATE TABLE IF NOT EXISTS session (
  user_id INT(11) NOT NULL,
  verify CHAR(64),
  lease TIMESTAMP
);

-- extended tables

CREATE TABLE IF NOT EXISTS locations (
  location_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL
);


CREATE TABLE IF NOT EXISTS rooms (
  room_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  location_id INT(11) NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  size INT(11) NOT NULL,
  FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

CREATE TABLE IF NOT EXISTS reservations (
  res_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  room_id INT(11) NOT NULL,
  user_id INT(11) NOT NULL,
  name VARCHAR(255) NOT NULL,
  date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  description TEXT NOT NULL,
  state BOOLEAN NOT NULL DEFAULT 1,
  FOREIGN KEY (room_id) REFERENCES rooms(room_id),
  FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS Invites (
  res_id INT(11) NOT NULL,
  user_id INT(11) NOT NULL,
  state BOOLEAN NOT NULL DEFAULT 1,
  PRIMARY KEY (res_id, user_id),
  FOREIGN KEY (res_id) REFERENCES Reservations(res_id),
  FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TRIGGER user_date BEFORE INSERT ON users FOR EACH ROW SET NEW.lease = CURDATE();

-- test data

INSERT INTO `Locations` (`location_id`,`name`) VALUES (1,"Amersfoort"),(2,"Apeldoorn"),(3,"Den Haag"),(4,"Oldenzaal"),(5,"Münster");

#discription is empty
INSERT INTO `rooms` (`room_id`,`location_id`,`name`,`size`) VALUES (1,3,"magna",415),(2,1,"rutrum",81),(3,4,"ac",295),(4,4,"lobortis,",388),(5,5,"eu",163),(6,1,"erat,",282),(7,1,"Suspendisse",317),(8,1,"in,",164),(9,2,"sed",369),(10,1,"mi",284);
INSERT INTO `rooms` (`room_id`,`location_id`,`name`,`size`) VALUES (11,5,"dolor.",136),(12,4,"in",368),(13,4,"tellus",424),(14,5,"erat",155),(15,5,"adipiscing.",389),(16,3,"semper",136),(17,3,"eros",433),(18,4,"neque.",496),(19,5,"fermentum",113),(20,3,"ullamcorper",474);
INSERT INTO `rooms` (`room_id`,`location_id`,`name`,`size`) VALUES (21,3,"In",241),(22,3,"facilisis",236),(23,5,"dis",169),(24,1,"sociis",486),(25,5,"laoreet,",149),(26,5,"natoque",479),(27,4,"lorem",202),(28,5,"eget",162),(29,5,"ligula.",77),(30,1,"ultrices",288);
INSERT INTO `rooms` (`room_id`,`location_id`,`name`,`size`) VALUES (31,1,"adipiscing",329),(32,3,"Integer",155),(33,4,"velit.",255),(34,2,"Donec",496),(35,1,"Lorem",301),(36,2,"Phasellus",282),(37,2,"neque.",87),(38,1,"non",191),(39,4,"orci",436),(40,3,"vulputate,",161);
INSERT INTO `rooms` (`room_id`,`location_id`,`name`,`size`) VALUES (41,3,"lacus.",406),(42,1,"ac",277),(43,1,"natoque",485),(44,1,"sem",83),(45,5,"sit",210),(46,2,"elementum",209),(47,3,"quis",445),(48,3,"adipiscing",282),(49,2,"urna",120),(50,2,"blandit",138);
INSERT INTO `rooms` (`room_id`,`location_id`,`name`,`size`) VALUES (51,1,"nisl.",50),(52,2,"metus",354),(53,4,"sed",423),(54,4,"Integer",251),(55,5,"ligula.",393),(56,5,"dictum.",388),(57,3,"pede.",419),(58,2,"arcu",109),(59,4,"tortor.",151),(60,3,"sit",479);
INSERT INTO `rooms` (`room_id`,`location_id`,`name`,`size`) VALUES (61,1,"Duis",336),(62,3,"Pellentesque",82),(63,2,"eros.",340),(64,3,"nisl.",165),(65,5,"risus",279),(66,2,"arcu",151),(67,3,"fermentum",225),(68,4,"tortor.",257),(69,4,"mauris,",435),(70,1,"eget",305);
INSERT INTO `rooms` (`room_id`,`location_id`,`name`,`size`) VALUES (71,5,"posuere",181),(72,2,"tincidunt",329),(73,5,"tellus.",367),(74,3,"molestie",139),(75,1,"orci.",273),(76,1,"fringilla,",156),(77,1,"nec,",399),(78,1,"at,",500),(79,2,"sapien",382),(80,2,"egestas",85);
INSERT INTO `rooms` (`room_id`,`location_id`,`name`,`size`) VALUES (81,2,"ullamcorper",320),(82,4,"varius",123),(83,2,"mattis.",240),(84,1,"Proin",332),(85,1,"luctus",436),(86,2,"enim.",140),(87,4,"et,",94),(88,2,"luctus.",189),(89,5,"mi.",344),(90,4,"et",97);
INSERT INTO `rooms` (`room_id`,`location_id`,`name`,`size`) VALUES (91,5,"vel,",167),(92,2,"ipsum.",360),(93,3,"eu",170),(94,1,"in",471),(95,1,"convallis",193),(96,4,"cursus",113),(97,1,"iaculis",181),(98,4,"sem.",477),(99,2,"eu",101),(100,2,"auctor",478);

INSERT INTO `users` (`user_id`,`username`,`password`) VALUES (1,"tim","tim"),(2,"garcia","garcia"),(3,"bjorn","bjorn"),(3,"andre","andre");