Provides a wrapper for the lipsum.com API to generate Lorem Ipsum text. Useful for generating test fixture data.

Features
========

This bundle allows to easily use generate default Lorem Ipsum text in your features. It relies on the API of the
webservice at www.lipsum.com

Installation
-----------------------------

Use Composer
::

"require": {
    "php": ">=5.3.2",
    "symfony/symfony": "2.1.*",
    "_comment": "your other packages",

    "fourchimps/lipsum-bundle": "master",
}

This will automatically set up the autoloading

You still need to register the bundle with your app/AppKernel.php by adding the following

::

new FourChimps\LipsumBundle\FourChimpsLipsumBundle()

DEPS installation is (currently) possible in the usual way for 2.0 but as the symfony require is 2.1.* I wont consider
it a BC break if this gets lost at some point.