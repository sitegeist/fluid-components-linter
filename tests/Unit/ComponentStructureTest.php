<?php

declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Tests\Unit;

final class ComponentStructureTest extends AbstractTest
{
    public function dataProvider()
    {
        return [
            'fluidSyntax' => [
                '<fc:component><fc:renderer></fc:component>',
                false,
                \TYPO3Fluid\Fluid\Core\Parser\Exception::class
            ],
            'missingComponent' => [
                'some content',
                false,
                \Sitegeist\FluidComponentsLinter\Exception\ComponentStructureException::class
            ],
            'missingRenderer1' => [
                '<fc:component>some content</fc:component>',
                false,
                \Sitegeist\FluidComponentsLinter\Exception\ComponentStructureException::class
            ],
            'missingRenderer2' => [
                '<fc:component><fc:param name="a" type="string" /></fc:component>',
                false,
                \Sitegeist\FluidComponentsLinter\Exception\ComponentStructureException::class
            ],
            'strictComponentStructure1' => [
                'some content<fc:component><fc:renderer></fc:renderer></fc:component>',
                true,
                \Sitegeist\FluidComponentsLinter\Exception\StrictComponentStructureException::class
            ],
            'strictComponentStructure2' => [
                '<fc:component>some content<fc:renderer></fc:renderer></fc:component>',
                true,
                \Sitegeist\FluidComponentsLinter\Exception\StrictComponentStructureException::class
            ],
            'strictComponentStructure3' => [
                '<fc:component><fc:renderer></fc:renderer>some content</fc:component>',
                true,
                \Sitegeist\FluidComponentsLinter\Exception\StrictComponentStructureException::class
            ],
            'strictComponentStructure4' => [
                '<fc:component><fc:renderer></fc:renderer></fc:component>some content',
                true,
                \Sitegeist\FluidComponentsLinter\Exception\StrictComponentStructureException::class
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testComponentStructure(string $componentSource, bool $strictSyntax, string $expectedException): void
    {
        $this->expectException($expectedException);
        $this->createComponent($componentSource, $strictSyntax);
    }
}
