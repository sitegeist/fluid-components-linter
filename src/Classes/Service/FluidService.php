<?php
declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Service;

use Sitegeist\FluidComponentsLinter\ViewHelpers\IntrospectionViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

class FluidService
{
    public function extractNodeType(NodeInterface $node, string $nodeType, $recursive = true): array
    {
        $node = $this->resolveEscapingNode($node);
        $nodes = [];

        $checkNodes = $node->getChildNodes();
        if ($node instanceof ViewHelperNode) {
            foreach ($node->getArguments() as $argument) {
                if ($argument instanceof NodeInterface) {
                    $checkNodes[] = $argument;
                }
            }
        }

        foreach ($checkNodes as $childNode) {
            $childNode = $this->resolveEscapingNode($childNode);

            if ($childNode instanceof $nodeType) {
                $nodes[] = $childNode;
            }

            if ($recursive) {
                $nodes = array_merge($nodes, $this->extractNodeType($childNode, $nodeType, $recursive));
            }
        }

        return $nodes;
    }

    public function generateNodeExceptionPreview(NodeInterface $node): string
    {
        if ($node instanceof ViewHelperNode) {
            $uninitializedViewHelper = $node->getUninitializedViewHelper();
            if ($uninitializedViewHelper instanceof IntrospectionViewHelper) {
                return $uninitializedViewHelper->getViewhelperTag();
            } else {
                return $uninitializedViewHelper::class;
            }
        } elseif ($node instanceof TextNode) {
            return trim($node->getText());
        } elseif ($node instanceof ObjectAccessorNode) {
            return '{' . $node->getObjectPath() . '}';
        } else {
            return $node::class;
        }
    }

    public function isEmptyTextNode(NodeInterface $node): bool
    {
        return $node instanceof TextNode && trim($node->getText()) === '';
    }

    public function resolveEscapingNode(NodeInterface $node): NodeInterface
    {
        return ($node instanceof EscapingNode) ? $this->resolveEscapingNode($node->getNode()) : $node;
    }
}
