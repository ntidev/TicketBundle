<?php

namespace NTI\TicketBundle\Command;

use garethp\ews\API;
use garethp\ews\API\Enumeration\DistinguishedFolderIdNameType;
use garethp\ews\API\Type;
use garethp\ews\API\Type\FolderType;
use garethp\ews\API\Type\MessageType;
use Monolog\Logger;
use NTI\TicketBundle\Exception\ExchangeConnectionFailedException;
use NTI\TicketBundle\Exception\ExchangeInactiveConfigurationException;
use NTI\TicketBundle\Exception\ExchangeServerInvalidException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteProcessedEmailCommand extends ContainerAwareCommand
{

    CONST ERROR_SERVER_INVALID = "NTI Ticket: The server name specified can not be reached.";
    CONST ERROR_INVALID_CREDENTIALS = "NTI Ticket: Invalid credentials.";
    CONST ERROR_UNKNOWN = "NTI Ticket: Unknown error.";

    private $container;

    /** @var  Logger $logger */
    private $logger;

    /** @var API $api */
    private $api;

    protected function configure()
    {
        $this
            ->setName('nti:ticket:delete-processed-emails')
            ->setDescription('Delete emails older than specific number of days.')
            ->addArgument('numberOfDays', InputArgument::REQUIRED, 'Delete emails older than x days.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        # -- assigning global variable scope
        $this->container = $this->getContainer();
        $this->logger = $this->container->get('logger');


        /**
         * parameters validation and connection test.
         */
        try{
            $this->container->get('nti_ticket.connector.exchange.service')->testConnection();
        } catch (\Exception $exception){
            if ($exception instanceof ExchangeServerInvalidException){
                $this->logger->alert("NTI Ticket: {$exception->getMessage()}");
            }elseif ($exception instanceof ExchangeConnectionFailedException){
                $this->logger->alert("NTI Ticket: {$exception->getMessage()}");
            }elseif ($exception instanceof ExchangeInactiveConfigurationException){
                $this->logger->alert("NTI Ticket: {$exception->getMessage()}");
            }else {
                $this->logger->alert(self::ERROR_UNKNOWN, array('message' => $exception->getMessage()));
            }
            die;
        }

        $this->api = $this->container->get('nti_ticket.connector.exchange.service')->getConnection();

        /**
         * Inbox directory
         */
        try {
            $inboxId = $this->api->getFolderByDistinguishedId(DistinguishedFolderIdNameType::INBOX)->getFolderId();
        } catch (\Exception $exception) {
            // -- general error handler
            $this->logger->alert(self::ERROR_UNKNOWN, array('message' => $exception->getMessage()));
            die;
        }

        /**
         * Processed inbox directory
         * The processed emails are in this directory.
         * @var FolderType $processedDir
         */
        $processedDir = $this->api->getFolderByDisplayName('processed', $inboxId);
        if (!$processedDir){
            $this->logger->error('NTI Ticket: No inbox processed directory found.');
            die;
        }

        $days =  $input->getArgument('numberOfDays');
        $this->logger->notice("Emails older than {$days} days will be deleted.");
        $this->logger->notice("Processing...");

        $start = (new \DateTime('23:59:59'))->modify("-{$days} day");
        $this->logger->notice("Processing...", array('start' => $start));

        /**
         * Building exchange query
         */
        $request = array(
            'Traversal' => 'Shallow',
            'ItemShape' => array(
                'BaseShape' => 'AllProperties'
            ),
            'SortOrder' => array(
                'FieldOrder' => array(
                    'order' => 'Descending',
                    'fieldURI' => [
                        'FieldURI' => 'item:DateTimeReceived',
                    ]
                )
            ),
            'Restriction' => array(
                'IsLessThanOrEqualTo' => array(
                    'fieldURI' => [
                        'FieldURI' => 'item:DateTimeReceived',
                    ],
                    'FieldURIOrConstant' => [
                        'Constant' => [
                            'Value' => $start->format('c')
                        ]
                    ]
                )
            ),
            'IndexedPageItemView' => array(
                'BasePoint' => "End",
                "Offset" => 0,
            ),
            'ParentFolderIds' => array(
                'FolderId' => $processedDir->getFolderId()->toXmlObject()
            )
        );
        $request = Type::buildFromArray($request);
        /** @var Type\FindItemParentType $response */
        $response = $this->api->getClient()->FindItem($request);
        /** @var Type\ArrayOfRealItemsType $resObjects */
        $resObjects = $response->getItems();
        $items = $resObjects->getItems();


        $toDelete = array();
        if (is_array($items) == false && $items instanceof MessageType) {
            array_push($toDelete, $items->getItemId());
        }elseif (is_array($items) == true) { # handling multiple calendar items
            /** @var MessageType $item */
            foreach ($items as $item) {
                array_push($toDelete, $item->getItemId());
            }
        }

        if (count($toDelete) > 0){
            try {
                $this->api->deleteItems($toDelete);
                $this->logger->notice("NTI Ticket:: Completed:: Emails Deleted.");
                die;
            }catch (\Exception $exception){
                $this->logger->alert("NTI Ticket:: Error deleting the emails.", array('error' => $exception->getMessage()));
                die;
            }
        }

        $this->logger->notice("NTI Ticket:: Completed:: No emails to deleted.");

    }



}