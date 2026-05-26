<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use App\Models\KeywordLibrary;
use Illuminate\Http\Request;

class TuozhanController extends Controller
{
    private const DIMENSIONS = ['A', 'B', 'C', 'D', 'E', 'F'];

    private const COMBINATIONS = [
        'C+D'           => ['C', 'D'],
        'A+C+D'         => ['A', 'C', 'D'],
        'B+C+D'         => ['B', 'C', 'D'],
        'A+B+C+D'       => ['A', 'B', 'C', 'D'],
        'C+D+E'         => ['C', 'D', 'E'],
        'C+D+F'         => ['C', 'D', 'F'],
        'A+C+D+E'       => ['A', 'C', 'D', 'E'],
        'B+C+D+E'       => ['B', 'C', 'D', 'E'],
        'A+B+C+D+E'     => ['A', 'B', 'C', 'D', 'E'],
        'A+B+C+D+F'     => ['A', 'B', 'C', 'D', 'F'],
    ];

    public function index()
    {
        return view('admin.tuozhan.index', [
            'libraries' => KeywordLibrary::orderBy('name')->get(),
            'combinations' => self::COMBINATIONS,
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'combinations' => 'required|array|min:1',
            'c' => 'required|string',
        ]);

        $selectedCombos = $request->input('combinations', []);
        $inputs = [];

        foreach (self::DIMENSIONS as $dim) {
            $raw = $request->input($dim, '');
            $lines = array_filter(array_map('trim', explode("\n", $raw)), fn($l) => $l !== '');
            if (!empty($lines)) {
                $inputs[$dim] = $lines;
            }
        }

        $results = [];
        $counts = [];

        foreach ($selectedCombos as $comboKey) {
            if (!isset(self::COMBINATIONS[$comboKey])) continue;

            $fields = self::COMBINATIONS[$comboKey];
            $arrays = [];

            foreach ($fields as $field) {
                if (!isset($inputs[$field])) {
                    $arrays[$field] = [];
                } else {
                    $arrays[$field] = $inputs[$field];
                }
            }

            // Skip if any field is empty
            if (in_array([], $arrays, true)) continue;

            $keywords = $this->cartesian($arrays);
            $results[$comboKey] = $keywords;
            $counts[$comboKey] = count($keywords);
        }

        $allKeywords = [];
        foreach ($results as $keywords) {
            $allKeywords = array_merge($allKeywords, $keywords);
        }

        $allKeywords = array_values(array_unique($allKeywords));

        return response()->json([
            'keywords' => $allKeywords,
            'total' => count($allKeywords),
            'counts' => $counts,
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'keywords' => 'required|string',
            'library_id' => 'required|exists:keyword_libraries,id',
        ]);

        $libraryId = $request->input('library_id');
        $raw = $request->input('keywords');
        $lines = array_filter(array_filter(array_map('trim', explode("\n", $raw)), fn($l) => $l !== ''));

        $saved = 0;
        $skipped = 0;

        foreach ($lines as $keyword) {
            $exists = Keyword::where('library_id', $libraryId)
                ->where('keyword', $keyword)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Keyword::create([
                'library_id' => $libraryId,
                'keyword' => $keyword,
                'used_count' => 0,
                'usage_count' => 0,
            ]);
            $saved++;
        }

        return response()->json([
            'saved' => $saved,
            'skipped' => $skipped,
            'message' => "成功保存 {$saved} 个关键词" . ($skipped > 0 ? "，{$skipped} 个已存在" : ''),
        ]);
    }

    private function cartesian(array $arrays): array
    {
        $result = [[]];
        foreach ($arrays as $key => $values) {
            $temp = [];
            foreach ($result as $existing) {
                foreach ($values as $value) {
                    $temp[] = array_merge($existing, [$key => $value]);
                }
            }
            $result = $temp;
        }
        return array_map(fn($item) => implode('', $item), $result);
    }
}
