<script type="text/html" id="{{ $template ?? null }}">
    <div class="card repeater-item" data-sort="@{{it.block_key}}">
        <div class="card-header">
            <h5 class="actions">
                <span class="action card-handle icon-size-fullscreen" data-parent-container-key="@{{it.name}}">
                    <x-orchid-icon path="cursor-move" class="small me-2"></x-orchid-icon>
                </span>
                @if($collapse ?? false)
                <span class="action collapse-switch" data-action="click->fields--repeater#collapse">
                    <x-orchid-icon path="arrow-down" class="small me-2 transition"></x-orchid-icon>
                </span>
                @endif
                <span class="action icon-plus" data-action="click->fields--repeater#addBlockAfter">
                    <x-orchid-icon path="plus" class="small me-2"></x-orchid-icon>
                </span>
                <span class="action" data-action="click->fields--repeater#deleteBlock">
                    <x-orchid-icon path="minus" class="small me-2"></x-orchid-icon>
                </span>
                <span class="badge badge-success small v-top bg-primary text-white pull-right"
                      data-target="fields--repeater.repeaterBlockCount">@{{it.block_count}}</span>
            </h5>
        </div>
        <div class="card-body repeater-content">
            @{{it.content}}
        </div>
    </div>
</script>
