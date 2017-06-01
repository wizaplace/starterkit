<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

use VCR\Request;
use VCR\VCR;


require_once(__DIR__.'/../vendor/autoload.php');

// Configure VCR
VCR::configure()->setCassettePath(__DIR__.'/fixtures/VCR/');
VCR::configure()->setMode(VCR::MODE_ONCE);
VCR::configure()->enableLibraryHooks(['stream_wrapper', 'curl'])
    ->addRequestMatcher('headers_custom_matcher', function (Request $first, Request $second) {
        $headersBags = [$first->getHeaders(), $second->getHeaders()];

        foreach ($headersBags as &$headers) {
            // Remove flaky headers that we don't care about
            unset($headers['User-Agent']);
        }

        return $headersBags[0] == $headersBags[1];
    })
    ->enableRequestMatchers(array('method', 'url', 'query_string', 'body', 'post_fields', 'headers_custom_matcher'));
