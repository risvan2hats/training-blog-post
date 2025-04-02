<?php

namespace App\Exports;

use App\Repositories\PostRepository;
use App\Services\PostService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PostsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;
    protected $postService;
    protected $serialNumber = 1;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->postService = app(PostService::class);
    }

    public function collection()
    {
        return $this->postService->getPostsForExport($this->filters);
    }

    public function headings(): array
    {
        return [
            'Sl No',
            'Title',
            'Content',
            'Author',
            'Status',
            'Published At',
            'Comments Count',
        ];
    }

    public function map($post): array
    {
        return [
            $this->serialNumber++,
            $post->title,
            $post->content,
            $post->author->name ?? '',
            $post->status,
            $post->published_at?->format('d/m/Y h:i A'),
            $post->comments_count,
        ];
    }

    public function resetSerialNumber()
    {
        $this->serialNumber = 1;
    }
}