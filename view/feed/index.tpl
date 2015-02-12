{extends file='layout.tpl'}

{block "content"}
    <h1>{$feed->title}</h1>
    <p>
        {$feed->description}
    </p>

    {foreach from=$news item=new}
        <strong>{$new->title}</strong>
        <p>
            {$new->text}
        </p>
    {/foreach}

{/block}