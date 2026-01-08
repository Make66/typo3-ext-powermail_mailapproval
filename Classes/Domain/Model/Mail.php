<?php
declare(strict_types=1);

namespace Taketool\PowermailMailapproval\Domain\Model;

/**
 * Extended Mail Model with approval functionality
 * 
 * This XClass extends the powermail Mail model with an approved property
 * and corresponding getter/setter methods
 */
class Mail extends \In2code\Powermail\Domain\Model\Mail
{
    /**
     * @var bool
     */
    protected $approved = false;

    /**
     * Get approved status
     * 
     * @return bool
     */
    public function getApproved(): bool
    {
        return $this->approved;
    }

    /**
     * Set approved status
     * 
     * @param bool $approved
     * @return void
     */
    public function setApproved(bool $approved): void
    {
        $this->approved = $approved;
    }

    /**
     * Check if mail is approved
     * 
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->approved;
    }
}
