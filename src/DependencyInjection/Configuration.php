<?php declare(strict_types=1);

namespace Sms77\SyliusPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface {
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder {
        $treeBuilder = new TreeBuilder('sms77_sylius_plugin');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('sms77_sylius_plugin');
        }

        return $treeBuilder;
    }
}
