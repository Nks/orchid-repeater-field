<script type="text/html" id="{{ $template ?? null }}">
    <div class="card repeater-item" data-sort="@{{block_key}}">
        <div class="card-header">
            <h5 class="actions mb-0">
                <span class="action card-handle icon-size-fullscreen" data-parent-container-key="@{{name}}"></span>
                <span class="action icon-plus" data-action="click->fields--repeater#addBlockAfter"></span>
                <span class="action icon-minus" data-action="click->fields--repeater#deleteBlock"></span>
                <span class="badge badge-light pull-right"
                      data-target="fields--repeater.repeaterBlockCount">@{{block_count}}</span>
            </h5>
        </div>
        <div class="card-body repeater-content">
            @{{content}}
        </div>
    </div>
</script>
