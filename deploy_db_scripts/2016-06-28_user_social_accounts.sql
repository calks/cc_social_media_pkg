drop table if exists user_social_accounts;
create table user_social_accounts(
  user_id int not null,
  social_network_name varchar(255),
  uid varchar(255),
  profile_link varchar(255),
  primary key (user_id, social_network_name),
  constraint con_user_social_accounts_01 foreign key(user_id) references `user`(id) on delete cascade
) engine=innodb, default charset=utf8;