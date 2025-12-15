<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NsiaCommercialFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $form = Form::where(['slug' => 'nsia-application'])->firstOrFail();

        $fields = collect([
            'commercial_revenue' => [
                'Have you started generating revenue',
                'select',
                'text',
                [
                    ['label' => 'Yes', 'value' => 1],
                    ['label' => 'No', 'value' => 0],
                ],
                true,
                null,
            ],
            'commercial_invesment_details' => [
                'Details of any investments/external funding that you have received',
                'select',
                'text',
                [
                    ['label' => 'Grants', 'value' => 'grants'],
                    ['label' => 'Equity Investment', 'value' => 'equity-investment'],
                    ['label' => 'Donations', 'value' => 'donations'],
                    ['label' => 'Debt financing', 'value' => 'debt-financing'],
                    ['label' => 'Not applicable', 'value' => 'not-applicable'],
                ],
                true,
                null,
            ],
            'commercial_fundrasing' => [
                'Are you currently fundraising',
                'select',
                'text',
                [
                    ['label' => 'Yes', 'value' => 1],
                    ['label' => 'No', 'value' => 0],
                ],
                true,
                null,
            ],
            'commercial_valuation' => [
                'What is your current company valuation',
                'select',
                'text',
                [
                    ['label' => 'Less than ₦100M', 'value' => '100m'],
                    ['label' => '₦100M - ₦500M', 'value' => '100m-500m'],
                    ['label' => '₦500M - ₦1B', 'value' => '500m-1b'],
                    ['label' => 'Over ₦1B', 'value' => 'over-1b'],
                ],
                true,
                null,
            ],
            'commercial_evidence' => [
                'What evidence proves demand for your solution',
                'select',
                'text',
                [
                    ['label' => 'Paid pilots with 500+ users', 'value' => 'pilots-with-500-users'],
                    ['label' => 'LOIs/MOUs from institutional buyers', 'value' => 'lo-is-mo-us'],
                    ['label' => 'Pre-orders/waitlists', 'value' => 'pre-orders'],
                    ['label' => 'No formal validation', 'value' => 'no-validation'],
                ],
                true,
                null,
            ],
            'commercial_revenue_streams' => [
                'How many distinct revenue streams do you have (e.g., subscriptions, licensing, training)',
                'select',
                'text',
                [
                    ['label' => '3+', 'value' => '3+'],
                    ['label' => '2', 'value' => '2'],
                    ['label' => '1', 'value' => '1'],
                    ['label' => 'pre-revenue', 'value' => 'Pre-revenue'],
                ],
                true,
                null,
            ],
            'commercial_scaling_ease' => [
                'How easily can your solution scale geographically',
                'select',
                'text',
                [
                    ['label' => 'Already scaled to 5+ regions', 'value' => 'already-scaled'],
                    ['label' => 'Requires minimal localization', 'value' => 'minimal-localization'],
                    ['label' => 'Needs moderate investment', 'value' => 'moderate-investment'],
                    ['label' => 'Not scalable', 'value' => 'not-scalable'],
                ],
                true,
                null,
            ],
            'commercial_gross_margin' => [
                'What are your gross margins',
                'select',
                'text',
                [
                    ['label' => '50%', 'value' => '50'],
                    ['label' => '30–50%', 'value' => '30–50'],
                    ['label' => '10–30%', 'value' => '10–30'],
                    ['label' => '10%', 'value' => '10'],
                ],
                true,
                null,
            ],
            'commercial_competitive_edge' => [
                'What is your primary competitive edge',
                'select',
                'text',
                [
                    ['label' => 'Proprietary technology/IP', 'value' => 'proprietary-technology-ip'],
                    ['label' => '25%+ cost reduction vs. alternatives', 'value' => '25-cost-reduction'],
                    ['label' => 'Exclusive partnerships', 'value' => 'exclusive-partnerships'],
                    ['label' => 'No clear advantage', 'value' => 'no-clear-advantage'],
                ],
                true,
                null,
            ],
            'commercial_longevity' => [
                'How long can you operate currently without external funding',
                'select',
                'text',
                [
                    ['label' => '18+ months', 'value' => '18'],
                    ['label' => '12–18 months', 'value' => '12-18'],
                    ['label' => '6–12 months', 'value' => '6-12'],
                    ['label' => '6 months', 'value' => '6'],
                ],
                true,
                null,
            ],
            'commercial_partnerships' => [
                'How many active partnerships do you have (e.g., NGOs, governments, corporates)',
                'select',
                'text',
                [
                    ['label' => '10+', 'value' => '10'],
                    ['label' => '5–9', 'value' => '5-9'],
                    ['label' => '1–4', 'value' => '1-4'],
                    ['label' => 'None', 'value' => 'none'],
                ],
                true,
                null,
            ],
            'commercial_regulations' => [
                'How does your solution handle regulations',
                'select',
                'text',
                [
                    ['label' => 'Fully compliant with certifications', 'value' => 'fully-compliant'],
                    ['label' => 'In-process compliance', 'value' => 'in-process-compliance'],
                    ['label' => 'Low regulatory risk', 'value' => 'low-regulatory-risk'],
                    ['label' => 'High regulatory risk', 'value' => 'high-regulatory-risk'],
                ],
                true,
                null,
            ],
            'commercial_market_share' => [
                'What is your estimated market share in your niche',
                'select',
                'text',
                [
                    ['label' => '25', 'value' => '25%'],
                    ['label' => '10-25', 'value' => '10-25%'],
                    ['label' => '1–10', 'value' => '1–10%'],
                    ['label' => '1', 'value' => '1%'],
                ],
                true,
                null,
            ],
            'commercial_for_others' => [
                'Can your solution serve other industries',
                'select',
                'text',
                [
                    ['label' => 'Already used in 2+ sectors', 'value' => 'already-used'],
                    ['label' => 'Easily adaptable', 'value' => 'easily-adaptable'],
                    ['label' => 'Requires significant rework', 'value' => 'requires-rework'],
                    ['label' => 'Industry-specific', 'value' => 'industry-specific'],
                ],
                true,
                null,
            ],
            'commercial_growth_plan' => [
                'What is your long-term growth plan',
                'select',
                'text',
                [
                    ['label' => 'Acquisition by a major player', 'value' => 'acquisition'],
                    ['label' => 'IPO in 5–7 years', 'value' => 'IPO'],
                    ['label' => 'Sustainable profitability', 'value' => 'sustainable'],
                    ['label' => 'No defined plan', 'value' => 'no-plan'],
                ],
                true,
                null,
            ],
            'commercial_update_frequency' => [
                'How often do you update your solution based on feedback',
                'select',
                'text',
                [
                    ['label' => 'Real-time updates', 'value' => 'real-time'],
                    ['label' => 'Quarterly improvements', 'value' => 'quarterly'],
                    ['label' => 'Annual revisions', 'value' => 'annual'],
                    ['label' => 'No structured process', 'value' => 'no-process'],
                ],
                true,
                null,
            ],
            'commercial_chain_reliability' => [
                'How reliable is your supply chain',
                'select',
                'text',
                [
                    ['label' => 'Fully diversified (no single-point failure)', 'value' => 'fully-diversified'],
                    ['label' => 'Mostly reliable with backups', 'value' => 'mostly-reliable'],
                    ['label' => 'Moderate risk of disruption', 'value' => 'moderate-risk'],
                    ['label' => 'Fragile', 'value' => 'fragile'],
                ],
                true,
                null,
            ],
            'commercial_protected_ip' => [
                'Do you own protected IP',
                'select',
                'text',
                [
                    ['label' => 'Multiple patents granted', 'value' => 'multiple-patents'],
                    ['label' => '1 patent or pending application', 'value' => '1-patent-pending'],
                    ['label' => 'Trade secrets only', 'value' => 'trade-secrets-only'],
                    ['label' => 'No IP', 'value' => 'no-ip'],
                ],
                true,
                null,
            ],
            'commercial_projected_revenue' => [
                'What is your projected revenue for the next year',
                'select',
                'text',
                [
                    ['label' => 'Revenue of ₦300M+', 'value' => '300m'],
                    ['label' => '1Revenue between ₦200M & ₦300M', 'value' => '200m-300m'],
                    ['label' => 'Revenue between ₦100M & ₦200M', 'value' => '100m-200m'],
                    ['label' => 'Revenue less than ₦100M', 'value' => '100m'],
                ],
                true,
                null,
            ],
            'commercial_pricing_strategy' => [
                'What is your pricing strategy',
                'select',
                'text',
                [
                    ['label' => 'Competitive pricing', 'value' => 'competitive'],
                    ['label' => 'Premium pricing', 'value' => 'premium'],
                    ['label' => 'Value-based pricing', 'value' => 'value-based'],
                    ['label' => 'Penetration pricing', 'value' => 'penetration'],
                ],
                true,
                null,
            ],
            'commercial_bigest_risks' => [
                'What are the biggest risks and challenges you face',
                'select',
                'text',
                [
                    ['label' => 'Market competition', 'value' => 'competition'],
                    ['label' => 'Regulatory hurdles', 'value' => 'regulatory'],
                    ['label' => 'Funding and economic stability', 'value' => 'economic-stability'],
                    ['label' => 'Customer adoption and retention', 'value' => 'customer-retention'],
                ],
                true,
                null,
            ],
            'commercial_customer_count' => [
                'How many customers/users have you acquired in the past 6 months',
                'select',
                'text',
                [
                    ['label' => 'Less than 100', 'value' => '100'],
                    ['label' => '100 - 500', 'value' => '100-500'],
                    ['label' => '500 - 1,000', 'value' => '500-1000'],
                    ['label' => 'Over 1,000', 'value' => '1000'],
                ],
                true,
                null,
            ],
            'commercial_customer_growth' => [
                'What is your user/customer growth rate over the past year',
                'select',
                'text',
                [
                    ['label' => 'Less than 10%', 'value' => '10'],
                    ['label' => '10% - 25%', 'value' => '10-25'],
                    ['label' => '25% - 50%', 'value' => '25-50'],
                    ['label' => 'Over 50%', 'value' => '50'],
                ],
                true,
                null,
            ],
            'commercial_customer_cost' => [
                'What is your average user/customer acquisition cost (CAC)',
                'select',
                'text',
                [
                    ['label' => 'Less than ₦1,000', 'value' => '1000'],
                    ['label' => '₦1,000 - ₦5,000', 'value' => '1000-5000'],
                    ['label' => '₦5,000 - ₦10,000', 'value' => '5000-10000'],
                    ['label' => 'Over ₦10,000', 'value' => '10000'],
                ],
                true,
                null,
            ],
            'commercial_repeat_percentage' => [
                'What percentage of your revenue comes from repeat customers',
                'select',
                'text',
                [
                    ['label' => 'Less than 10%', 'value' => '10'],
                    ['label' => '10% - 30%', 'value' => '10-30'],
                    ['label' => '30% - 50%', 'value' => '30-50'],
                    ['label' => 'Over 50%', 'value' => '50'],
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
