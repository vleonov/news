<!DOCTYPE html>
<html lang="ru">
  <head>
    <title>news</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="vleonov">

    <base href="{$BaseHref}">

    <link href="./css/bootstrap.css" rel="stylesheet">
    <link href="./css/base.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Noto+Sans:400,700&subset=cyrillic-ext,latin' rel='stylesheet' type='text/css'>
    {block "css"}{/block}

    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./js/reader.js"></script>
    <script src="./js/switcher.js"></script>
    {block "js"}{/block}

  </head>

  <body>

    {include file="menu.tpl"}

    <div id="content">
      {block "content"}{/block}
    </div>

  </body>

</html>