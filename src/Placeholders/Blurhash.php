<?php

namespace Artemdanilov\Flexipic\Placeholders;

use Bepsvpt\Blurhash\Facades\BlurHash as BlurHashFacade;
use Statamic\Contracts\Assets\Asset as AssetContract;

class Blurhash
{
    public $image;
    public $width;
    public $height;

    public function __construct($image_url = null, $width = 64, $height = 64)
    {
        $this->image = $image_url;
        $this->width = $width;
        $this->height = $height;
    }
    
    public function create()
    {
        $hash = $this->encode($this->image);
        
        return $this->decode($hash);
    }

    protected function encode($image = null)
    {   
        if ($image instanceof AssetContract) {
            $image = $image->contents();
        }

        return BlurHashFacade::encode($image);
    }

    protected function decode($encodedString = null)
    {
        $image = BlurHashFacade::decode($encodedString, $this->width, $this->height);
        
        return $image->encode('jpg', 90);
    }
}