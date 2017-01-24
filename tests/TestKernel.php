<?php
namespace tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollectionBuilder;


/**
 * TestKernel
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
abstract class TestKernel extends Kernel
{
    use MicroKernelTrait {
        registerContainerConfiguration as registerContainerConfigurationRouting;
    }

    // KERNEL
    public function __construct($environment = 'dev', $debug = true)
    {
        parent::__construct($environment, $debug);
    }

    public function registerBundles()
    {
        return array(
            new FrameworkBundle(),
            new SecurityBundle()
        );
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/symfony-cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/symfony-logs';
    }

    public function load(LoaderInterface $loader)
    {
        $fileConfig = sys_get_temp_dir().'/config.yml';
        $config = ob_get_clean();
        $config = implode(
            PHP_EOL,
            array_map(
                function($item) { return substr($item, 16); },
                explode(PHP_EOL, $config)
            )
        );

        file_put_contents($fileConfig, $config);

        $loader->load($fileConfig);
    }

    abstract public function registerContainerConfigurationInternal();

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        // TODO: Implement configureContainer() method.
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        ob_start();
        $this->registerContainerConfigurationInternal();
        $this->load($loader);
        $this->registerContainerConfigurationRouting($loader);
    }
}