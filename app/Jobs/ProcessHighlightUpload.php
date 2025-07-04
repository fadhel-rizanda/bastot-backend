<?php

namespace App\Jobs;

use App\Models\game\Highlight;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessHighlightUpload implements ShouldQueue
{
//    use Queueable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//    public function __construct(int $statId, ?int $highlightId, UploadedFile $file, ?string $notes = null)
//     {
//         $this->statId = $statId;
//         $this->highlightId = $highlightId;
//         $this->file = $file;
//         $this->notes = $notes;
//     }
//
//    public function handle(): void
//    {
//        $fileName = Str::uuid() . '.' . $this->file->getClientOriginalExtension();
//        $path = $this->file->storeAs('videos/highlights', $fileName, 'public');
//
//        if ($this->highlightId) {
//            $highlight = Highlight::find($this->highlightId);
//            if ($highlight) {
//                if (Storage::disk('public')->exists($highlight->content)) {
//                    Storage::disk('public')->delete($highlight->content);
//                }
//                $highlight->update([
//                    'content' => $path,
//                    'notes' => $this->notes,
//                ]);
//                return;
//            }
//        }
//
//        Highlight::create([
//           'stat_id' => $this->statId,
//           'content' => $path,
//           'notes' => $this->notes,
//        ]);
//    }

//    v2
    public function __construct(
        protected int $statId,
        protected ?int $highlightId,
        protected ?string $filePath,
        protected ?string $notes = null,
    ) {}

    public function handlev2(): void
    {
        if (!Storage::disk('local')->exists($this->filePath)) return;

        $ext = pathinfo($this->filePath, PATHINFO_EXTENSION);
        $newName = Str::uuid() . '.' . $ext;
        $newPath = 'videos/highlights/' . $newName;

        Storage::disk('public')->put($newPath, Storage::disk('local')->get($this->filePath));
        Storage::disk('local')->delete($this->filePath); // cleanup

        if ($this->highlightId) {
            $highlight = Highlight::find($this->highlightId);
            if ($highlight) {
                if (Storage::disk('public')->exists($highlight->content)) {
                    Storage::disk('public')->delete($highlight->content);
                }
                $highlight->update([
                    'notes' => $this->notes,
                ]);
                return;
            }
        }

        Highlight::create([
            'stat_id' => $this->statId,
            'content' => $newPath,
            'notes' => $this->notes,
        ]);
    }

    public function handle(): void
    {
        $newPath = null;

        if ($this->filePath && Storage::disk('local')->exists($this->filePath)) {
            $ext = pathinfo($this->filePath, PATHINFO_EXTENSION);
            $newName = Str::uuid() . '.' . $ext;
            $newPath = 'videos/highlights/' . $newName;

            Storage::disk('public')->put($newPath, Storage::disk('local')->get($this->filePath));
            Storage::disk('local')->delete($this->filePath); // cleanup
        }

        if ($this->highlightId) {
            $highlight = Highlight::find($this->highlightId);
            if ($highlight) {
                if ($newPath && Storage::disk('public')->exists($highlight->content)) {
                    Storage::disk('public')->delete($highlight->content);
                }

                $highlight->update([
                    'content' => $newPath ?? $highlight->content,
                    'notes' => $this->notes,
                ]);

                return;
            }
        }

        if ($newPath) {
            Highlight::create([
                'stat_id' => $this->statId,
                'content' => $newPath,
                'notes' => $this->notes,
            ]);
        }
    }
}
