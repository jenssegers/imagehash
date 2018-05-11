ImageHash
=========

[![Latest Stable Version](http://img.shields.io/github/release/jenssegers/imagehash.svg)](https://packagist.org/packages/jenssegers/imagehash) [![Build Status](http://img.shields.io/travis/jenssegers/imagehash.svg)](https://travis-ci.org/jenssegers/imagehash) [![Coverage Status](http://img.shields.io/coveralls/jenssegers/imagehash.svg)](https://coveralls.io/r/jenssegers/imagehash) [![Donate](https://img.shields.io/badge/donate-paypal-blue.svg)](https://www.paypal.me/jenssegers)

> A perceptual hash is a fingerprint of a multimedia file derived from various features from its content. Unlike cryptographic hash functions which rely on the avalanche effect of small changes in input leading to drastic changes in the output, perceptual hashes are "close" to one another if the features are similar.

<p align="center"><img src="https://jenssegers.com/uploads/images/fingerprint.png"></p>

Perceptual hashes are a different concept compared to cryptographic hash functions like MD5 and SHA1. With cryptographic hashes, the hash values are random. The data used to generate the hash acts like a random seed, so the same data will generate the same result, but different data will create different results. Comparing two SHA1 hash values really only tells you two things. If the hashes are different, then the data is different. And if the hashes are the same, then the data is likely the same. In contrast, perceptual hashes can be compared -- giving you a sense of similarity between the two data sets.

This code was inspired/based on:
 - https://github.com/kennethrapp/phasher
 - http://www.phash.org
 - http://blockhash.io
 - http://www.hackerfactor.com/blog/?/archives/529-Kind-of-Like-That.html
 - http://www.hackerfactor.com/blog/?/archives/432-Looks-Like-It.html
 - http://blog.iconfinder.com/detecting-duplicate-images-using-python

Requirements
------------

 - PHP 5.6 or higher
 - The [gd](http://php.net/manual/en/book.image.php) or [imagick](http://php.net/manual/en/book.imagick.php) extension
 - Optionally, install the [GMP](http://php.net/manual/en/book.gmp.php) extension for faster fingerprint comparisons

Installation
------------

*This package has not reached a stable version yet, backwards compatibility may be broken between 0.x releases. Make sure to lock your version if you intend to use this in production!*

Install using composer:

	composer require jenssegers/imagehash

Usage
-----

The library comes with 4 built-in hashing implementations:

 - `Jenssegers\ImageHash\Implementation\AverageHash` - Hash based the average image color
 - `Jenssegers\ImageHash\Implementation\DifferenceHash` - Hash based on the previous pixel
  - `Jenssegers\ImageHash\Implementation\BlockHash` - Hash based on blockhash.io **Still under development**
 - `Jenssegers\ImageHash\Implementation\PerceptualHash` - The original pHash **Still under development**

Choose one of these implementations. If you don't know which one to use, try the `DifferenceHash` implementation. Some implementations allow some configuration, be sure to check the constructor.

```php
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

$hasher = new ImageHash(new DifferenceHash());
$hash = $hasher->hash('path/to/image.jpg');

echo $hash;
// or
echo $hash->toHex();
```

The resulting `Hash` object, is a hexadecimal image fingerprint that can be stored in your database once calculated. The hamming distance is used to compare two image fingerprints for similarities. Low distance values will indicate that the images are similar or the same, high distance values indicate that the images are different. Use the following method to detect if images are similar or not:

```php
$distance = $hasher->distance($hash1, $hash2);
// or
$distance = $hash1->distance($hash2);
```

Equal images will not always have a distance of 0, so you will need to decide at which distance you will evaluate images as equal. For the image set that I tested, a max distance of 5 was acceptable. But this will depend on the implementation, the images and the number of images. For example; when comparing a small set of images, a lower maximum distances should be acceptable as the chances of false positives are quite low. If however you are comparing a large amount of images, 5 might already be too much.

The `Hash` object can return the internal binary hash in a couple of different format:

```php
echo $hash->toHex(); // 7878787c7c707c3c
echo $hash->toBin(); // 0111100001111000011110000111110001111100011100000111110000111100
echo $hash->toInt(); // 8680820757815655484
```

Choose your preference for storing your hashes in your database. If you want to reconstruct a `Hash` object from a previous calculated value, use:

```php
$hash = Hash::fromHex('7878787c7c707c3c');
$hash = Hash::fromBin('0111100001111000011110000111110001111100011100000111110000111100');
$hash = Hash::fromInt('8680820757815655484');
```

Demo
----

These images are similar:

![Equals1](https://raw.githubusercontent.com/jenssegers/imagehash/master/tests/images/forest/forest-high.jpg)
![Equals2](https://raw.githubusercontent.com/jenssegers/imagehash/master/tests/images/forest/forest-copyright.jpg)

	Image 1 hash: 3c3e0e1a3a1e1e1e (0011110000111110000011100001101000111010000111100001111000011110)
	Image 2 hash: 3c3e0e3e3e1e1e1e (0011110000111110000011100011111000111110000111100001111000011110)
	Hamming distance: 3

These images are different:

![Equals1](https://raw.githubusercontent.com/jenssegers/imagehash/master/tests/images/office/tumblr_ndyfnr7lk21tubinno1_1280.jpg)
![Equals2](https://raw.githubusercontent.com/jenssegers/imagehash/master/tests/images/office/tumblr_ndyfq386o41tubinno1_1280.jpg)

	Image 1 hash: 69684858535b7575 (0010100010101000101010001010100010101011001010110101011100110111)
	Image 2 hash: e1e1e2a7bbaf6faf (0111000011110000111100101101001101011011011101010011010101001111)
	Hamming distance: 32
