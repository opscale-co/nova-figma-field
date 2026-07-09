<?php

declare(strict_types=1);

namespace Opscale\Fields;

use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\File;
use Override;

class Figma extends File
{
    public $component = 'nova-figma-field';

    public $showOnIndex = false;

    public $acceptedTypes = '.fig,.json';

    /**
     * @param  \Stringable|string  $name
     */
    final public function __construct($name, mixed $attribute = null, ?string $disk = null, ?callable $storageCallback = null)
    {
        parent::__construct($name, $attribute, $disk, $storageCallback);

        $this->preview(fn (?string $value, ?string $diskName): ?string => $value !== null && $value !== ''
            ? Storage::disk($diskName ?? $this->getStorageDisk())->url($value)
            : null,
        );
    }

    final public function height(int $pixels): self
    {
        return $this->withMeta(['height' => $pixels]);
    }

    final public function scale(float $factor): self
    {
        return $this->withMeta(['scale' => $factor]);
    }

    final public function framePicker(bool $enabled = true): self
    {
        return $this->withMeta(['framePicker' => $enabled]);
    }

    final public function wasmUrl(string $url): self
    {
        return $this->withMeta(['wasmUrl' => $url]);
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function meta()
    {
        return array_merge(parent::meta(), [
            'height' => 600,
            'scale' => 2,
            'framePicker' => true,
            'wasmUrl' => '/nova-vendor/nova-figma-field/grida_canvas_wasm.wasm',
        ], $this->meta);
    }
}
