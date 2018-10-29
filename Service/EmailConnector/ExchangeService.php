<?php

namespace NTI\TicketBundle\Service\EmailConnector;


use Exception;
use garethp\ews\API;
use garethp\ews\API\Exception\UnauthorizedException;
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
        $this->setParams();
    }


    private function setParams(){
        $active = $this->em->getRepository(Configuration::class)->findOneBy(array('name' => 'EXCHANGE_EMAIL_CONNECTOR'));
        $this->active =  ($active instanceof Configuration && !empty($active->getValue())) ? $active->getValue() : null;

        $server = $this->em->getRepository(Configuration::class)->findOneBy(array('name' => 'EXCHANGE_EMAIL_CONNECTOR_SERVER'));
        $this->server =  ($server instanceof Configuration && !empty($server->getValue())) ? $server->getValue() : null;

        $account = $this->em->getRepository(Configuration::class)->findOneBy(array('name' => 'EXCHANGE_EMAIL_CONNECTOR_ACCOUNT'));
        $this->account =  ($account instanceof Configuration && !empty($account->getValue())) ? $account->getValue() : null;

        $password = $this->em->getRepository(Configuration::class)->findOneBy(array('name' => 'EXCHANGE_EMAIL_CONNECTOR_PASSWORD'));
        $this->password =  ($password instanceof Configuration && !empty($password->getValue())) ? $password->getValue() : null;
    }

    public function setConnection(){
        $this->api = API::withUsernameAndPassword($this->server, $this->account, $this->password);
    }

    public function getConnection(){
        return $this->api;
    }


    /**
     * This method check if the server is reachable doing a curl request to the server uri.
     * @return bool
     * @throws ExchangeServerInvalidException
     */
    public function validateServer()
    {
        if (!$this->server)
            throw new ExchangeServerInvalidException("Exchange server not configured.");

        try {

            $uri = self::URI_HTTPS . $this->server . self::URI_BODY;
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
     * @return bool
     * @throws ExchangeConnectionFailedException
     * @throws ExchangeInactiveConfigurationException
     * @throws ExchangeServerInvalidException
     */
    public function testConnection(){
        // -- get parameters from the database
        $this->setParams();

        if (!$this->active || $this->active == 'false')
            throw new ExchangeInactiveConfigurationException("The exchange configuration is disabled.");

        // -- validate if the server is reachable
        if ($this->validateServer()){
            if (!$this->account)
                throw new ExchangeConnectionFailedException("Exchange account is required.");

            if (!$this->password)
                throw new ExchangeServerInvalidException("Exchange password is required.");

            // -- create EWS client
            $this->setConnection();

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