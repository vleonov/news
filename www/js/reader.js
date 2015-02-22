var config = config || {};

$( function() {
    "use strict";

    $(window).on('beforeunload', function() {
        $(window).scrollTop(0);
    });

    var timer,
        $modal = $('#news-modal'),
        $modalTitle = $('#news-modal .item .title a span'),
        $modalText = $('#news-modal .item .text'),
        $modalLinks = $('#news-modal .item .link a, #news-modal .item .title a'),
        readCount = 0;

    config = $.extend(
        {
            'urlSkip': './news/%d/markRead/m',
            'urlRead': './news/%d/markRead/p',
            'urlGet': './news/%d',
            'urlList': './news/list/%d',
            moreCount: 5,
            isUnread: true,
            feedId: null,
            getting: false
        },
        config
    );

    $(document).scroll(function() {
        if (timer) {
            clearTimeout(timer);
        }
        timer = setTimeout(markRead, 50);
    }).on('click', '#news-bar .title a', show);

    function markRead()
    {
        var $news,
            offset,
            scrolled = $('html').scrollTop();

        $('#news-bar .item:not(".scrolled")').each(function() {
            $news = $(this);
            offset = $news.offset();

            if (offset.top - scrolled < 20) {
                $news.addClass('scrolled');
                if (!$news.hasClass('read')) {
                    updateCounter($news);
                    $news.addClass('read');
                    $.post(
                        config.urlSkip.replace('%d', $news.data('id'))
                    );
                }
                if (++readCount >= config.moreCount) {
                    readCount = 0;
                    getMore();
                }
            } else {
                return false;
            }
        })
    }

    function show()
    {
        var $news = $(this).parents('.item'),
            $newsTitle = $('.title', $news),
            $newsLink = $('a', $newsTitle);

        $modalTitle.text($newsTitle.text());
        $modalText.html('<i>Загрузка ...</i>');
        $modalLinks.attr('href', $newsLink.attr('href'));

        $.get(
            config.urlGet.replace('%d', $news.data('id'))
        ).success(
            function(data) {
                $modalText.html(data.text);
                $('a', $modalText).attr('target', '_blank');
                $('img', $modalText).removeAttr('align')
                    .removeAttr('style')
                    .removeAttr('width')
                    .removeAttr('height');
            }
        );

        $modal.modal({});

        updateCounter($news);
        if (!$news.hasClass('read')) {
            $news.addClass('read');
            $.post(
                config.urlRead.replace('%d', $news.data('id'))
            );
        }

        return false;
    }

    function updateCounter($news)
    {
        if ($news.hasClass('read')) {
            return;
        }

        var feedId = $news.data('feed-id'),
            $counters = $('.js-counter-total, .js-counter-' + feedId),
            $counter,
            count;

        $counters.each(function() {
            $counter = $(this);
            count = parseInt($counter.text());
            if (count > 1) {
                $counter.text(count - 1);
            } else {
                $counter.text('');
            }
        });
    }

    function getMore()
    {
        if (config.getting) {
            return;
        }

        var lastPublicated = $('#news-bar .item:last').data('publicated-at');
        config.getting = true;

        $.get(
            config.urlList.replace('%d', config.feedId ? config.feedId : '')
                + '?p=' + lastPublicated
                + '&u=' + (config.isUnread ? '1' : '')
        ).success(function(response) {
            $('#news-bar').append(response);
            config.getting = false;
        });
    }
});