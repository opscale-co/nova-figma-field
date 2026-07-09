<?php

declare(strict_types=1);

namespace Opscale\Fields\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\Fields\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

final class FigmaDetailTest extends DuskTestCase
{
    #[Test]
    public function detail_view_renders_the_figma_design_as_an_image(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/designs/1')
                ->waitFor('[data-testid="figma-detail"]', 15)
                ->waitFor('[data-testid="figma-viewer"]', 15)
                // WASM renderer + default fonts load lazily on first render.
                ->waitFor('[data-testid="figma-image"]', 60)
                ->assertPresent('[data-testid="figma-canvas"]');
        });
    }

    #[Test]
    public function the_rendered_image_is_a_non_empty_png(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/designs/1')
                ->waitFor('[data-testid="figma-image"]', 60);

            $isRendered = $browser->script(
                'const img = document.querySelector(\'[data-testid="figma-image"]\');'
                .'return img && img.complete && img.naturalWidth > 0;'
            )[0];

            $this->assertTrue((bool) $isRendered, 'The rendered PNG should have decoded pixels.');
        });
    }
}
