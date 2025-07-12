<?php

namespace App\Domain\QrCode\Entities;

use App\Domain\QrCode\ValueObjects\Content;
use Carbon\Carbon;

class QrCode
{
    private string $id;
    private Content $content;
    private QrCodeConfiguration $configuration;
    private ?string $data;
    private ?string $filePath;
    private Carbon $createdAt;
    private ?Carbon $cachedAt;

    public function __construct(
        Content $content,
        QrCodeConfiguration $configuration,
        ?string $id = null
    ) {
        $this->id = $id ?? uniqid('qr_', true);
        $this->content = $content;
        $this->configuration = $configuration;
        $this->data = null;
        $this->filePath = null;
        $this->createdAt = Carbon::now();
        $this->cachedAt = null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getConfiguration(): QrCodeConfiguration
    {
        return $this->configuration;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    public function getCachedAt(): ?Carbon
    {
        return $this->cachedAt;
    }

    public function markAsCached(): void
    {
        $this->cachedAt = Carbon::now();
    }

    public function isCached(): bool
    {
        return $this->cachedAt !== null;
    }

    public function getCacheKey(): string
    {
        return 'qr_code:' . md5($this->content->getValue() . $this->configuration->getHash());
    }

    public function getFileName(): string
    {
        $timestamp = $this->createdAt->format('Y-m-d_H-i-s');
        $hash = substr(md5($this->content->getValue()), 0, 8);
        return "qr_{$timestamp}_{$hash}.{$this->configuration->getFileType()->getValue()}";
    }
}