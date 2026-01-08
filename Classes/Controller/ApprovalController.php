<?php
declare(strict_types=1);

namespace Taketool\PowermailMailapproval\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Fluid\View\StandaloneView;

class ApprovalController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;

    public function __construct(ModuleTemplateFactory $moduleTemplateFactory)
    {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $action = $request->getQueryParams()['action'] ?? 'list';

        switch ($action) {
            case 'approve':
                return $this->approveAction($request);
            case 'reject':
                return $this->rejectAction($request);
            default:
                return $this->listAction($request);
        }
    }

    protected function listAction(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $moduleTemplate->setTitle('Powermail Approval');

        // Get unapproved mails sorted by crdate DESC
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_powermail_domain_model_mail');

        $mails = $queryBuilder
            ->select('*')
            ->from('tx_powermail_domain_model_mail')
            ->where(
                $queryBuilder->expr()->eq('approved', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
            )
            ->orderBy('crdate', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        // Get form titles and answers for each mail
        foreach ($mails as &$mail) {
            $formQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tx_powermail_domain_model_form');

            $form = $formQueryBuilder
                ->select('title')
                ->from('tx_powermail_domain_model_form')
                ->where(
                    $formQueryBuilder->expr()->eq('uid', $formQueryBuilder->createNamedParameter($mail['form'], \PDO::PARAM_INT))
                )
                ->executeQuery()
                ->fetchAssociative();

            $mail['form_title'] = $form['title'] ?? 'Unknown Form';

            // Get answers
            $answerQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tx_powermail_domain_model_answer');

            $answers = $answerQueryBuilder
                ->select('a.value', 'a.field', 'f.title as field_title')
                ->from('tx_powermail_domain_model_answer', 'a')
                ->leftJoin('a', 'tx_powermail_domain_model_field', 'f', 'a.field = f.uid')
                ->where(
                    $answerQueryBuilder->expr()->eq('a.mail', $answerQueryBuilder->createNamedParameter($mail['uid'], \PDO::PARAM_INT))
                )
                ->executeQuery()
                ->fetchAllAssociative();

            $mail['answers'] = $answers;
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName('EXT:powermail_mailapproval/Resources/Private/Templates/Approval/List.html')
        );
        $view->assign('mails', $mails);

        $moduleTemplate->setContent($view->render());
        return $moduleTemplate->renderResponse();
    }

    protected function approveAction(ServerRequestInterface $request): ResponseInterface
    {
        $uid = (int)($request->getQueryParams()['uid'] ?? 0);

        if ($uid > 0) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tx_powermail_domain_model_mail');

            $queryBuilder
                ->update('tx_powermail_domain_model_mail')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
                )
                ->set('approved', 1)
                ->executeStatement();

            $this->addFlashMessage('Mail entry approved successfully.', 'Success', ContextualFeedbackSeverity::OK);
        }

        return $this->listAction($request);
    }

    protected function rejectAction(ServerRequestInterface $request): ResponseInterface
    {
        $uid = (int)($request->getQueryParams()['uid'] ?? 0);

        if ($uid > 0) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tx_powermail_domain_model_mail');

            $queryBuilder
                ->update('tx_powermail_domain_model_mail')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
                )
                ->set('deleted', 1)
                ->executeStatement();

            $this->addFlashMessage('Mail entry rejected and deleted.', 'Success', ContextualFeedbackSeverity::OK);
        }

        return $this->listAction($request);
    }

    protected function addFlashMessage(string $message, string $title, ContextualFeedbackSeverity $severity): void
    {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity,
            true
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->addMessage($flashMessage);
    }
}
