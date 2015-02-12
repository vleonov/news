{extends file='layout.tpl'}

{block "content"}

    <div id="content" style="padding: 20px">

        <div class="pull-left">
            <form action="" method="post">

                <textarea class="input-xlarge" name="text" rows="28" style="margin: 0">{$text}</textarea>
                <br>
                <input class="input btn btn-large" type="submit" value="go"/>

            </form>
        </div>

        <div style="padding-left: 400px; display: block">
        {*<div style="padding-left: 100px; display: block; -moz-column-width: 200px; -webkit-column-width: 200px; column-width: 200px;">*}
            {foreach from=$sort key=categoryId item=f}

                <span title="{foreach from=$categoryToWords[$categoryId] item=word}{$word} ({$data[$word]}) {/foreach}">
                    {$f} {$categories[$categoryId].title} {$categoryId}
                </span>
                <sup style="white-space: nowrap">
                    {$data[$categoryId]}
                </sup>
                <br>
            {/foreach}
        </div>

    </div>


{/block}