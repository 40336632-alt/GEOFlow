<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiUrl = config('services.ai.api_url', 'https://api.openai.com/v1');
        $this->apiKey = config('services.ai.api_key', '');
        $this->model = config('services.ai.model', 'gpt-4o-mini');
    }

    /**
     * 调用AI生成内容
     */
    public function chat(string $systemPrompt, string $userMessage, array $options = []): ?string
    {
        $model = $options['model'] ?? $this->model;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2000;

        try {
            $response = Http::timeout(120)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }

            Log::error('AI API Error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('AI API Exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * AI智能拓词 - 生成相关搜索问题
     */
    public function expandKeywords(string $keyword, string $brandName = null, int $count = 20): array
    {
        $systemPrompt = '你是一个专业的SEO和GEO优化专家。请根据用户提供的主关键词，生成相关的搜索问题。

要求：
1. 生成的问题要自然、真实，像真实用户会问的问题
2. 问题要涵盖：哪家好、怎么选、推荐、排名、价格、效果、对比等维度
3. 如果有品牌名称，部分问题要包含品牌名
4. 必须返回JSON数组格式，例如：["问题1","问题2","问题3"]
5. 不要返回任何其他内容，只返回JSON数组';

        $userMessage = "主关键词：{$keyword}";
        if ($brandName) {
            $userMessage .= "\n品牌名称：{$brandName}";
        }
        $userMessage .= "\n请生成 {$count} 个相关搜索问题，只返回JSON数组。";

        $result = $this->chat($systemPrompt, $userMessage, ['temperature' => 0.8, 'max_tokens' => 1000]);

        if ($result) {
            // 清理响应，提取JSON部分
            $result = trim($result);

            // 尝试提取JSON数组
            if (preg_match('/\[[\s\S]*\]/', $result, $matches)) {
                $questions = json_decode($matches[0], true);
                if (is_array($questions)) {
                    return array_slice($questions, 0, $count);
                }
            }

            // 如果不是JSON，尝试提取问题
            $lines = array_filter(explode("\n", $result));
            $questions = [];
            foreach ($lines as $line) {
                $line = trim($line);
                $line = preg_replace('/^\d+[\.\)、]\s*/', '', $line);
                if (!empty($line) && mb_strlen($line) > 5) {
                    $questions[] = $line;
                }
            }
            return array_slice($questions, 0, $count);
        }

        return [];
    }

    /**
     * AI收录查询 - 查询AI搜索引擎是否收录品牌
     */
    public function checkIndex(string $question, string $brandName, string $platform): array
    {
        $systemPrompt = "你是{$platform}AI搜索引擎的模拟回答。请根据用户的问题，提供一个真实、自然的回答。
如果问题中提到了品牌或公司名称，请在回答中自然地提及它。
回答要详细、专业，像真实的AI搜索结果。";

        $result = $this->chat($systemPrompt, $question, ['temperature' => 0.9]);

        $brandMentioned = false;
        $mentionPosition = null;

        if ($result && $brandName) {
            $position = stripos($result, $brandName);
            if ($position !== false) {
                $brandMentioned = true;
                $mentionPosition = $position;
            }
        }

        return [
            'platform' => $platform,
            'answer' => $result,
            'brand_mentioned' => $brandMentioned,
            'mention_position' => $mentionPosition,
        ];
    }

    /**
     * AI可见度诊断 - 批量查询品牌可见度
     */
    public function diagnoseVisibility(array $queries, string $brandName, array $platforms): array
    {
        $results = [];

        foreach ($queries as $query) {
            foreach ($platforms as $platform) {
                $result = $this->checkIndex($query, $brandName, $platform);
                $result['query'] = $query;
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * 流量复刻 - 改写文章
     */
    public function rewriteArticle(string $content, string $instruction = null): ?string
    {
        $systemPrompt = $instruction ?: "你是一个专业的内容创作专家。请改写以下文章，要求：
1. 保持原文的核心信息和观点
2. 使用不同的表达方式和句式结构
3. 确保内容原创、自然、可读性强
4. 适合在中文自媒体平台发布
5. 保持专业性和准确性";

        return $this->chat($systemPrompt, $content, ['max_tokens' => 4000]);
    }

    /**
     * 调用AutoGEO微服务进行内容优化
     */
    public function optimizeWithAutoGEO(string $content, string $dataset = 'default', string $engine = 'openai'): ?array
    {
        $autoGeoUrl = config('services.autogeo.url', 'http://localhost:5000');

        try {
            $response = Http::timeout(120)->post($autoGeoUrl . '/api/rewrite', [
                'content' => $content,
                'dataset' => $dataset,
                'engine_llm' => $engine,
                'evaluate' => true,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('AutoGEO Error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
