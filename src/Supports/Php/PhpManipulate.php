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

class PhpManipulate
{
    private Parser $parser;
    private Filesystem $file;
    private string $basePath;
    private string $filePath;
    private $ast;

    public function __construct(string $filePath)
    {
        $this->file = new Filesystem();
        $this->basePath = realpath('./');
        $this->filePath = $filePath;

        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        return $this;
    }

    /**
     * Parse th given PHP file
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public function parse(): self
    {
        $fullfilePath = "{$this->basePath}/{$this->filePath}";

        if ($this->file->exists($fullfilePath)) {
            $fileContents = $this->file->get($fullfilePath);

            try {
                $ast = $this->parser->parse($fileContents);

                if ($ast && isset($ast[0]) && $ast[0] instanceof Namespace_) {
                    $this->ast = $ast[0];
                }
            } catch (Error $error) {
                throw new \Exception("Parse error: {$error->getMessage()}\n");
            }
        }

        return $this;
    }

    /**
     * Append namespace to class
     *
     * @param string $namespace
     * @return $this
     */
    public function appendUse(string $namespace): self
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor(visitor: new class ($namespace) extends NodeVisitorAbstract {
            private string $namespace;

            public function __construct(string $namespace)
            {
                $this->namespace = $namespace;
            }

            public function enterNode(Node $node)
            {
                if (!$node instanceof Namespace_) return;

                $uses = array_filter($node->stmts, function ($stmt) {
                    return $stmt instanceof Use_;
                });

                $usesLength = count($uses);

                array_splice($node->stmts, $usesLength, 0, [
                    new Use_([
                        new UseUse(new Name($this->namespace))
                    ]),
                ]);
            }
        });

        $this->ast = $traverser->traverse(nodes: [$this->ast])[0];

        return $this;
    }

    /**
     * Get the parsed AST as printable string.
     *
     * @return string
     */
    public function output(): string
    {
        $prettyPrinter = new PrettyPrinter\Standard;

        return $prettyPrinter->prettyPrintFile([$this->ast]);
    }

    /**
     * Save the parsed AST to the given file.
     *
     * @return bool|int
     */
    public function save(): bool|int
    {
        $phpFileContent = $this->output();
        $fullFilePath = "{$this->basePath}/{$this->filePath}";

        return $this->file->put($fullFilePath, $phpFileContent);
    }

    /**
     * Save as the parsed AST to the another file.
     *
     * @return bool|int
     */
    public function saveAs(string $filePath): bool|int
    {
        $phpFileContent = $this->output();
        $fullFilePath = "{$this->basePath}/{$filePath}";

        return $this->file->put($fullFilePath, $phpFileContent);
    }
}