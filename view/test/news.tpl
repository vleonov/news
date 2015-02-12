{extends file='layout.tpl'}

{block "content"}

    <div id="content" style="padding: 20px">
        {foreach from=$distr key=categoryId item=coeff}
            <p>
                <a href="/test/news/{$categoryId}">{$categories[$categoryId]->title}</a>
                <sup>{$categoryId}</sup> {$coeff}
            </p>
        {/foreach}

    </div>


{/block}