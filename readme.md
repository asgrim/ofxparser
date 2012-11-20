OFX parser bundle
=================

##Installation

Add GrimforOfxParserBundle in your composer.json:

```js
{
    "require": {
        "grimfor/ofxparser": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update grimfor/ofxparser
```

Add GrimforOfxParserBundle to your AppKernel.php

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Grimfor\OfxParserBundle\GrimforOfxParserBundle(),
    );
}
```