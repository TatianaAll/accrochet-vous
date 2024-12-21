<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageImporter
{
    //I need this 2 services in my import methods
    private ParameterBagInterface $parameterBag;
    private UniqueFileNameGenerator $uniqueFileNameGenerator;

    // the class constructor to inject the 2 services in all the methods of this class
    public function __construct(
        ParameterBagInterface $parameterBag,
        UniqueFileNameGenerator $uniqueFileNameGenerator
    ) {
        // name the instances of my service classes
        $this->parameterBag = $parameterBag;
        $this->uniqueFileNameGenerator = $uniqueFileNameGenerator;
    }

    //method for image import --> need an uploaded file as argument (the image in the form)
    public function importImage(UploadedFile $imageFile){

        $imageName = $imageFile->getClientOriginalName();
        $imageExtension = $imageFile->getClientOriginalExtension();

        //rename with a service class
        $newImageName = $this->uniqueFileNameGenerator->generateUniqueFileName($imageName, $imageExtension);

        // get, with ParameterBag class, the path to the project's root directory
        $rootDir = $this->parameterBag->get('kernel.project_dir');
        //move the image to the target directory
        $uploadsDir = $rootDir . '/public/assets/uploads';

        // Move the image & rename with the new name (second argument)
        $imageFile->move($uploadsDir, $newImageName);

        return $newImageName;
    }
}