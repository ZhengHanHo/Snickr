DROP SCHEMA IF EXISTS  snickr;
CREATE SCHEMA snickr;
USE snickr;

CREATE TABLE Users(
  uid INT(11)  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(32) NOT NULL UNIQUE,
  username VARCHAR(32),
  nickname VARCHAR(32),
  password VARCHAR(32),
  registertime TIMESTAMP
);

CREATE TABLE Workspaces(
  wid INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  wname VARCHAR(32),
  description VARCHAR(32),
  wcreatetime TIMESTAMP
);

CREATE TABLE Channels(
  cid INT(11) NOT NULL AUTO_INCREMENT,
  wid INT(11),
  cname VARCHAR(32),
  ctype ENUM('PUBLIC', 'PRIVATE','DIRECT'),
  ccreatetime TIMESTAMP,
  PRIMARY KEY (cid,wid),
  FOREIGN KEY (wid) references Workspaces(wid) ON DELETE CASCADE
);

CREATE TABLE MessageContent(
  mid INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uid INT(11),
  mtype ENUM('TEXT','IMAGE','HYPERLINK','MARKUP','EMOJIS','HYBRID'),
  content VARCHAR(128),
  FOREIGN KEY (uid) references Users(uid) ON DELETE CASCADE
);

CREATE TABLE MessageFrom(
  wid INT(11),
  cid INT(11),
  mid INT(11),
  mtime TIMESTAMP,
  PRIMARY KEY (wid,cid,mid),
  FOREIGN KEY (mid) 
       REFERENCES MessageContent(mid)
       ON DELETE CASCADE,
  FOREIGN KEY (cid,wid) references Channels(cid,wid) ON DELETE CASCADE
);

CREATE TABLE WorkspacesInviteLog(
  createuid INT(11),
  inviteduid INT(11),
  wid INT(11),
  wstatus ENUM('SENT','ACCEPT','REFUSE'),
  wptime TIMESTAMP,
  PRIMARY KEY (createuid,inviteduid,wid,wstatus,wptime),
   FOREIGN KEY (createuid) references Users(uid) ON DELETE CASCADE,
  FOREIGN KEY (inviteduid) references Users(uid) ON DELETE CASCADE,
  FOREIGN KEY (wid) references Workspaces(wid) ON DELETE CASCADE
);

CREATE TABLE ChannelsInviteLog(
  createuid INT(11),
  inviteduid INT(11),
  wid INT(11),
  cid INT(11),
  cstatus ENUM('SENT','ACCEPT','REFUSE'),
  cptime TIMESTAMP,
  PRIMARY KEY (createuid,inviteduid,wid,cid,cstatus,cptime),
  FOREIGN KEY (createuid) references Users(uid) ON DELETE CASCADE,
  FOREIGN KEY (inviteduid) references Users(uid) ON DELETE CASCADE,
  FOREIGN KEY (cid,wid) references Channels(cid,wid) ON DELETE CASCADE
);

CREATE TABLE WU(
   wid INT(11),
   uid INT(11),
   wutype ENUM('ORIGINAL_ADMIN','SELECTED_ADMIN','MEMBER'),
   PRIMARY KEY (wid,uid),
   FOREIGN KEY (wid) references Workspaces(wid) ON DELETE CASCADE,
   FOREIGN KEY (uid) references Users(uid) ON DELETE CASCADE

);

CREATE TABLE CU(
   wid INT(11),
   cid INT(11) ,
   uid INT(11) ,
   cutype ENUM('CREATOR','MEMBER'),
   PRIMARY KEY (wid,cid,uid),
   FOREIGN KEY (cid, wid)
        REFERENCES Channels(cid, wid)
        ON DELETE CASCADE,
   FOREIGN KEY (uid)
      REFERENCES Users(uid)
      ON DELETE CASCADE
);