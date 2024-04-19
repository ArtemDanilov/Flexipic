<?php

namespace Artemdanilov\Flexipic\Tags;

use ErrorException;
use Statamic\Tags\Tags;
use Statamic\Statamic;
use Statamic\Facades\Asset;
use Artemdanilov\Flexipic\Placeholders\Blurhash;
use Statamic\Tags\Concerns\RendersAttributes;

class Flexipic extends Tags
{
    use RendersAttributes;

    public function index()
    {
        $src = $this->params->get('src');
        $width = $this->params->get('width');
        $height = $this->params->get('height');

        $asset = Asset::findByUrl($src);
        $isOutsider = strpos($src, 'http') === 0;

        if ($isOutsider && (!$width = $this->params->get('width') || !$height = $this->params->get('height'))) {
            throw new ErrorException('Error: properties "width" and "height" are invalid or not found.');
        } elseif (!$isOutsider && !$asset) {
            throw new ErrorException('Error: property "src" is invalid or not found.');
        }

        $data = [
            'src' => $src ?? $asset->url,
            'width' => $width ?? $asset->width,
            'height' => $height ?? $asset->height,
            'alt' => $this->params->get('alt') ?? ($asset->alt ?? ''),
            'fit' => $this->params->get('fit') ?? config('statamic.flexipic.fit'),
            'quality' => $this->params->get('quality') ?? config('statamic.flexipic.quality'),
            'sizes' => $this->params->get('sizes') ?? config('statamic.flexipic.sizes'),
            'loading' => $this->params->get('loading') ?? config('statamic.flexipic.loading'),
            'placeholder' => $this->params->get('placeholder') ?? config('statamic.flexipic.placeholder')
        ];

        $data = array_merge($data, $this->params->all());

        return $this->createPicture($data);
    }

    public function createPicture($data)
    {
        $excluded_attrs = ['placeholder', 'fit', 'quality', 'image_sizes', 'sizes'];

        $glide = Statamic::tag('glide:generate')
            ->src($data['src'])
            ->width($data['width'])
            ->height($data['height'])
            ->fit('crop')
            ->quality($data['quality']);

        $filtered_data = array_filter($data, fn($key) => !in_array($key, $excluded_attrs), ARRAY_FILTER_USE_KEY);

        if (isset($data['placeholder'])) {
            if ($data['placeholder'] === 'blur') {
                $filtered_data['src'] = $this->createBlurPlaceholder($data);
            } else {
                $filtered_data['src'] = $data['placeholder'];
            }

            $filtered_data['data-src'] = $glide[0]['url'];
        }

        return view('flexipic::output', [
            'sources' => $this->generatePictureSources($data),
            'attributes' => $this->renderAttributes($filtered_data)
        ])->render();
    }

    protected function generatePictureSources($data)
    {
        $formats = config('statamic.flexipic.formats') ?? ['jpeg'];

        $sources = array_map(function ($format) use ($data) {
            $image_sizes = json_decode($data['image_sizes'] ?? '') ?? config('statamic.flexipic.image_sizes');
            
            $urls = collect($image_sizes)->map(function ($size) use ($data, $format) {
                $width = $size['w'] ?? $size;
                $height = $size['h'] ?? $data['height'];

                $glide = Statamic::tag('glide:generate')
                    ->src($data['src'])
                    ->width($width)
                    ->height(round(($width * $height) / $data['width']))
                    ->format($format)
                    ->fit($data['fit'])
                    ->quality($data['quality']);

                return "{$glide[0]['url']} {$width}w";
            })->implode(", ");

            $sources_data = [
                'type' => "image/$format",
                'sizes' => $data['sizes']
            ];

            if (isset($data['placeholder'])) {
                $sources_data['data-srcset'] = $urls;
            } else {
                $sources_data['srcset'] = $urls;
            }

            return $this->renderAttributes($sources_data);
        }, $formats);

        return $sources;
    }

    protected function createBlurPlaceholder($source)
    {
        $blurhash = new Blurhash($source['src']);

        return 'data:image/png;base64,' . base64_encode($blurhash->create());
    }
}
