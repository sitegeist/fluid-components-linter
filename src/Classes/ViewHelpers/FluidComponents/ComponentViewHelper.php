<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\ViewHelpers\FluidComponents;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;

class ComponentViewHelper extends AbstractViewHelper
{
    use ParserRuntimeOnly;

    public function initializeArguments()
    {
        $this->registerArgument('description', 'string', 'Description of the component');
    }
}
