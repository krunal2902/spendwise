<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Category::forUser($request->user()->id)
                ->select('categories.*');

            return DataTables::eloquent($query)
                ->addColumn('type_badge', function ($row) {
                    $color = $row->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    return '<span class="px-2 py-1 text-xs rounded-full '.$color.'">'.ucfirst($row->type).'</span>';
                })
                ->addColumn('system_badge', function ($row) {
                    if ($row->is_system) {
                        return '<span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">System</span>';
                    }
                    return '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">Custom</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    $color = $row->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500';
                    $text = $row->is_active ? 'Active' : 'Inactive';
                    return '<span class="px-2 py-1 text-xs rounded-full '.$color.'">'.$text.'</span>';
                })
                ->addColumn('color_dot', function ($row) {
                    if ($row->color) {
                        return '<span class="inline-block w-4 h-4 rounded-full" style="background-color: '.$row->color.'"></span>';
                    }
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="flex items-center gap-2">';
                    // Toggle
                    $toggleUrl = route('categories.toggle', $row->id);
                    $html .= '<form method="POST" action="'.$toggleUrl.'">'.csrf_field().method_field('PATCH').'
                        <button type="submit" class="text-sm '.($row->is_active ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800').'">
                            <i class="fas '.($row->is_active ? 'fa-toggle-on' : 'fa-toggle-off').'"></i>
                        </button></form>';
                    // Edit (only non-system)
                    if (!$row->is_system) {
                        $editUrl = route('categories.edit', $row->id);
                        $html .= '<a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-800 text-sm"><i class="fas fa-edit"></i></a>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['type_badge', 'system_badge', 'status_badge', 'color_dot', 'action'])
                ->make(true);
        }

        return view('categories.index');
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create($request->user(), $request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Request $request, Category $category): View
    {
        if ($category->is_system || $category->user_id !== $request->user()->id) {
            abort(403, 'You cannot edit this category.');
        }

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            $this->categoryService->update($category, $request->validated());
            return redirect()->route('categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('categories.index')
                ->with('error', $e->getMessage());
        }
    }

    public function toggle(Request $request, Category $category): RedirectResponse
    {
        if (!$category->is_system && $category->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->categoryService->toggle($category);

        $status = $category->fresh()->is_active ? 'activated' : 'deactivated';
        return redirect()->route('categories.index')
            ->with('success', "Category {$status} successfully.");
    }
}
