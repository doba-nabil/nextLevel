<?php

namespace App\Services;

use App\Models\AddonGroup;

class AddonGroupService
{
    public function getAll()
    {
        return AddonGroup::all();
    }

    public function getById($id)
    {
        return AddonGroup::findOrFail($id);
    }

    public function create(array $data)
    {
        if (!isset($data['type']) && isset($data['name']['en'])) {
            $data['type'] = \Illuminate\Support\Str::slug($data['name']['en']);
        }
        // Handle checkbox - if not set, default to false
        $data['is_selection_mandatory'] = isset($data['is_selection_mandatory']) ? (bool)$data['is_selection_mandatory'] : false;
        $addonGroup = AddonGroup::create($data);
        return $addonGroup;
    }

    public function update(AddonGroup $addonGroup, array $data)
    {
        if (!isset($data['type']) && isset($data['name']['en'])) {
            $data['type'] = \Illuminate\Support\Str::slug($data['name']['en']);
        }
        // Handle checkbox - if not set, default to false
        $data['is_selection_mandatory'] = isset($data['is_selection_mandatory']) ? (bool)$data['is_selection_mandatory'] : false;
        $addonGroup->update($data);
        return $addonGroup;
    }


    public function delete($id)
    {
        $addonGroup = AddonGroup::findOrFail($id);
        return $addonGroup->delete();
    }
}
