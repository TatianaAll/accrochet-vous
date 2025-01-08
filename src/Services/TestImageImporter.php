<?php

namespace App\Services;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TestImageImporter extends TestCase {

    public function testImportImage()
    {
        //the test will need mock services because ImageImporter use ParameterBagInterface and UniqueNameGenerator

        // Mock = false ParameterBagInterface
        $parameterBagMock = $this->createMock(ParameterBagInterface::class);
        $parameterBagMock->method('get')
            ->with('kernel.project_dir')
            ->willReturn('/test/'); // Simulate the path

        // Mock UniqueFileNameGenerator
        $uniqueFileNameGeneratorMock = $this->createMock(UniqueFileNameGenerator::class);
        $uniqueFileNameGeneratorMock->method('generateUniqueFileName')
            ->willReturn('image-uniqueFileName.jpg');

        // Creation of a temporary file
        $tempFile = tmpfile();
        fwrite($tempFile, 'image');
        $tempFilePath = stream_get_meta_data($tempFile)['uri'];

        // Create an Uploaded file simulation
        $imageFile = new UploadedFile(
            $tempFilePath, // temp path
            'image.jpg', // original name
            'image/jpeg',
            null,
            true
        );

        // New instance of ImageImporter
        $imageImporter = new ImageImporter($parameterBagMock, $uniqueFileNameGeneratorMock);

        // Call the method imporImage
        $newImageName = $imageImporter->importImage($imageFile);

        // Test the method
        $this->assertEquals('image-uniqueFileName.jpg', $newImageName);
        //test if the name given by the uniqueFileNameGenerator is the same as the newImageName
    }

}