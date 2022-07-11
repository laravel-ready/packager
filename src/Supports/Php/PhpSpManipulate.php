<?php

declare(strict_types=1);

namespace LaravelReady\Packager\Supports\Php;

use Illuminate\Filesystem\Filesystem;

use PhpParser\Error;
use PhpParser\Parser;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

class PhpSpManipulate extends PhpManipulate
{
    public function appendCommand(): void
    {

    }
}