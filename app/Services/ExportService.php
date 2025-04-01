<?php

namespace App\Services;

use App\Exports\PostsExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    /**
     * Export posts to Excel with applied filters
     *
     * @param array $filters
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportPosts(array $filters)
    {
        $filename = $this->generateExportFilename('posts');
        return Excel::download(new PostsExport($filters), $filename);
    }

    /**
     * Generate a standardized export filename
     *
     * @param string $type
     * @return string
     */
    protected function generateExportFilename(string $type): string
    {
        return $type . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
    }
}