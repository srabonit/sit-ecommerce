<?php

namespace App\Services;

use App\Models\Form;
use Illuminate\Support\HtmlString;

class FormRenderer
{
    /**
     * Render Dynamic Form
     *
     * Usage:
     *
     * @form(1)
     * @form('contact-form')
     */
    public function render($formIdentifier): HtmlString
    {
        /*
        |--------------------------------------------------------------------------
        | Find Form
        |--------------------------------------------------------------------------
        */

        $form = Form::with([
            'fields' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            }
        ])
        ->where('is_active', true)
        ->where(function ($query) use ($formIdentifier) {

            if (is_numeric($formIdentifier)) {

                $query->where('id', $formIdentifier);

            } else {

                $query->where('slug', $formIdentifier);

            }

        })
        ->first();

        /*
        |--------------------------------------------------------------------------
        | Form Not Found
        |--------------------------------------------------------------------------
        */

        if (!$form) {
            return new HtmlString(
                '<div class="dynamic-form-error">
                    Form not found or inactive.
                </div>'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Render View
        |--------------------------------------------------------------------------
        */

        return new HtmlString(
            view('frontend.forms.render', [
                'form' => $form,
            ])->render()
        );
    }
}