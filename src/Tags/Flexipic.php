<?php

namespace ArtemDanilov\Flexipic\Tags;

use ErrorException;
use Statamic\Tags\Tags;
use Statamic\Statamic;
use Statamic\Facades\Asset;

class Flexipic extends Tags
{
    /**
     * The {{ flexipic }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        $url = $this->params->get('url');
        $width = $this->params->get('width');
        $height = $this->params->get('height');
        $alt = $this->params->get('alt');
        $fit = $this->params->get('fit');
        $quality = $this->params->get('quality');
        $sizes = $this->params->get('sizes');
        $loading = $this->params->get('loading');
        $placeholder = $this->params->get('placeholder');

        $assets_data = Asset::findByUrl($url ?? $this->context->get('url')->raw());

        $isOutsider = strpos($url, 'http') === 0;

        if ($isOutsider && (!$width || !$height)) {
            throw new ErrorException('Error: properties "width" and "height" are invalid or not found.');
        } elseif (!$isOutsider && !$assets_data) {
            throw new ErrorException('Error: property "url" is invalid or not found.');
        }

        $data = [
            'src' => $url ?? $assets_data->url,
            'width' => $width ?? $assets_data->width,
            'height' => $height ?? $assets_data->height,
            'alt' => $alt ?? ($assets_data->alt ?? ''),
            'fit' => $fit ?? config('statamic.flexipic.fit'),
            'quality' => $quality ?? config('statamic.flexipic.quality'),
            'sizes' => $sizes ?? config('statamic.flexipic.sizes'),
            'loading' => $loading ?? config('statamic.flexipic.loading'),
            'placeholder' => $placeholder ?? config('statamic.flexipic.placeholder')
        ];

        $data = array_merge($data, $this->params->all());

        return $this->createPicture($data);
    }

    public function createPicture($data)
    {
        $glide = Statamic::tag('glide:generate')
            ->src($data['src'])
            ->width($data['width'])
            ->height($data['height'])
            ->fit('crop')
            ->quality($data['quality']);

        $excluded_attrs = ['placeholder', 'fit', 'quality', 'image_sizes', 'sizes'];
        $attributes = '';
        
        foreach ($data as $key => $value) {
            $is_attr_available = !in_array($key, $excluded_attrs);

            if ($is_attr_available && $value !== '') {
                if ($key === 'src') {
                    $value = $glide[0]['url'];

                    if (isset($data['placeholder'])) {
                        $key = 'data-src';
                        $attributes .= "decoding='async'";
                    }
                }

                $attributes .= "{$key}='{$value}' ";
            }

            if ($key === 'placeholder') {
                if ($value === 'blur') {
                    $attributes .= "src='{$this->createBlurPlaceholder($data)}' ";
                } else {
                    $attributes .= "src='{$data['placeholder']}' ";
                }
            }
        }

        $attributes = trim($attributes);

        return " 
            <picture>
                {$this->generatePictureSources($data)}
                <img {$attributes} />
            </picture>
        ";
    }

    private function generatePictureSources($data)
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

            $srcset = "srcset='$urls'";
            $placeholder = '';

            if (isset($data['placeholder'])) {
                $srcset = "data-srcset='$urls'";

                if ($data['placeholder'] === 'blur') {
                    $placeholder = 'srcset=' . $this->createBlurPlaceholder($data);
                }
            }

            return "<source $srcset $placeholder type='image/$format' sizes='{$data['sizes']}' />";
        }, $formats);

        return implode("", $sources);
    }

    private function createBlurPlaceholder($source)
    {
        $width = 16;
        $height = round(($width * $source['height']) / $source['width']);

        $glide = Statamic::tag('glide:generate')
            ->src($source['src'])
            ->width($width)
            ->height($height)
            ->blur(8)
            ->format('jpeg')
            ->fit($source['fit'])
            ->quality(60);

        return $glide[0]['url'];
    }
}
