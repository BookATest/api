<?php

namespace App\Models;

use App\Models\Mutators\FileMutators;
use App\Models\Relationships\FileRelationships;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class File extends Model implements Responsable
{
    use FileMutators;
    use FileRelationships;

    const MIME_PNG = 'image/png';
    const MIME_JPEG = 'image/jpeg';
    const MIME_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /**
     * Called just after the model has deleted.
     *
     * @param \App\Models\Model $model
     */
    protected function onDeleted(Model $model)
    {
        /** @var \App\Models\File $model */
        Storage::cloud()->delete($model->path());

        parent::onDeleted($model);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $content = Storage::cloud()->get($this->path());

        return response()->make($content, Response::HTTP_OK, [
            'Content-Type' => $this->mime_type,
            'Content-Disposition' => "inline; filename=\"{$this->filename}\"",
        ]);
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return "/files/{$this->id}-{$this->filename}";
    }

    /**
     * @param string $content
     * @return \App\Models\File
     */
    public function upload(string $content): self
    {
        Storage::cloud()->put($this->path(), $content);

        return $this;
    }

    /**
     * @param string $content
     * @return \App\Models\File
     */
    public function uploadBase64EncodedImage(string $content): self
    {
        return $this->upload(
            base64_decode_image($content)
        );
    }
}
