<?php

namespace Database\Seeders;

use App\Models\Portal\Portal;
use Illuminate\Database\Seeder;

class PortalFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Portal::where('allow_registration', true)->get()->each(function ($portal) {
            $portal->forms()->delete();
            $form = $portal->forms()->create([
                'portal_id' => $portal->id,
                'name' => $portal->name.' Registration',
                'slug' => str($portal->name.' Registration')->slug(),
                'title' => 'Register now for '.$portal->name,
                'failure_message' => 'Hello :fullname, Unfortunattely we could not submit your form.',
                'success_message' => "Hello :fullname, This is to confirm that your registration for {$portal->name} was successfull.",
                'logo' => $portal->images['logo'],
                'banner' => $portal->images['banner'],
                'socials' => collect($portal->socials)->mapWithKeys(function ($link) {
                    return [$link['type'] => $link['link']];
                })->toArray(),
            ]);

            $portal->reg_form_id = $form->id;
            $portal->save();
        });
    }
}
