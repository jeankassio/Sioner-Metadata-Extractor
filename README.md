# Sioner Metadata Extractor
Sioner Metadata Extractor uses Chromedriver to extract metadata from websites with javascript using Symfony/Panther.

[![Total Downloads](https://poser.pugx.org/jeankassio/Sioner-Metadata-Extractor/downloads)](https://packagist.org/packages/jeankassio/sioner-metadata-extractor)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

### Installing Sioner

Use [Composer](https://getcomposer.org/) to install Sioner in your project:

 ```sh
composer require jeankassio/sioner-metadata-extractor
````

## Dependencies:

### Installing ChromeDriver

```
sudo apt update
```

```
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
```

```
sudo apt install wget
```

```
sudo dpkg -i google-chrome-stable_current_amd64.deb
```

```
sudo apt-get install -f
```

```
google-chrome --version
```

### See the version of the Chrome

Go to: [https://chromedriver.chromium.org/downloads](https://chromedriver.chromium.org/downloads)

<img width="424" alt="image" src="https://user-images.githubusercontent.com/26697873/235277165-6bdbb597-221a-4b0e-99a7-d4faa7bcc3a9.png">

Click in your version.

<img width="549" alt="image" src="https://user-images.githubusercontent.com/26697873/235277185-94a3df57-2da1-4d1f-8585-8b1cf6d05f7e.png">

Download to your system

Unzip and upload to your server

```
sudo mv chromedriver /usr/bin/chromedriver
```

```
sudo chown root:root /usr/bin/chromedriver
```

```
sudo chmod +x /usr/bin/chromedriver
```

Finished

# Usage

### Here is some available metadata that was returned by Sioner Metadata Extractor:


```json
{
   "domain":"github.com",
   "canonical":"https:\/\/github.com\/jeankassio\/Sioner-Metadata-Extractor",
   "title":"GitHub - jeankassio\/Sioner-Metadata-Extractor: Sioner Metadata Extractor uses Chromedriver to extract metadata from websites with javascript, even if it is written in PHP",
   "image":"https:\/\/opengraph.githubassets.com\/b22dbba9d6ae7f1bf3f540334ce5b7c01e728daa06739db48430ca0804af9ab0\/jeankassio\/Sioner-Metadata-Extractor",
   "description":"Sioner Metadata Extractor uses Chromedriver to extract metadata from websites with javascript, even if it is written in PHP - GitHub - jeankassio\/Sioner-Metadata-Extractor: Sioner Metadata Extracto...",
   "icon":"https:\/\/github.com\/favicon.ico"
}
```

```json
{
   "domain":"techland.time.com",
   "canonical":"https:\/\/techland.time.com\/2011\/04\/06\/linux-exec-competing-against-microsoft-is-like-kicking-a-puppy\/",
   "title":"Linux Exec: Competing Against Microsoft Is Like “Kicking a Puppy” | TIME.com",
   "image":"https:\/\/techland.time.com\/wp-content\/themes\/time2012\/library\/assets\/images\/time-logo-og.png",
   "description":"Depending who you ask, you'll get a different answer about who's winning the operating system wars. Of course, the Linux people think they've won, but here's the thing--they may be right.",
   "keywords":"business, news, linux, open-source, windows",
   "icon":"https:\/\/techland.time.com\/favicon.ico"
}
```

```json
{
   "domain": "domain string",
   "canonical": "og:canonical link string",
   "title": "og:title/title website string",
   "image": "og:image/first image string",
   "description": "og:description/description string",
   "keywords": "keywords string",
   "icon": "apple-touch-icon/icon string",
   "author": "og:author/author string",
   "copyright": "copyright string"
}
```

## How it works?

Sioner Metadata Extractor can, before running completely in the determined amount of seconds (explained later), quickly run the search for the determined data and obtain this data without the need to use javascript, thus saving its execution time.
To do this, just pass the data you want to obtain as mandatory in the first verification as parameter #4

```php
use JeanKassio\Sioner\MetadataExtractor

$YourLink = "https://github.com/jeankassio/Sioner-Metadata-Extractor";

$code = new MetadataExtractor($YourLink, null, null, ['website', 'title', 'image', 'description']);

$response = $code->ExtractMetadata();

echo json_encode($response, JSON_UNESCAPED_UNICODE);

```

But if that doesn't happen, it will run the Browser with javascript to get the data. The time for which it will run by default is 3 seconds, but you can change this value by setting parameter #2

```php
use JeanKassio\Sioner\MetadataExtractor

$YourLink = "https://github.com/jeankassio/Sioner-Metadata-Extractor";

$code = new MetadataExtractor($YourLink, 2.5); //2.5 seconds

$response = $code->ExtractMetadata();

echo json_encode($response, JSON_UNESCAPED_UNICODE);
```

We get og:image by default of 200x200, and if it's not found, the next image larger than that dimension will be returned. If none match, the largest is returned. But you can also set these values.

```php
use JeanKassio\Sioner\MetadataExtractor

$YourLink = "https://github.com/jeankassio/Sioner-Metadata-Extractor";

$code = new MetadataExtractor($YourLink, null, [250,300]); //250 width, 300 height

$response = $code->ExtractMetadata();

echo json_encode($response, JSON_UNESCAPED_UNICODE);
```

And that way you can pass the parameters you want and build the way you want. Watch

```php
use JeanKassio\Sioner\MetadataExtractor

$YourLink = "https://github.com/jeankassio/Sioner-Metadata-Extractor";

$code = new MetadataExtractor($YourLink, 5, [500,100], ['website', 'title', 'image', 'description']);

$response = $code->ExtractMetadata();

echo json_encode($response, JSON_UNESCAPED_UNICODE);
```


## Copyright and license

Code released under the [MIT license](https://github.com/jeankassio/Sioner-Metadata-Extractor/blob/main/LICENSE).
