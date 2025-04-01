<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExportService;

class ExportController extends Controller
{
    /**
     * @var ExportService
     */
    protected $exportService;

    /**
     * ExportController constructor.
     *
     * @param ExportService $exportService
     */
    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export posts to Excel according to filters
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportPosts(Request $request)
    {
        return $this->exportService->exportPosts($request->all());
    }
}