<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/getCurrentEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/EmailService.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/GeoIp.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/repositories/UserEventJobsRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/UserEventJobCreator.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/interfaces/UserEventJobHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/handlers/EmailSendJobHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/handlers/SpreadsheetSaveJobHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/handlers/DopplerListAddJobHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/UserEventJobHandlerRegistry.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/user-events/InlineUserEventJobRunner.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/domain/CheckoutProviderRejectionCatalog.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/infrastructure/payments/ProviderPaymentRequest.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/infrastructure/payments/ProviderPaymentResult.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/infrastructure/payments/SuperUserJwtService.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/infrastructure/payments/PaymentProviderClient.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/infrastructure/payments/DopplerPaymentsApiClient.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/pricing/CheckoutPricingService.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/pricing/coupons/CheckoutCouponCode.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/application/CheckoutRequestNormalizer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/pricing/coupons/CheckoutCouponService.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/services/CheckoutEligibilityService.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/services/CheckoutEventContextResolver.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/domain/CheckoutTransactionStatus.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/infrastructure/repositories/CheckoutTransactionsRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/infrastructure/repositories/CheckoutTicketsRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/infrastructure/repositories/CheckoutCouponsRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/application/calculate/CalculateCheckoutUseCase.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/application/create/CreateCheckoutUseCase.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/application/get/GetCheckoutUseCase.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/application/create/CheckoutFailureHandler.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/application/create/CheckoutResponseFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/application/create/CheckoutPaymentProcessor.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/post-checkout/RegisteredProfileRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/post-checkout/VipAccessService.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/post-checkout/PostCheckoutUserEventsFactory.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/post-checkout/PostCheckoutService.php');
