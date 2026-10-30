<?php
declare(strict_types=1);

namespace Taketool\PowermailMailapproval\EventListener;

use In2code\Powermail\Events\FormControllerCreateActionAfterMailDbSavedEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

/**
 * ApprovalHandler
 *
 * PSR-14 listener that sets newly submitted powermail records
 * to unapproved (approved=0) by default
 */
#[AsEventListener(identifier: 'powermail-mailapproval/set-default-approval-status')]
final class ApprovalHandler
{
    public function __invoke(FormControllerCreateActionAfterMailDbSavedEvent $event): void
    {
        $mail = $event->getMail();
        if (method_exists($mail, 'setApproved')) {
            $mail->setApproved(false);
        }
    }
}
