<?php

namespace CraftCamp\AbacBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use CraftCamp\AbacBundle\DependencyInjection\CraftCampAbacExtension;

class CraftCampAbacBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new CraftCampAbacExtension();
    }
}
