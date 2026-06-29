<?php

interface PaymentProviderClient
{
    public function purchase(ProviderPaymentRequest $request): ProviderPaymentResult;
}
