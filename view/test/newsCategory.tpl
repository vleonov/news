{extends file='layout.tpl'}

{block "content"}

    <div id="content" style="padding: 20px">
        <h3>
            <a href="/test/news/">&larr;</a>
            {$category->title}
        </h3>

        {foreach from=$news item=new}
            <p style="padding-bottom: 15px">
                {$new->title}
                <a href="{$new->url}" target="_blank"><i class="icon-share-alt"></i></a>
                <br>
                <small>{$new->text|strip_tags}</small><br>
                <small class="muted">
                    {foreach from=$new->tags item=tag name=l}
                        {$tag}{if not $smarty.foreach.l.last}, {/if}
                    {/foreach}
                </small>
            </p>
        {/foreach}

    </div>

{/block}