<?php
// DIC configuration

$container = $app->getContainer();

// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------

// Twig
$container['view'] = function ($c) {
    $settings = $c->get('settings');
    $view = new \Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);

    // Add extensions
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};

// Flash messages
$container['flash'] = function ($c) {
    return new \Slim\Flash\Messages;
};

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new \Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['path'], \Monolog\Logger::DEBUG));
    return $logger;
};

// Doctrine
$container['em'] = function ($c) {
    $settings = $c->get('settings');
    $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
        $settings['doctrine']['meta']['entity_path'],
        $settings['doctrine']['meta']['auto_generate_proxies'],
        $settings['doctrine']['meta']['proxy_dir'],
        $settings['doctrine']['meta']['cache'],
        false
    );
    return \Doctrine\ORM\EntityManager::create($settings['doctrine']['connection'], $config);
};

// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------
$container['App\Controller\HomeController'] = function ($c) {
    return new App\Controller\HomeController($c->get('view'), $c->get('logger'));
};

$container['App\Controller\MusicController'] = function ($c) {
    $musicRepository = new App\Repository\MusicRepository($c->get('em'));
    return new App\Controller\MusicController($musicRepository);
};

$container['App\Controller\FoodController'] = function ($c) {
    $userRepository = new App\Repository\UserRepository($c->get('em'));
    $foodRepository = new App\Repository\FoodRepository($c->get('em'));
    return new App\Controller\FoodController($userRepository, $foodRepository);
};

$container['App\Controller\UserController'] = function ($c) {
    $userRepository = new App\Repository\UserRepository($c->get('em'));
    $foodRepository = new App\Repository\FoodRepository($c->get('em'));
    return new App\Controller\UserController($userRepository, $foodRepository);
};
