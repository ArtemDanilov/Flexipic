## Flexipic Statamic Addon

### Overview

Flexipic is a Statamic addon designed to effortlessly generate responsive images on-the-fly using Glide and integrate them seamlessly into your Statamic projects using the `picture` tag.

### Installation

Require it using Composer.

    composer require artemdanilov/flexipic
    
After installation, Flexipic will create `config/statamic/flexipic.php` file.

### Usage

To enable Flexipic and generate responsive images, simply insert the following line of code wherever you intend to display an image:

    {{ flexipic :src="assets" }}

or

    {{ flexipic src="url_to_image" }}

It will generate a `<picture>` tag with `<source>` and `<img>` tags inside.

In example above, images are generated based on parameters from the **config** file. These parameters are necessary for global usage. However, you are free to rewrite these parameters directly in your tag:

    {{ flexipic
        :src="image"
        :width="[320, 640]"
    }}

In the example above, the tag will generate two images with widths of **320px** and **640px** respectively. As you may notice, we do not specify a height property, as the height is calculated automatically based on meta tags. If you want to set another height for your image, feel free to do so:

    {{ flexipic
        :src="image"
        :width="[320, 640]"
        height="400"
    }}

The tag will set a height of **400px** for the first value from the `width` array and calculate the height for the second value.

Of course, there are more parameters you can use inside.

:::tip If your image URL is from an external source, you need to specify a height. :::

### Available parameters

Flexipic supports various parameters that you can include in your tag, each of which will be generated as HTML attributes. However, certain parameters are excluded from generating attributes, like `quality`, `fit` or `placeholder`

###
|Parameters|Values|description|
|--|--|--|
| `src` | url or path | Specifies the source URL or path of the image |
| `width` | [480, 768, 1024, ...] | Specifies the width(s) of the image in pixels |
| `height` | number | Specifies the height of the image in pixels |
| `sizes` | (max-width: 320px) 480px, 768px | Specifies the sizes of the image in a responsive context |
| `quality` | 0-100 | Specifies the image quality, which affects file size and compression level |
| `fit` | contain, max, fill, fill-max, stretch, crop, crop-focal | Sets how the image is fitted to its target dimensions |
| `loading` | eager or lazy | Specifies whether the image should be loaded immediately (eager) or lazily (lazy). |
| `placeholder` | "blur" or your custom value | Specifies whether a placeholder should be generated for lazy loading purposes |
| `class` | string of classes | Specifies additional CSS classes to apply to the image element. |

### Lazyloading

If you set `loading` and `placeholder` parameters for smooth lazyload effect, you should to pass a **JS** function to your `resources/js/site.js` file

    import flexipicLazyload from '../../vendor/artemdanilov/flexipic/dist/flexipicLazyload.min';

    window.addEventListener('DOMContentLoaded', () => {
        flexipicLazyload('.flexipic');
    })

### Support

If you encounter any issues or have questions about using Flexipic, please don't hesitate to reach out to me artemdanilow@gmail.com

### License

Flexipic is licensed under the MIT License. Feel free to use, modify, and distribute it according to your needs.