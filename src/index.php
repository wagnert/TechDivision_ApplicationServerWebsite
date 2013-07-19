<?php

$path = "WEB-INF/classes/TechDivision/ApplicationServerWebsite";

require_once $path . DIRECTORY_SEPARATOR . "Utilities/Template.php";
require_once $path . DIRECTORY_SEPARATOR . "Utilities/I18n.php";
require_once 'vendor/autoload.php';

use TechDivision\ApplicationServerWebsite\Utilities\Template;

$template = new Template();


echo $template->test();