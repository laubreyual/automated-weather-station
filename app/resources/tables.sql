CREATE TABLE user (
   	user_id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    username VARCHAR(60) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE aws (
    aws_id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    username VARCHAR(60) NOT NULL,
    password VARCHAR(60) NOT NULL
);

CREATE TABLE reading (
    reading_id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    aws_id INT(12) NOT NULL,
    observation_time DATETIME NOT NULL,
    location VARCHAR(255),
    latitude DECIMAL(16,12),
    longitude DECIMAL(16,12),
    date_recorded DATETIME NOT NULL,
    station_id VARCHAR(60),
    station_name VARCHAR(255),
    temperature DECIMAL(12,5),
    wind_speed DECIMAL(12,5),
    wind_direction VARCHAR(60),
    solar_radiation INT(10),
    rain DECIMAL(12,5)
    wind_degrees DECIMAL(12,5),
    pressure DECIMAL(12,5),
);