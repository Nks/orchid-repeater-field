@component($typeForm,get_defined_vars())
    <div class="repeater" data-controller="fields--repeater" data-id="accordion">
        <div class="row">
            <div id="accordion" class="col-md-12">
                <div class="card repeater-item" data-id="{{ $id }}">
                    <div class="card-header" id="heading{{$id}}">
                        <h5 class="mb-0 actions">
                            <span class="card-hcopy icon-plus"></span>
                            <span class="card-hdelete icon-minus"></span>
                        </h5>
                    </div>
                    <div id="collapse{{$id}}"
                         class="collapse show"
                         aria-labelledby="heading{{$id}}"
                         data-parent="#accordion">
                        <div class="card-body">
                            @foreach($fields as $field)
                                {!! $field !!}
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endcomponent