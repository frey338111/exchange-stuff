<?php

namespace App\Http\Controllers;

use App\Models\Pages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Pages::query()
            ->select(['id', 'title', 'slug', 'published', 'created_at'])
            ->latest()
            ->paginate(12);

        return view('pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'published' => ['nullable', 'boolean'],
        ]);

        Pages::create([
            'title' => $validated['title'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['title']),
            'content' => $validated['content'],
            'published' => $request->boolean('published'),
        ]);

        return redirect()
            ->route('dashboard.pages.index')
            ->with('status', 'Page created.');
    }

    public function edit(Pages $page): View
    {
        return view('pages.edit', compact('page'));
    }

    public function update(Request $request, Pages $page): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'published' => ['nullable', 'boolean'],
        ]);

        $page->update([
            'title' => $validated['title'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['title'], $page->id),
            'content' => $validated['content'],
            'published' => $request->boolean('published'),
        ]);

        return redirect()
            ->route('dashboard.pages.index')
            ->with('status', 'Page updated.');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'upload' => ['required', 'image', 'max:5120'],
        ]);

        $path = $validated['upload']->store('pages/content', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    public function destroy(Pages $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('dashboard.pages.index')
            ->with('status', 'Page deleted.');
    }

    private function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'page';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Pages::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
