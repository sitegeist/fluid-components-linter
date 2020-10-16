<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\CodeQuality;

use Sitegeist\FluidComponentsLinter\Configuration\LintConfiguration;
use Sitegeist\FluidComponentsLinter\Exception\ComponentStructureException;
use Sitegeist\FluidComponentsLinter\Exception\StrictComponentStructureException;
use Sitegeist\FluidComponentsLinter\Service\FluidService;
use Sitegeist\FluidComponentsLinter\ViewHelpers\Fluid\CommentViewHelper;
use Sitegeist\FluidComponentsLinter\ViewHelpers\FluidComponents\ComponentViewHelper;
use Sitegeist\FluidComponentsLinter\ViewHelpers\FluidComponents\ParamViewHelper;
use Sitegeist\FluidComponentsLinter\ViewHelpers\FluidComponents\RendererViewHelper;
use Symfony\Component\Config\Definition\Processor;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
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

    public $configuration;
    public $customConfiguration;

    public function __construct(string $path, RootNode $node, bool $useStrictSyntax = false, array $configuration = [])
    {
        $this->path = realpath($path);
        $this->rootNode = $node;
        $this->fluidService = new FluidService;

        $this->extractComponentNode($useStrictSyntax);
        $this->extractParamsAndRendererNode($useStrictSyntax);
        $this->extractCustomConfiguration();

        $processor = new Processor();
        $this->configuration = $processor->processConfiguration(
            new LintConfiguration,
            [
                $configuration,
                $this->customConfiguration
            ]
        );
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

            if ($componentCandidate->getViewHelperClassName() === CommentViewHelper::class) {
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

                case CommentViewHelper::class:
                    continue 2;

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

    protected function extractCustomConfiguration()
    {
        $comments = $this->fluidService->extractComments($this->rootNode);
        $ignores = array_reduce($comments, function ($result, $comment) {
            if (preg_match('#fclint:ignore +([A-Za-z0-9\.,]+)#', $comment, $matches)) {
                $result = array_merge($result, explode(',', $matches[1]));
            }
            return $result;
        }, []);

        $this->customConfiguration = [];
        foreach ($ignores as $command) {
            $this->setConfigByPath($command . '.check', false);
        }
    }

    protected function setConfigByPath(string $path, $value)
    {
        $currentPosition =& $this->customConfiguration;
        foreach (explode('.', $path) as $key) {
            if (!isset($currentPosition[$key]) || !is_array($currentPosition[$key])) {
                $currentPosition[$key] = [];
            }
            $currentPosition =& $currentPosition[$key];
        }
        $currentPosition = $value;
    }
}
