<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        // Keep inline PHPStan/Psalm variable annotations like:
        // /** @var list<ExchangeRequest> $result */
        'phpdoc_to_comment' => false,
    ])
    ->setFinder($finder)
;
