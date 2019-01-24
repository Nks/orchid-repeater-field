@component($typeForm,get_defined_vars())
    @php dump($typeForm, get_defined_vars()) @endphp
    <div class="repeater"
         data-controller="fields--repeater"
         data-fields--repeater-id="{{ $id }}"
         data-fields--repeater-name="{{ $name }}"
         data-fields--repeater-handler="{{$handler}}"
         data-fields--repeater-url="{{route('platform.systems.widget', Base64Url\Base64Url::encode($handler))}}"
         data-fields--repeater-count="{{ count($value) }}"
         data-fields--repeater-value="{{ json_encode($value) }}">
        <div class="row">
            <div class="col-md-12">
                <section class="repeaters_container" id="{{ $id }}">
                </section>
            </div>
        </div>


        <script type="text/html" data-target="fields--repeater.template">
            <div class="card repeater-item">
                <div class="card-header">
                    <h5 class="actions">
                        <span class="card-handle icon-size-fullscreen"></span>
                        <span class="card-hcopy icon-plus"></span>
                        <span class="card-hdelete icon-minus"></span>
                    </h5>
                </div>
                <div class="card-body repeater-content">
                    @{{content}}
                </div>
            </div>
        </script>
    </div>
@endcomponent