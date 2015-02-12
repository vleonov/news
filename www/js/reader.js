$( function() {
    "use strict";

    $(window).on('beforeunload', function() {
        $(window).scrollTop(0);
    });

    var timer,
        $modal = $('#news-modal'),
        $modalTitle = $('#news-modal .item .title'),
        $modalText = $('#news-modal .item .text'),
        $modalLink = $('#news-modal .item .link a'),
        config = {
            'urlSkip': '/news/%d/markRead/m',
            'urlRead': '/news/%d/markRead/p',
            'urlGet': '/news/%d'
        };

    $(document).scroll(function() {
            if (timer) {
                clearTimeout(timer);
            }
            timer = setTimeout(markRead, 100);
    }).on('click', '#news-bar .title a', show);

    function markRead()
    {
        var $news,
            offset,
            scrolled = $('html').scrollTop();

        $('#news-bar .item:not(".read")').each(function() {
            $news = $(this);
            offset = $news.offset();

            if (offset.top < scrolled) {
                $news.addClass('read');
                $.post(
                    config.urlSkip.replace('%d', $news.data('id'))
                );
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
        $modalLink.attr('href', $newsLink.attr('href'));

        $.get(
            config.urlGet.replace('%d', $news.data('id'))
        ).success(
            function(data) {
                $modalText.html(data.text);
            }
        );

        $modal.modal({});

        $news.addClass('read');
        $.post(
            config.urlRead.replace('%d', $news.data('id'))
        );

        return false;
    }
});