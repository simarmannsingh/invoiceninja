<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2023. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Http\Requests\TaskScheduler;

use App\Http\Requests\Request;
use App\Http\ValidationRules\Scheduler\ValidClientIds;
use App\Models\Client;
use App\Utils\Traits\MakesHash;
use Illuminate\Validation\Rule;

class StoreSchedulerRequest extends Request
{
    use MakesHash;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules()
    {

        $rules = [
            'name' => ['bail', 'required', Rule::unique('schedulers')->where('company_id', auth()->user()->company()->id)],
            'is_paused' => 'bail|sometimes|boolean',
            'frequency_id' => 'bail|required|integer|digits_between:1,12',
            'next_run' => 'bail|required|date:Y-m-d|after_or_equal:today',
            'next_run_client' => 'bail|sometimes|date:Y-m-d',
            'template' => 'bail|required|string',
            'parameters' => 'bail|array',
            'parameters.clients' => ['bail','sometimes', 'array', new ValidClientIds()],
        ];

        return $rules;

    }

    public function prepareForValidation()
    {

        $input = $this->all();

        if (array_key_exists('next_run', $input) && is_string($input['next_run'])) 
            $this->merge(['next_run_client' => $input['next_run']]);
        
        return $input;
    }


}