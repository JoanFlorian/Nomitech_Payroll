<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

abstract class BaseDuskTestCase extends DuskTestCase
{
    // Asegura que las pruebas corran con una base de datos limpia de forma rápida
    use DatabaseTruncation;

    /**
     * Define the constraints on the test database.
     * Esta validación es importante para evitar borrar la DB de producción por error.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Safety check to ensure we are using the testing database
        if (env('DB_DATABASE') !== 'nomitech_dusk_testing') {
            $this->markTestSkipped('Skipping test to prevent accidental data loss. Please ensure DB_DATABASE is set to a testing database in .env.dusk.local');
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            // '---disable-gpu', // Eliminado para mejor visibilidad en algunos sistemas
            '--start-maximized',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--ignore-certificate-errors',
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ]);

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    /**
     * Anular la captura automática de fallos de Dusk para evitar archivos en el proyecto.
     */
    protected function captureFailuresFor($browsers)
    {
        // No hacer nada para evitar screenshots locales en tests/Browser/screenshots
    }

    protected static $runTimestamp = null;

    /**
     * Captura un screenshot en el Escritorio del usuario.
     */
    protected function takeValidationScreenshot($browser, $className, $testCaseName)
    {
        if (self::$runTimestamp === null) {
            self::$runTimestamp = date('Y-m-d_H-i-s');
        }

        $desktopPath = 'C:\\Users\\Usuario\\Desktop\\Dusk_Screenshots';
        $folderName = str_replace(['Tests\\Browser\\', 'Tests/Browser/'], '', $className);
        $fullPath = $desktopPath . DIRECTORY_SEPARATOR . $folderName . '_' . self::$runTimestamp;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $testCaseName) . '.png';
        $savePath = $fullPath . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($savePath, $browser->driver->takeScreenshot());
    }
}
