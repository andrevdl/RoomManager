CREATE DATABASE persistent;

USE persistent;

CREATE TABLE IF NOT EXISTS Locations (
  location_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS Rooms (
  room_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  location_id INT(11) NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  FOREIGN KEY (location_id) REFERENCES Locations(location_id)
);

CREATE TABLE IF NOT EXISTS Users (
  user_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL, #need possible data type change
  salt VARCHAR(255) NOT NULL #need possible data type change
);

CREATE TABLE IF NOT EXISTS Reservations (
  res_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  room_id INT(11) NOT NULL,
  user_id INT(11) NOT NULL,
  name VARCHAR(255) NOT NULL,
  date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  description TEXT NOT NULL,
  size INT(11) NOT NULL,
  state BOOLEAN NOT NULL,
  FOREIGN KEY (room_id) REFERENCES Rooms(room_id),
  FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

CREATE TABLE IF NOT EXISTS Invites (
  res_id INT(11) NOT NULL,
  user_id INT(11) NOT NULL,
  state BOOLEAN NOT NULL,
  PRIMARY KEY (res_id, user_id),
  FOREIGN KEY (res_id) REFERENCES Reservations(res_id),
  FOREIGN KEY (user_id) REFERENCES Users(user_id)
);