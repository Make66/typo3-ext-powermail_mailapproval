<?php
declare(strict_types=1);

namespace Taketool\PowermailMailapproval\Hooks;

/**
 * ListViewHook
 * 
 * Ensures only approved entries are shown in powermail Pi2 list view
 */
class ListViewHook
{
    /**
     * Add approved filter to powermail list view
     * 
     * @param array $params
     * @return void
     */
    public function listViewExtended(array &$params): void
    {
        if (!isset($params['filter']['approved'])) {
            $params['filter']['approved'] = 1;
        }
    }
}
