ImageHash
=========

[![Latest Stable Version](http://img.shields.io/github/release/jenssegers/php-imagehash.svg)](https://packagist.org/packages/jenssegers/imagehash) [![Build Status](http://img.shields.io/travis/jenssegers/php-imagehash.svg)](https://travis-ci.org/jenssegers/php-imagehash) [![Coverage Status](http://img.shields.io/coveralls/jenssegers/php-imagehash.svg)](https://coveralls.io/r/jenssegers/php-imagehash)

> A perceptual hash is a fingerprint of a multimedia file derived from various features from its content. Unlike cryptographic hash functions which rely on the avalanche effect of small changes in input leading to drastic changes in the output, perceptual hashes are "close" to one another if the features are similar.

Perceptual hashes are a different concept compared to cryptographic hash functions like MD5 and SHA1. With cryptographic hashes, the hash values are random. The data used to generate the hash acts like a random seed, so the same data will generate the same result, but different data will create different results. Comparing two SHA1 hash values really only tells you two things. If the hashes are different, then the data is different. And if the hashes are the same, then the data is likely the same. In contrast, perceptual hashes can be compared -- giving you a sense of similarity between the two data sets.

This code was based on:
 - https://github.com/kennethrapp/phasher
 - http://www.phash.org
 - http://www.hackerfactor.com/blog/index.php?/archives/529-Kind-of-Like-That.html
 - http://www.hackerfactor.com/blog/?/archives/432-Looks-Like-It.html
 - http://blog.iconfinder.com/detecting-duplicate-images-using-python

**WARNING**: the PerceptualHash implementation is still under development.

Installation
------------

Install using composer:

	composer require jenssegers/imagehash

Usage
-----

Calculating a perceptual hash for an image using the default implementation:

```php
$hasher = new Jenssegers\ImageHash\ImageHash;
$hash = $hasher->hash('path/to/image.jpg');
```

The hamming distance is used to compare hashes. Low values will indicate that the images are similar or the same, high values indicate that the images are different. Use the following method to detect if images are the same or not:

	$distance = $hasher->distance($hash1, $hash2);

Equal images will not have a distance of 0, so you will need to decided at which distance you will evaluate images. For the images I tested with, a distance between 5 and 10 usually works.

Calculating a perceptual hash for an image using a different implementation:

```php
$implementation = new Jenssegers\ImageHash\Implementation\DifferenceHash;
$hasher = new Jenssegers\ImageHash\ImageHash($implementation);
$hash = $hasher->hash('path/to/image.jpg');
```

Compare 2 images and get their hamming distance:

```php
$hasher = new Jenssegers\ImageHash\ImageHash;
$distance = $hasher->compare('path/to/image1.jpg', 'path/to/image2.jpg');
```

Demo
----

These images are similar:

![Equals1](https://raw.githubusercontent.com/jenssegers/php-imagehash/master/tests/images/forest/forest-high.jpg)
![Equals2](https://raw.githubusercontent.com/jenssegers/php-imagehash/master/tests/images/forest/forest-copyright.jpg)

	Image 1 hash: 4340922596638727710 (0011110000111110000011100001101000111010000111100001111000011110)
	Image 2 hash: 4340922751324659230 (0011110000111110000011100011111000111110000111100001111000011110)
	Hamming distance: 3

These images are diferent:

![Equals1](https://github.com/jenssegers/php-imagehash/raw/master/tests/images/office/tumblr_ndyfnr7lk21tubinno1_1280.jpg)
![Equals2](https://raw.githubusercontent.com/jenssegers/php-imagehash/master/tests/images/office/tumblr_ndyfq386o41tubinno1_1280.jpg)

	Image 1 hash: 2929776999984224055 (0010100010101000101010001010100010101011001010110101011100110111)
	Image 2 hash: 8138271516244915535 (0111000011110000111100101101001101011011011101010011010101001111)
	Hamming distance: 32
