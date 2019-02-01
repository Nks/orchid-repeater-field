<script type="text/html" data-target="fields--repeater.template">
    <div class="card repeater-item" data-sort="@{{block_key}}">
        <div class="card-header">
            <h5 class="actions">
                <span class="action card-handle icon-size-fullscreen"></span>
                <span class="action icon-plus" data-action="click->fields--repeater#addBlock"
                      data-toggle="tooltip" data-placement="top" title="{{ __('Add new block below') }}"></span>
                <span class="action icon-minus" data-action="click->fields--repeater#deleteBlock"
                      data-toggle="tooltip" data-placement="top" title="{{ __('Delete block') }}"></span>
                <span class="badge badge-light pull-right"
                      data-target="fields--repeater.repeaterBlockCount">@{{block_count}}</span>
            </h5>
        </div>
        <div class="card-body repeater-content">
            @{{content}}
        </div>
    </div>
</script>