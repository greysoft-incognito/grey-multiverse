<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Form;
use Illuminate\Support\Facades\DB;

class NsiaImpactFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $form = Form::where(['slug' => 'nsia-application'])->firstOrFail();

        $fields = collect([
            'impact_sustainable_dev_goals' => [
                "Do you align with the UN Sustainable Development Goals (SDGs)? (Yes/No)",
                "select",
                "text",
                [
                    ["label" => "Yes", "value" => "Yes"],
                    ["label" => "No", "value" => "No"]
                ],
                true,
                null,
                null,
            ],
            'impact_addressed_sustainable_dev_goals' => [
                "How many UN Sustainable Development Goals (SDGs) does your work address?",
                "select",
                "text",
                [
                    ["label" => "4+", "value" => "4+"],
                    ["label" => "2–3", "value" => "2–3"],
                    ["label" => "1", "value" => "1"],
                    ["label" => "None", "value" => "None"]
                ],
                true,
                null,
                null,
            ],
            'impact_market_underserved' => [
                "What percentage of your market are underserved (low-income, rural, etc.)?",
                "select",
                "text",
                [
                    ["label" => ">80%", "value" => ">80%"],
                    ["label" => "60–80%", "value" => "60–80%"],
                    ["label" => "40–60%", "value" => "40–60%"],
                    ["label" => "<40%", "value" => "<40%"]
                ],
                true,
                null,
                null,
            ],
            'impact_environmental_harm' => [
                "Does your solution impact environmental harm?",
                "select",
                "text",
                [
                    ["label" => "Yes", "value" => true],
                    ["label" => "No", "value" => false]
                ],
                true,
                null,
                null,
            ],
            'impact_how_product_reduces_environ_harm' => [
                "If yes, how does your product/service reduce environmental harm?",
                "select",
                "text",
                [
                    ["label" => "Net-positive impact (e.g., carbon-negative)", "value" => "Net-positive impact (e.g., carbon-negative)"],
                    ["label" => "Neutral (zero waste)", "value" => "Neutral (zero waste)"],
                    ["label" => "Reduces harm by 50%+", "value" => "Reduces harm by 50%+"],
                    ["label" => "No focus", "value" => "No focus"]
                ],
                true,
                null,
                null,
            ],
            'impact_track_social_outcomes' => [
                "How do you track social outcomes?",
                "select",
                "text",
                [
                    ["label" => "Third-party audited metrics", "value" => "Third-party audited metrics"],
                    ["label" => "Internal KPIs (e.g., lives impacted)", "value" => "Internal KPIs (e.g., lives impacted)"],
                    ["label" => "Surveys/anecdotes", "value" => "Surveys/anecdotes"],
                    ["label" => "No tracking", "value" => "No tracking"]
                ],
                true,
                null,
                null,
            ],
            'impact_reduce_systemic_inequities' => [
                "Does your solution actively reduce systemic inequities (gender, income, tribe)?",
                "select",
                "text",
                [
                    ["label" => "Already scaled to 5+ regions", "value" => "Already scaled to 5+ regions"],
                    ["label" => "Addresses 1 inequity", "value" => "Addresses 1 inequity"],
                    ["label" => "Indirect benefits", "value" => "Indirect benefits"],
                    ["label" => "No focus", "value" => "No focus"]
                ],
                true,
                null,
                null,
            ],
            'impact_beneficiary_involvement' => [
                "How involved are beneficiaries in designing your solution?",
                "select",
                "text",
                [
                    ["label" => "Co-created with community input", "value" => "Co-created with community input"],
                    ["label" => "Piloted with user feedback", "value" => "Piloted with user feedback"],
                    ["label" => "Minimal involvement", "value" => "Minimal involvement"],
                    ["label" => "No involvement", "value" => "No involvement"]
                ],
                true,
                null,
                null,
            ],
            'impact_replicate_in_new_regions' => [
                "Can your model be replicated in new regions?",
                "select",
                "text",
                [
                    ["label" => "Already scaled to 5+ regions", "value" => "Already scaled to 5+ regions"],
                    ["label" => "Designed for easy replication", "value" => "Designed for easy replication"],
                    ["label" => "Needs adaptation", "value" => "Needs adaptation"],
                    ["label" => "Not replicable", "value" => "Not replicable"]
                ],
                true,
                null,
                null,
            ],
            'impact_endure_beyond_five_years' => [
                "How will impact endure beyond 5 years?",
                "select",
                "text",
                [
                    ["label" => "Locals trained to lead independently", "value" => "Locals trained to lead independently"],
                    ["label" => "Partnerships with governments/NGOs", "value" => "Partnerships with governments/NGOs"],
                    ["label" => "Reliant on external funding", "value" => "Reliant on external funding"],
                    ["label" => "No plan", "value" => "No plan"]
                ],
                true,
                null,
                null,
            ],
            'impact_ensure_ethical_practices' => [
                "How do you ensure ethical practices?",
                "select",
                "text",
                [
                    ["label" => "Third-party certifications/audits", "value" => "Third-party certifications/audits"],
                    ["label" => "Internal policies enforced", "value" => "Internal policies enforced"],
                    ["label" => "Ad-hoc compliance", "value" => "Ad-hoc compliance"],
                    ["label" => "No policies", "value" => "No policies"]
                ],
                true,
                null,
                null,
            ],
            'impact_self_reliant_users' => [
                "Does your product/service empower users to be self-reliant?",
                "select",
                "text",
                [
                    ["label" => "Fully self-sustaining", "value" => "Fully self-sustaining"],
                    ["label" => "Reduces dependency by 50%+", "value" => "Reduces dependency by 50%+"]
                ],
                true,
                null,
                null,
            ],
            'impact_solution_during_crises' => [
                "How does your solution perform during crises (e.g., offline access, during global supply chain disruptions)?",
                "select",
                "text",
                [
                    ["label" => "Built for crisis response", "value" => "Built for crisis response"],
                    ["label" => "Adaptable to disruptions", "value" => "Adaptable to disruptions"],
                    ["label" => "No specific features", "value" => "No specific features"],
                    ["label" => "Vulnerable", "value" => "Vulnerable"]
                ],
                true,
                null,
                null,
            ],
            'impact_social_impact' => [
                "How do you measure the social impact of your product/service?",
                "select",
                "text",
                [
                    ["label" => "Conducting surveys and impact assessments", "value" => "Conducting surveys and impact assessments"],
                    ["label" => "Data analytics and usage statistics", "value" => "Data analytics and usage statistics"],
                    ["label" => "Direct feedback from beneficiaries", "value" => "Direct feedback from beneficiaries"],
                    ["label" => "We do not currently measure social impact", "value" => "We do not currently measure social impact"]
                ],
                true,
                null,
                null,
            ],
            'impact_share_growth' => [
                "How openly do you share impact data?",
                "select",
                "text",
                [
                    ["label" => "Public reports with audits", "value" => "Public reports with audits"],
                    ["label" => "Regular stakeholder updates", "value" => "Regular stakeholder updates"],
                    ["label" => "Limited internal reviews", "value" => "Limited internal reviews"],
                    ["label" => "No transparency", "value" => "No transparency"]
                ],
                true,
                null,
                null,
            ],
            'impact_local_context_tailored' => [
                "Is your solution tailored to local contexts?",
                "select",
                "text",
                [
                    ["label" => "Co-designed with communities", "value" => "Co-designed with communities"],
                    ["label" => "Adapted from global models", "value" => "Adapted from global models"],
                    ["label" => "Minimal localization", "value" => "Minimal localization"],
                    ["label" => "One-size-fits-all", "value" => "One-size-fits-all"],
                ],
                false,
                null,
                null,
            ],
            'impact_jobs_created' => [
                "How many jobs have you created in your operating regions?",
                "select",
                "text",
                [
                    ["label" => "200+", "value" => "200+"],
                    ["label" => "50–200", "value" => "50–200"],
                    ["label" => "10–50", "value" => "10–50"],
                    ["label" => "<10", "value" => "<10"],
                ],
                false,
                null,
                null,
            ],
            'impact_gender_gaps' => [
                "How does your product/service address gender gaps?",
                "select",
                "text",
                [
                    ["label" => "50%+ female leadership/beneficiaries", "value" => "50%+ female leadership/beneficiaries"],
                    ["label" => "Targeted programs for women/girls", "value" => "Targeted programs for women/girls"],
                    ["label" => "Neutral (no focus)", "value" => "Neutral (no focus)"],
                    ["label" => "Worsens gaps", "value" => "Worsens gaps"],
                ],
                false,
                null,
                null,
            ],
            'impact_underserved_group_access' => [
                "How does your solution improve access for underserved groups?",
                "select",
                "text",
                [
                    ["label" => "Free/low-cost for 10k+ users", "value" => "Free/low-cost for 10k+ users"],
                    ["label" => "Affordable with proven outcomes", "value" => "Affordable with proven outcomes"],
                    ["label" => "Limited accessibility", "value" => "Limited accessibility"],
                    ["label" => "No focus", "value" => "No focus"],
                ],
                false,
                null,
                null,
            ],
            'impact_optimize_resource' => [
                "How does your solution optimize resource use?",
                "select",
                "text",
                [
                    ["label" => "Reduces waste by 70%+", "value" => "Reduces waste by 70%+"],
                    ["label" => "Reduces waste by 30–70%", "value" => "Reduces waste by 30–70%"],
                    ["label" => "Minimal efficiency gains", "value" => "Minimal efficiency gains"],
                    ["label" => "No focus", "value" => "No focus"],
                ],
                false,
                null,
                null,
            ],
            'impact_policy_reforms' => [
                "Do you advocate for policy/industry reforms?",
                "select",
                "text",
                [
                    ["label" => "Lead campaigns/coalitions", "value" => "Lead campaigns/coalitions"],
                    ["label" => "Partner with advocacy groups", "value" => "Partner with advocacy groups"],
                    ["label" => "Informal knowledge-sharing", "value" => "Informal knowledge-sharing"],
                    ["label" => "No advocacy", "value" => "No advocacy"],
                ],
                false,
                null,
                null,
            ],
            'impact_protect_user_data' => [
                "How do you protect user/customer data?",
                "select",
                "text",
                [
                    ["label" => "NDPR/ISO-certified compliance", "value" => "NDPR/ISO-certified compliance"],
                    ["label" => "Internal encryption/policies", "value" => "Internal encryption/policies"],
                    ["label" => "Basic safeguards", "value" => "Basic safeguards"],
                    ["label" => "No protection", "value" => "No protection"]
                ],
                true,
                null,
                null,
            ],
            'impact_build_beneficiary_trust' => [
                "How do you build trust with beneficiaries?",
                "select",
                "text",
                [
                    ["label" => "Transparent reporting and accountability", "value" => "Transparent reporting and accountability"],
                    ["label" => "Regular community engagement", "value" => "Regular community engagement"],
                    ["label" => "Limited communication", "value" => "Limited communication"],
                    ["label" => "No effort", "value" => "No effort"]
                ],
                true,
                null,
                null,
            ],
            'impact_people_impacted' => [
                "How many people has your product/service impacted?",
                "select",
                "text",
                [
                    ["label" => ">80%", "value" => ">80%"],
                    ["label" => "60–80%", "value" => "60–80%"],
                    ["label" => "40–60%", "value" => "40–60%"],
                    ["label" => "<40%", "value" => "<40%"]
                ],
                true,
                null,
                null,
            ],
            'impact_service_impact_data' => [
                "Can you share data or statistics that demonstrate the impact your product/service has had so far?",
                "textarea",
                "text",
                [],
                false,
                null,
                null,
            ],
            'impact_measurable_difference' => [
                "Can you share specific examples where your product/service has made a measurable difference in the community or environment?",
                "textarea",
                "text",
                [],
                false,
                null,
                null,
            ],
            'impact_measurable_difference_details' => [
                "If yes, please provide details (Top 3, 10, 25, 100)",
                "textarea",
                "text",
                [],
                false,
                null,
                null,
            ],
            'impact_npi_data_protection_terms' => [
                "I agree to the terms and conditions outlined in the NSIA Data Protection and Privacy Policy.",
                "input",
                "checkbox",
                [],
                true,
                null,
                "You must agree to the terms and conditions outlined in the NSIA Data Protection and Privacy Policy.",
            ],
            'impact_nsia_data_protection_terms' => [
                "I agree to the terms and conditions of the NPI 3.0 Competition Submission Agreement.",
                "input",
                "checkbox",
                [],
                true,
                null,
                "You must agree to the terms and conditions of the NPI 3.0 Competition Submission Agreement.",
            ],
        ]);

        DB::transaction(function () use ($fields, $form) {
            $fields->each(function ($data, $name) use ($form) {

                [$label, $element, $type, $options, $required, $required_if, $custom_error] = $data;

                $field = $form->fields()->where('name', $name)->firstOrNew();

                $field->name = $name;
                $field->field_id = $name;
                $field->label = $label;
                $field->value = null;
                $field->hint = null;
                $field->custom_error = $custom_error;
                $field->compare = null;
                $field->options = $options;
                $field->required = $required;
                $field->required_if = $required_if;
                $field->restricted = false;
                $field->key = false;
                $field->min = null;
                $field->max = null;
                $field->element = $element;
                $field->type = $type;
                $field->expected_value = in_array($name, [
                    'impact_npi_data_protection_terms',
                    'impact_nsia_data_protection_terms'
                ]) ? true : null;
                $field->save();
            });
        });
    }
}
