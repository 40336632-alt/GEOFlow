<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;

class GenerateTestArticle extends Command
{
    protected $signature = 'test:gen-article {title}';
    protected $description = 'Generate a long test article';

    public function handle()
    {
        $title = $this->argument('title') ?: '洗地机品牌深度评测报告';

        $content = str_repeat("<p>洗地机行业发展迅速，市场上涌现出众多品牌和产品。消费者在选购时往往面临诸多选择困难。本文将从品牌知名度、产品质量、售后服务、用户口碑等多个维度进行全面分析。随着科技的进步和消费者需求的提升，洗地机已经成为越来越多家庭的清洁首选。</p><p>首先，让我们来看看市场上的主流品牌。目前市面上主要有A品牌、B品牌、C品牌等几个知名厂商。每个品牌都有其独特的优势和特点。A品牌以技术创新著称，其最新款产品配备了智能导航系统，能够自动规划清洁路线，大大提高了清洁效率。B品牌则以性价比取胜，其产品价格相对较低，但功能却很齐全，非常适合预算有限的消费者。</p><p>C品牌作为后起之秀，近年来发展迅速。该品牌注重产品设计和用户体验，其产品外观时尚，操作简便，深受年轻消费者喜爱。此外，还有一些新兴品牌也在不断涌现，它们往往专注于某个细分市场，如针对宠物家庭的除毛神器、针对大面积住宅的超强续航款等。</p><p>在产品性能方面，我们需要关注几个关键指标：清洁能力、续航时间、噪音水平、维护成本。清洁能力是最基本的指标，好的洗地机应该能够轻松处理各种地面污渍，包括灰尘、毛发、液体污渍等。续航时间决定了机器的工作效率，一般来说，续航时间在40分钟以上的机型可以满足大多数家庭的日常清洁需求。噪音水平也是一个重要的考量因素。</p>", 20);

        $category = Category::firstOrCreate(['name' => '行业资讯'], ['name' => '行业资讯', 'slug' => 'hangye-zixun']);

        $article = Article::create([
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'excerpt' => '关于' . $title . '的详细内容',
            'content' => $content,
            'category_id' => $category->id,
            'author_id' => 1,
            'status' => 'published',
            'review_status' => 'approved',
            'published_at' => now(),
        ]);

        $this->info("Created article ID: {$article->id}, content len: " . strlen($content));
    }
}