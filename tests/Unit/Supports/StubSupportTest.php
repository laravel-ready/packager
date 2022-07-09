<?php

use LaravelReady\Packager\Supports\StubSupport;

test('stub content replaced', function () {
    $stubMaker = new StubSupport();

    $result = $stubMaker->replaceStubContent(content: 'TEMPLATE {{ REPLACE_ME }}', replacements: [
        'REPLACE_ME' => 'is replaced'
    ]);

    expect($result)->toBeString()->toBe('TEMPLATE is replaced');
});