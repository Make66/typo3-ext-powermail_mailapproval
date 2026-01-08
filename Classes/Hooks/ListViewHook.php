<?php
declare(strict_types=1);

namespace Taketool\PowermailMailapproval\Hooks;

class ListViewHook
{
    /**
     * Add approved filter to powermail list view
     */
    public function listViewExtended(array &$params): void
    {
        if (!isset($params['filter']['approved'])) {
            $params['filter']['approved'] = 1;
        }
    }
}
