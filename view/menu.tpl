<!-- Navbar
================================================== -->
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button data-target=".bs-navbar-collapse" data-toggle="collapse" type="button" class="navbar-toggle collapsed btn btn-default">
                <span class="sr-only">Добавить RSS-ленту</span>
                <span class="glyphicon glyphicon-plus"></span>
            </button>
            <a href="./" class="navbar-brand no-underline">
                news
            </a>
        </div>
        <nav class="collapse navbar-collapse bs-navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <form action="./feed/add/" method="post" style="padding: 8px 15px; width: 350px">
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