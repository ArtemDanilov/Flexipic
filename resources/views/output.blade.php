<picture class="flexipic">
    @foreach ($sources as $source_attributes)
        <source {!! $source_attributes !!} />
    @endforeach

    <img {!! $attributes !!} />
</picture>