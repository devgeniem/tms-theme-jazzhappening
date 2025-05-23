<?php

// Require the child theme autoloader.
require_once __DIR__ . '/lib/autoload.php';

// Require the main theme autoloader.
require_once \get_template_directory() . '/lib/autoload.php';

// Child theme setup
( new \TMS\Theme\Jazzhappening\ThemeController() );
