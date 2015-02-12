{extends file='layout.tpl'}

{block "content"}

    <div id="left-bar">
        <div id="feeds-bar">
            {foreach from=$newsCounts key=feedId item=counter}
                <div class="item">
                    <div class="title">
                        {if $feedId == 'total'}
                            {if !$feed}
                                <span class="active">Всё</span>
                            {else}
                                <a class="no-underline" href="/">Всё</a>
                            {/if}
                        {else}
                            {if $feed && $feed->id == $feedId}
                                <span class="active">{$feeds[$feedId]->title}</span>
                            {else}
                                <a class="no-underline" href="/{$feedId}/">{$feeds[$feedId]->title}</a>
                            {/if}
                        {/if}
                    </div>
                    <div class="counter">{if $counter>0}{$counter}{/if}</div>
                </div>
                <div class="clearfix"></div>
            {/foreach}
        </div>
    </div>

    <div id="content-bar">
        <div id="news-bar">
            {foreach from=$news item=new}
                <div class="item" data-id="{$new->id}">
                    <div class="title">
                        <a href="/news/{$new->id}/go" target="_blank" class="no-underline">{$new->title|escape}</a>
                    </div>
                    <div class="text">
                        {$new->descr|strip_tags}
                    </div>
                    <div class="meta">
                        {$feeds[$new->feedId]->title}
                    </div>
                </div>
            {/foreach}
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="news-modal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-rz">
        <div class="modal-content item">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title title"></h4>
          </div>
          <div class="modal-body">
              <div class="text"></div>
          </div>
          <div class="modal-footer link">
              <a target="_blank">Перейти на сайт</a>
          </div>
        </div>
      </div>
    </div>

{/block}