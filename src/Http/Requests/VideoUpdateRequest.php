<?php

namespace Vng\EvaCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Vng\EvaCore\Http\Validation\VideoValidation;
use Vng\EvaCore\Models\Video;
use Vng\EvaCore\Repositories\VideoRepositoryInterface;

class VideoUpdateRequest extends FormRequest implements FormRequestInterface
{
    public function authorize(): bool
    {
        return Auth::user()->can('update', $this->getVideo());
    }

    public function rules(): array
    {
        $video = $this->getVideo();
        if (!$video instanceof Video) {
            throw new \Exception('Cannot derive video from route');
        }
        return VideoValidation::make($this)->getUpdateRules($video);
    }

    protected function getVideo()
    {
        /** @var VideoRepositoryInterface $videoRepository */
        $videoRepository = App::make(VideoRepositoryInterface::class);
        return $videoRepository->find($this->route('videoId'));
    }
}
