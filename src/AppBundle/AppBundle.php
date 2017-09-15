<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function getParent(): string
    {
        return 'WizaplaceFrontBundle';
    }
}
