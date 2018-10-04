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

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
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
    public function uploadBase64EncodedPng(string $content): self
    {
        list(, $data) = explode(';', $content);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        return $this->upload($data);
    }
}
