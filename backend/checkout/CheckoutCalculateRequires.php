<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/services/getCurrentEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/DB.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/Logger.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/pricing/CheckoutPricingService.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/pricing/coupons/CheckoutCouponCode.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/application/CheckoutRequestNormalizer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/pricing/coupons/CheckoutCouponService.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/services/CheckoutEventContextResolver.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/infrastructure/repositories/CheckoutTicketsRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/infrastructure/repositories/CheckoutCouponsRepository.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/application/calculate/CalculateCheckoutUseCase.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/post-checkout/RegisteredProfileRepository.php');
