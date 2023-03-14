<?php

namespace LaravelReady\Packager\Supports;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

use LaravelReady\Packager\Exceptions\StubException;

class TemplateSupport
{
    private Filesystem $file;
    private BladeSupport $bladeSupport;

    public function __construct()
    {
        $this->file = new Filesystem();
        $this->bladeSupport = new BladeSupport();
    }

    /**
     * Copy file from stub to target with replaced values
     *
     * @param string $stubPath stub template path
     * @param string $targetPath target output path
     * @param array|null $replacements replacements list
     * @return bool|int
     * @throws FileNotFoundException
     * @throws StubException
     */
    public function replaceTemplate(string $stubPath, string $targetPath, array|null $replacements = null): bool|int
    {
        if (!empty($stubPath)) {
            if (!$this->file->exists(path: $stubPath)) {
                throw new FileNotFoundException(message: "Stub file is not exists: {$stubPath}");
            }
        }

        if (!$this->file->exists($targetPath)) {
            $subContent = $this->file->get($stubPath);

            if (!empty($subContent)) {
                $replaceContent = $this->bladeSupport->renderTemplate($stubPath, $replacements);

                if ($replaceContent) {
                    $outputFileExtension = pathinfo($targetPath, PATHINFO_EXTENSION);

                    if ($outputFileExtension === 'json') {
                        $replaceContent = StrSupport::jsonFix($replaceContent);
                        $replaceContent = json_encode(json_decode($replaceContent), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    }

                    return $this->file->put($targetPath, $replaceContent);
                }
            }
        }

        return false;
    }
}
