<?php

namespace App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

$loader = require __DIR__.'/../vendor/autoload.php';
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        $bundles = array(
            new FrameworkBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new DoctrineMigrationsBundle(),
        );

        return $bundles;
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/../config/framework.yaml');

        $c->loadFromExtension('doctrine', array(
            'dbal' => array(
                'driver' => 'pdo_mysql',
                'charset' => 'UTF8',
                'server_version' => '5.7',
                'default_table_options' => [
                    'charset' => 'UTF8',
                    'collate' => 'utf8_unicode_ci',
                ],
                'url' => getenv('DATABASE_URL'),
            )
        ));
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        // load the annotation routes
        $routes->import(__DIR__.'/../config/routes.yaml', '/');
    }

    // optional, to use the standard Symfony cache directory
    public function getCacheDir()
    {
        return __DIR__.'/../var/cache/'.$this->getEnvironment();
    }

    // optional, to use the standard Symfony logs directory
    public function getLogDir()
    {
        return __DIR__.'/../var/log';
    }
}