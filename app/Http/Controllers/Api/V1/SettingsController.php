<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    //socile media settings
    public function socialMediaSettings(Request $request)
{
    $validated = $request->validate([
        'facebook' => 'nullable|url|max:255',
        'twitter' => 'nullable|url|max:255',
        'instagram' => 'nullable|url|max:255',
        'linkedin' => 'nullable|url|max:255',
    ]);

    $socialMedia = SocialMedia::firstOrNew();

    $socialMedia->fill($validated)->save();

    return response_success('Social media settings updated successfully', $socialMedia);
}

//get social media settings
public function getSocialMediaSettings()
{
    $socialMedia = SocialMedia::first();
    if(!$socialMedia) {
        return response_error('No social media settings found', [], 404);
    }
    return response_success('Social media settings retrieved successfully', $socialMedia);
}

}
