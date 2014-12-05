ImageHash
=========

[![Latest Stable Version](http://img.shields.io/github/release/jenssegers/php-imagehash.svg)](https://packagist.org/packages/jenssegers/php-imagehash) [![Build Status](http://img.shields.io/travis/jenssegers/php-imagehash.svg)](https://travis-ci.org/jenssegers/php-imagehash) [![Coverage Status](http://img.shields.io/coveralls/jenssegers/php-imagehash.svg)](https://coveralls.io/r/jenssegers/php-imagehash)

> A perceptual hash is a fingerprint of a multimedia file derived from various features from its content. Unlike cryptographic hash functions which rely on the avalanche effect of small changes in input leading to drastic changes in the output, perceptual hashes are "close" to one another if the features are similar.

This code was based on:
 - https://github.com/kennethrapp/phasher
 - http://www.phash.org
 - http://www.hackerfactor.com/blog/index.php?/archives/529-Kind-of-Like-That.html

**WARNING**: the PerceptualHash implementation is still under development.

Installation
------------

Install using composer:

	composer require jenssegers/php-imagehash

Usage
-----

Calculating a perceptual hash for an image using the default implementation:

	$hasher = new Jenssegers\ImageHash\ImageHash;
	$hash = $hasher->hash('path/to/image.jpg');

Calculating a perceptual hash for an image using a different implementation:

	$implementation = new Jenssegers\ImageHash\Implementation\DifferenceHash;
	$hasher = new Jenssegers\ImageHash\ImageHash($implementation);
	$hash = $hasher->hash('path/to/image.jpg');

Demo
----

These images are equal:

![Equals1](https://raw.githubusercontent.com/jenssegers/php-imagehash/master/tests/images/forest/forest-high.jpg)
![Equals2](https://raw.githubusercontent.com/jenssegers/php-imagehash/master/tests/images/forest/forest-low.jpg)

	Image 1 hash: 4340922596638727710 (0011110000111110000011100001101000111010000111100001111000011110)
	Image 2 hash: 4340922596640824862 (0011110000111110000011100001101000111010001111100001111000011110)
	Hamming distance: 1

These images are not equal:

![Equals1](https://github.com/jenssegers/php-imagehash/raw/master/tests/images/office/tumblr_ndyfnr7lk21tubinno1_1280.jpg)
![Equals2](https://raw.githubusercontent.com/jenssegers/php-imagehash/master/tests/images/office/tumblr_ndyfq386o41tubinno1_1280.jpg)

	Image 1 hash: 2929776999984224055 (0010100010101000101010001010100010101011001010110101011100110111)
	Image 2 hash: 8138271516244915535 (0111000011110000111100101101001101011011011101010011010101001111)
	Hamming distance: 32
