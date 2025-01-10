<?php

namespace App\Services;

use PHPUnit\Framework\TestCase;

class TestUniqueFileNameGenerator extends TestCase
{
    public function testGenerateUniqueFilename(){
      //Instance the class to test
      $uniqueFileNameGenerator = new UniqueFileNameGenerator();

      // create a variable with the new instance to test and call it with the method
      $uniqueFilename = $uniqueFileNameGenerator->generateUniqueFileName('hello', 'jpeg');

      //test conditions given to the unique file name generate previously
      // php bin/phpunit src/Services/TestUniqueFilenameGenerator.php
      $this->assertStringContainsString('jpeg', $uniqueFilename);
      $this->assertStringContainsString(time(), $uniqueFilename);
      $this->assertStringNotContainsString('hello', $uniqueFilename);
    }
}