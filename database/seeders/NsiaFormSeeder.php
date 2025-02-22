<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Form;
use Illuminate\Support\Facades\DB;

class NsiaFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'name' => 'NSIA Application',
            'title' => 'NSIA Application',
            'banner_title' => 'Complete Your Application',
            'banner_info' => 'Complete Your Application',
            'socials' => [
                'facebook' => 'http://facebook.com/nsia',
                'twitter' => 'http://twitter.com/nsia',
                'instagram' => 'http://instagram.com/nsia',
            ],
            'deadline' => '2025/12/12',
            'template' => 'default',
            'require_auth' => false,
            'dont_notify' => false,
            'data_emails' =>  [],
            'success_message' => 'Hello :fullname, This is to confirm that your application for the NSIA Prize for innovation has been received successfully and will be reviewed
            soon, we will notify you once we\'re done.',
            'failure_message' => 'Hello :fullname, Unfortunattely we could not complete your application, you may try again soon.',
        ];

        $form = Form::updateOrCreate(
            ['slug' => 'nsia-application'],
            $data,
        );

        $fields = collect([
            "applicant_name" => [
                "Full Name of the Applicant",
                "input",
                "text",
                [],
                true,
                null,
            ],
            "applicant_email" => [
                "Email Address",
                "input",
                "email",
                [],
                true,
                null,
            ],
            "applicant_phone" => [
                "Phone Number",
                "input",
                "tel",
                [],
                true,
                null,
            ],
            "applicant_gender" => [
                "Gender",
                "select",
                "text",
                [
                    ["label" => "Male", "value" => "male"],
                    ["label" => "Female", "value" => "female"]
                ],
                false,
                null,
            ],
            //================
            "company_name" => [
                "Company Name",
                "input",
                "text",
                [],
                true,
                null,
            ],
            "company_url" => [
                "Company URL (if any)",
                "input",
                "url",
                [],
                false,
                null,
            ],
            "company_socials" => [
                "Social Media Handles (e.g., Twitter, Instagram, LinkedIn, Facebook)",
                "input",
                "text",
                [],
                false,
                null,
            ],
            "company_info" => [
                "Describe your company in 200 words or less",
                "textarea",
                "text",
                [],
                true,
                null,
            ],
            "company_address" => [
                "Business Address or Location",
                "input",
                "text",
                [],
                true,
                null,
            ],
            "company_registered" => [
                "Is your company legally registered in Nigeria? (Yes/No)",
                "select",
                "text",
                [
                    ["label" => "Yes", "value" => 1],
                    ["label" => "No", "value" => 0]
                ],
                true,
                null,
            ],
            "company_reg" => [
                "Year of Incorporation",
                "input",
                "text",
                [],
                false,
                null,
            ],
            "company_reg_year" => [
                "Company Registration Number (if applicable)",
                "input",
                "number",
                [],
                true,
                null,
            ],
            "company_has_affiliates" => [
                "Does your company have any other affiliates or subsidiaries registered outside Nigeria?",
                "select",
                "text",
                [
                    ["label" => "Yes", "value" => 1],
                    ["label" => "No", "value" => 0]
                ],
                true,
                null,
            ],
            "company_affiliate_info" => [
                "If yes, provide details of affiliates or subsidiaries registered outside Nigeria",
                "textarea",
                "text",
                [],
                false,
                'company_has_affiliates=true'
            ],
            "company_nigerian_entity" => [
                "Is the Nigerian company the operational entity for the business?",
                "select",
                "text",
                [
                    ["label" => "Yes", "value" => 1],
                    ["label" => "No", "value" => 0]
                ],
                true,
                null,
            ],
            "company_milestone" => [
                "What milestones have you achieved so far?",
                "select",
                "text",
                [
                    ["label" => "Secured initial funding and developed a prototype", "value" => "initial-funding"],
                    ["label" => "Launched a pilot program and gained early adopters", "value" => "launched-a-pilot"],
                    ["label" => "Established key partnerships and collaborations", "value" => "established-partnerships"],
                    ["label" => "Received industry recognition and awards", "value" => "received-recognition"],
                ],
                true,
                null,
            ],
            "company_success_measure" => [
                "How do you measure success?",
                "select",
                "text",
                [
                    ["label" => "Revenue growth", "value" => "revenue-growth"],
                    ["label" => "Customer satisfaction", "value" => "customer-satisfaction"],
                    ["label" => "Social and environmental impact", "value" => "social-environmental"],
                ],
                true,
                null,
            ],
            "company_vision" => [
                "What is your long-term vision for the company?",
                "select",
                "text",
                [
                    ["label" => "Becoming a market leader in Nigeria", "value" => "becoming-leader"],
                    ["label" => "Expanding to other African countries", "value" => "expanding-to-africa"],
                    ["label" => "Diversifying product/service offerings", "value" => "diversifying-offerings"],
                    ["label" => "Achieving sustainable growth and impact", "value" => "sustainable-growth"],
                ],
                true,
                null,
            ],
            //================
            "founders_count" => [
                "How many founders are on the team?",
                "input",
                "number",
                [],
                true,
                null,
            ],
            "founders_team" => [
                "Who is on your founding team?",
                "select",
                "text",
                [
                    ["label" => "Experienced professionals with industry expertise", "value" => "experienced-professionals"],
                    ["label" => "Innovators with a track record of success", "value" => "innovators"],
                    ["label" => "Recent graduates with fresh perspectives", "value" => "recent-graduates"],
                    ["label" => "A diverse mix of technical and business skills", "value" => "diverse-mix"],
                ],
                true,
                null,
            ],
            "founders_details" => [
                "Founders' Details (Name, Number, Role, LinkedIn/Profile, Nationality)",
                "input",
                "text",
                [],
                true,
                null,
            ],
            "founders_employee_count" => [
                "Total Number of Employees",
                "input",
                "number",
                [],
                true,
                null,
            ],
            //================
            "product_stage" => [
                "What stage of growth are you at? (Select from: Idea, Prototype, MVP, Early Revenue, Scaling)",
                "select",
                "text",
                [
                    ["label" => "Prototype", "value" => "Prototype"],
                    ["label" => "MVP", "value" => "MVP"],
                    ["label" => "Early Revenue", "value" => "Early Revenue"],
                    ["label" => "Scaling", "value" => "Scaling"],
                ],
                true,
                null,
            ],
            "product_url" => [
                "Provide a link to your Minimum Viable Product (MVP) (if applicable)",
                "input",
                "url",
                [],
                false,
                null,
            ],
            "product_sector" => [
                "Startup Sector (Select from: Healthcare, Education, Agriculture, Other)",
                "select",
                "text",
                [
                    ["label" => "Healthcare", "value" => "healthcare"],
                    ["label" => "Education", "value" => "education"],
                    ["label" => "Agriculture", "value" => "agriculture"],
                    ["label" => "Multi-Sector", "value" => "multi-sector"],
                ],
                true,
                null,
            ],
            "product_info" => [
                "Describe your product and what it does or will do (200 words or less)",
                "textarea",
                "text",
                [],
                true,
                null,
            ],
            "product_users" => [
                "How many existing users/customers do you have?",
                "select",
                "text",
                [
                    ["label" => "1-50 Users/customers", "value" => "1-50"],
                    ["label" => "51-200 Users/customers", "value" => "51-200"],
                    ["label" => "500 + users/customers", "value" => "500 +"],
                ],
                true,
                null,
            ],
            "product_model" => [
                "What is your business Model?",
                "select",
                "text",
                [
                    ["label" => "Subscription-based services", "value" => "subscription-based"],
                    ["label" => "One-time purchase with maintenance options", "value" => "one-time"],
                    ["label" => "Partnership and licensing agreements", "value" => "partnership"],
                    ["label" => "Free with premium features", "value" => "free-premium"],
                    ["label" => "Other", "value" => "other"],
                ],
                true,
                null,
            ],
            "product_selling_point" => [
                "What is your unique selling point?",
                "select",
                "text",
                [
                    ["label" => "Affordable and scalable solutions tailored to local needs", "value" => "affordable-and-scalable"],
                    ["label" => "Innovative technology with user-friendly interfaces", "value" => "innovative-technology"],
                    ["label" => "Comprehensive support and training", "value" => "comprehensive-support"],
                    ["label" => "Sustainable and eco-friendly practices", "value" => "eco-friendly"],
                ],
                true,
                null,
            ],
            "product_competitors" => [
                "Who are your main competitors?",
                "select",
                "text",
                [
                    ["label" => "Established industry leaders", "value" => "industry-leaders"],
                    ["label" => "Emerging startups", "value" => "emerging-startups"],
                    ["label" => "Local service providers", "value" => "local-providers"],
                    ["label" => "International corporations", "value" => "international-corporations"],
                ],
                true,
                null,
            ],
            "product_strategy" => [
                "What is your go-to-market strategy?",
                "select",
                "text",
                [
                    ["label" => "Direct sales and strategic partnerships", "value" => "direct-sales"],
                    ["label" => "Online marketing and social media campaigns", "value" => "online-marketing"],
                    ["label" => "Community outreach and engagement", "value" => "community-outreach"],
                    ["label" => "Industry events and trade shows", "value" => "industry-events"],
                ],
                true,
                null,
            ],
            "product_key_features" => [
                "What are the key features of your product/service?",
                "select",
                "text",
                [
                    ["label" => "User-friendly interface", "value" => "user-friendly"],
                    ["label" => "Cost-effectiveness", "value" => "cost-effectiveness"],
                    ["label" => "Scalability", "value" => "scalability"],
                    ["label" => "Sustainability", "value" => "sustainability"],
                ],
                true,
                null,
            ],
        ]);

        DB::transaction(function () use ($fields, $form) {
            $fields->each(function ($data, $name) use ($form) {

                [$label, $element, $type, $options, $required, $required_if] = $data;

                $field = $form->fields()->where('name', $name)->firstOrNew();

                $field->name = $name;
                $field->field_id = $name;
                $field->label = $label;
                $field->value = null;
                $field->hint = null;
                $field->custom_error = null;
                $field->compare = null;
                $field->options = $options;
                $field->required = $required;
                $field->required_if = $required_if;
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
            NsiaCommercialFormSeeder::class,
            NsiaImpactFormSeeder::class,
        ]);
    }
}
