<?php

use LaravelReady\Packager\Supports\TemplateSupport;

test('stub content replaced', function () {
    $stubMaker = new TemplateSupport();

    $result = $stubMaker->replaceStubContent(content: 'TEMPLATE {{ REPLACE_ME }}', replacements: [
        'REPLACE_ME' => 'is replaced'
    ]);

    expect($result)->toBeString()->toBe('TEMPLATE is replaced');
});