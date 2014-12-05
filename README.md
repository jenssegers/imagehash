ImageHash
=========

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
