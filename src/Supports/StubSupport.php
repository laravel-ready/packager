<?php

declare(strict_types=1);

namespace LaravelReady\Packager\Supports;

use LaravelReady\Packager\Exceptions\StubException;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class StubSupport
{
    private Filesystem $file;

    public function __construct()
    {
        $this->file = new Filesystem();
    }

    /**
     * Copy file from stub to target with replaced values
     *
     * @param string $stubPath stub template path
     * @param string $targetPath target output path
     * @param array|null $replacements replacements list
     * @param string $outputBasePath output base path
     * @return bool|int
     * @throws FileNotFoundException
     * @throws StubException
     */
    public function applyStub(string $stubPath, string $targetPath, array|null $replacements = null): bool|int
    {
        $outputPath = "{$targetPath}";

        if (!empty($stubPath)) {
            if (!$this->file->exists(path: $stubPath)) {
                throw new FileNotFoundException(message: "Stub file is not exists: {$stubPath}");
            }
        }

        if (!$this->file->exists($outputPath)) {
            $subContent = $this->file->get($stubPath);

            if (empty($subContent)) {
                throw new StubException(message: "Stub file content is empty");
            }

            return $this->file->put($outputPath, self::replaceStubContent($subContent, $replacements));
        }

        return false;
    }

    /***
     * Replace values with keys in given stub content
     *
     * @param string $content
     * @param array $replacements
     *
     * @return string|null
     */
    public function replaceStubContent(string $content, array|null $replacements = null): string|null
    {
        if ($replacements) {
            foreach ($replacements as $key => $replacement) {
                $content = Str::replace("{{ {$key} }}", $replacement, $content);

                if (Str::startsWith($key, 'SETUP_')) {
                    if ($replacement === false) {
                        preg_match_all('/{{ CON_' . $key . '_START }}/', $content, $preMatches);

                        if (count($preMatches[0]) > 0) {
                            for ($i = 0; $i < count($preMatches[0]); $i++) {
                                $pattern = '/{{ CON_' . $key . '_START }}((.|\n)*?){{ CON_' . $key . '_END }}/';

                                preg_match_all(pattern: $pattern, subject: $content, matches: $matches);

                                if ($matches && count($matches) && count($matches[0]) > 0) {
                                    $content = Str::replace($matches[0], '', $content);
                                }
                            }
                        }
                    } else if ($replacement === true) {
                        $content = Str::replace("{{ CON_{$key}_START }}", '', $content);
                        $content = Str::replace("{{ CON_{$key}_END }}", '', $content);
                    }
                }
            }
        }

        return $content;
    }
}