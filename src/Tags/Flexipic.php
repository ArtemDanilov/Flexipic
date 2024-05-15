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

    /**
     * The {{ flexipic }} tag
     *
     * @return string
     */
    public function index()
    {
        $src = $this->getParam('src') ?? '';

        if (!$src) {
            return '';
        }

        $width = $this->getParam('width', [480, 768, 1024]);
        $height = $this->getParam('height');

        $asset = Asset::findByUrl($src);
        $isOutsider = strpos($src, 'http') === 0;

        if ($isOutsider && !$height) {
            throw new ErrorException('Error: property "height" is invalid or not found.');
        }

        $data = [
            'src' => $asset ?? $src,
            'width' => $width,
            'height' => $height,
            'alt' => $this->getParam('alt', ($asset->alt ?? null)),
            'fit' => $this->getParam('fit', 'crop_focal'),
            'quality' => $this->getParam('quality', '75'),
            'sizes' => $this->getParam('sizes', '100vw'),
            'loading' => $this->getParam('loading', 'eager'),
            'placeholder' => $this->getParam('placeholder'),
        ];

        $data = array_merge($this->params->all(), $data);

        return $this->createPicture($data);
    }

    public function createPicture($data)
    {
        $sources = $this->generatePictureSources($data);

        $image_attrs = $this->generateImgAttributes($sources, $data);
        $sources_attrs = $this->generateSourcesAttributes($sources);

        return view('flexipic::output', [
            'sources' => array_map(fn ($source) => $this->renderAttributes($source), $sources_attrs),
            'attributes' => $this->renderAttributes($image_attrs),
            'wrapper_class' => $this->getParam('wrapper_class') ?? ''
        ])->render();
    }

    public function generateImgAttributes($sources, $data)
    {
        $excluded_attrs = ['placeholder', 'fit', 'quality', 'width', 'sizes', 'wrapper_class'];
        $attrs = $this->removeAttributes($data, $excluded_attrs);

        $index = $this->getIndexFromArray($data['width']);
        $source_image = $sources[0]['images'][$index];

        $attrs['src'] = $source_image['url'];
        $attrs['width'] = $source_image['width'];
        $attrs['height'] = $source_image['height'];

        $placeholder = $this->getParam('placeholder');

        if (isset($placeholder) && $placeholder !== false) {
            $attrs['src'] = ($placeholder === 'blur') ? $this->createBlurPlaceholder($data['src']) : $placeholder;
            $attrs['data-src'] = $source_image['url'];
        }

        return $attrs;
    }

    protected function generateSourcesAttributes($sources)
    {
        return array_map(function ($source) {
            $srcset_string = $this->stringifyPictureSources($source['images']);
            $srcset_attr = $this->getParam('placeholder') ? 'data-srcset' : 'srcset';

            return [
                $srcset_attr => $srcset_string,
                'type' => "image/{$source['format']}",
                'sizes' => $this->getParam('sizes')
            ];
        }, $sources);
    }

    protected function generatePictureSources($data)
    {
        $formats = config('statamic.flexipic.formats') ?? ['jpeg'];

        $sources = array_map(function ($format) use ($data) {
            $urls = collect($data['width'])->map(function ($size) use ($data, $format) {
                $height = $this->calculateHeight($size);

                $glide = Statamic::tag('glide:generate')
                    ->src($data['src'])
                    ->width($size)
                    ->height($height)
                    ->format($format)
                    ->fit($data['fit'])
                    ->quality($data['quality']);

                return [
                    'url' => $glide[0]['url'],
                    'width' => $size,
                    'height' => $height,
                ];
            });

            return [
                'images' => $urls,
                'format' => $format,
            ];
        }, $formats);

        return $sources;
    }

    protected function createBlurPlaceholder($asset)
    {
        $width = 64;
        $height = $this->calculateHeight($width);

        $blurhash = new Blurhash($asset, $width, $height);

        return 'data:image/png;base64,' . base64_encode($blurhash->create());
    }

    protected function getIndexFromArray($sizes)
    {
        $length = count($sizes);
        return $length <= 2 ? 0 : round($length / 1.25) - 1;
    }

    protected function stringifyPictureSources($array_with_sources)
    {
        $srcset = collect($array_with_sources)->map(function ($source) {
            return $source['url'] . ' ' . $source['width'] . 'w';
        });

        return $srcset->implode(', ');
    }

    protected function calculateHeight($size)
    {
        $asset = Asset::findByUrl($this->getParam('src'));

        $_WIDTH = $this->getParam('width');
        $_HEIGHT = $this->getParam('height');

        $width = $_HEIGHT ? $_WIDTH[0] : $asset->width;
        $height = $_HEIGHT ?? $asset->height;

        $aspect_ratio = $height / $width;

        return round($size * $aspect_ratio);
    }

    protected function removeAttributes($data, $excluded_attrs)
    {
        return array_filter($data, fn ($key) => !in_array($key, $excluded_attrs), ARRAY_FILTER_USE_KEY);
    }

    protected function getParam($key, $default = null)
    {
        return $this->params->get($key) ?? config("statamic.flexipic.$key", $default);
    }
}
