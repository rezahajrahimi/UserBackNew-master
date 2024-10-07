<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;


class FixRequestInputs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $allInputs = $request->all();
        $allInputs = $this->removeArabicCharacters($allInputs);
        $allInputs = $this->removePersianNumbers($allInputs);
        $request->replace($allInputs);
        return $next($request);
    }
    protected function removeArabicCharacters(array $inputs)
    {
        foreach ($inputs as $key => $value) {
            if (is_array($value)) {
                $inputs[$key] = $this->removeArabicCharacters($value);
            } elseif (! ($value instanceof UploadedFile) && is_string($value)) {
                $inputs[$key] = str_replace(
                    ['ي', 'ك'],
                    ['ی', 'ک'],
                    $value
                );
            }
        }

        return $inputs;
    }
    protected function removePersianNumbers(array $inputs)
    {
        foreach ($inputs as $key => $value) {
            if (is_array($value)) {
                $inputs[$key] = $this->removePersianNumbers($value);
            } elseif (! ($value instanceof UploadedFile) && is_string($value)) {
                $inputs[$key] = $this->fa_to_en($value);
            }
        }

        return $inputs;
    }
    function en_to_fa($text)
    {
        $en_num = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $fa_num = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

        return str_replace($en_num, $fa_num, $text);
    }
    function fa_to_en($text)
    {
        $fa_num = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $en_num = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($fa_num, $en_num, $text);
    }
}
