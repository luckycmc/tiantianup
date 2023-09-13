<div class="image-gallery">
    @foreach ($images as $image)
        <img src="{{ $image->url }}" alt="Product Image">
    @endforeach
</div>