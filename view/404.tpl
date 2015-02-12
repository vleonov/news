{extends "layout.tpl"}

{block "content"}
    <div class="error-404">
        <h1>404, sorry</h1>
        <strong>{$errorText|default:'the requested page not found'}</strong>
    </div>
{/block}