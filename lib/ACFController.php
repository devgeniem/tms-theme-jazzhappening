<?php

namespace TMS\Theme\Jazzhappening;

use TMS\Theme\Base;
/**
 * Class ACFController
 *
 * @package TMS\Theme\Jazzhappening
 */
class ACFController extends Base\ACFController implements Base\Interfaces\Controller {

    /**
     * Get ACF base dir
     *
     * @return string
     */
    protected function get_base_dir(): string {
        return __DIR__ . '/ACF';
    }
}
