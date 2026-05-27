<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryImageService $categoryImageService,
    ) {
    }

    public function index(): View
    {
        $categories = Category::query()
            ->orderBy('parent_id')
            ->orderBy('category_title')
            ->get();

        $parentCategories = Category::query()
            ->select(['category_id', 'category_title'])
            ->where('parent_id', 0)
            ->orderBy('category_title')
            ->get();

        return view('categories.index', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validatedCategoryData($request));

        return redirect()->route('categories.index')->with('status', 'Category created.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validatedCategoryData($request, $category));

        return redirect()->route('categories.index')->with('status', 'Category updated.');
    }

    private function validatedCategoryData(Request $request, ?Category $category = null): array
    {
        $input = $request->all();
        $input['url_key'] = $input['url_key'] ?? '';

        if (trim((string) $input['url_key']) === '') {
            $input['url_key'] = Str::slug((string) ($input['category_title'] ?? ''));
        } else {
            $input['url_key'] = Str::slug((string) $input['url_key']);
        }

        $validator = Validator::make($input, [
            'category_title' => ['required', 'string', 'max:255'],
            'url_key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category', 'url_key')->ignore($category?->category_id, 'category_id'),
            ],
            'parent_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($category) {
                    if ((int) $value === 0) {
                        return;
                    }

                    if ($category && (int) $value === (int) $category->category_id) {
                        $fail('A category cannot be its own parent.');

                        return;
                    }

                    $parentExists = Category::query()
                        ->where('category_id', $value)
                        ->where('parent_id', 0)
                        ->exists();

                    if (! $parentExists) {
                        $fail('The selected parent category is invalid.');
                    }
                },
            ],
            'base_point' => ['nullable', 'integer', 'min:10', 'max:100'],
            'meta_tag' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'status' => ['nullable', Rule::in(['1'])],
        ]);

        $data = $validator->validate();
        $data['status'] = $request->boolean('status');
        $data['base_point'] = $data['base_point'] ?? null;

        if ((int) $data['parent_id'] === 0) {
            $data['base_point'] = null;
        }

        if ($request->hasFile('category_image')) {
            $data['category_image'] = $this->categoryImageService->store(
                $request->file('category_image'),
                $category?->category_image,
            );
        } else {
            unset($data['category_image']);
        }

        return $data;
    }
}
