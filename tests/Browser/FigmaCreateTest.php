<?php

declare(strict_types=1);

namespace Opscale\Fields\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\Fields\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

final class FigmaCreateTest extends DuskTestCase
{
    #[Test]
    public function create_form_renders_a_file_upload_input_for_the_figma_field(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/designs/new')
                ->waitForText('Create Design', 15)
                ->waitFor('[data-testid="figma-form-field"]', 15)
                ->waitFor('[data-testid="figma-form-field"] input[type="file"]', 15)
                ->assertAttribute(
                    '[data-testid="figma-form-field"] input[type="file"]',
                    'accept',
                    '.fig,.json'
                );
        });
    }

    #[Test]
    public function update_form_also_renders_the_file_upload_input(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/designs/1/edit')
                ->waitForText('Update Design', 15)
                ->waitFor('[data-testid="figma-form-field"]', 15)
                ->waitFor('[data-testid="figma-form-field"] input[type="file"]', 15)
                ->assertPresent('[data-testid="figma-form-field"] input[type="file"]');
        });
    }

    #[Test]
    public function submitting_the_form_uploads_the_file_and_renders_it_on_detail(): void
    {
        $fixture = (string) realpath(__DIR__.'/../fixtures/sample.json');

        $this->browse(function (Browser $browser) use ($fixture): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/designs/new')
                ->waitForText('Create Design', 15)
                ->waitFor('@name', 15)
                ->type('@name', 'Uploaded via Dusk')
                ->waitFor('[data-testid="figma-form-field"] input[type="file"]', 15)
                ->attach('[data-testid="figma-form-field"] input[type="file"]', $fixture)
                ->pause(300)
                ->press('Create Design')
                // Nova redirects to /nova/resources/designs/{id} on successful save.
                ->waitForText('Design Details: Uploaded via Dusk', 15)
                // If figma_path was persisted AND the file was uploaded, the detail
                // page fetches it, the WASM renderer boots, and the design appears
                // as an image — end-to-end proof. WASM + font boot takes a while.
                ->waitFor('[data-testid="figma-image"]', 60)
                ->assertVisible('[data-testid="figma-image"]');
        });
    }
}
