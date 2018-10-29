<?php

namespace NTI\TicketBundle\Controller\Configuration;

use NTI\TicketBundle\Entity\Configuration\Configuration;
use NTI\TicketBundle\Exception\ExchangeConnectionFailedException;
use NTI\TicketBundle\Exception\ExchangeInactiveConfigurationException;
use NTI\TicketBundle\Exception\ExchangeServerInvalidException;
use NTI\TicketBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConfigurationController
 * @package NTI\TicketBundle\Controller\Configuration
 * @Route("/configurations")
 */
class ConfigurationController extends Controller
{

    /**
     * @Route("/nextNo", name="nti_tickets_config_next_number", methods={"GET"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function getNextTicketNumber(Request $request){
        /** @var Configuration $event */
        $event = $this->getDoctrine()->getRepository(Configuration::class)->findOneBy(array('name' => 'NEXT_TICKET_NUMBER'));
        $eventNo = $this->get('nti_ticket.service')->getNextTicketNumber();
        $event->setValue($eventNo);
        $serializer = $this->get('jms_serializer');
        $event = json_decode($serializer->serialize($event, 'json'), true);
        return new JsonResponse($event);
    }

    /**
     * @Route("/events", name="nti_tickets_config_events", methods={"GET"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function getEvents(Request $request){
        $events = $this->getDoctrine()->getRepository(Configuration::class)->findBy(array('section' => 'NOTIFICATION'));
        $serializer = $this->get('jms_serializer');
        $events = json_decode($serializer->serialize($events, 'json'), true);
        return new JsonResponse($events);
    }

    /**
     * @Route("/events", name="nti_tickets_config_events_save", methods={"PUT"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function saveEvents(Request $request){
        $data = json_decode($request->getContent(), true);
        foreach ($data as $changes){
            if (array_key_exists('name', $changes) && array_key_exists('value', $changes)){
                $name = $changes['name'];
                $value = $changes['value'];
                $config = $this->getDoctrine()->getRepository(Configuration::class)->findOneBy(array('name' => $name));
                // -- handling next ticket number
                if ($config->getName() == 'NEXT_TICKET_NUMBER'){
                    $eventNo = $this->get('nti_ticket.service')->getNextTicketNumber();
                    if ((int)$value < $eventNo)
                        return new RestResponse(null, 400, "Next number provided is not valid. Should be higher thant {$eventNo}.");

                    $config->setValue((int)$value);
                } elseif ($config->getSection() == 'NOTIFICATION'){
                    if ($value) {
                        $config->setValue('true');
                    }else{
                        $config->setValue('false');
                    }
                }
            }

        }

        try{
            $this->getDoctrine()->getManager()->flush();
            return new RestResponse(null, 200, "Configuration changes successfully saved.");
        }catch (\Exception $exception){
            return new RestResponse(null, 500, "An unknown error occurred saving the configurations, please refresh and try again.");
        }

    }

    /**
     * @Route("/email-connector/exchange", name="nti_tickets_config_email_connector_exchange", methods={"GET"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function getExchangeEmailConfig(Request $request){
        $data = $this->getDoctrine()->getRepository(Configuration::class)->findBy(array('section' => 'EXCHANGE_EMAIL_CONNECTOR'));
        $serializer = $this->get('jms_serializer');
        $data = json_decode($serializer->serialize($data, 'json'), true);
        return new JsonResponse($data);
    }


    /**
     * @Route("/email-connector/exchange", name="nti_tickets_config_save_email_connector_exchange", methods={"PUT"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function saveExchangeConfig(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $current = $this->getDoctrine()->getRepository(Configuration::class)->findBy(array('section' => 'EXCHANGE_EMAIL_CONNECTOR'));

        /** @var Configuration $emailConfig */
        foreach ($current as $emailConfig) {
            $found = false;
            foreach ($data as $changes) {
                if (array_key_exists('name', $changes) && array_key_exists('value', $changes)) {
                    if ($changes['name'] == $emailConfig->getName()) {

                        if ($emailConfig->getName() == 'EXCHANGE_EMAIL_CONNECTOR') {
                            $found = true;
                            if ($changes['value']) {
                                $emailConfig->setValue('true');
                            } else {
                                $emailConfig->setValue('false');
                            }
                        } elseif ('' != $value = trim($changes['value'])) {
                            $found = true;
                            $emailConfig->setValue($value);
                        }
                    }
                }
            }

            if (!$found) {
                $name = array(
                    'EXCHANGE_EMAIL_CONNECTOR_SERVER' => 'server',
                    'EXCHANGE_EMAIL_CONNECTOR_ACCOUNT' => 'account',
                    'EXCHANGE_EMAIL_CONNECTOR_PASSWORD' => 'password',
                    'EXCHANGE_EMAIL_CONNECTOR' => 'active',
                );
                return new RestResponse(null, 400, "The {$name[$emailConfig->getName()]} field is required.");
            }
        }

        try{
            $this->getDoctrine()->getManager()->flush();
        }catch (\Exception $exception){
            return new RestResponse(null, 500, "An unknown error occurred saving the configurations, please refresh and try again.");
        }

        /**
         * parameters validation and connection test.
         */
        try{
            $this->container->get('nti_ticket.connector.exchange.service')->testConnection();
        } catch (\Exception $exception){
            if ($exception instanceof ExchangeServerInvalidException){
                return new RestResponse(null, 400, $exception->getMessage());
            }elseif ($exception instanceof ExchangeConnectionFailedException){
                return new RestResponse(null, 400, $exception->getMessage());
            }elseif ($exception instanceof ExchangeInactiveConfigurationException){
                return new RestResponse(null, 400, $exception->getMessage());
            }
            return new RestResponse(null, 500, "An error ocurred trying to connect with the server.");
        }

        return new RestResponse(null, 200, "Configuration changes successfully saved.");

    }

}