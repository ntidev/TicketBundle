<?php

namespace NTI\TicketBundle\Service\EmailConnector;


use Exception;
use garethp\ews\API;
use garethp\ews\API\Exception\UnauthorizedException;
use NTI\TicketBundle\Entity\Board\Board;
use NTI\TicketBundle\Entity\Configuration\Configuration;
use NTI\TicketBundle\Exception\ExchangeConnectionFailedException;
use NTI\TicketBundle\Exception\ExchangeInactiveConfigurationException;
use NTI\TicketBundle\Exception\ExchangeServerInvalidException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExchangeService
{
    private $container;
    private $em;

    CONST URI_HTTPS = "https://";
    CONST URI_BODY = "/EWS/Exchange.asmx";

    private $server;
    private $account;
    private $password;
    private $active;
    /** @var API $api */
    private $api;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->loadDefaultParams();
    }

    /**
     * Load default valiues to init the exchange service connection
     */
    private function loadDefaultParams(){
        $active = $this->em->getRepository(Configuration::class)->findOneBy(array('name' => 'EXCHANGE_EMAIL_CONNECTOR'));
        $this->active =  ($active instanceof Configuration && !empty($active->getValue())) ? $active->getValue() : null;

//        /* TODO: take this value from current account board */
//        $server = $this->em->getRepository(Configuration::class)->findOneBy(array('name' => 'EXCHANGE_EMAIL_CONNECTOR_SERVER'));
//        $this->server =  ($server instanceof Configuration && !empty($server->getValue())) ? $server->getValue() : null;
//        /* TODO: take this value from current account board */
//        $account = $this->em->getRepository(Configuration::class)->findOneBy(array('name' => 'EXCHANGE_EMAIL_CONNECTOR_ACCOUNT'));
//        $this->account =  ($account instanceof Configuration && !empty($account->getValue())) ? $account->getValue() : null;
//        /* TODO: take this value from current account board */
//        $password = $this->em->getRepository(Configuration::class)->findOneBy(array('name' => 'EXCHANGE_EMAIL_CONNECTOR_PASSWORD'));
//        $this->password =  ($password instanceof Configuration && !empty($password->getValue())) ? $password->getValue() : null;
    }

    /**
     * @param $server
     * @param $account
     * @param $password
     */
    public function setConnection($server, $account, $password){
        $this->api = API::withUsernameAndPassword($server, $account, $password);
    }

    public function getConnection(){
        return $this->api;
    }


    /**
     * This method check if the server is reachable doing a curl request to the server uri.
     * @param string|null $server
     * @return bool
     * @throws ExchangeServerInvalidException
     */
    public function validateServer(string $server = null)
    {
        if (!$server)
            throw new ExchangeServerInvalidException("Exchange server not configured.");

        try {

            $uri = self::URI_HTTPS . $server . self::URI_BODY;
            $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($statusCode == 0) {
                throw new ExchangeServerInvalidException("Exchange server provided is not reachable.");
            }

            return true;

        } catch (\Exception $exception) {
            throw new ExchangeServerInvalidException("Exchange server could not be validated.");
        }
    }

    /**
     * @param Board $board
     * @return bool
     * @throws ExchangeConnectionFailedException
     * @throws ExchangeInactiveConfigurationException
     * @throws ExchangeServerInvalidException
     */
    public function testConnection(Board $board){
        // -- get parameters from the database
      //  $this->setParams($board);
        $server = $board->getConnectorServer();
        $account = $board->getConnectorAccount();
        $password = $board->getconnectorPassword();

        if (!$board->getIsActive())
            throw new ExchangeInactiveConfigurationException("The exchange configuration is disabled or this board status is inactivated.");

        // -- validate if the server is reachable
        if ($this->validateServer($server)){
            if (!$account)
                throw new ExchangeConnectionFailedException("Exchange account is required.");

            if (!$password)
                throw new ExchangeServerInvalidException("Exchange password is required.");

            // -- create EWS client
            $this->setConnection($server, $account, $password);

            // -- simple request to validate credentials
            try {
                $this->api->getServerTimezones(array('Eastern Standard Time'), true);
                return true;
            } catch (Exception $e) {
                if ( $e instanceof UnauthorizedException || $e->getCode() == 401) {
                   throw new ExchangeConnectionFailedException("Exchange connection failed, invalid account or password. ");
                }
                throw new ExchangeConnectionFailedException("Exchange connection failed, {$e->getMessage()} ");
            }

        }

    }





}