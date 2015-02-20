CREATE OR REPLACE VIEW `news_v_unread` AS
    SELECT
        n.id,
        n.title,
        n.descr,
        n.content,
        n.publicatedAt,
        n.feedId,
        u.userId
    FROM
        news_news n
        JOIN news_users_unread u ON (u.newsId=n.id)
    WHERE
        n.isProcessed = TRUE;

CREATE OR REPLACE VIEW `news_v_users_feeds` AS
    SELECT
        f.*,
        uf.userId
    FROM
        news_feeds f
        JOIN news_users_feeds uf ON (uf.feedId=f.id);

CREATE OR REPLACE VIEW `news_v_users_news` AS
    SELECT
        n.id,
        n.title,
        n.descr,
        n.content,
        n.publicatedAt,
        n.feedId,
        f.userId
    FROM
        news_news n
        JOIN news_users_feeds f ON (f.feedId=n.feedId);