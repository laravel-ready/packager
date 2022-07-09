<?php

declare(strict_types=1);

namespace LaravelReady\Packager\Supports;

use PhpParser\Error;
use PhpParser\Parser;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;

class PhpManipulate
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    }

    public function appendNamespace(){

    }
}