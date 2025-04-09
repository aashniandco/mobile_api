<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Api;

interface ExportHandlerInterface
{
    /**
     * Get content as a CSV string
     *
     * @param array $ids
     * @return mixed
     */
    public function getContent(array $ids);
}