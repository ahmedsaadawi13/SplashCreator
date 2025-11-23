<?php
// FILE: /app/models/BrandKit.php

class BrandKit extends Model {
    protected $table = 'brand_kits';

    public function createBrandKit($data) {
        // Encode JSON fields
        if (isset($data['colors']) && is_array($data['colors'])) {
            $data['colors_json'] = json_encode($data['colors']);
            unset($data['colors']);
        }

        if (isset($data['fonts']) && is_array($data['fonts'])) {
            $data['fonts_json'] = json_encode($data['fonts']);
            unset($data['fonts']);
        }

        return $this->create($data);
    }

    public function updateBrandKit($id, $data) {
        // Encode JSON fields
        if (isset($data['colors']) && is_array($data['colors'])) {
            $data['colors_json'] = json_encode($data['colors']);
            unset($data['colors']);
        }

        if (isset($data['fonts']) && is_array($data['fonts'])) {
            $data['fonts_json'] = json_encode($data['fonts']);
            unset($data['fonts']);
        }

        return $this->update($id, $data);
    }

    public function getBrandKitWithDecoded($id) {
        $brandKit = $this->findById($id);

        if ($brandKit) {
            $brandKit['colors'] = json_decode($brandKit['colors_json'], true) ?? [];
            $brandKit['fonts'] = json_decode($brandKit['fonts_json'], true) ?? [];
        }

        return $brandKit;
    }

    public function getAllWithDecoded() {
        $brandKits = $this->findAll();

        foreach ($brandKits as &$brandKit) {
            $brandKit['colors'] = json_decode($brandKit['colors_json'], true) ?? [];
            $brandKit['fonts'] = json_decode($brandKit['fonts_json'], true) ?? [];
        }

        return $brandKits;
    }

    public function getDefaultBrandKit() {
        $brandKits = $this->findAll([], 'created_at ASC', 1);
        return !empty($brandKits) ? $this->getBrandKitWithDecoded($brandKits[0]['id']) : null;
    }
}
