<?php

namespace App\Services;

class UniqueFileNameGenerator
{
    public function generateUniqueFileName(string $imageName, string $imageExtension)
    {
        $currentTimestamp = time();
        $nameHashed = hash('sha256', $imageName);

        return $imageNewName = $nameHashed . '-' . $currentTimestamp . '.' . $imageExtension;
    }

}