<?php

namespace App\Application\QrCode\DTOs;

class QrCodeGenerationRequest
{
    public function __construct(
        public readonly string $content,
        public readonly ?int $size = null,
        public readonly ?string $dotStyle = null,
        public readonly ?string $color = null,
        public readonly ?string $background = null,
        public readonly ?string $fileType = null,
        public readonly ?string $outputType = null,
        public readonly ?string $errorCorrection = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'] ?? '',
            size: isset($data['options']['size']) ? (int) $data['options']['size'] : null,
            dotStyle: $data['options']['dot_style'] ?? null,
            color: $data['options']['color'] ?? null,
            background: $data['options']['background'] ?? null,
            fileType: $data['options']['file_type'] ?? null,
            outputType: $data['options']['output_type'] ?? null,
            errorCorrection: $data['options']['error_correction'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'options' => array_filter([
                'size' => $this->size,
                'dot_style' => $this->dotStyle,
                'color' => $this->color,
                'background' => $this->background,
                'file_type' => $this->fileType,
                'output_type' => $this->outputType,
                'error_correction' => $this->errorCorrection,
            ], fn($value) => $value !== null),
        ];
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->content)) {
            $errors['content'] = 'Content is required';
        }

        if ($this->size !== null && ($this->size < 50 || $this->size > 2000)) {
            $errors['size'] = 'Size must be between 50 and 2000 pixels';
        }

        if ($this->dotStyle !== null && !in_array($this->dotStyle, ['square', 'circle', 'rounded'])) {
            $errors['dot_style'] = 'Invalid dot style. Valid options: square, circle, rounded';
        }

        if ($this->fileType !== null && !in_array($this->fileType, ['png', 'jpg', 'jpeg', 'svg', 'pdf'])) {
            $errors['file_type'] = 'Invalid file type. Valid options: png, jpg, jpeg, svg, pdf';
        }

        if ($this->outputType !== null && !in_array($this->outputType, ['storage', 'base64', 'stream'])) {
            $errors['output_type'] = 'Invalid output type. Valid options: storage, base64, stream';
        }

        if ($this->errorCorrection !== null && !in_array(strtoupper($this->errorCorrection), ['L', 'M', 'Q', 'H'])) {
            $errors['error_correction'] = 'Invalid error correction level. Valid options: L, M, Q, H';
        }

        if ($this->color !== null && !$this->isValidColor($this->color)) {
            $errors['color'] = 'Invalid color format. Use hex format like #000000 or named colors';
        }

        if ($this->background !== null && !$this->isValidColor($this->background)) {
            $errors['background'] = 'Invalid background color format. Use hex format like #ffffff or named colors';
        }

        return $errors;
    }

    private function isValidColor(string $color): bool
    {
        // Check hex format
        if (preg_match('/^#?[0-9a-f]{6}$/i', $color)) {
            return true;
        }

        // Check named colors
        $namedColors = ['black', 'white', 'red', 'green', 'blue', 'yellow', 'cyan', 'magenta'];
        return in_array(strtolower($color), $namedColors);
    }
}