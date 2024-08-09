<?php
use Illuminate\Database\Capsule\Manager as Capsule;

return function ($app) {
    $container = $app->getContainer();

    $capsule = new Capsule;
    $capsule->addConnection($container->get('settings')['db']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    $container->set('db', function ($container) use ($capsule) {
        return $capsule;
    });

    $container->set('jwt', function ($container) {
        return new StdClass;
    });
};
