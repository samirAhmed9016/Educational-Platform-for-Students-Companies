<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'instructions' => $this->instructions,
            'duration_minutes' => $this->duration_minutes,
            'passing_score' => $this->passing_score,
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id,
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
