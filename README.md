# PalWeb: Social Network

PalWeb is a personal project inspired by sites like Facebook and Twitter, made specifically for programmers and engineers to get feedback or help on their personal projects, share their opinions on the industry, or simply expand their network. Features include activities such as adding and deleting posts and comments, liking other posts, adding and removing friends, calculating mutual friends, receiving notifications, searching for other users, and so on.

It was built in the back-end with PHP and JavaScript, and in the front-end with HTML and CSS. Ajax calls are used to dynamically feed the user data from the server. The database was also designed with ACID properties in mind, with an emphasis on minimizing redundancy and maximizing consistency.

## DBMS Schema

There are seven tables in the database, with the following schemas:

```
TABLE user {
  id: INT PRIMARY KEY,
  handle: VARCHAR UNIQUE KEY,
  first_name: VARCHAR,
  last_name: VARCHAR,
  email: VARCHAR UNIQUE KEY,
  password: VARCHAR,
  profile_pic: VARCHAR,
  sign_up_time: DATETIME,
  latest_login_time: DATETIME,
  deactivated: BOOLEAN
}
```

```
TABLE post {
  id: INT PRIMARY KEY,
  poster_handle: VARCHAR FK user.handle,
  body: TEXT,
  time: DATETIME
}
```

```
TABLE comment {
  id: INT PRIMARY KEY,
  commenter_handle: VARCHAR FK user.handle,
  post_id: INT FK post.id,
  body: TEXT,
  time: DATETIME
}
```

```
TABLE like {
  id: INT PRIMARY KEY,
  liker_handle: VARCHAR COMPOSITE KEY FK user.handle,
  post_id: INT COMPOSITE KEY FK post.id
}
```

```
TABLE friend {
  id: INT PRIMARY KEY,
  sender_handle: VARCHAR COMPOSITE KEY FK user.handle,
  receiver_handle: VARCHAR COMPOSITE KEY FK user.handle,
  status: VARCHAR,
  time: DATETIME
}
```

```
TABLE message {
  id: INT PRIMARY KEY,
  sender_handle: VARCHAR FK user.handle,
  receiver_handle: VARCHAR FK user.handle,
  body: TEXT,
  viewed: BOOLEAN,
  time: DATETIME
}
```

```
TABLE notification {
  id: INT PRIMARY KEY,
  sender_handle: VARCHAR FK user.handle,
  receiver_handle: VARCHAR FK user.handle,
  body: TEXT,
  link: VARCHAR,
  viewed: BOOLEAN,
  time: DATETIME
}
```

This ensures that any information required can be easily procured with no more than a few nested queries, and it also guarantees consistency as no information is being stored redundantly.

Because of the foreign key constraints, all updates to a table will cascade to connected tables. For example, if a user X deletes his account, all tables with foreign key to X in the user table will have their tuples automatically deleted. So, there is no need to explicitly again delete all of X's messages, posts, comments, etc.

## Amazon Web Services: S3 Bucket

Because Heroku has an ephemeral file system, all user uploaded photos are wiped clean every few hours. To persistently store user photos, we use an AWS S3 bucket. Photos uploaded on PalWeb are direct-uploaded to an S3 bucket, from which the photos are then retrieved.
