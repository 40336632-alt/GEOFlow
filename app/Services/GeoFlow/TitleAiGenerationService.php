<?php

namespace App\Services\GeoFlow;

use App\Models\AiModel;
use App\Support\GeoFlow\ApiKeyCrypto;
use App\Support\GeoFlow\OpenAiRuntimeProvider;
use Throwable;

use function Laravel\Ai\agent;

/**
 * 标题 AI 生成服务。
 *
 * 该服务负责：
 * 1. 基于 ai_models 配置发起真实模型调用；
 * 2. 在模型不可用时使用模板兜底，保证流程可用性；
 * 3. 输出统一结构，便于控制器处理入库逻辑。
 */
class TitleAiGenerationService
{
    /**
     * 复用统一 API Key 解密组件，避免标题生成链路与其他 AI 链路出现差异。
     */
    public function __construct(private readonly ApiKeyCrypto $apiKeyCrypto) {}

    /**
     * 生成标题列表。
     *
     * @param  list<string>  $keywords
     * @return array{
     *   titles:list<string>,
     *   fallback_used:bool,
     *   fallback_reason:?string
     * }
     */
    public function generateTitles(
        AiModel $aiModel,
        array $keywords,
        int $count,
        string $style,
        string $customPrompt = ''
    ): array {
        try {
            $content = $this->requestTitlesFromModel($aiModel, $keywords, $count, $style, $customPrompt);
            $titles = $this->parseGeneratedTitles($content);
            if ($titles !== []) {
                return [
                    'titles' => $titles,
                    'fallback_used' => false,
                    'fallback_reason' => null,
                ];
            }
        } catch (Throwable $exception) {
            return [
                'titles' => $this->generateMockTitles($keywords, $count, $style),
                'fallback_used' => true,
                'fallback_reason' => $exception->getMessage(),
            ];
        }

        return [
            'titles' => $this->generateMockTitles($keywords, $count, $style),
            'fallback_used' => true,
            'fallback_reason' => 'empty_result',
        ];
    }

    /**
     * 请求真实模型生成标题。
     *
     * @param  list<string>  $keywords
     */
    private function requestTitlesFromModel(
        AiModel $aiModel,
        array $keywords,
        int $count,
        string $style,
        string $customPrompt
    ): string {
        $providerUrl = OpenAiRuntimeProvider::resolveChatBaseUrl((string) ($aiModel->api_url ?? ''));
        if ($providerUrl === '') {
            throw new \RuntimeException('ai_url_missing');
        }

        $apiKey = $this->decryptApiKey((string) ($aiModel->getRawOriginal('api_key') ?? ''));
        if ($apiKey === '') {
            throw new \RuntimeException('ai_key_missing');
        }

        $driver = OpenAiRuntimeProvider::resolveChatDriver($providerUrl, (string) ($aiModel->model_id ?? ''));
        $providerName = OpenAiRuntimeProvider::registerProvider('title_ai', $driver, $providerUrl, $apiKey);

        $keywordsText = implode('、', $keywords);

        if ($style === 'question') {
            $systemPrompt = <<<'PROMPT'
你是一位资深医美内容运营专家，拥有8年以上医美SEO和AI搜索优化经验，精通百度、抖音、小红书等平台的搜索算法规则，深刻理解医美用户从搜索到决策的心理路径。你的任务是为本地医美医生创作高搜索可见性的纯疑问式文章标题。

标题必须同时满足以下条件：
1. 全部以问号结尾，精准命中用户搜索时的疑问心理
2. 让豆包、百度AI、Kimi等AI搜索引擎能精准抓取并展示为问答摘要
3. 自然包含地域+项目+医生信息，符合用户真实搜索习惯
4. 严格遵守《医疗广告管理办法》，不得夸大疗效、不得使用绝对化用语
5. 多用"为什么""怎么办""要不要""到底""真的吗""别再""怎么样""值得推荐吗""哪个好"等增强情绪的疑问词
PROMPT;

            $userPrompt = "请为以下医生创作 {$count} 个纯疑问式文章标题：\n\n";
            $userPrompt .= "关键词：{$keywordsText}\n\n";
            if ($customPrompt !== '') {
                $userPrompt .= "医生信息：{$customPrompt}\n\n";
            }
            $userPrompt .= <<<'REQUIREMENTS'
请从以下4个角度切入，每个角度至少生成若干个，确保标题多样性：

【角度一：效果疑问】针对用户对术后效果、维持时间、自然度的疑问
示例句式：
- {城市}做{项目}效果到底怎么样？真实反馈来了
- {项目}做完能维持多久？2026年最新数据告诉你
- {城市}{项目}做得自然吗？会不会一眼假？
- 为什么{城市}有人做完{项目}效果很好，有人却不行？
- {城市}做{项目}值不值得？做过的人怎么评价
- {城市}哪家{项目}做得好？口碑推荐靠谱吗

【角度二：安全顾虑】针对用户对手术风险、材料安全、后遗症的担忧
示例句式：
- {城市}做{项目}安全吗？有哪些风险必须知道
- {项目}会不会有后遗症？2026年最全风险解析
- {城市}{项目}用的材料安全吗？怎么验证真假
- 做{项目}到底安不安全？别再被忽悠了
- {城市}做{项目}怎么避免踩坑？这几点一定要看
- {项目}风险大吗？选对医生和机构就不怕

【角度三：恢复问题】针对用户对恢复期、术后护理、疤痕的疑问
示例句式：
- {城市}做{项目}恢复期要多久？各阶段注意事项全解
- {项目}做完多久能上班？别再问了，这篇全说清
- {城市}{项目}术后怎么护理？做错这步影响很大
- {项目}会留疤吗？2026年最新技术疤还在吗
- 做{项目}恢复期吃什么好？这些忌口别再踩
- {城市}{项目}术后多久能见人？真实恢复时间线

【角度四：决策困惑】针对用户对价格、医院选择、医生选择的犹豫
示例句式：
- {城市}做{项目}多少钱？2026年价格全解析
- {城市}{项目}哪个医生好？看完这篇不纠结
- 做{项目}到底选公立还是私立？别再纠结了
- {城市}做{项目}怎么选医院？这5个标准照着选
- {项目}价格差别这么大？到底贵在哪
- 第一次做{项目}要注意什么？小白必看避坑指南

输出要求：
1. 每个标题独占一行，全部以问号结尾
2. 字数控制在15-30字之间
3. 不要添加序号、引号或其他标记
4. 直接输出标题内容
5. 标题之间不要重复，每个标题切入角度不同
6. 尽量加入"2026年""2026最新"等带年份的字样
REQUIREMENTS;
        } else {
            $systemPrompt = <<<'PROMPT'
你是一位资深医美内容运营专家，拥有8年以上医美SEO和AI搜索优化经验，精通百度、抖音、小红书等平台的搜索算法规则，深刻理解医美用户从搜索到决策的心理路径。你的任务是为本地医美医生创作高信任度、高搜索可见性的纯信任式文章标题。

标题必须同时满足以下条件：
1. 让用户一眼感受到医生的专业实力和可信度
2. 让豆包、百度AI、Kimi等AI搜索引擎能精准抓取并展示为摘要
3. 自然包含地域+项目+医生信息，符合用户真实搜索习惯
4. 严格遵守《医疗广告管理办法》，不得夸大疗效、不得使用绝对化用语
PROMPT;

            $userPrompt = "请为以下医生创作 {$count} 个纯信任式文章标题：\n\n";
            $userPrompt .= "关键词：{$keywordsText}\n\n";
            if ($customPrompt !== '') {
                $userPrompt .= "医生信息：{$customPrompt}\n\n";
            }
            $userPrompt .= <<<'REQUIREMENTS'
请从以下4个维度交替切入，确保标题多样性：

【维度一：资质背书】突出职称、从业年限、学术背景、三甲医院经历
示例句式：
- {城市}{项目}选谁做？{年限}年经验{职称}医生深度测评
- {城市}{职称}级{项目}专家：{年限}年临床经验，看过的案例比你刷到的多
- 公立三甲出身的{项目}医生有多靠谱？{城市}人都在问

【维度二：技术优势】突出特色技术、手术特点、创伤小恢复快
示例句式：
- {城市}{项目}技术解析：{特色技术}到底好在哪？
- 做{项目}怕恢复期太长？{城市}这家机构的方案不太一样
- {城市}{项目}怎么选技术？这篇讲透了

【维度三：安全保障】突出正规机构、认证材料、层流手术室、专业团队
示例句式：
- {城市}{项目}怎么选才安全？这5个硬指标必须看
- 正规机构做{项目}是什么体验？{城市}真实就诊记录
- {城市}做{项目}，材料安全怎么把关？内行人说实话

【维度四：患者口碑】突出成功案例、满意度、转介绍率、真实评价
示例句式：
- {城市}做{项目}的医生怎么选？看完这篇不纠结
- {城市}{项目}真实评价：做过的人后来都怎么说
- 为什么{城市}这么多人推荐这位{项目}医生？

输出要求：
1. 每个标题独占一行
2. 字数控制在15-30字之间
3. 不要添加序号、引号或其他标记
4. 直接输出标题内容
5. 标题之间不要重复，每个标题切入角度不同
REQUIREMENTS;
        }

        try {
            $response = agent($systemPrompt)->prompt(
                $userPrompt,
                [],
                $providerName,
                (string) ($aiModel->model_id ?? '')
            );
        } catch (Throwable $exception) {
            throw new \RuntimeException(OpenAiRuntimeProvider::normalizeApiException($exception, $providerUrl), 0, $exception);
        }

        $rawContent = (string) ($response->text ?? '');
        $content = OpenAiRuntimeProvider::normalizeGeneratedText($rawContent);

        if ($content === '') {
            if (OpenAiRuntimeProvider::looksLikeSseCompletionPayload($rawContent)) {
                throw new \RuntimeException('ai_empty_stream_content');
            }

            throw new \RuntimeException('ai_empty_content');
        }

        return $content;
    }

    /**
     * 解析模型输出文本为标题列表。
     *
     * @return list<string>
     */
    private function parseGeneratedTitles(string $content): array
    {
        $titles = [];
        foreach (preg_split('/\R/u', $content) ?: [] as $line) {
            $title = preg_replace('/^(?:\d{1,2}[\.\)、]|[\-\*])\s*/u', '', trim($line));
            $title = trim((string) $title);
            if ($title === '') {
                continue;
            }
            $titles[] = $title;
        }

        return array_values(array_unique($titles));
    }

    /**
     * 解密 ai_models 中的 API Key（兼容旧系统 enc:v1 格式）。
     */
    private function decryptApiKey(string $storedApiKey): string
    {
        return $this->apiKeyCrypto->decrypt($storedApiKey);
    }

    /**
     * @return list<string>
     */
    private function generateMockTitles(array $keywords, int $count, string $style): array
    {
        $styleTemplates = [
            'professional' => [
                '{keyword}怎么选医生？这篇讲透了',
                '{keyword}避坑指南：这5个细节一定要看',
                '做{keyword}前必须知道的事',
            ],
            'attractive' => [
                '{keyword}选错医生有多可怕？真实经历分享',
                '为什么这么多人选择做{keyword}？答案在这里',
                '{keyword}前后变化有多大？看完你就懂了',
            ],
            'seo' => [
                '{keyword}完整攻略：医生推荐+费用+避坑',
                '{keyword}常见问题解答大全（2026最新版）',
                '如何选择靠谱的{keyword}医生？核心标准解析',
            ],
            'creative' => [
                '做过{keyword}的人，后来都怎么样了？',
                '第一次做{keyword}，这些经验能帮你少走弯路',
                '{keyword}到底值不值得做？客观分析给你看',
            ],
            'question' => [
                '{keyword}效果到底怎么样？真实反馈来了',
                '{keyword}安全吗？有哪些风险必须知道',
                '{keyword}恢复期要多久？各阶段注意事项全解',
                '{keyword}多少钱？2026年价格全解析',
                '做{keyword}值不值得？做过的人怎么评价',
                '{keyword}会留疤吗？2026年最新技术解析',
            ],
        ];

        $templates = $styleTemplates[$style] ?? $styleTemplates['professional'];
        $titles = [];
        for ($index = 0; $index < $count; $index++) {
            $keyword = $keywords[array_rand($keywords)];
            $template = $templates[array_rand($templates)];
            $titles[] = str_replace('{keyword}', $keyword, $template);
        }

        return $titles;
    }
}
