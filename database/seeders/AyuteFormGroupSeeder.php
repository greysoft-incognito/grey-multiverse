<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyuteFormGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var Form $form */
        $form = Form::where(['slug' => 'ayute-application'])->firstOrFail();

        $fields = collect([
            [
                'Personal Information',
                'person',
                '',
                true,
                'applicant_',
            ],
            [
                'Company Information',
                'business',
                '',
                false,
                'company_',
            ],
            [
                'Team Information',
                'groups',
                '',
                false,
                'founders_',
            ],
            [
                'Product Information',
                'shopping_cart',
                '',
                false,
                'product_',
            ],
            [
                'Commercial',
                'wallet',
                '',
                false,
                'commercial_',
            ],
            [
                'Impact and Sustainability',
                'emoji_nature',
                '',
                false,
                'impact_',
            ],
            [
                'Additional Information',
                'read_more',
                '',
                false,
                'additional_',
            ],
        ]);

        DB::transaction(function () use ($fields, $form) {
            $fields->each(function ($data, $i) use ($form, $fields) {

                [$name, $icon, $description, $auth, $prefix] = $data;

                /** @var \App\Models\FormFieldGroup $group */
                $group = $form->fieldGroups()->where('name', $name)->firstOrNew();

                $group->name = $name;
                $group->icon = $icon;
                $group->priority = count($fields) - $i;
                $group->description = $description;
                $group->authenticator = $auth;
                $group->save();

                $field_ids = $form->fields()->where('name', 'like', "$prefix%")->pluck('id');
                $group->fields()->sync($field_ids);
            });
        });
    }
}
