<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Form;
use Illuminate\Support\Facades\DB;

class NsiaAdditionalFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $form = Form::where(['slug' => 'nsia-application'])->firstOrFail();

        $fields = collect([
            'additional_documents' => [
                "Upload any additional documents (Optional)",
                "input",
                "file",
                'e.g., Business Plan, Financial Projections',
            ],
            'additional_documents_details' => [
                "Document Details",
                "textarea",
                "text",
                'Provide details about the documents you uploaded, if any.'
            ],
        ]);

        DB::transaction(function () use ($fields, $form) {
            $fields->each(function ($data, $name) use ($form) {

                [$label, $element, $type, $hint] = $data;

                $field = $form->fields()->where('name', $name)->firstOrNew();

                $field->name = $name;
                $field->field_id = $name;
                $field->label = $label;
                $field->value = null;
                $field->hint = $hint;
                $field->custom_error = null;
                $field->compare = null;
                $field->options = [];
                $field->required = false;
                $field->required_if = null;
                $field->restricted = false;
                $field->key = false;
                $field->min = null;
                $field->max = null;
                $field->element = $element;
                $field->type = $type;
                $field->save();
            });
        });
    }
}
