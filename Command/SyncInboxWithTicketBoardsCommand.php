<?php

namespace NTI\TicketBundle\Command;

use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\ORM\EntityManager;
use garethp\ews\API;
use garethp\ews\API\Enumeration\BodyTypeResponseType;
use garethp\ews\API\Message\SyncFolderItemsResponseMessageType;
use garethp\ews\API\Type\DistinguishedFolderIdNameType;
use garethp\ews\API\Type\FolderIdType;
use garethp\ews\API\Type\MessageType;
use garethp\ews\API\Type\SyncFolderItemsCreateOrUpdateType;
use Monolog\Logger;
use NTI\TicketBundle\Entity\Configuration\Configuration;
use NTI\TicketBundle\Entity\Board\Board;
use NTI\TicketBundle\Entity\Ticket\Document;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use NTI\TicketBundle\Entity\Ticket\TicketResource;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\ExchangeConnectionFailedException;
use NTI\TicketBundle\Exception\ExchangeInactiveConfigurationException;
use NTI\TicketBundle\Exception\ExchangeServerInvalidException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Exception\TicketProcessStoppedException;
use NTI\TicketBundle\Model\Email;
use NTI\TicketBundle\Util\Rest\RestResponse;
use NTI\TicketBundle\Util\Utilities;
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

    private $blackListEmail;

    /** @var OutputInterface $output */
    private $output;

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
        $this->output = $output;

        $this->MIN_DATE = $this->getContainer()->getParameter('app.date.sync.inbox');
        $this->minDate = (new \DateTime('00:00:00'))->modify('-'. $this->MIN_DATE . ' day');

        # entity manager service.
        $this->em = $this->getContainer()->get('doctrine')->getManager();

        /**@var Configuration $config */
        $config = $this->em->getRepository(Configuration::class)->findOneBy(["name" => "EXCHANGE_EMAIL_CAN_NOT_BE_PROCESSED"]);
        $this->blackListEmail = $config->getValue();

        # getting active boards with exchange credentials set from the db
        /**@var Board $board */
        $boards = $this->em->getRepository(Board::class)->findAll();

        foreach ($boards as $board) {
            if ($board->getConnectorServer() != null && $board->getConnectorAccount() != null && $board->getConnectorPassword() && $board->getIsActive()) {
                /**
                 * parameters validation and connection test.
                 */
                try {
                    $this->container->get('nti_ticket.connector.exchange.service')->testConnection($board);
                } catch (\Exception $exception) {
                    if ($exception instanceof ExchangeServerInvalidException) {
                        $this->logger->alert("NTI Ticket: {$exception->getMessage()}" . $board->getConnectorAccount());
                    } elseif ($exception instanceof ExchangeConnectionFailedException) {
                        $this->logger->alert("NTI Ticket: {$exception->getMessage()}" . $board->getConnectorAccount());
                    } elseif ($exception instanceof ExchangeInactiveConfigurationException) {
                        $this->logger->alert("NTI Ticket: {$exception->getMessage()}" . $board->getConnectorAccount());
                    } else {
                        $this->logger->alert(self::ERROR_UNKNOWN, array('message' => $exception->getMessage()));
                    }
                    continue; // Remove this die to allow that command continues with others board
                }

                $this->api = $this->container->get('nti_ticket.connector.exchange.service')->getConnection();
                $this->logger->alert("NTI Ticket: {$board->getConnectorAccount()}");

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
                $notProcessedDir = $this->api->getFolderByDisplayName('not_processed', $inboxId);
                if (!$processedDir || !$notProcessedDir) {
                    $this->logger->error('NTI Ticket: No inbox processed or no_processed directory found. ' . $board->getConnectorAccount());
                    continue;
                }


                /**
                 * Getting the list of emails in the inbox directory. pagination
                 */
                $maxEntries = 500;
                $syncString = null;
                $processedId = $processedDir->getFolderId();
                $notProcessedId = $notProcessedDir->getFolderId();

                do {
                    /** @var SyncFolderItemsResponseMessageType $changes */
                    $changes = $this->api->listItemChanges($inboxId, $syncString, ['MaxChangesReturned' => $maxEntries, 'ItemShape' => array('BaseShape' => 'IdOnly')]);

                    $maxEntries = count($changes->getChanges()->getCreate());
                    $syncString = $changes->getSyncState();

                    // -- processing emails
                    $created = $changes->getChanges()->getCreate();

                    # -- we're good to go!! Applying best programming skills
                    if ($created instanceof SyncFolderItemsCreateOrUpdateType) { # -- found just one item in the folder
                        # -- if message is null is not a email item type (can be calendar meeting response, request or something)
                        if (null != $message = $created->getMessage()) {
                            $this->sendEmailToProcess($message, $processedId, $notProcessedId, $board);
                        }
                    } elseif (is_array($created)) { # -- found more than one item in the folder
                        /** @var SyncFolderItemsCreateOrUpdateType $itemReceived */
                        foreach ($created as $itemReceived) {
                            if (null == $message = $itemReceived->getMessage()) {
                                # -- if message is null is not a email item type (can be calendar meeting response, request or something)
                                continue;
                            }
                            $this->sendEmailToProcess($message, $processedId, $notProcessedId, $board);
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
     * @param FolderIdType $notProcessedId
     * @param Board $board
     * @return bool
     */
    private function sendEmailToProcess(MessageType $message, FolderIdType $processedId, FolderIdType $notProcessedId, Board $board)
    {
        /** @var MessageType $item */
        $item = $this->api->getItem($message->getItemId(), ['ItemShape' => array('IncludeMimeContent' => true, 'BodyType' => BodyTypeResponseType::TEXT)]);
        $start = (new \DateTime($item->getDateTimeSent()))->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $from = $item->getFrom()->getMailbox()->getEmailAddress();

        if($start >= $this->minDate && $this->canAccountEmailBeProcessed($board, $from)) {
            try {
                // full email
                $body = $item->getBody();
                $subject = $item->getSubject();

                $email = new Email();
                $email->setFrom($from);
                $email->setBody($body);
                $email->setSubject($subject);
                $email->setMessage($item);

                // Create Ticket and Send Notification
                $ticket = $this->container->get('nti_ticket.service')->newEmailReceived($email, $board);
                $this->uploadDocument($ticket, $item, $start);
            } catch (\Exception $exception) {
                dump($exception->getMessage());die;

                if ($exception instanceof InvalidFormException) {
                    $this->logger->critical("NTI Tickets: Form errors.", RestResponse::getFormErrors($exception->getForm()));
                } elseif ($exception instanceof DatabaseException) {
                    $this->logger->critical("NTI Tickets: Database error:: " . $exception->getMessage());
                } elseif ($exception instanceof TicketProcessStoppedException) {
                    $process = $exception->getProcess();
                    $this->logger->critical("NTI Tickets: Process stopped by the user.", $process->getErrors());
                }

                /**
                 * Moving email to the no_processed folder.
                 */
                try {
                    $this->api->moveItem($message->getItemId(), $notProcessedId);
                    $this->logger->debug('NTI Tickets: Email moved to no_processed folder. Reason: ' . json_encode($exception));
                } catch (\Exception $ex) {
                    $this->logger->critical("NTI Tickets: Error moving the email to the no_processed folder:: ", $ex->getMessage());
                    return false;
                }

                $this->logger->critical("NTI Tickets: Unknown error:: " . $exception->getMessage());

                return false;
            }

            /**
             * Moving email to the processed folder.
             */
            try {
                if($ticket instanceof Ticket) {
                    $this->api->moveItem($message->getItemId(), $processedId);
                    $this->logger->debug('NTI Tickets: Email moved to processed folder.');
                }
                else {
                    $this->api->moveItem($message->getItemId(), $notProcessedId);
                    $this->logger->critical('NTI Tickets: Email moved to no_processed folder.');
                }
            } catch (\Exception $exception) {
                $this->logger->critical("NTI Tickets: Error moving the email to the processed folder:: ", $exception->getMessage());
                return false;
            }

            /**
             * Create Document
             */
            if($ticket instanceof Ticket) {

            }
        }

        return true;
    }

    public function uploadDocument(Ticket $ticket, $item, \DateTime $date) {
        # -- dependencies
        $validFormats = Document::ALLOWED_FORMATS;
        $directory = $this->container->getParameter('nti_ticket.documents.dir');
        $path = $directory . "/" . $ticket->getId();

        # -- Get posted information
        $name = $date->format('Y-m-d') . "_" . ($item->getSubject() != null ? Utilities::normalizeString($item->getSubject()) : "No_Subject_Found") . ".eml";;
        $fileName = $path . '/' . $name;

        # -- filename config
        $hash = sha1("1" . time() . $fileName);

        // Prepare upload folder
        if (!file_exists($path)) {
            if (!mkdir($path, 0777, true)) {
                $this->logger->critical("Unable to create or write in the upload directory provided.");
                throw new \Exception("Unable to create or write in the upload directory provided.");
            }
        }

        // Write the content to the file (replace if exists)
        if (file_put_contents($fileName, base64_decode($item->getMimeContent()->_)) === false) { # -- could not create the file.
            $this->logger->critical("The EML file could not be created.");
            throw new \Exception("The EML file could not be created.");
            $this->output->writeln('The EML file could not be created');
            return false;
        }

        $size = filesize($path);

        try {
            # -- adding document to the ticket
            $document = new Document();
            $document->setTicket($ticket);
            $document->setDirectory($ticket->getId());
            $document->setFileName($name);
            $document->setFormat("EML");
            $document->setHash($hash);
            $document->setName($name);
            $document->setPath($path);
            $document->setType("message/rfc822");
            $document->setSize($size);
            $document->setUploadDate(new \DateTime());
            $document->setResource(TicketResource::EMAIL_RESOURCE);

            $this->em->persist($document);
            $this->em->flush();
            return $document;
        } catch (NotNullConstraintViolationException $exception) {
            $this->rmdir_recursive($path, $output);
            $this->logger->critical("NTI Tickets: An error has occurred creating the document." . $exception->getMessage());
            throw new \Exception("NTI Tickets: An error has occurred creating the document." . $exception->getMessage());
        } catch (Exception $exception) {
            $this->rmdir_recursive($path, $output);
            $this->logger->critical("NTI Tickets: An error has occurred creating the document." . $exception->getMessage());
            throw new \Exception("NTI Tickets: An error has occurred creating the document." . $exception->getMessage());
        }
    }

    public function rmdir_recursive($path)
    {
        foreach (scandir($path) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (is_dir("$path/$file")) $this->rmdir_recursive("$path/$file");
            else unlink("$path/$file");
        }
        if (rmdir($path)) {
            $this->output->writeln("The directory was removed.");
        } else {
            $this->output->writeln("The directory couldn't be removed.");
        };
    }

    public function getTicketNumberFromEmailSubject($subject) {
        $subject = $subject;
        $startStr = strpos($subject, '[#');
        $string = substr($subject,$startStr+2, strlen($subject));
        return strtok($string, ']');
    }

    /**
     * @description Check if the email to be processed is a board account, if true the return false to not be processed
     * @example if receives email is email@unitex.com and in the board list [email@unitex.com] exists that email
     * @param Board $board
     * @param $email
     * @return boolean
     */
    public function canAccountEmailBeProcessed(Board $board, $email) {
        # entity manager service.
//        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $boardAccount = $board->getConnectorAccount();

        // Set emails string to array
        $email_arr = preg_split ("/\,/", $this->blackListEmail);
        $isEmailForbidden = false;
        // Check if is an array type
        if(is_array($email_arr)) {
            $isEmailForbidden = in_array($email, $email_arr);
        }

        return $boardAccount != $email && !$isEmailForbidden;
    }

}