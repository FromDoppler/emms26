<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/CheckoutCalculateRequires.php');

class CheckoutModule
{
    public static function createCalculateCheckoutService(): CalculateCheckoutUseCase
    {
        $db = self::newDb();

        return new CalculateCheckoutUseCase(
            self::createEventContextResolver(),
            self::createPricingService($db),
            new RegisteredProfileRepository($db)
        );
    }

    public static function createCreateCheckoutService(): CreateCheckoutUseCase
    {
        $db = self::newDb();
        $transactions = new CheckoutTransactionsRepository($db);
        $pricingService = self::createPricingService($db);
        $providerClient = new DopplerPaymentsApiClient(new SuperUserJwtService());
        $eligibilityService = new CheckoutEligibilityService(new RegisteredProfileRepository($db));
        $jobCreator = new UserEventJobCreator(new UserEventJobsRepository($db));
        $handlerRegistry = new UserEventJobHandlerRegistry([
            new EmailSendJobHandler(),
            new SpreadsheetSaveJobHandler(),
            new DopplerListAddJobHandler(),
        ]);
        $inlineRunner = new InlineUserEventJobRunner(new UserEventJobsRepository(self::newDb()), $handlerRegistry);
        $responseFactory = new CheckoutResponseFactory();
        $idempotencyResolver = new CheckoutIdempotencyResolver($transactions, $responseFactory);
        $transitionHandler = new CheckoutTransactionTransitionHandler($transactions, $responseFactory);
        $paymentProcessor = new CheckoutPaymentProcessor(
            $db,
            $pricingService,
            $providerClient,
            new PostCheckoutService(
                $transactions,
                new RegisteredProfileRepository($db),
                new VipAccessService($db),
                $jobCreator,
                $inlineRunner,
                new PostCheckoutUserEventsFactory()
            ),
            $transitionHandler,
            $responseFactory
        );
        $failureHandler = new CheckoutFailureHandler($db, function (): DB {
            return CheckoutModule::newDb();
        }, $responseFactory);
        $eventContextResolver = self::createEventContextResolver();

        return new CreateCheckoutUseCase(
            $pricingService,
            $transactions,
            $eligibilityService,
            $eventContextResolver,
            $idempotencyResolver,
            $paymentProcessor,
            $failureHandler
        );
    }

    public static function createGetCheckoutService(): GetCheckoutUseCase
    {
        return new GetCheckoutUseCase(
            new CheckoutTransactionsRepository(self::newDb())
        );
    }

    public static function createEventContextResolver(): CheckoutEventContextResolver
    {
        return new CheckoutEventContextResolver();
    }

    private static function createPricingService(DB $db): CheckoutPricingService
    {
        $coupons = new CheckoutCouponsRepository($db);
        $couponService = new CheckoutCouponService($coupons);
        $tickets = new CheckoutTicketsRepository($db);

        return new CheckoutPricingService($tickets, $couponService);
    }

    private static function newDb(): DB
    {
        return new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    }
}
