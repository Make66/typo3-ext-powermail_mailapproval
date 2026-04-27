<?php

declare(strict_types=1);
namespace Taketool\PowermailMailapproval\Domain\Model;

class Mail extends \In2code\Powermail\Domain\Model\Mail
{
    protected bool $approved = false;

    public function getApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): void
    {
        $this->approved = $approved;
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }
}
