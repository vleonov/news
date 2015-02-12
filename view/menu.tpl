<!-- Navbar
================================================== -->
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <a href="/" class="navbar-brand no-underline">
                news
            </a>
        </div>
        <nav class="collapse navbar-collapse bs-navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <form action="/feed/add/" method="post" style="padding: 8px 15px; width: 350px">
                        <div class="input-group">
                            <input type="text" name="feedUrl" class="form-control input-" placeholder="Feed url">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="submit">Add</button>
                            </span>
                        </div>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</nav>