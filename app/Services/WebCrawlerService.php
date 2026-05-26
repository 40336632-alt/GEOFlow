<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebCrawlerService
{
    /**
     * 抓取网页内容
     */
    public function crawl(string $url): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();

            return [
                'url' => $url,
                'title' => $this->extractTitle($html),
                'content' => $this->extractContent($html),
                'images' => $this->extractImages($html, $url),
            ];
        } catch (\Exception $e) {
            Log::error('Crawler Error', ['url' => $url, 'message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 提取标题
     */
    protected function extractTitle(string $html): string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return trim(strip_tags($matches[1]));
        }

        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $matches)) {
            return trim(strip_tags($matches[1]));
        }

        return '';
    }

    /**
     * 提取正文内容
     */
    protected function extractContent(string $html): string
    {
        // 移除script和style标签
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $html);
        $html = preg_replace('/<header[^>]*>.*?<\/header>/is', '', $html);
        $html = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $html);

        // 尝试提取article或main标签内容
        if (preg_match('/<article[^>]*>(.*?)<\/article>/is', $html, $matches)) {
            $html = $matches[1];
        } elseif (preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $matches)) {
            $html = $matches[1];
        }

        // 提取段落文本
        $paragraphs = [];
        if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $matches)) {
            foreach ($matches[1] as $p) {
                $text = trim(strip_tags($p));
                if (mb_strlen($text) > 10) {
                    $paragraphs[] = $text;
                }
            }
        }

        if (!empty($paragraphs)) {
            return implode("\n\n", $paragraphs);
        }

        // 如果没有段落，提取所有文本
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * 提取图片
     */
    protected function extractImages(string $html, string $baseUrl): array
    {
        $images = [];

        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $html, $matches)) {
            foreach ($matches[1] as $src) {
                // 处理相对路径
                if (str_starts_with($src, '//')) {
                    $src = 'https:' . $src;
                } elseif (str_starts_with($src, '/')) {
                    $parsed = parse_url($baseUrl);
                    $src = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . $src;
                } elseif (!str_starts_with($src, 'http')) {
                    $src = $baseUrl . '/' . $src;
                }

                // 过滤小图标和base64
                if (!str_starts_with($src, 'data:') && !str_contains($src, 'icon') && !str_contains($src, 'logo')) {
                    $images[] = $src;
                }
            }
        }

        return array_unique(array_slice($images, 0, 10));
    }
}
