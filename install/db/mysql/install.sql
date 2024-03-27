create table if not exists digitmind_redirecturlwriter_options
(
    `ID` int not null auto_increment,
    `CODE` varchar(255) not null,
    `VALUE` text not null,
    primary key (ID)
);
