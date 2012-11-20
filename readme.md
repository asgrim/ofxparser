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

## Usage

``` php
// src/Acme/FooBundle/Controller/BarController.php
<?php

namespace Acme\FooBundle\Controller;
use Grimfor\OfxParserBundle\Classes\OfxParser;


class BarController extends BaseController
{
    public function fooAction()
    {
        $ofx= new OFXParser();
        $ofx->loadFromFile('example.ofx');
        
        $credits = $ofx->getCredits(); 
        $debits = $ofx->getDebits(); 
        $byDate = $ofx->getByDate(11, 02, 2009); 
        $secMov = $ofx->getMoviment(2); // the second moviment 
        $filtered = $ofx->filter('MEMO', 'DOC', true, true); 

        return $this->render('AcmeFooBundle::layout.html.twig', array(
            'credit' => $credits,
            'debits' => $debits,
            'byDate' => $byDate,
            'secMov' => $secMov,
            'filtered' => $filtered,
        ));
    }
}
```
