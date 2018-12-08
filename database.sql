create table user
(
  user_id              int auto_increment
    primary key,
  user_key             text                   not null,
  user_password        text                   not null,
  user_type_id         int                    not null,
  user_is_active       tinyint(1) default '1' not null,
  user_lastmodified    datetime               not null,
  user_lastmodified_id int                    not null,
  constraint user_user_user_id_fk
    foreign key (user_lastmodified_id) references user (user_id)
      on update cascade
      on delete cascade
);

create table item
(
  item_id              int auto_increment
    primary key,
  item_name            text       null,
  item_created         datetime   null,
  item_created_id      int        null,
  item_lastmodified    datetime   null,
  item_lastmodified_id int        null,
  item_is_active       tinyint(1) null,
  constraint item_user_user_id_fk
    foreign key (item_created_id) references user (user_id)
      on update cascade
      on delete cascade,
  constraint item_user_user_id_fk_2
    foreign key (item_lastmodified_id) references user (user_id)
      on update cascade
      on delete cascade
);

create table user_type
(
  user_type_id              int auto_increment
    primary key,
  user_type_name            text                   not null,
  user_type_lastmodified    datetime               null,
  user_type_lastmodified_id int                    null,
  user_type_created         datetime               null,
  user_type_created_id      int                    null,
  user_type_access          text                   null,
  user_is_active            tinyint(1) default '1' null,
  constraint user_type_user_user_id_fk
    foreign key (user_type_lastmodified_id) references user (user_id)
      on update cascade
      on delete cascade,
  constraint user_type_user_user_id_fk_2
    foreign key (user_type_created_id) references user (user_id)
      on update cascade
      on delete cascade
);

create table price
(
  price_id        int auto_increment
    primary key,
  price_price     int      not null,
  price_start     datetime null,
  price_created   datetime null,
  price_creted_id int      null,
  item_id         int      null,
  user_type_id    int      null,
  constraint price_item_item_id_fk
    foreign key (item_id) references item (item_id)
      on update cascade
      on delete cascade,
  constraint price_user_type_user_type_id_fk
    foreign key (user_type_id) references user_type (user_type_id)
      on update cascade
      on delete cascade,
  constraint price_user_user_id_fk
    foreign key (price_creted_id) references user (user_id)
      on update cascade
      on delete cascade
);

create table reservation
(
  reservation_id                int auto_increment
    primary key,
  reservation_code              varchar(20)            not null,
  reservation_datetime          datetime               null,
  reservation_is_approved       tinyint(1)             null,
  reservation_approved_datetime datetime               null,
  reservation_approved_id       int                    null,
  reservation_is_active         tinyint(1) default '1' null,
  user_id                       int                    null,
  price_id                      int                    null,
  constraint reservation_reservation_code_uindex
    unique (reservation_code),
  constraint reservation_price_price_id_fk
    foreign key (price_id) references price (price_id)
      on update cascade
      on delete cascade,
  constraint reservation_user_user_id_fk
    foreign key (user_id) references user (user_id)
      on update cascade
      on delete cascade,
  constraint reservation_user_user_id_fk_2
    foreign key (reservation_approved_id) references user (user_id)
      on update cascade
      on delete cascade
);

alter table user
  add constraint user_user_type_user_type_id_fk
    foreign key (user_type_id) references user_type (user_type_id)
      on update cascade
      on delete cascade;

create table vehicle
(
  vehicle_id              int auto_increment
    primary key,
  vehicle_number          varchar(10) not null,
  vehicle_lastmodified    datetime    null,
  vehicle_lastmodified_id int         null,
  vehicle_created         datetime    null,
  vehicle_created_id      int         null,
  constraint vehicle_user_user_id_fk
    foreign key (vehicle_lastmodified_id) references user (user_id)
      on update cascade
      on delete cascade,
  constraint vehicle_user_user_id_fk_2
    foreign key (vehicle_created_id) references user (user_id)
      on update cascade
      on delete cascade
);

create table vehicle_feature
(
  vehicle_feature_id    int  null,
  vehicle_feature_key   text null,
  vehicle_feature_value text null,
  constraint vehicle_feature_vehicle_vehicle_id_fk
    foreign key (vehicle_feature_id) references vehicle (vehicle_id)
      on update cascade
      on delete cascade
);

create table view_user
(
  view_user_id              int auto_increment
    primary key,
  view_user_view            text       null,
  view_user_column          text       null,
  view_user_type            text       null,
  view_user_show            tinyint(1) null,
  view_user_nullable        tinyint(1) null,
  view_user_is_active       tinyint(1) null,
  view_user_lastmodified    datetime   null,
  view_user_lastmodified_id int        null,
  view_user_created         datetime   null,
  view_user_created_id      int        null,
  constraint view_user_user_user_id_fk
    foreign key (view_user_lastmodified_id) references user (user_id)
      on update cascade,
  constraint view_user_user_user_id_fk_2
    foreign key (view_user_created_id) references user (user_id)
      on update cascade
);

create table view_vehicle
(
  view_vehicle_id              int auto_increment
    primary key,
  view_vehicle_view            text       null,
  view_vehicle_column          text       null,
  view_vehicle_type            text       null,
  view_vehicle_show            tinyint(1) null,
  view_vehicle_nullable        tinyint(1) null,
  view_vehicle_is_active       tinyint(1) null,
  view_vehicle_created_id      int        null,
  view_vehicle_created         datetime   null,
  view_vehicle_lastmodified_id int        null,
  view_vehicle_lastmodified    datetime   null,
  constraint view_vehicle_user_user_id_fk
    foreign key (view_vehicle_lastmodified_id) references user (user_id)
      on update cascade,
  constraint view_vehicle_user_user_id_fk_2
    foreign key (view_vehicle_created_id) references user (user_id)
      on update cascade
);