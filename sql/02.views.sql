CREATE OR REPLACE VIEW `news_unread` AS
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