<?php
declare(strict_types=1);

namespace Taketool\PowermailMailapproval\EventListener;

use In2code\Powermail\Domain\Model\Mail;

/**
 * ApprovalHandler
 * 
 * SignalSlot handler that sets newly submitted powermail records 
 * to unapproved (approved=0) by default
 */
class ApprovalHandler
{
    /**
     * Set default approval status to false for new mail entries
     * 
     * This method is called after a mail is persisted via the
     * createActionAfterPersist signal in FormController
     * 
     * @param Mail $mail The mail object
     * @param string $hash The hash
     * @param mixed $formController The form controller instance
     * @return void
     */
    public function setDefaultApprovalStatus(Mail $mail, string $hash, $formController): void
    {
        // Set approved to false (0) by default for new submissions
        if (method_exists($mail, 'setApproved')) {
            $mail->setApproved(false);
        }
    }
}
