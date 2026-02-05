create table users (
 id int AUTO_INCREMENT primary key,
 username varchar(20) not null,
 password varchar(20) not null,
 role varchar(10)
);

create table notes (
 id int auto_increment primary key,
 uid int,
 name varchar(255),
 data blob,
 vis varchar(10),
 type varchar(20)
);

insert into users values (1,'admin','admin','admin');
