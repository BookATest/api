<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class ResourceDeletedResponse implements Responsable
{
    /**
     * @var string
     */
    protected $model;

    /**
     * ResourceDeletedResponse constructor.
     *
     * @param string $model
     */
    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response()->json(['message' => "The {$this->getResourceName()} has been successfully deleted"]);
    }

    /**
     * @return string
     */
    protected function getResourceName(): string
    {
        return (mb_substr($this->model, mb_strrpos($this->model, '\\') + 1));
    }
}
