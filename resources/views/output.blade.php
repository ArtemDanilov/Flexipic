<picture class="flexipic {{ $wrapper_class }}">
    @foreach ($sources as $source_attributes)
        <source {!! $source_attributes !!} />
    @endforeach

    <img {!! $attributes !!} />
</picture>