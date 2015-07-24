<?php
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

ErrorHandler::register();
ExceptionHandler::register();

// Register service providers.
$app->register(new Silex\Provider\DoctrineServiceProvider());

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

// Register services.
$app['dao.erp'] = $app->share(function ($app) {
    return new ERP\DAO\ErpDAO($app['db']);
});

$app['dao.bati'] = $app->share(function ($app) {
    return new ERP\DAO\ErpBatiDAO($app['db']);
});

$app['dao.enceinte'] = $app->share(function ($app) {
    return new ERP\DAO\ErpEnceinteDAO($app['db']);
});

$app['dao.infos'] = $app->share(function ($app) {
    return new ERP\DAO\ErpInfosDAO($app['db']);
});

$app['dao.utils'] = $app->share(function ($app) {
    return new ERP\DAO\UtilsDAO($app['db']);
});
