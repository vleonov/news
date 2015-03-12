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

  <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container-fluid">
          <div class="navbar-title navbar-header">
              <div class="navbar-brand">
                  <a href="./" class="no-underline">
                      news
                  </a>
                  {block "navbar-title"}{/block}
              </div>
          </div>
          <div class="collapsed navbar-buttons">
              <button data-target=".bs-navbar-collapse" data-toggle="collapse" type="button" class="navbar-toggle btn btn-default">
                  <span class="sr-only">Добавить RSS-ленту</span>
                  <span class="glyphicon glyphicon-plus"></span>
              </button>
          </div>
          <nav class="collapse navbar-collapse bs-navbar-collapse navbar-buttons">
              <ul class="nav navbar-nav navbar-right">
                  <li>
                      <form action="./feed/add/" method="post" style="padding: 8px 25px 7px 15px; width: 350px">
                          <div class="input-group">
                              <input type="text" name="feedUrl" class="form-control input-" placeholder="RSS-лента">
                              <span class="input-group-btn">
                                  <button class="btn btn-default" type="submit">Добавить</button>
                              </span>
                          </div>
                      </form>
                  </li>
              </ul>
          </nav>
      </div>
  </nav>

    <div id="content">
      {block "content"}{/block}
    </div>

  </body>

</html>