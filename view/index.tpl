{extends file='layout.tpl'}

{block "content"}
    <script language="JavaScript">
        config = {ldelim}
            feedId: {if $feedId}{$feedId}{else}null{/if},
            isUnread: {if $isUnread}true{else}false{/if}
        {rdelim}
    </script>

    <div id="left-bar">
        <div id="feeds-bar">
            <div class="item">
                <div class="counter js-counter-total">{if !empty($newsCounts.total)}{$newsCounts.total}{/if}</div>
                <div class="title">
                    {if !$feedId}
                        <span class="active">Всё</span>
                    {else}
                        <a class="block no-underline" href="/"><span>Всё</span></a>
                    {/if}
                </div>
            </div>
            {foreach from=$feeds item=feed}
                <div class="item">
                    <div class="counter js-counter-{$feed->id}">{if !empty($newsCounts[$feed->id])}{$newsCounts[$feed->id]}{/if}</div>
                    <div class="title">
                        {if $feedId && $feed->id == $feedId}
                            <span class="active">{$feed->title}</span>
                        {else}
                            <a class="block no-underline" href="/{$feed->id}/"><span>{$feed->title}</span></a>
                        {/if}
                    </div>
                </div>
                <div class="clearfix"></div>
            {/foreach}
        </div>
    </div>

    <div id="content-bar">
        <div id="news-bar">
            {include file="blocks/news.tpl"}
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="news-modal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-rz">
        <div class="modal-content item">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title title">
                <a class="block no-underline" target="_blank"><span></span></a>
            </h4>
          </div>
          <div class="modal-body">
              <div class="text"></div>
          </div>
          <div class="modal-footer link">
              <a target="_blank" class="block"><span>Перейти на сайт</span></a>
          </div>
        </div>
      </div>
    </div>

{/block}