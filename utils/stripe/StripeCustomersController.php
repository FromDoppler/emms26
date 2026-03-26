<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/stripe/StripeCustomersModel.php');
class StripeCustomersController
{
    private $model;

    public function __construct()
    {
        $this->model = new StripeCustomersModel();
    }

    public function getRegisteredByEmail($email)
    {
        try {
            $user = $this->model->getRegisteredByEmail($email);
            return $user;
        } catch (Exception $e) {
            $errorMessage = json_encode(["getRegisteredByEmail", $e->getMessage()]);
            http_response_code(500);
            throw new Exception($errorMessage);
        }
    }
}
