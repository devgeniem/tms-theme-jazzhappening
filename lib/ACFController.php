<?php

namespace TMS\Theme\jazzhappening;

use TMS\Theme\Base;
/**
 * Class ACFController
 *
 * @package TMS\Theme\jazzhappening
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
