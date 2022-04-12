<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

require_once __DIR__ . '/vendor/autoload.php';

AnnotationRegistry::registerLoader('class_exists');
date_default_timezone_set('UTC');
 