<?php

namespace Database\Seeders;

use App\Models\v1\GenericFormField;
use Illuminate\Database\Seeder;

class GenericFormFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GenericFormField::truncate();
        GenericFormField::insert([
            [
                'form_id' => '1',
                'label' => 'First Name',
                'name' => 'firstname',
                'field_id' => 'firstname',
                'element' => 'input',
                'type' => 'text',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Last name',
                'name' => 'lastname',
                'field_id' => 'lastname',
                'element' => 'input',
                'type' => 'text',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Website',
                'name' => 'website',
                'field_id' => 'website',
                'element' => 'input',
                'type' => 'url',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Linkedin',
                'name' => 'linkedin',
                'field_id' => 'linkedin',
                'element' => 'input',
                'type' => 'url',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Facebook',
                'name' => 'facebook',
                'field_id' => 'facebook',
                'element' => 'input',
                'type' => 'url',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Twitter',
                'name' => 'twitter',
                'field_id' => 'twitter',
                'element' => 'input',
                'type' => 'url',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Github',
                'name' => 'github',
                'field_id' => 'github',
                'element' => 'input',
                'type' => 'url',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Email Address',
                'name' => 'email',
                'field_id' => 'email',
                'element' => 'input',
                'type' => 'email',
                'options' => null,
                'required_if' => null,
                'key' => true,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Phone Number',
                'name' => 'phone',
                'field_id' => 'phone',
                'element' => 'input',
                'type' => 'tel',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Date of Birth',
                'name' => 'dob',
                'field_id' => 'dob',
                'element' => 'input',
                'type' => 'date',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Residential address',
                'name' => 'address',
                'field_id' => 'address',
                'element' => 'input',
                'type' => 'text',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Country',
                'name' => 'country',
                'field_id' => 'country',
                'element' => 'input',
                'type' => 'text',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => 'Nigeria',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Gender',
                'name' => 'gender',
                'field_id' => 'gender',
                'element' => 'input',
                'type' => 'radio',
                'options' => json_encode([
                    ['label' => 'Male', 'value' => 'male'],
                    ['label' => 'Female', 'value' => 'female'],
                    ['label' => 'Other', 'value' => 'other'],
                    ['label' => 'Prefer not to say', 'value' => 'na'],
                ]),
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => 'male',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Do you have any disabilities?',
                'name' => 'disabled',
                'field_id' => 'disabled',
                'element' => 'input',
                'type' => 'radio',
                'options' => json_encode([
                    ['label' => 'Yes', 'value' => 1],
                    ['label' => 'No', 'value' => 0],
                ]),
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => 0,
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'If yes, tell us about your disability/disabilites',
                'name' => 'disabilities',
                'field_id' => 'disabilities',
                'element' => 'textarea',
                'type' => 'text',
                'options' => null,
                'required_if' => 'disabled=1',
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => 'Please tell us about your disability/disabilites',
            ],
            [
                'form_id' => '1',
                'label' => 'Highest academic qualification',
                'name' => 'qualification',
                'field_id' => 'qualification',
                'element' => 'textarea',
                'type' => 'text',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Last school Attended',
                'name' => 'last_school',
                'field_id' => 'last_school',
                'element' => 'input',
                'type' => 'text',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'What problem is your idea solving and why',
                'name' => 'problem',
                'field_id' => 'problem',
                'element' => 'textarea',
                'type' => 'text',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'How are you solving this problem',
                'name' => 'solution',
                'field_id' => 'solution',
                'element' => 'textarea',
                'type' => 'text',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'How does your idea stand out from the crowd',
                'name' => 'uniquenes',
                'field_id' => 'uniquenes',
                'element' => 'textarea',
                'type' => 'text',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'What is the potential value of your idea',
                'name' => 'potential',
                'field_id' => 'potential',
                'element' => 'textarea',
                'type' => 'text',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Introductory Video Link',
                'name' => 'video_link',
                'field_id' => 'video_link',
                'element' => 'input',
                'type' => 'url',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => 'Upload a one minute video of your on YouTube introducing yourself, your idea, and elaborating on how your idea will make your community and the world a better place. Share the YouTube link here',
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Are there any publications about your idea, if yes share the url',
                'name' => 'publication_link',
                'field_id' => 'publication_link',
                'element' => 'input',
                'type' => 'url',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Idea URL',
                'name' => 'idea_link',
                'field_id' => 'idea_link',
                'element' => 'input',
                'type' => 'url',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
            [
                'form_id' => '1',
                'label' => 'Resource URL',
                'name' => 'resource_link',
                'field_id' => 'resource_link',
                'element' => 'input',
                'type' => 'url',
                'options' => null,
                'required_if' => null,
                'key' => false,
                'restricted' => false,
                'value' => '',
                'hint' => null,
                'custom_error' => null,
            ],
        ]);
    }
}