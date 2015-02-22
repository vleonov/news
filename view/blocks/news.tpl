{foreach from=$news item=new}
    <div class="item {if !$isUnread}read{/if}" data-id="{$new->id}" data-feed-id="{$new->feedId}" data-publicated-at="{$new->publicatedAt|date_format:'U'}">
        <div class="title">
            <a href="./news/{$new->id}/go" target="_blank" class="block no-underline"><span>{$new->title|escape}</span></a>
        </div>
        <div class="text">
            {$new->descr|strip_tags}
        </div>
        <div class="meta">
            <span>{$feeds[$new->feedId]->title}</span>
            <span class="dot"></span>
            <span>{$new->publicatedAt|date:'H:i':'d M'}</span>
            <span class="dot"></span>
            <span>{if isset($coeff[$new->id])}{$coeff[$new->id]}{else}0{/if}</span>
        </div>
    </div>
{/foreach}