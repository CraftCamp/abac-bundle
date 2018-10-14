<?php

namespace CraftCamp\AbacBundle\Tests\DependencyInjection;

use CraftCamp\AbacBundle\DependencyInjection\CraftCampAbacExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use PHPUnit\Framework\TestCase;

class CraftCampAbacExtensionTest extends TestCase
{
    /** @var CraftCampAbacExtension **/
    protected $extension;
    
    public function setUp()
    {
        $this->extension = new CraftCampAbacExtension();
    }
    
    public function testLoad()
    {
        $containerBuilder = new ContainerBuilder();
        
        $this->extension->load($this->getConfigurationMock(), $containerBuilder);
        
        $this->assertEquals($containerBuilder->getParameter('craftcamp_abac.configuration_files'), ['config/abac/policy_rules.yaml']);
        $this->assertEquals($containerBuilder->getParameter('craftcamp_abac.cache_options'), ['cache_folder' => '/var/cache']);
        $this->assertEquals($containerBuilder->getParameter('craftcamp_abac.attribute_options'), []);
        $this->assertTrue($containerBuilder->hasDefinition('PhpAbac\Abac'));
        $this->assertTrue($containerBuilder->hasAlias('craftcamp_abac.security'));
    }

    public function getConfigurationMock()
    {
        return [
            'craftcamp_abac' => [
                'configuration_files' => [
                    'config/abac/policy_rules.yaml'
                ],
                'cache_options' => [
                    'cache_folder' => '/var/cache'
                ]
            ]
        ];
    }
}