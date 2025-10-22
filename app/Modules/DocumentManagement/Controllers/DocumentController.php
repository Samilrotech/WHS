<?php

namespace App\Modules\DocumentManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Modules\DocumentManagement\Models\Document;
use App\Modules\DocumentManagement\Requests\StoreDocumentRequest;
use App\Modules\DocumentManagement\Requests\UpdateDocumentRequest;
use App\Modules\DocumentManagement\Requests\ApproveDocumentRequest;
use App\Modules\DocumentManagement\Requests\CreateDocumentVersionRequest;
use App\Modules\DocumentManagement\Services\DocumentService;
use App\Modules\DocumentManagement\Services\DocumentCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $service,
        protected DocumentCategoryService $categoryService
    ) {}

    /**
     * Display a listing of the documents
     */
    public function index(): View
    {
        $perPage = request()->input('per_page', 25);
        $documents = $this->service->getAllForBranch(auth()->user()->branch_id, $perPage);
        $categories = $this->categoryService->getAllForBranch(auth()->user()->branch_id);

        return view('content.DocumentManagement.Index', compact('documents', 'categories'));
    }

    /**
     * Show the form for creating a new document
     */
    public function create(): View
    {
        $categories = $this->categoryService->getAllForBranch(auth()->user()->branch_id);

        return view('content.document-management.documents.create', compact('categories'));
    }

    /**
     * Store a newly created document
     */
    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['branch_id'] = auth()->user()->branch_id;
        $file = $request->file('file');

        try {
            $document = $this->service->create($data, $file);

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document uploaded successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified document
     */
    public function show(Document $document): View
    {
        $this->service->logAccess($document, 'view');

        return view('content.document-management.documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified document
     */
    public function edit(Document $document): View
    {
        $categories = $this->categoryService->getAllForBranch(auth()->user()->branch_id);

        return view('content.document-management.documents.edit', compact('document', 'categories'));
    }

    /**
     * Update the specified document
     */
    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        try {
            $this->service->update($document, $request->validated());

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update document: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified document
     */
    public function destroy(Document $document): RedirectResponse
    {
        try {
            $this->service->delete($document);

            return redirect()->route('documents.index')
                ->with('success', 'Document deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }

    /**
     * Download the document
     */
    public function download(Document $document)
    {
        try {
            return $this->service->download($document);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download document: ' . $e->getMessage());
        }
    }

    /**
     * Create a new version of the document
     */
    public function createVersion(CreateDocumentVersionRequest $request, Document $document): RedirectResponse
    {
        $file = $request->file('file');
        $changeNotes = $request->input('change_notes');

        try {
            $this->service->createNewVersion($document, $file, $changeNotes);

            return redirect()->route('documents.show', $document)
                ->with('success', 'New version created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create new version: ' . $e->getMessage());
        }
    }

    /**
     * Show documents pending review
     */
    public function pendingReview(): View
    {
        $documents = $this->service->getPendingReview(auth()->user()->branch_id);

        return view('content.document-management.documents.pending-review', compact('documents'));
    }

    /**
     * Approve a document
     */
    public function approve(ApproveDocumentRequest $request, Document $document): RedirectResponse
    {
        try {
            $this->service->approve($document, $request->input('review_notes'));

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve document: ' . $e->getMessage());
        }
    }

    /**
     * Reject a document
     */
    public function reject(ApproveDocumentRequest $request, Document $document): RedirectResponse
    {
        try {
            $this->service->reject($document, $request->input('review_notes'));

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document rejected.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reject document: ' . $e->getMessage());
        }
    }

    /**
     * Show expired documents
     */
    public function expired(): View
    {
        $documents = $this->service->getExpired(auth()->user()->branch_id);

        return view('content.document-management.documents.expired', compact('documents'));
    }

    /**
     * Show expiring soon documents
     */
    public function expiringSoon(): View
    {
        $documents = $this->service->getExpiringSoon(auth()->user()->branch_id);

        return view('content.document-management.documents.expiring-soon', compact('documents'));
    }

    /**
     * Search documents
     */
    public function search(): View
    {
        $query = request()->input('q');
        $perPage = request()->input('per_page', 25);
        $documents = $this->service->search(auth()->user()->branch_id, $query, $perPage);

        return view('content.document-management.documents.search', compact('documents', 'query'));
    }
}
