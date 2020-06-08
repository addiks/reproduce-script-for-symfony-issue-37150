<?php
/**
 * Reproduce script for https://github.com/symfony/symfony/issues/37150
 */

use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;

require_once(__DIR__ . '/vendor/autoload.php');

$rootName = explode("/", __DIR__)[1];

# Lets just quickly store this URL in this service-argument ...
$expectedURL = 'http://example.com/foo/' . $rootName . '/bar';
$service = new Definition(Parameter::class, [$expectedURL]);

$builder = new ContainerBuilder();
$builder->addDefinitions(['foo.bar' => $service]);

$dumper = new PhpDumper($builder);

$code =  $dumper->dump(['file' => __DIR__ . "/container.php"]);
file_put_contents(__DIR__ . "/container.php", $code);

if (!is_dir(__DIR__ . "/foo")) {mkdir(__DIR__ . "/foo");}
rename(__DIR__ . "/container.php", __DIR__ . "/foo/container.php");
require_once(__DIR__ . "/foo/container.php");

$container = new ProjectServiceContainer();

# What was the stored URL again?
# The service should still have the original URL, right? ;-)
$actualURL = $container->get('foo.bar')->__toString();

if ($expectedURL === $actualURL) {
    echo " PASSED! The URL is still the same. :-)\n";
    exit(0);

} else {
    echo " FAILED! The container changed my URL! WTF?! :-(";
    echo sprintf("\n ['%s' != '%s']\n", $expectedURL, $actualURL);
    exit(-1);
}
