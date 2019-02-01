@component($typeForm,get_defined_vars())
    <div class="repeater"
         data-controller="fields--repeater"
         data-fields--repeater-name="{{ $name }}"
         data-fields--repeater-handler="{{$handler}}"
         data-fields--repeater-options="{{ json_encode($attributes) }}"
         data-fields--repeater-url="{{route('platform.systems.widget', Base64Url\Base64Url::encode($handler))}}"
         data-fields--repeater-value="{{ json_encode($value) }}">
        <input type="hidden" name="{{ $name }}" value=""/>
        <div class="row">
            <div class="col-md-12">
                <section class="repeaters_container" data-target="fields--repeater.repeaterContainer"></section>
                <span data-action="click->fields--repeater#addBlock" class="icon-plus add_block"
                      data-target="fields--repeater.addBlockButton"
                      data-toggle="tooltip" data-placement="top" title="{{ __('Add new block') }}"></span>
            </div>
        </div>

        @include('platform::partials.fields._repeater_field_template')
    </div>
@endcomponent