<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class LintConfiguration implements ConfigurationInterface
{
    protected $errorLevels = [
        'error',
        'warning',
        'notice'
    ];

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('fclint');
        /** @var TreeNode */
        $fclint = $treeBuilder->getRootNode();

        $fclint->children()
            ->arrayNode('_patterns')
                ->scalarPrototype()->end()
            ->end()
            ->arrayNode('component')
                ->children()
                    ->booleanNode('requireDocumentation')->end()
                    ->booleanNode('requireFluidInsideRenderer')->end()
                    ->booleanNode('requireFixtureFile')->end()
                    ->booleanNode('requireDocumentationWithFixtureFile')->end()
                ->end()
            ->end()
            ->arrayNode('params')
                ->children()
                    ->arrayNode('count')
                        ->children()
                            ->integerNode('max')->end()
                            ->integerNode('min')->end()
                        ->end()
                    ->end()
                    ->arrayNode('extraNamingConventionsPerType')
                        ->arrayPrototype()
                            ->scalarPrototype()->end()
                            ->beforeNormalization()->castToArray()->end()
                            ->requiresAtLeastOneElement()
                        ->end()
                    ->end()
                    ->arrayNode('nameLength')
                        ->children()
                            ->integerNode('max')->end()
                            ->integerNode('min')->end()
                        ->end()
                    ->end()
                    ->arrayNode('namingConventions')
                        ->scalarPrototype()->end()
                        ->beforeNormalization()->castToArray()->end()
                        ->requiresAtLeastOneElement()
                    ->end()
                    ->booleanNode('requireDescription')->end()
                    ->arrayNode('requireDescriptionForType')
                        ->arrayPrototype()
                            ->booleanPrototype()->end()
                        ->end()
                    ->end()
                    ->booleanNode('requireNamespaceWithoutLeadingSlash')->end()
                    ->arrayNode('typeHints')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('namePattern')->end()
                                ->scalarNode('typeHint')->end()
                                ->scalarNode('message')->end()
                                ->enumNode('level')->values($this->errorLevels)->end()
                            ->end()
                        ->end()
                        ->useAttributeAsKey('namePattern')
                    ->end()
                    ->arrayNode('typeRestrictions')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('typeValue')->end()
                                ->scalarNode('typeRecommendation')->end()
                                ->scalarNode('message')->end()
                                ->enumNode('level')->values($this->errorLevels)->end()
                            ->end()
                        ->end()
                        ->useAttributeAsKey('typeValue')
                    ->end()
                ->end()
                ->ignoreExtraKeys()
            ->end()
            ->arrayNode('renderer')
                ->children()
                    ->booleanNode('requireClass')->end()
                    ->booleanNode('requireComponentPrefixer')->end()
                    ->booleanNode('requireRawContent')->end()
                    ->arrayNode('viewHelperRestrictions')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('viewHelperPattern')->end()
                                ->scalarNode('message')->end()
                                ->enumNode('level')->values($this->errorLevels)->end()
                            ->end()
                        ->end()
                        ->useAttributeAsKey('viewHelperPattern')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
