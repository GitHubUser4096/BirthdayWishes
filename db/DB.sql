create database Wishes;

drop table if exists Scheduled;
create table Scheduled(
	id int primary key not null auto_increment,
    sent_by varchar(30),
    email varchar(63) not null,
    date date not null,
    document varchar(255) not null
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

drop table if exists User;
create table User(
	id int primary key not null auto_increment,
	username varchar(30) not null unique,
	password varchar(255) not null,
    email varchar(63),
    verified bool,
    admin bool
);

drop table if exists NumberInfo;
create table NumberInfo(
	id int primary key not null auto_increment,
	number int not null,
	content varchar(1023),
    link varchar(100),
	imgSrc varchar(50),
    createdBy int,
    createdTime datetime,
	state enum('approved', 'dismissed', 'pending') default 'pending',
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

alter table NumberInfo drop column approved;
alter table NumberInfo add column state enum('approved', 'dismissed', 'pending') default 'pending';
alter table User add column verified bool;
