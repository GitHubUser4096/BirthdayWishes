-- Full setup (up to date)
drop database if exists Wishes;
create database Wishes;
use Wishes;

drop table if exists User;
create table User(
	id int primary key not null auto_increment,
	username varchar(30) not null unique,
	password varchar(255) not null,
    email varchar(63),
    verified bool,
    admin bool
);

drop table if exists Wish;
create table Wish(
	id int primary key auto_increment,
    uid varchar(50) unique not null,
    userId int,
    sessionId varchar(40),
    number int,
    preview_text varchar(255),
    date_created date,
    lastEdited date,
    mail_address varchar(255),
    mail_hidden varchar(255),
    mail_date date,
    mail_sent bool,
    deleted bool not null default 0,
    foreign key (userId) references User(id)
);

drop table if exists PassRequests;
create table PassRequests(
	id int primary key not null auto_increment,
    token varchar(255) not null unique,
    username varchar(30) not null,
    valid_until datetime not null
);

drop table if exists VerifyRequests;
create table VerifyRequests(
	id int primary key not null auto_increment,
    token varchar(255) not null unique,
    userId int,
    valid_until datetime not null,
    foreign key (userId) references User(id)
);

drop table if exists NumberInfo;
create table NumberInfo(
	id int primary key not null auto_increment,
	number int not null,
	content varchar(1023),
    background varchar(23),
    color varchar(23),
    link varchar(100),
	imgSrc varchar(80),
    imgAttrib varchar(255),
    createdBy int,
    createdTime datetime,
	state enum('approved', 'dismissed', 'pending') default 'pending',
    titlePage bool,
    foreign key (createdBy) references User(id)
);

drop table if exists Category;
create table Category(
	id int primary key not null auto_increment,
	name varchar(20) not null unique
);

drop table if exists InfoCat;
create table InfoCat(
	id int primary key not null auto_increment,
	infoId int not null,
	catId int not null,
	foreign key (infoId) references NumberInfo(id),
	foreign key (catId) references Category(id)
);

drop table if exists Config;
create table Config(
	id int primary key not null auto_increment,
    description varchar(255),
    name varchar(32) not null unique,
    value varchar(32),
    type varchar(32)
);

insert into Config(description, name, value, type) values ('Limit přidaných zajímavostí (za daný čas)', 'infoLimit', '1', 'number');
insert into Config(description, name, value, type) values ('Čas resetování limitu (v minutách)', 'infoLimitReset', '1', 'number');
insert into Config(description, name, value, type) values ('Doba dostupnosti přání od poslední úpravy (dny)', 'wishAccessTime', '10', 'number');

-- Update to V0.1

alter table NumberInfo drop column approved;
alter table NumberInfo add column state enum('approved', 'dismissed', 'pending') default 'pending';
alter table User add column verified bool;

-- Update to V0.2

alter table NumberInfo add column background varchar(23);
alter table NumberInfo add column color varchar(23);
alter table NumberInfo modify column imgSrc varchar(80);
alter table NumberInfo add column imgAttrib varchar(255);
drop table Scheduled;

create table Wish(
	id int primary key auto_increment,
    uid varchar(50) unique not null,
    userId int,
    number int,
    preview_text varchar(255),
    date_created date,
    mail_address varchar(255),
    mail_hidden varchar(255),
    mail_date date,
    mail_sent bool,
    foreign key (userId) references User(id)
);

-- Update to V0.3
alter table NumberInfo add column titlePage bool;
alter table Wish add column deleted bool not null default 0;
alter table Wish add column lastEdited date;
insert into Config(description, name, value, type) values ('Doba dostupnosti přání od poslední úpravy (dny)', 'wishAccessTime', '10', 'number');
alter table Wish add column sessionId varchar(40);
