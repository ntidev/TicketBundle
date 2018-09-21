<?php

namespace NTI\TicketBundle\Command;

use garethp\ews\API;
use garethp\ews\API\Enumeration\BodyTypeResponseType;
use garethp\ews\API\Exception\UnauthorizedException;
use garethp\ews\API\Message\SyncFolderItemsResponseMessageType;
use garethp\ews\API\Type\DistinguishedFolderIdNameType;
use garethp\ews\API\Type\FolderIdType;
use garethp\ews\API\Type\MessageType;
use garethp\ews\API\Type\SyncFolderItemsCreateOrUpdateType;
use Monolog\Logger;
use NTI\TicketBundle\Exception\DatabaseException;
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
class SyncInboxCommand extends ContainerAwareCommand
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

    private $provider;
    private $server;
    private $account;
    private $password;

    /** @var API $api */
    private $api;

    protected function configure()
    {
        $this
            ->setName('nti:ticket:synchronize-inbox')
            ->setDescription('');
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

        // -- setting parameters
        $params = $this->container->getParameter('nti_ticket.email.client');
        $this->provider = array_key_exists('name', $params) ? $params['name'] : null;
        $this->server = array_key_exists('server', $params) ? $params['server'] : null;
        $this->account = array_key_exists('account', $params) ? $params['account'] : null;
        $this->password = array_key_exists('password', $params) ? $params['password'] : null;

        // -- checking if the server is reachable
        if (!$this->setConnection()) die;

        /**
         * Inbox directory and credentials validation.
         */
        try {
            $inboxId = $this->api->getFolderByDistinguishedId(DistinguishedFolderIdNameType::INBOX)->getFolderId();
        } catch (\Exception $exception) {
            if ($exception instanceof UnauthorizedException || $exception->getCode() == 401) {
                $this->logger->alert(self::ERROR_INVALID_CREDENTIALS);
                die;
            }
            // -- general error handler
            $this->logger->alert(self::ERROR_UNKNOWN, array('message' => $exception->getMessage()));
            die;
        }

        /**
         * Processed inbox directory
         * The processed emails will be moved to this directory.
         */
        $processedDir = $this->api->getFolderByDisplayName('processed', $inboxId);
        if (!$processedDir){
            $this->logger->error('NTI Ticket: No inbox processed directory found.');
            die;
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
                if (null != $message = $created->getMessage()) { # -- if message is null is not a email item type (can be calendar meeting response, request or something)
                    $this->sendEmailToProcess($message, $processedId);
                }
            } elseif (is_array($created)) { # -- found more than one item in the folder
                /** @var SyncFolderItemsCreateOrUpdateType $itemReceived */
                foreach ($created as $itemReceived) {
                    if (null == $message = $itemReceived->getMessage()) continue; # -- if message is null is not a email item type (can be calendar meeting response, request or something)
                    $this->sendEmailToProcess($message, $processedId);
                }
            }

        } while ($maxEntries > 0);


        $output->writeln('OK.');

    }


    /**
     * This method check if the server is reachable doing a curl request to the server uri.
     * @return bool
     */
    private function validateServer()
    {
        if (!$this->server) {
            $this->logger->alert(self::ERROR_SERVER_INVALID);
            return false;
        }

        try {

            $uri = self::URI_HTTPS . $this->server . self::URI_BODY;
            $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($statusCode == 0) {
                $this->logger->alert(self::ERROR_SERVER_INVALID);
                return false;
            }

            return true;
        } catch (\Exception $exception) {
            $this->logger->error($this::ERROR_SERVER_INVALID, array('message' => $exception->getMessage()));
            return false;
        }
    }

    private function setConnection()
    {
        # check if is a valid user
        if (!$this->account) {
            $this->logger->alert("NTI Ticket: No user account provided.");
            return false;
        }

        if (!$this->password) {
            $this->logger->alert("NTI Ticket: No user password provided.");
            return false;
        }

        if (!$this->validateServer()) {
            return false;
        }

        $this->api = API::withUsernameAndPassword($this->server, $this->account, $this->password);

        return true;

    }

    /**
     * @param MessageType $message
     * @param FolderIdType $processedId
     * @return bool
     */
    private function sendEmailToProcess(MessageType $message, FolderIdType $processedId)
    {
        // full email
        /** @var MessageType $item */
        $item = $this->api->getItem($message->getItemId(), ['ItemShape' => array('IncludeMimeContent' => true, 'BodyType'=> BodyTypeResponseType::TEXT)]);

        $from = $item->getFrom()->getMailbox()->getEmailAddress();
        $body = $item->getBody();
        $subject = $item->getSubject();

        $email = new Email();
        $email->setFrom($from);
        $email->setBody($body);
        $email->setSubject($subject);
        $email->setMessage($item);

        try{
            $this->container->get('nti_ticket.service')->newEmailReceived($email);
        }catch (\Exception $exception){
            if ($exception instanceof InvalidFormException) {
                $this->logger->critical("NTI Tickets: Form errors.", RestResponse::getFormErrors($exception->getForm()));
            }elseif ($exception instanceof DatabaseException) {
                $this->logger->critical("NTI Tickets: Database error:: ".$exception->getMessage());
            }elseif ($exception instanceof TicketProcessStoppedException) {
                $process = $exception->getProcess();
                $this->logger->critical("NTI Tickets: Process stopped by the user.", $process->getErrors());
            }

            $this->logger->critical("NTI Tickets: Unknown error:: ".$exception->getMessage());

            return false;
        }

        /**
         * Moving email to the processed folder.
         */
        try {
            $this->api->moveItem($message->getItemId(), $processedId);
            $this->logger->debug('NTI Tickets: Email moved to processed folder.');
        }catch (\Exception $exception){
            $this->logger->critical("NTI Tickets: Error moving the email to the processed folder:: ", $exception->getMessage());
            return false;
        }

        return true;
    }

}