<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Form;
use Illuminate\Support\Facades\DB;

class AyuteFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reset = $this->command->confirm('Do you want to reset this form?', false);

        if ($reset) {
            Form::whereSlug('ayute-application')->delete();
        }

        $data = [
            'id' => 10,
            'name' => 'AYuTe 4.0 Questionaire',
            'title' => 'AYuTe 4.0 Questionaire',
            'banner_title' => 'Complete Your AYuTe 4.0 Application',
            'banner_info' => 'Complete Your AYuTe 4.0 Application',
            'socials' => json_encode([
                'facebook' => 'http://facebook.com/ayute',
                'twitter' => 'http://twitter.com/ayute',
                'instagram' => 'http://instagram.com/ayute',
            ]),
            'deadline' => '2025/12/12',
            'template' => 'default',
            'require_auth' => false,
            'dont_notify' => false,
            'data_emails' =>  json_encode([]),
            'success_message' => 'Hello :fullname, This is to confirm that your application for the AYuTe 4.0 has been received successfully and will be reviewed
            soon, we will notify you once we\'re done.',
            'failure_message' => 'Hello :fullname, Unfortunattely we could not complete your application, you may try again soon.',
        ];

        $form = Form::updateOrCreate(
            ['slug' => 'ayute-application'],
            $data,
        );

        $fields = collect([
            'applicant_full_name' => [
                "Full Name of the Applicant",
                "input",
                "text",
                [],
                true,
                'e.g., Joe Amaka',
                null,
            ],
            'applicant_email' => [
                "Email Address",
                "input",
                "email",
                [],
                true,
                'Enter Email Address',
                null,
            ],
            'applicant_phone_number' => [
                "Phone Number",
                "input",
                "tel",
                [],
                true,
                'Enter Phone Number',
                null,
            ],
            "applicant_gender" => [
                "Gender",
                "select",
                "text",
                [
                    ["label" => "Male", "value" => "male"],
                    ["label" => "Female", "value" => "female"],
                ],
                true,
                null,
                null,
            ],
            'company_name' => [
                "Company Name",
                "input",
                "text",
                [],
                true,
                'Enter Company Name',
                null,
            ],

            'company_url' => [
                "Company Name if any",
                "input",
                "text",
                [],
                false,
                'Enter Company URL',
                null,
            ],
            'company_media_handles' => [
                "Social Media Handles (e.g., Twitter, Instagram, LinkedIn, Facebook)",
                "input",
                "text",
                [],
                false,
                'Enter Social Media Handles',
                null,
            ],
            'company_media_info' => [
                "Describe your company in 200 words or less",
                "textarea",
                "text",
                [],
                true,
                null,
                null,
            ],
            'company_business_address' => [
                "Business Address or Location",
                "input",
                "text",
                [],
                true,
                'Enter address',
                null,
            ],
            'company_legally_registered' => [
                "Is your company legally registered in Nigeria? (Yes/No)",
                "select",
                "text",
                [
                    ["label" => "Yes", "value" => true],
                    ["label" => "No", "value" => false],
                ],
                true,
                null,
                null,
            ],
            'company_incorporation_year' => [
                "Year of Incorporation",
                "input",
                "number",
                [],
                false,
                'Enter year',
                null,
            ],
            'company_registration_number' => [
                "Company Registration Number (if applicable)",
                "input",
                "text",
                [],
                false,
                null,
                null,
            ],
            'company_milestones' => [
                "What milestones have you achieved so far?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],
            'company_meaure_success' => [
                "How do you measure success?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'company_long_term' => [
                "What is your long-term vision for the company?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'founders_count' => [
                "How many founders are on the team?",
                "input",
                "number",
                [],
                true,
                null,
                null,
            ],
            'founders_founding_team' => [
                "Who is on your founding team?",
                "textarea",
                "text",
                [],
                true,
                null,
                null,
            ],

            'founders_details' => [
                "Founders' Details (Name, Number, Role, LinkedIn/Profile, Nationality)",
                "textarea",
                "text",
                [],
                true,
                null,
                null,
            ],
            'founders_number_of_employees' => [
                "Total Number of Employees",
                "input",
                "number",
                [],
                true,
                null,
                null,
            ],

            'founders_total_number_of_smallholders_farmers' => [
                "Total Number of Smallholder Farmers",
                "input",
                "number",
                [],
                true,
                null,
                null,
            ],

            'product_total_number_of_smallholders_farmers' => [
                "What stage of growth are you at? (Select from: Idea, Prototype, MVP, Early Revenue, Scaling)",
                "select",
                "text",
                [
                    ["label" => "Idea", "value" => "Idea"],
                    ["label" => "Prototype", "value" => "Prototype"],
                    ["label" => "MVP", "value" => "MVP"],
                    ["label" => "Early Revenue", "value" => "Early Revenue"],
                    ["label" => "Scaling", "value" => "Scaling"],
                ],
                true,
                null,
                null,
            ],


            'product_mvp_link' => [
                "Provide a link to your Minimum Viable Product (MVP) (if applicable)",
                "input",
                "url",
                [],
                false,
                null,
                null,
            ],

            'product_tech_problem' => [
                "What technological problem does your startup solve in the agricultural sector?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'product_describe_product' => [
                "Describe your product and what it does or will do (200 words or less)",
                "textarea",
                "text",
                [],
                true,
                null,
                null,
            ],

            'product_no_of_existing_customers' => [
                "How many existing users/customers do you have?",
                "input",
                "number",
                [],
                true,
                null,
                null,
            ],

            'product_business_model' => [
                "What is your business Model?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'product_unique_selling_point' => [
                "What is your unique selling point?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'product_main_competitors' => [
                "Who are your main competitors?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'product_market_strategy' => [
                "What is your go-to-market strategy?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'product_key_features' => [
                "What are the key features of your product/service?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'commercial_generated_revenue' => [
                "Have you started generating revenue?",
                "select",
                "text",
                [
                    ["label" => "Yes", "value" => true],
                    ["label" => "No", "value" => false],
                ],
                true,
                null,
                null,
            ],

            'commercial_growth_plan' => [
                "What is your long-term growth plan?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'commercial_solution_update' => [
                "How often do you update your solution based on feedback?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'commercial_how_reliable_supply_chain' => [
                "How reliable is your supply chain?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'commercial_protected_ip' => [
                "Do you own protected IP?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'commercial_projected_revenue' => [
                "What is your projected revenue for the next year?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'commercial_pricing_strategy' => [
                "What is your pricing strategy?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'commercial_risks_failed' => [
                "What are the biggest risks and challenges you face?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'commercial_no_of_users' => [
                "How many customers/users have you acquired in the past 6 months?",
                "input",
                "number",
                [],
                true,
                null,
                null,
            ],

            'commercial_user_growth_rate' => [
                "What is your user/customer growth rate over the past year?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'commercial_avg_customer_acquisition' => [
                "What is your average user/customer acquisition cost (CAC)?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'commercial_percentage_of_revenue' => [
                "What percentage of your revenue comes from repeat customers?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_align_sdgs' => [
                "Do you align with the UN Sustainable Development Goals (SDGs)? (Yes/No)",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_no_of_sdgs' => [
                "How many UN Sustainable Development Goals (SDGs) does your work address?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_innovative_tech' => [
                "Describe the innovative technology or solution your startup offers, especially to smallholder farmers",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_model_replicated' => [
                "Can your model be replicated in new regions? (Yes/No)",
                "select",
                "text",
                [
                    ["label" => "Yes", "value" => true],
                    ["label" => "No", "value" => false],
                ],
                true,
                null,
                null,
            ],

            'impact_impact_beyond_five_years' => [
                "How will impact endure beyond 5 years?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_empower_user_to_be_self_reliant' => [
                "Does your product/service empower users to be self-reliant?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_solutions_performance' => [
                "How does your solution perform during crises (e.g., offline access, during global supply chain disruptions)?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_measure_social_media_impact' => [
                "How do you measure the social impact of your product/service?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_impact_data_share' => [
                "How openly do you share impact data?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_impact_larhe_number_of_smallholders' => [
                "Is your solution tailored to scale and impact a large number of smallholder farmers?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_no_of_jobs_created' => [
                "How many jobs have you created in your operating regions?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_address_gender_gaps' => [
                "How does your product/service address gender gaps?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_protect_customer_data' => [
                "How do you protect user/customer data?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_no_of_people_impacted' => [
                "How many people has your product/service impacted?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_statistics_of_impact' => [
                "Can you share data or statistics that demonstrate the impact your product/service has had so far?",
                "input",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_areas_of_difference' => [
                "Can you share specific examples where your product/service has made a measurable difference in the community or environment?",
                "textarea",
                "text",
                [],
                true,
                null,
                null,
            ],

            'impact_areas_of_difference_answer' => [
                "If yes, please provide details (Top 3, 10, 25, 100)",
                "textarea",
                "text",
                [],
                true,
                null,
                null,
            ],
            'impact_agree_to_heifer' => [
                "I agree to the terms and conditions outlined in the Hiefer International for AYute Africa Challenge Nigeria and Privacy Policy.",
                "input",
                "checkbox",
                [],
                true,
                null,
                null,
                "You must agree to the terms and conditions outlined in the Hiefer International for AYute Africa Challenge Nigeria and Privacy Policy.",
            ],

            'impact_agree_to_ayute' => [
                "I agree to the terms and conditions of the AYuTe Africa Challenge Nigeria 4.0 Competition Submission Agreement.",
                "input",
                "checkbox",
                [],
                true,
                null,
                null,
                "You must agree to the terms and conditions of the AYuTe Africa Challenge Nigeria 4.0 Competition Submission Agreement.",
            ],
            'additional_info' => [
                "Upload any additional documents (e.g., Incoperation Certificate (CAC), FIRS TIN, Nigeria Official ID)",
                "input",
                "file",
                [],
                true,
                null,
                null,
            ],
        ]);

        DB::transaction(function () use ($fields, $form) {
            $fields->each(function ($data, $name) use ($form) {
                [$label, $element, $type, $options, $required, $hint, $custom_error] = $data;

                $field = $form->fields()->where('name', $name)->firstOrNew();

                $field->name = $name;
                $field->field_id = $name;
                $field->label = $label;
                $field->value = null;
                $field->hint = $hint;
                $field->custom_error = $custom_error;
                $field->compare = null;
                $field->options = $options;
                $field->required = $required;
                $field->required_if = null;
                $field->restricted = false;
                $field->key = $name === 'applicant_email';
                $field->min = null;
                $field->max = null;
                $field->element = $element;
                $field->type = $type;
                $field->save();
            });
        });

        $this->call([
            AyuteFormGroupSeeder::class,
        ]);
    }
}
