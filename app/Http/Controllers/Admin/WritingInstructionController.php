<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WritingInstruction;
use Illuminate\Http\Request;

class WritingInstructionController extends Controller
{
    public function index(Request $request)
    {
        $query = WritingInstruction::query();

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $instructions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.writing-instructions.index', [
            'instructions' => $instructions,
            'currentType' => $request->get('type'),
        ]);
    }

    public function create()
    {
        return view('admin.writing-instructions.form', [
            'instruction' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:article,title,replication',
            'content' => 'required|string',
            'is_default' => 'boolean',
        ]);

        $validated['user_id'] = auth('admin')->id();
        $validated['is_default'] = $request->boolean('is_default');

        WritingInstruction::create($validated);

        return redirect()
            ->route('admin.writing-instructions.index')
            ->with('message', '写作指令创建成功');
    }

    public function edit(WritingInstruction $writingInstruction)
    {
        if ($writingInstruction->user_id !== auth('admin')->id()) {
            abort(403);
        }
        return view('admin.writing-instructions.form', [
            'instruction' => $writingInstruction,
        ]);
    }

    public function update(Request $request, WritingInstruction $writingInstruction)
    {
        if ($writingInstruction->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:article,title,replication',
            'content' => 'required|string',
            'is_default' => 'boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');

        $writingInstruction->update($validated);

        return redirect()
            ->route('admin.writing-instructions.index')
            ->with('message', '写作指令更新成功');
    }

    public function destroy(WritingInstruction $writingInstruction)
    {
        if ($writingInstruction->user_id !== auth('admin')->id()) {
            abort(403);
        }
        $writingInstruction->delete();

        return redirect()
            ->route('admin.writing-instructions.index')
            ->with('message', '写作指令删除成功');
    }
}
