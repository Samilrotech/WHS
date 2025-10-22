<?php

namespace App\Modules\DocumentManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Modules\DocumentManagement\Models\DocumentCategory;
use App\Modules\DocumentManagement\Requests\StoreDocumentCategoryRequest;
use App\Modules\DocumentManagement\Requests\UpdateDocumentCategoryRequest;
use App\Modules\DocumentManagement\Services\DocumentCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentCategoryController extends Controller
{
    public function __construct(
        protected DocumentCategoryService $service
    ) {}

    /**
     * Display a listing of the categories
     */
    public function index(): View
    {
        $categories = $this->service->getCategoryTree(auth()->user()->branch_id);

        return view('content.document-management.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create(): View
    {
        $parentCategories = $this->service->getAllForBranch(auth()->user()->branch_id);

        return view('content.document-management.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category
     */
    public function store(StoreDocumentCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['branch_id'] = auth()->user()->branch_id;

        try {
            $category = $this->service->create($data);

            return redirect()->route('document-categories.index')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified category
     */
    public function show(DocumentCategory $documentCategory): View
    {
        $category = $this->service->getWithDocumentsCount($documentCategory->id);

        return view('content.document-management.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(DocumentCategory $documentCategory): View
    {
        $category = $documentCategory;
        $parentCategories = $this->service->getAllForBranch(auth()->user()->branch_id);

        return view('content.document-management.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category
     */
    public function update(UpdateDocumentCategoryRequest $request, DocumentCategory $documentCategory): RedirectResponse
    {
        try {
            $this->service->update($documentCategory, $request->validated());

            return redirect()->route('document-categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy(DocumentCategory $documentCategory): RedirectResponse
    {
        try {
            $this->service->delete($documentCategory);

            return redirect()->route('document-categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }

    /**
     * Reorder categories
     */
    public function reorder(): RedirectResponse
    {
        try {
            $orderData = request()->input('order', []);
            $this->service->reorder($orderData);

            return back()->with('success', 'Categories reordered successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reorder categories: ' . $e->getMessage());
        }
    }
}
