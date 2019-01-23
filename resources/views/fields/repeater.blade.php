@component($typeForm,get_defined_vars())
    <div data-controller="fields--repeater" data-id="{{ $id }}" class="repeater">
        <div class="group">
            @foreach($fields as $field)
                {!! $field !!}
            @endforeach
        </div>
    </div>
@endcomponent