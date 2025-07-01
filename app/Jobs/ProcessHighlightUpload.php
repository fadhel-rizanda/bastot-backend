<?php

namespace App\Jobs;

use App\Models\game\Highlight;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessHighlightUpload implements ShouldQueue
{
//    use Queueable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(int $statId, ?int $highlightId, UploadedFile $file, ?string $notes = null)
    {
        $this->statId = $statId;
        $this->highlightId = $highlightId;
        $this->file = $file;
        $this->notes = $notes;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fileName = Str::uuid() . '.' . $this->file->getClientOriginalExtension();
        $path = $this->file->storeAs('videos/highlights', $fileName, 'public');

        if ($this->highlightId) {
            $highlight = Highlight::find($this->highlightId);
            if ($highlight) {
                if (Storage::disk('public')->exists($highlight->content)) {
                    Storage::disk('public')->delete($highlight->content);
                }
                $highlight->update([
                    'content' => $path,
                    'notes' => $this->notes,
                ]);
                return;
            }
        }

        Highlight::create([
           'stat_id' => $this->statId,
           'content' => $path,
           'notes' => $this->notes,
        ]);
    }
}
