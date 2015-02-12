{extends file='layout.tpl'}

{block "content"}

    <div id="content" style="padding: 20px">
        <form action="" method="get">

            <input class="input-xxlarge" type="text" name="feed" value="{$feed.url}" style="margin: 0"/>
            <input class="input btn" type="submit" value="go"/>

        </form>

        <p>
            <h1>{$feed.title}</h1>
            {$feed.description}
        </p>

        {foreach from=$result key=sentence item=words}
            <p>
                <br>
                {$sentence}<br>
                {foreach from=$words item=word}
                    <span style="color: rgb({$colors[$word]|default:0},{$colors[$word]|default:0},{$colors[$word]|default:0});">
                        <span style="{if $grammas[$word]|default:0 == 2}text-decoration: underline{/if}">{$word}</span><sup>{$freqs[$word]|default:0}</sup>
                    </span>
                {/foreach}
            </p>
        {/foreach}

    </div>


{/block}