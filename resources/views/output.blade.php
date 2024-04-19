<picture class="flexipic">
    @foreach ($sources as $source)
        <source {!! $source !!} />
    @endforeach

    <img {!! $attributes !!} />
</picture>