## Flexipic Statamic Addon

### Overview

Flexipic is a Statamic addon designed to effortlessly generate responsive images on-the-fly using Glide and integrate them seamlessly into your Statamic projects using the `picture` tag.

### Installation

Require it using Composer.

    composer require artemdanilov/flexipic
    
After installation, Flexipic will create the following files:

 -   **flexipic.php**: This configuration file will be generated in the `config/statamic` directory.

### Usage

To enable Flexipic and generate responsive images, simply insert the following line of code wherever you intend to display an image

    {{ flexipic :src="url_to_your_image_asset" }}

### Available parameters

Flexipic supports various parameters that you can include in your tag, each of which will be generated as HTML attributes. However, certain parameters are excluded from generating attributes.

###
|Parameters|Values|description|
|--|--|--|
| quality | 0-100 | Specifies the image quality, which affects file size and compression level |
| fit | contain, max, fill, fill-max, stretch, crop, crop-focal | Sets how the image is fitted to its target dimensions |
| image_sizes | [375, 480, 640, ...] | Specifies the sizes of the image in `w`. |
| placeholder | "blur" or your custom value |Specifies whether a placeholder should be generated for lazy loading purposes |

### Example

    {{ flexipic
        :src="image"
        width="320"
        height="280"
        image_sizes="[320, 640]"
        sizes="(min-width: 375px) 320px"
        loading="lazy"
        decoding="async"
        placeholder="blur"
        class="w-full h-full"
    }}

### Global parameters
You also can specife `formats` and global parameters like `image_sizes`, `sizes`, `quality`, `fit`, `loading`, `placeholder` in configuration file.

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