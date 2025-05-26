# RESTphulSrv Class README

## Overview

This README provides instructions on how to use the `RESTphulSrv` class and outlines expectations for configuring the API file feature using an 
ini file. Additionally, it includes an example directory structure with images, audio, and video files that can be viewed in a browser by 
default.

## Installation

1. **Download the Class File**:
   - Download the `RESTphulSrv.php` file from the provided link or source.
   
2. **Include the Class File**:
   ```php
   require_once 'RESTphulSrv.php';
   ```

## Usage Example

### Basic RESTphulSrv Initialization

```php
<?php
require_once 'RESTphulSrv.php';

$restsrv = new RESTphulSrv();

// Simulate an HTTP GET request
$requestHeaders = apache_request_headers();
if (isset($requestHeaders['HTTP_METHOD']) && $requestHeaders['HTTP_METHOD'] == 'GET') {
    // Handle GET request logic here
} else {
    $restsrv->error('method');
}

// Check if a function exists
echo $restsrv->check_support('file_get_contents'); // Outputs: Yes

// Simulate an error scenario
$restsrv->error('php_fail');
?>
```

## API File Configuration (Example ini file)

To configure the API file feature, create an `api.ini` file in your project root. Below is an example of how this file should be structured:

```ini
[RESTphulSrv]
debug = true

; Directory structure for images, audio, and video files
[image_directory]
path = /var/www/html/images/
mime_type = image/*

[audio_directory]
path = /var/www/html/audio/
mime_type = audio/*

[video_directory]
path = /var/www/html/video/
mime_type = video/*
```

### Directory Structure

The example directory structure for images, audio, and video files is as follows:

```
/var/www/html/
├── index.php
├── api.ini
├── images/
│   ├── image1.jpg
│   ├── image2.png
│   └── ...
├── audio/
│   ├── audio1.mp3
│   ├── audio2.ogg
│   └── ...
└── video/
    ├── video1.mp4
    ├── video2.avi
    └── ...
```

### Accessing Files via Browser

Once the directory structure is set up and the `api.ini` file is configured, you can access the files directly from your web browser. For 
example:

- Images: `http://yourdomain.com/images/image1.jpg`
- Audio: `http://yourdomain.com/audio/audio1.mp3`
- Video: `http://yourdomain.com/video/video1.mp4`

## Error Handling

The `RESTphulSrv` class includes a detailed error handling mechanism that provides user-friendly error pages using jQuery Mobile. You can 
customize error messages by modifying the `$msg` array within the class.

## Compatibility

Ensure that your PHP environment supports the necessary functions and classes used in the `RESTphulSrv` class, such as `file_get_contents`, 
`apache_request_headers`, etc.

## Conclusion

This README provides a comprehensive guide on how to use the `RESTphulSrv` class for building RESTful services. By following the instructions 
above, you can set up an API file feature with custom directory structures and access files directly from your browser.




### References:
#### Books:

- REST in Practice: http://restinpractice.com/book/book.html

- REST API Design Rulebook: http://shop.oreilly.com/product/0636920021575.do

- Essential PHP Security: http://shop.oreilly.com/product/9780596006563.do

#### Videos:

- ZC13 Grokking HTTP: http://youtu.be/IagYBYn4Wt8

- ZC13 API First: http://youtu.be/nmgxYa5cW3E