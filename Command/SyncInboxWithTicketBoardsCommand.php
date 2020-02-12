<?php

namespace AppBundle\Command\Ticketing;

use Doctrine\ORM\EntityManager;
use garethp\ews\API;
use garethp\ews\API\Enumeration\BodyTypeResponseType;
use garethp\ews\API\Message\SyncFolderItemsResponseMessageType;
use garethp\ews\API\Type\DistinguishedFolderIdNameType;
use garethp\ews\API\Type\FolderIdType;
use garethp\ews\API\Type\MessageType;
use garethp\ews\API\Type\SyncFolderItemsCreateOrUpdateType;
use Monolog\Logger;
use NTI\TicketBundle\Entity\Board\Board;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\ExchangeConnectionFailedException;
use NTI\TicketBundle\Exception\ExchangeInactiveConfigurationException;
use NTI\TicketBundle\Exception\ExchangeServerInvalidException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Exception\TicketProcessStoppedException;
use NTI\TicketBundle\Model\Email;
use NTI\TicketBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SyncInboxCommand
 * @package NTI\TicketBundle\Command
 *
 * DISCLAIMER: This entire logic will be moved to another bundle.
 *
 * ONLY Microsoft Exchange client is supported.
 */
class SyncInboxWithTicketBoardsCommand extends ContainerAwareCommand
{

    CONST ERROR_SERVER_INVALID = "NTI Ticket: The server name specified can not be reached.";
    CONST ERROR_INVALID_CREDENTIALS = "NTI Ticket: Invalid credentials.";
    CONST ERROR_UNKNOWN = "NTI Ticket: Unknown error.";


    CONST URI_HTTPS = "https://";
    CONST URI_BODY = "/EWS/Exchange.asmx";

    /** @var  ContainerInterface $container */
    private $container;

    /** @var  Logger $logger */
    private $logger;

    /** @var API $api */
    private $api;

    /** @var EntityManager $em */
    private $em;

    private $minDate;

    private $MIN_DATE;

    protected function configure()
    {
        $this
            ->setName('nti:ticket:synchronize-boards-inbox')
            ->setDescription('Sync all boards with inbox exchange');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        # -- assigning global variable scope
        $this->container = $this->getContainer();
        $this->logger = $this->container->get('logger');

        $this->MIN_DATE = $this->getContainer()->getParameter('app.date.sync.inbox');
        $this->minDate = (new \DateTime('00:00:00'))->modify('-'. $this->MIN_DATE . ' day');

        # entity manager service.
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        # getting active boards with exchange credentials set from the db
        /**@var Board $board */
        $boards = $this->em->getRepository(Board::class)->findAll();

        foreach ($boards as $board) {

            if ($board->getEmailConnectorServer() != null && $board->getEmailConnectorAccount() != null && $board->getEmailConnectorPassword()) {
                /**
                 * parameters validation and connection test.
                 */
                try {
                    $this->container->get('nti_ticket.connector.exchange.service')->testConnection($board);
                } catch (\Exception $exception) {
                    if ($exception instanceof ExchangeServerInvalidException) {
                        $this->logger->alert("NTI Ticket: {$exception->getMessage()}" . $board->getEmailConnectorAccount());
                    } elseif ($exception instanceof ExchangeConnectionFailedException) {
                        $this->logger->alert("NTI Ticket: {$exception->getMessage()}" . $board->getEmailConnectorAccount());
                    } elseif ($exception instanceof ExchangeInactiveConfigurationException) {
                        $this->logger->alert("NTI Ticket: {$exception->getMessage()}" . $board->getEmailConnectorAccount());
                    } else {
                        $this->logger->alert(self::ERROR_UNKNOWN, array('message' => $exception->getMessage()));
                    }
                    continue; // Remove this die to allow that command continues with others board
                }

                $this->api = $this->container->get('nti_ticket.connector.exchange.service')->getConnection();
                $this->logger->alert("NTI Ticket: {$board->getEmailConnectorAccount()}");

                /**
                 * Inbox directory
                 */
                try {
                    // Get all email in inbox folder
                    $inboxId = $this->api->getFolderByDistinguishedId(DistinguishedFolderIdNameType::INBOX)->getFolderId();
                } catch (\Exception $exception) {
                    // -- general error handler
                    $this->logger->alert(self::ERROR_UNKNOWN, array('message' => $exception->getMessage()));
                    continue;
                }

                /**
                 * Processed inbox directory
                 * The processed emails will be moved to this directory.
                 */
                $processedDir = $this->api->getFolderByDisplayName('processed', $inboxId);
                if (!$processedDir) {
                    $this->logger->error('NTI Ticket: No inbox processed directory found. ' . $board->getEmailConnectorAccount());
                    continue;
                }


                /**
                 * Getting the list of emails in the inbox directory. pagination
                 */
                $maxEntries = 500;
                $syncString = null;
                $processedId = $processedDir->getFolderId();

                do {
                    /** @var SyncFolderItemsResponseMessageType $changes */
                    $changes = $this->api->listItemChanges($inboxId, $syncString, ['MaxChangesReturned' => $maxEntries, 'ItemShape' => array('BaseShape' => 'IdOnly')]);

                    $maxEntries = count($changes->getChanges()->getCreate());
                    $syncString = $changes->getSyncState();

                    // -- processing emails
                    $created = $changes->getChanges()->getCreate();

                    # -- we're good to go!! Applying best programming skills
                    if ($created instanceof SyncFolderItemsCreateOrUpdateType) { # -- found just one item in the folder
                        if (null != $message = $created->getMessage()) {
                            # -- if message is null is not a email item type (can be calendar meeting response, request or something)
                            $this->sendEmailToProcess($message, $processedId, $board);

                        }
                    } elseif (is_array($created)) { # -- found more than one item in the folder
                        /** @var SyncFolderItemsCreateOrUpdateType $itemReceived */
                        foreach ($created as $itemReceived) {
                            if (null == $message = $itemReceived->getMessage()) {
                                # -- if message is null is not a email item type (can be calendar meeting response, request or something)
                                continue;
                            }
                            $this->sendEmailToProcess($message, $processedId, $board);
                        }
                    }

                } while ($maxEntries > 0);
            }
        }


        $output->writeln('NTI Ticket: Synchronization completed.');

    }

    /**
     * @param MessageType $message
     * @param FolderIdType $processedId
     * @param Board $board
     * @return bool
     */
    private function sendEmailToProcess(MessageType $message, FolderIdType $processedId, Board $board)
    {
        // full email
        /** @var MessageType $item */
        $item = $this->api->getItem($message->getItemId(), ['ItemShape' => array('IncludeMimeContent' => true, 'BodyType' => BodyTypeResponseType::TEXT)]);

        $from = $item->getFrom()->getMailbox()->getEmailAddress();
        $body = $item->getBody();
        $subject = $item->getSubject();

        $email = new Email();
        $email->setFrom($from);
        $email->setBody($body);
        $email->setSubject($subject);
        $email->setMessage($item);
        $start = (new \DateTime($item->getDateTimeSent()))->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        if($start >= $this->minDate) {
            try {
                $successResponse = $this->container->get('nti_ticket.service')->newEmailReceived($email, $board);
            } catch (\Exception $exception) {
                if ($exception instanceof InvalidFormException) {
                    $this->logger->critical("NTI Tickets: Form errors.", RestResponse::getFormErrors($exception->getForm()));
                } elseif ($exception instanceof DatabaseException) {
                    $this->logger->critical("NTI Tickets: Database error:: " . $exception->getMessage());
                } elseif ($exception instanceof TicketProcessStoppedException) {
                    $process = $exception->getProcess();
                    $this->logger->critical("NTI Tickets: Process stopped by the user.", $process->getErrors());
                }

                $this->logger->critical("NTI Tickets: Unknown error:: " . $exception->getMessage());

                return false;
            }

            /**
             * Moving email to the processed folder.
             */
            try {
                if ($successResponse) {
                    $this->api->moveItem($message->getItemId(), $processedId);
                    $this->logger->debug('NTI Tickets: Email moved to processed folder.');
                }
            } catch (\Exception $exception) {
                $this->logger->critical("NTI Tickets: Error moving the email to the processed folder:: ", $exception->getMessage());
                return false;
            }
        }

        return true;
    }

}