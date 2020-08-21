<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality;

use Sitegeist\FluidComponentsLinter\Exception\ComponentStructureException;
use Sitegeist\FluidComponentsLinter\Exception\StrictComponentStructureException;
use Sitegeist\FluidComponentsLinter\Service\FluidService;
use Sitegeist\FluidComponentsLinter\ViewHelpers\ComponentViewHelper;
use Sitegeist\FluidComponentsLinter\ViewHelpers\ParamViewHelper;
use Sitegeist\FluidComponentsLinter\ViewHelpers\RendererViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class Component
{
    public $path;

    public $rootNode;
    public $fluidService;

    public $componentNode;
    public $paramNodes = [];
    public $rendererNode;

    public function __construct(string $path, RootNode $node, bool $useStrictSyntax = false)
    {
        $this->path = realpath($path);
        $this->rootNode = $node;
        $this->fluidService = new FluidService;

        $this->extractComponentNode($useStrictSyntax);
        $this->extractParamsAndRendererNode($useStrictSyntax);
    }

    protected function extractComponentNode(bool $useStrictSyntax): void
    {
        foreach ($this->rootNode->getChildNodes() as $componentCandidate) {
            $componentCandidate = $this->fluidService->resolveEscapingNode($componentCandidate);
            if (!$componentCandidate instanceof ViewHelperNode) {
                if ($useStrictSyntax && !$this->fluidService->isEmptyTextNode($componentCandidate)) {
                    throw new StrictComponentStructureException(sprintf(
                        'Arbitrary Fluid code is not allowed outside of <fc:component>: %s',
                        $this->fluidService->generateNodeExceptionPreview($componentCandidate)
                    ), 1595789404);
                }
                continue;
            }

            if ($useStrictSyntax && $componentCandidate->getViewHelperClassName() !== ComponentViewHelper::class) {
                throw new StrictComponentStructureException(sprintf(
                    'Arbitrary ViewHelpers are not allowed outside of <fc:component>: %s',
                    $this->fluidService->generateNodeExceptionPreview($componentCandidate)
                ), 1595789291);
                continue;
            }

            if (isset($this->componentNode)) {
                throw new ComponentStructureException(
                    'Multiple components in one file are not allowed.',
                    1595789372
                );
            }
            $this->componentNode = $componentCandidate;
        }

        if (!isset($this->componentNode)) {
            throw new ComponentStructureException(
                'The component has no component tag',
                1595868608
            );
        }
    }

    protected function extractParamsAndRendererNode(bool $useStrictSyntax): void
    {
        foreach ($this->componentNode->getChildNodes() as $paramRendererCandidate) {
            $paramRendererCandidate = $this->fluidService->resolveEscapingNode($paramRendererCandidate);
            if (!$paramRendererCandidate instanceof ViewHelperNode) {
                if ($useStrictSyntax && !$this->fluidService->isEmptyTextNode($paramRendererCandidate)) {
                    throw new StrictComponentStructureException(sprintf(
                        'Arbitrary Fluid code is not allowed outside of <fc:renderer>: %s',
                        $this->fluidService->generateNodeExceptionPreview($paramRendererCandidate)
                    ), 1595789859);
                }
                continue;
            }

            switch ($paramRendererCandidate->getViewHelperClassName()) {
                case ParamViewHelper::class:
                    $this->paramNodes[] = $paramRendererCandidate;
                    break;

                case RendererViewHelper::class:
                    if (isset($this->rendererNode)) {
                        throw new ComponentStructureException(
                            'Multiple renderers in one file are not allowed.',
                            1595868433
                        );
                    }
                    $this->rendererNode = $paramRendererCandidate;
                    break;

                default:
                    if ($useStrictSyntax) {
                        throw new StrictComponentStructureException(sprintf(
                            'Arbitrary ViewHelpers are not allowed outside of <fc:renderer>: %s',
                            $this->fluidService->generateNodeExceptionPreview($paramRendererCandidate)
                        ), 1595789864);
                    }
                    break;
            }
        }

        if (!isset($this->rendererNode)) {
            throw new ComponentStructureException(
                'The component has no renderer',
                1595868607
            );
        }
    }
}
