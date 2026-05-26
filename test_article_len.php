<?php
$articles = \App\Models\Article::where('status','published')
    ->orderBy('id','desc')
    ->limit(5)
    ->get(['id','title','content']);

foreach ($articles as $a) {
    echo "ID: {$a->id}, len=" . strlen($a->content) . ", plain=" . strlen(strip_tags($a->content)) . ", title={$a->title}\n";
}