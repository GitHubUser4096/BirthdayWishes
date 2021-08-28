create database Wishes;

drop table User;
create table User(
	id int primary key not null auto_increment,
	username varchar(30) not null unique,
	password varchar(255) not null,
    admin bool
);

drop table NumberInfo;
create table NumberInfo(
	id int primary key not null auto_increment,
	number int not null,
	content varchar(1023),
    link varchar(100),
	imgSrc varchar(30),
    createdBy int,
    createdTime datetime,
	approved bool,
    foreign key (createdBy) references User(id)
);

drop table Category;
create table Category(
	id int primary key not null auto_increment,
	name varchar(20) not null unique
);

drop table InfoCat;
create table InfoCat(
	id int primary key not null auto_increment,
	infoId int not null,
	catId int not null,
	foreign key (infoId) references NumberInfo(id),
	foreign key (catId) references Category(id)
);