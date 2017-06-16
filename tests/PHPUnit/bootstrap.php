<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

use Tests\VcrHelper;

require_once(__DIR__.'/../../vendor/autoload.php');

VcrHelper::configureVcr(__DIR__.'/fixtures/VCR/');
