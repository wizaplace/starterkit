<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests;

use VCR\Request;
use VCR\VCR;

class VcrHelper
{
    public static function configureVcr(string $fixturesPath)
    {
        ini_set('opcache.enable', '0');
        VCR::configure()->setCassettePath($fixturesPath);
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
    }
}
