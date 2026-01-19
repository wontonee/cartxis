<?php

declare(strict_types=1);

namespace Cartxis\CMS\Services;

use Illuminate\Support\Str;
use Cartxis\CMS\Models\Block;

class BlockService
{
    /**
     * Create a new block.
     */
    public function create(array $data): Block
    {
        // Auto-generate identifier if not provided
        if (empty($data['identifier'])) {
            $data['identifier'] = $this->generateUniqueIdentifier($data['title']);
        }

        return Block::create($data);
    }

    /**
     * Update an existing block.
     */
    public function update(Block $block, array $data): Block
    {
        $block->update($data);
        
        return $block->fresh();
    }

    /**
     * Generate a unique identifier from title.
     */
    public function generateUniqueIdentifier(string $title, ?string $preferredIdentifier = null, ?int $excludeId = null): string
    {
        $identifier = $preferredIdentifier ?? Str::slug($title);

        // Remove any leading or trailing hyphens
        $identifier = trim($identifier, '-');

        if (!$this->identifierExists($identifier, $excludeId)) {
            return $identifier;
        }

        // Identifier exists, append number
        $counter = 1;
        $newIdentifier = $identifier;

        while ($this->identifierExists($newIdentifier, $excludeId)) {
            $newIdentifier = $identifier . '-' . $counter;
            $counter++;
        }

        return $newIdentifier;
    }

    /**
     * Check if identifier already exists.
     */
    public function identifierExists(string $identifier, ?int $excludeId = null): bool
    {
        $query = Block::where('identifier', $identifier);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Bulk update status for multiple blocks.
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return Block::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Bulk delete multiple blocks (soft delete).
     */
    public function bulkDelete(array $ids): int
    {
        return Block::whereIn('id', $ids)->delete();
    }

    /**
     * Render block content based on type.
     */
    public function renderBlock(Block $block): string
    {
        if (!$block->isVisible()) {
            return '';
        }

        return match ($block->type) {
            'text' => $this->renderTextBlock($block),
            'html' => $this->renderHtmlBlock($block),
            'banner' => $this->renderBannerBlock($block),
            'promotion' => $this->renderPromotionBlock($block),
            'newsletter' => $this->renderNewsletterBlock($block),
            default => '',
        };
    }

    /**
     * Render text block.
     */
    protected function renderTextBlock(Block $block): string
    {
        return '<div class="text-block">' . e($block->content) . '</div>';
    }

    /**
     * Render HTML block.
     */
    protected function renderHtmlBlock(Block $block): string
    {
        return '<div class="html-block">' . $block->content . '</div>';
    }

    /**
     * Render banner block.
     */
    protected function renderBannerBlock(Block $block): string
    {
        $data = $block->content_array;
        
        if (!$data) {
            return '';
        }

        $html = '<div class="banner-block">';
        
        if (!empty($data['image'])) {
            $html .= '<img src="' . e($data['image']) . '" alt="' . e($data['alt'] ?? $block->title) . '">';
        }
        
        if (!empty($data['title'])) {
            $html .= '<h2>' . e($data['title']) . '</h2>';
        }
        
        if (!empty($data['description'])) {
            $html .= '<p>' . e($data['description']) . '</p>';
        }
        
        if (!empty($data['cta_text']) && !empty($data['cta_url'])) {
            $html .= '<a href="' . e($data['cta_url']) . '" class="btn">' . e($data['cta_text']) . '</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render promotion block.
     */
    protected function renderPromotionBlock(Block $block): string
    {
        $data = $block->content_array;
        
        if (!$data) {
            return '';
        }

        $html = '<div class="promotion-block">';
        
        if (!empty($data['heading'])) {
            $html .= '<h3>' . e($data['heading']) . '</h3>';
        }
        
        if (!empty($data['discount'])) {
            $html .= '<div class="discount">' . e($data['discount']) . '</div>';
        }
        
        if (!empty($data['description'])) {
            $html .= '<p>' . e($data['description']) . '</p>';
        }
        
        if (!empty($data['code'])) {
            $html .= '<div class="promo-code">Code: <strong>' . e($data['code']) . '</strong></div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render newsletter block.
     */
    protected function renderNewsletterBlock(Block $block): string
    {
        $data = $block->content_array;
        
        if (!$data) {
            return '';
        }

        $html = '<div class="newsletter-block">';
        
        if (!empty($data['heading'])) {
            $html .= '<h3>' . e($data['heading']) . '</h3>';
        }
        
        if (!empty($data['description'])) {
            $html .= '<p>' . e($data['description']) . '</p>';
        }
        
        $html .= '<form action="' . e($data['action'] ?? '/newsletter/subscribe') . '" method="POST">';
        $html .= csrf_field();
        $html .= '<input type="email" name="email" placeholder="' . e($data['placeholder'] ?? 'Enter your email') . '" required>';
        $html .= '<button type="submit">' . e($data['button_text'] ?? 'Subscribe') . '</button>';
        $html .= '</form>';
        $html .= '</div>';
        
        return $html;
    }
}
