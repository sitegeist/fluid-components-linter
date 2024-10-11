<?php

declare(strict_types=1);

namespace Sitegeist\FluidComponentsLinter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Sitegeist\FluidComponentsLinter\Exception\ComponentStructureException;
use Sitegeist\FluidComponentsLinter\Exception\StrictComponentStructureException;
use TYPO3Fluid\Fluid\Core\Parser\Exception;

final class ComponentStructureTest extends AbstractTestClass
{
    public static function dataProvider()
    {
        return [
            'fluidSyntax' => [
                '<fc:component><fc:renderer></fc:component>',
                false,
                Exception::class
            ],
            'missingComponent' => [
                'some content',
                false,
                ComponentStructureException::class
            ],
            'missingRenderer1' => [
                '<fc:component>some content</fc:component>',
                false,
                ComponentStructureException::class
            ],
            'missingRenderer2' => [
                '<fc:component><fc:param name="a" type="string" /></fc:component>',
                false,
                ComponentStructureException::class
            ],
            'strictComponentStructure1' => [
                'some content<fc:component><fc:renderer></fc:renderer></fc:component>',
                true,
                StrictComponentStructureException::class
            ],
            'strictComponentStructure2' => [
                '<fc:component>some content<fc:renderer></fc:renderer></fc:component>',
                true,
                StrictComponentStructureException::class
            ],
            'strictComponentStructure3' => [
                '<fc:component><fc:renderer></fc:renderer>some content</fc:component>',
                true,
                StrictComponentStructureException::class
            ],
            'strictComponentStructure4' => [
                '<fc:component><fc:renderer></fc:renderer></fc:component>some content',
                true,
                StrictComponentStructureException::class
            ]
        ];
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function testComponentStructure(
        string $componentSource,
        bool $strictSyntax,
        string $expectedException
    ): void {
        $this->expectException($expectedException);
        $this->createComponent($componentSource, $strictSyntax);
    }
}
