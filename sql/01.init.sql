CREATE TABLE `news_grammas` (
  `id` SERIAL NOT NULL,
  `parentId` bigint(20) unsigned,
  `key` VARCHAR(10) NOT NULL,
  `descr` text,
  PRIMARY KEY (`id`),
  FOREIGN KEY (parentId) REFERENCES news_grammas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `news_words` (
  `id` serial NOT NULL,
  `parentId` bigint(20) unsigned,
  `word` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `freq` float NOT NULL,
  `freqS` float NOT NULL,
  `isProcessed` tinyint(1) NOT NULL DEFAULT '0',
  `isKnown` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (parentId) REFERENCES news_words(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

create index news_word on news_words(word);

CREATE TABLE `news_words_grammas` (
  `id` serial not null,
  `wordId` bigint(20) unsigned not null,
  `grammaId` bigint(20) unsigned not null,
  PRIMARY KEY(`id`),
  FOREIGN KEY (wordId) REFERENCES news_words(id) ON DELETE CASCADE,
  FOREIGN KEY (grammaId) REFERENCES news_grammas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `news_categories` (
  `id` serial not null,
  `path` VARCHAR (255) not null,
  `title` VARCHAR (255) not null,
  `freq` float not null default 0,
  `freqS` float not null default 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

create index news_categories on news_categories(path);

CREATE TABLE `news_categories_links` (
  `id` bigint(20) unsigned not null,
  `parentId` bigint(20) unsigned not null,
  PRIMARY KEY (`id`, `parentId`),
  FOREIGN KEY (id) REFERENCES news_categories(id) ON DELETE CASCADE,
  FOREIGN KEY (parentId) REFERENCES news_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `news_words_categories` (
  `id` bigint(20) unsigned not null,
  `categoryId` bigint(20) unsigned not null,
  `level` int,
  PRIMARY KEY(`id`, `categoryId`),
  FOREIGN KEY (id) REFERENCES news_words(id) ON DELETE CASCADE,
  FOREIGN KEY (categoryId) REFERENCES news_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `news_feeds` (
  `id` serial not null,
  `url` varchar(255) not null,
  `url_crc32` varchar(32) not null unique,
  `title` varchar(255) not null,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `news_news` (
  `id` serial not null,
  `feedId` bigint(20) unsigned not null,
  `url` varchar(255) not null,
  `url_crc32` varchar(32) not null,
  `title` varchar(255) not null,
  `descr` text,
  `content` text,
  `tags` varchar(255),
  `updatedAt` timestamp not null,
  `publicatedAt` timestamp not null,
  `isProcessed` tinyint not null default 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`feedId`, `url_crc32`),
  FOREIGN KEY (feedId) REFERENCES news_feeds(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `news_news_coeff` (
  `id` bigint(20) unsigned not null,
  `categoryId` bigint(20) unsigned not null,
  `coeff` float not null,
  PRIMARY KEY(`id`, `categoryId`),
  FOREIGN KEY (id) REFERENCES news_news(id) ON DELETE CASCADE,
  FOREIGN KEY (categoryId) REFERENCES news_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `news_users` (
  `id` SERIAL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `news_users_feeds` (
  `userId` bigint(20) unsigned not null,
  `feedId` bigint(20) unsigned not null,
  PRIMARY KEY(`userId`, `feedId`),
  FOREIGN KEY (userId) REFERENCES news_users(id) ON DELETE CASCADE,
  FOREIGN KEY (feedId) REFERENCES news_feeds(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `news_users_unread` (
  `userId` bigint(20) unsigned not null,
  `newsId` bigint(20) unsigned not null,
  PRIMARY KEY(`userId`, `newsId`),
  FOREIGN KEY (userId) REFERENCES news_users(id) ON DELETE CASCADE,
  FOREIGN KEY (newsId) REFERENCES news_news(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `news_users_coeff` (
  `id` bigint(20) unsigned not null,
  `categoryId` bigint(20) unsigned not null,
  `coeff` float not null,
  PRIMARY KEY(`id`, `categoryId`),
  FOREIGN KEY (id) REFERENCES news_users(id) ON DELETE CASCADE,
  FOREIGN KEY (categoryId) REFERENCES news_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;