<?php
//Slim
require 'Slim/Slim.php';
require 'Views/TwigView.php';

// Paris and Idiorm
require 'Paris/idiorm.php';
require 'Paris/paris.php';

//Model
require 'models/Letter.php';

//Misc Functions
require 'includes/slugger.php';

//Pagination
require 'includes/pagination.php';

//HtmlPurifier
require 'includes/htmlpurifier/library/HTMLPurifier.auto.php';

require 'includes/SitemapGenerator.php';

require __DIR__ . '/vendor/autoload.php';

//Recaptcha

// Twig Configuration
TwigView::$twigDirectory = __DIR__ . '/Twig/lib/Twig/';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

require 'Recaptcha/recaptchalib.php';
define('RECAPTCHA_PUBLIC_KEY', $_ENV['RECAPTCHA_PUBLIC_KEY']);
define('RECAPTCHA_PRIVATE_KEY', $_ENV['RECAPTCHA_PRIVATE_KEY']);

$mode = $_ENV['MODE'];

// Start Slim.
$app = new Slim(array(
	'view' => new TwigView,
	'mode' => $mode
));

$baseUrl = sprintf("%s://%s%s",
	isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http', $_SERVER['SERVER_NAME'], '/');

define('BASE_URL', $baseUrl);


ORM::configure('mysql:host='.$_ENV['DB_HOST'].';dbname='.$_ENV['DB_NAME']);
ORM::configure('username', $_ENV['DB_USER']);
ORM::configure('password', $_ENV['DB_PASS']);

define('ASSET_URL', BASE_URL.'assets/');

//define the template data array with defaults
$adminPath = $_ENV['ADMIN_PATH'];
$sitemapPath = $_ENV['SITEMAP_PATH'];
$template_arr = array(
	'base_url' => BASE_URL, 'asset_url' => ASSET_URL,
	'admin_path' => $adminPath, 'sitemap_path' => $sitemapPath);
