<?php

namespace NTI\TicketBundle\Util\Rest;


use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class RestResponse extends JsonResponse {

    public function __construct($data, $status = 200, $message = "", $errors = null, $redirect = null, $headers = array())
    {
        // Prepare form errors if provided
        if($errors instanceof Form) {
            $errors = self::getFormErrors($errors);
        }

        $structure = array(
            "has_error" => $this->isError($status),
            "additional_errors" => $errors,
            "code" => $status,
            "message" => ($message != "") ? $message : null,
            "data" => $data,
            "redirect" => ($redirect != "") ? $redirect : null,
        );
        parent::__construct($structure, $status, $headers);
    }

    public static function getFormErrors(FormInterface $form) {

        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = self::getFormErrors($childForm)) {
//                    $errors[$childForm->getName()] = $childErrors;
                    foreach($childErrors as $childError) {
                        $errors[] =  $childError;
                    }
                }
            }
        }
        return $errors;
    }

    private function isError($status) {
        if($status >= 200 && $status <= 299) { return false; }
        if($status >= 300 && $status <= 399) { return false; }
        if($status >= 400 && $status <= 499) { return true; }
        if($status >= 500 && $status <= 599) { return true; }
        return true;
    }
}