debug
=====
Version 0.0.1 Created by ycheukf@gmail.com

Introduction
------------

ycheukfDebug is a debug module for zend framework 2.
This module will dump vars and SQL to a cache file instead of printing out directly.

Features / Goals
----------------

* dump vars as var_dump
* dump db profiler (support multiple db adapter)
* the debug info will not be rewrote when request a ajax

Requirements
------------

* [PHP5](https://php.net/)
* [Zend Framework 2](https://github.com/zendframework/zf2) - Not needed to generate your models

Installation
------------

**Install via git**

Clone this repo
`git clone https://github.com/ycheukf/debug.git`

**Install via Composer**

Add this to your composer.json under "require":
`"ycheukf/debug": "dev-master"`

Run command:
``php composer.phar update``

Usage
-----

1:  add module 'ycheukfDebug' to your application.config.php
```php
return array(
    'modules' => array(
        'ycheukfDebug',
        'Application',
    ),
);
```
2:  use the below code as var_dump
```php
	\ycheukfDebug\Model\Debug::dump($var, 'memo');
```
3:  check the output at http://yourzf2project/index.php/public/debug

Advanced Usage
-----

If a project has multiple db adapter and master-slaver adapter, you need to attach a new event  'ycheukfDebugSetProfiler' into your code.
just like the code in 'setAttach' function at vendor/ycheukf/debug/Module.php