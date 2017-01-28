<?php
namespace tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;


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

    protected $yamlSpace = 16;

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
        return sys_get_temp_dir().'/lesson-security-'.$this->environment.'-symfony-cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/symfony-logs';
    }

    private function autoDetectYamlSpace($config)
    {
        $before = $config;
        $after = ltrim($config);
        return strlen($before) - strlen($after);
    }

    public function load(LoaderInterface $loader)
    {
        $fileConfig = sys_get_temp_dir().'/config.yml';
        $config = ob_get_clean();
        $this->yamlSpace = $this->autoDetectYamlSpace($config);
        $config = implode(
            PHP_EOL,
            array_map(
                function($item) { return substr($item, $this->yamlSpace); },
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